<?php
/**
 * 退会
 */
namespace Rest\Controller;
class WithdrawalController extends CommonController {
    protected $action_logs = array('index');
    protected $_user = [];
    protected $_shop = [];
    protected $msg  = '操作成功';
    protected $_map = [];
    
    public function _initialize() {
        parent::_initialize();
        $this->_request_check();
        //必传参数检查
        $this->need_param=array('erp_uid','sign');
        $this->_need_param();
        $this->_check_sign();
        $erp_uid = I('post.erp_uid');
        if ($erp_uid) {
            $this->_user = M('user')->where(['erp_uid' => $erp_uid])->find();
            if ($this->_user == false) $this->apiReturn(0, '', 1, '会员不存在');
            $this->_shop = M('shop')->where(['uid' => $this->_user['id']])->find();
        }
    }
    
    public function index() {
        $data = $this->checkData();
        $isClose = true;
        foreach ($data as $v) {
            foreach ($v as $val) {
                if (!empty($val)) {
                    $isClose = false;
                    break;
                }
            }
        }
        if ($isClose == false) $this->apiReturn(0, ['data' => $data], 1, $this->msg);
        
        $buyer = $this->closeOrders();  //关闭买家订单
        if ($buyer['code'] != 1) {
            $this->apiReturn(0, '', 1, $buyer['msg']);
        }
        if ($this->_shop) {
            $seller = $this->closeOrders(2);    //关闭卖家订单
            if ($buyer['code'] != 1) {
                $this->apiReturn(0, '', 1, $seller['msg']);
            }
        }
        
        $this->apiReturn(1, '', 1, $this->msg);
    }
    
    /**
     * 数据
     * @return Ambigous <NULL, unknown>
     */
    private function checkData() {
        $data = [];
        if ($this->_shop) { //如果有店铺信息
            $this->_map['seller_id']   =    $this->_user['id'];
            $this->_map['shop_id']     =    $this->_shop['id'];
            $data['seller']['orders']  =    $this->checkOrders();
            $data['seller']['refund']  =    $this->checkRefund();
            $data['seller']['service'] =    $this->checkService();
        }
        $this->_map = [];
        $this->_map['uid']          =   $this->_user['id'];
        $data['buyer']['orders']    =   $this->checkOrders();
        $data['buyer']['refund']    =   $this->checkRefund();
        $data['buyer']['service']   =   $this->checkService();
        return $data;
    }
    
    /**
     * 返回开店信息
     */
    public function checkShop() {
        //['开店审核中', '店铺正常', '2店铺已关闭' '用户未开店','3店铺注销']
        if (!in_array($this->_shop['status'], [2,3]) && $this->_shop) {
            $this->apiReturn(1, '', 1, '店铺正常');
        }
        $data = M('zhaoshang_join')->where(['uid' => $this->_user['id'], 'status' => ['in', '0,1,2,3,4']])->field('uid,id,status,step')->find();
        if ($data) {
            $zhaoshangStatus = ['提交资料中', '等待审核', '被拒绝', '审核通过待寄合同', '未收到合同', '开店成功'];
            $data['statusName'] = $zhaoshangStatus[$data['status']];
            $this->apiReturn(5, '', 1, '用户正在开店中');
        }
        if ($this->_shop['status'] == 2) {
            $this->apiReturn(6, '', 1, '店铺已关闭');
        } elseif ($this->_shop['status'] == 3) {
            $this->apiReturn(6, '', 1, '店铺已注销');
        }
        $this->apiReturn(0, '', 1, '用户未开店');
    }

    /**
     * 关闭招商
     */
    public function closeJoin() {
        $flag = M('zhaoshang_join')->where(['uid' => $this->_user['id']])->save(['status', 10]);
        if ($flag) $this->apiReturn(1);
        $this->apiReturn(0);
    }

    //关闭店铺
    public function closeShop() {
        if ($this->_shop) {
            $model = M('shop');
            $model->startTrans();
            $flag = $model->where(['id' => $this->_shop['id']])->save(['status' => 2, 'etime' => date('Y-m-d H:i:s', NOW_TIME)]);
            if (!$flag) {
                $model->rollback();
                $this->apiReturn(0, '', 1, '关闭店铺失败');
            }
            $flag = M('zhaoshang_join')->where(['uid' => $this->_user['id']])->save(['status' => 10]);//锁定审核资料
            if (!$flag) {
                $model->rollback();
                $this->apiReturn(0, '', 1, '锁定审核资料失败');
            }
            $model->commit();
            $this->apiReturn(1, '', 1, '关闭店铺成功');
        }
        $this->apiReturn(1, '', 1, '用户未开店');
    }

    public function index1() {
        $this->_request_check();
        //必传参数检查
        $this->need_param=array('openid','erp_uid','sign');
        $this->_need_param();
        $this->_check_sign();
        $erp_uid = I('post.erp_uid');
        $mag = '操作失败';
        $user = M('user')->where(['erp_uid' => $erp_uid])->find();
        if(false == $user) $this->apiReturn(0, '', 1, '会员不存在');
        $shop = M('shop')->where(['uid' => $user['id'], 'status' => ['neq' => 2]])->find();
        if (false == $shop) $this->apiReturn(0, '', 1, '店铺不存在或为强制关闭状态');
        
        //关闭店铺
        if (false == $this->closeShop($shop['id'])) $this->apiReturn(0, '', 1, '强制关闭店铺失败');        
        
        //取消正在退款及正在售后的记录
        $cancelRefund = $this->canelRefund($shop['id'], $user['id']);
        if ($cancelRefund['code'] != 1) {
            $mag = $cancelRefund['msg'];
            goto error;
        }
        
        //需要把已发货及已支付的订单全额退款给买家
        $refundOrder = $this->refundOrders($shop['id'], $user['id'], $user, $shop);
        if ($refundOrder['code'] != 1) {
            $mag = $refundOrder['msg'];
            goto error;
        }
        
        // 关闭未付款订单
        $closeOrders = $this->closeOrders($shop['id'], $user['id']);
        if ($closeOrders['code'] != 1) {
            $mag = $closeOrders['msg'];
            goto error;
        }
        
        $this->apiReturn(1);
        error:
        $this->apiReturn(0, '', 1, $mag);
    }
    
    
    /**
     * 关闭订单（仅限于未付款订单）
     * @param number $type  1为买家，二为商家
     * @return multitype:number |multitype:number string
     */
    protected function closeOrders($type = 1) {
        $doOrders = M('orders_shop');
        $map = [
            'status'    => 1,
        ];
        if ($type == 1) {
            $map['uid'] = $this->_user['id'];
        } else {
            $map['shop_id']   = $this->_shop['id'];
            $map['seller_id'] = $this->_user['id'];
        }
        //需要关闭已拍下的订单
        $closeOrders = $doOrders->where($map)->order('atime desc')->field('o_no,s_no,id,o_id')->select();
        if ($closeOrders) {
            $doOrders->startTrans();
            $sql = 'UPDATE '.C('DB_PREFIX').'orders_shop SET status = 10 WHERE shop_id = ' . $this->_user['id'] . ' AND seller_id = ' . $this->_user['id'] . ' AND status = 1';
            $flag = $doOrders->execute($sql);
            if ($flag == false) {
                $msg = '关闭未付款订单错误';
                goto error;
            }
        
            //添加关闭订单日志
            foreach ($closeOrders as $v) {
                $v['status'] = 10;
                $v['remark'] = '商家退会';
                $v['is_sys'] = 1;
                if(M('orders_logs')->add($v) == false) {
                    $mag = '添加关闭未付款订单日志失败';
                    goto error;
                }
            }
        } else {
            return ['code' => 1];
        }
        $doOrders->commit();
        return ['code' => 1];
        error:
        $doOrders->rollback();
        return ['code' => 0, 'msg' => $msg];
    }
    
    /**
     * 退款 （仅限于已发货及已付款订单）
     * @param unknown $param
     */
    protected function refundOrders($shopId, $sellerId, $user, $shop) {
        $doOrders = M('orders_shop');
        $map = [
            'shop_id'   => $shopId,
            'seller_id' => $sellerId,
            'status'    => ['in', '2,3'],
        ];
        
        //需要写关联模型
        $refundOrders = $doOrders->where($map)->order('atime desc')->field('atime,etime,ip', true)->select();
        
        if ($refundOrders) {
            $insertRefundId     =   []; //退款ID
            $insertRefundLogsId =   []; //退款日志ID
            $rData              =   []; //退款数据
            $rLogData           =   []; //退款日志数据
            $erpParams          =   []; //退款数据
            $buyUser            =   []; //买家
            $acceptLogsData     =   []; //同意退款日志
            $acceptLogsDataId   =   []; //同意退款日志ID
            $upRefund           =   []; //更改退款数据
            $upOrdersShop       =   []; //更新商家订单
            $upOrdersGoods      =   []; //更新订单中的商品
            $getLastGoods       =   []; //获取最后一个商品信息
            $goodsTotalMoney    =   []; //商品的总金额
            $closeShopOrders    =   []; //关闭商家订单
            $doRefundLogs       =   M('refund_logs');
            $doRefund           =   M('refund');
            $doUser             =   M('user');
            $i = 0;
            $doOrders->startTrans();
            foreach ($refundOrders as $k => $v) {
                $buyUser[$k]        =   $doUser->where(['id' => $v['uid']])->field('erp_uid,nick,id')->find();
                $getLastGoods[$k]   =   end($v['orders_goods']);
                foreach ($v['orders_goods'] as $key => $val) {
                    $i++;
                    $rData[$i]['uid']               =   $v['uid'];
                    $rData[$i]['seller_id']         =   $v['seller_id'];
                    $rData[$i]['shop_id']           =   $v['shop_id'];
                    $rData[$i]['s_id']              =   $v['id'];
                    $rData[$i]['s_no']              =   $v['s_no'];
                    $rData[$i]['orders_goods_id']   =   $val['goods_id'];
                    $rData[$i]['num']               =   $val['num'] - $val['refund_num'];
                    $rData[$i]['money']             =   round($val['total_price_edit'] - $val['refund_price'], 2);
                    if ($val['id'] == $getLastGoods[$k]['id']) {
                        //如果为最后一个商品，则算上邮费,并且退邮费
                        $rData[$i]['money']        +=   $v['express_price_edit'];
                        $rData[$i]['refund_express']=   $v['express_price_edit'];
                    }
                    $rData[$i]['score']             =   ($rData[$k]['money'] * $val['score_ratio'] * 100);
                    $rData[$i]['orders_status']     =   $v['status'];
                    $rData[$i]['status']            =   1;
                    $rData[$i]['type']              =   2;
                    $rData[$i]['reason']            =   '商家强制关闭店铺';
                    $rData[$i]['r_no']              =   $this->create_orderno('TK',$v['uid']);
                    $insertRefundId[$i] =   $doRefund->add($rData[$i]);
                    if ($insertRefundId[$i] == false) {
                        $msg = '添加申请退款失败';
                        goto error;
                    }
                    $rLogData[$i]['r_id']   =   $insertRefundId[$i];
                    $rLogData[$i]['r_no']   =   $rData[$key]['r_no'] ;
                    $rLogData[$i]['uid']    =   $v['uid'];
                    $rLogData[$i]['status'] =   1;
                    $rLogData[$i]['type']   =   $rData[$key]['type'];
                    $rLogData[$i]['remark'] =   '商家强制关闭店铺';
                    $rLogData[$i]['is_sys'] =   1;
                    $insertRefundLogsId[$i] =   $doRefundLogs->add($rLogData[$i]);
                    if ($insertRefundLogsId[$i] == false) {
                        $msg = '添加申请退款日志失败';
                        goto error;
                    }
                    /**
                     * 退款
                     * @param string $param['r_no']			退款单号
                     * @param float  $param['money']		退款金额
                     * @param int 	$param['score']			退回积分
                     * @param string $param['buyer_uid']	买家UID
                     * @param string $param['buyer_nick']	买家昵称
                     * @param string $param['seller_uid']	卖家UID
                     * @param string $param['seller_nick']	卖家昵称
                     * @param string $param['s_no']			订单号
                     * @param int 	$param['pay_type']		支付类型
                     * @param int 	$param['inventory_type']库存结算方式,0=非即结算，1=即时结算
                     * @param int 	$param['refundType']	1=退运费，2=退商品
                     */
                    //$erpParams[$i]['pay_type']      =   '';
                    
                    //商家同意退款数据
                    $erpParams[$i]['refundID']      =   $rData[$i]['r_no'];
                    $erpParams[$i]['refundMoney']   =   round($rData[$i]['money'], 2);
                    $erpParams[$i]['refundScore']   =   $rData[$i]['score'];
                    $erpParams[$i]['buyerID']       =   $buyUser[$k]['erp_uid'];
                    $erpParams[$i]['buyerNick']     =   $buyUser[$k]['nick'];
                    $erpParams[$i]['sellerID']      =   $user['erp_uid'];
                    $erpParams[$i]['sellerNick']    =   $user['nick'];
                    $erpParams[$i]['orderID']       =   $v['s_no'];
                    $erpParams[$i]['payType']       =   $v['pay_type'] == 2 ? 2 : 1;
                    $erpParams[$i]['dealType']      =   $v['inventory_type'] == 0 ? 2 : 1;
                    $erpParams[$i]['refundType']    =   $rData[$i]['type'];
                    
                    //执行erp退款
                    $res[$i] = A('Erp')->_refund($erpParams[$i]);
                    if ($res->code != 1) {
                        $msg = $res->msg;
                        goto error;
                    } else {
                        //卖家同意日志
                        $acceptLogsData[$i]['r_id']     =   $insertRefundId[$i];
                        $acceptLogsData[$i]['r_no']     =   $rData[$i]['r_no'];
                        $acceptLogsData[$i]['uid']      =   $buyUser[$k]['id']; 
                        $acceptLogsData[$i]['status']   =   100;
                        $acceptLogsData[$i]['type']     =   $rData[$i]['type'];
                        $acceptLogsData[$i]['remark']   =   '卖家同意退款';
                        $acceptLogsData[$i]['is_sys']   =   1;
                        $acceptLogsDataId[$i]   =   $doRefundLogs->add($acceptLogsData[$i]);
                        if ($acceptLogsDataId[$i] == false) {
                            $msg = '卖家同意日志写入失败';
                            goto error;
                        }
                        
                        //更新退款记录
                        $upRefund[$i] = $doRefund->where(['r_no' => $rData[$i]['r_no']])->save(['status' => 100,'dotime' => date('Y-m-d H:i:s'),'accept_time' => date('Y-m-d H:i:s')]);
                        if ($upRefund[$i] == false) {
                            $msg = '更新退款记录失败';
                            goto error;
                        }
                        
                        //更新商家订单
                        if ($val['id'] == $getLastGoods[$k]['id']) {
                            //如果是最后一个商品，则加上运费及关闭订单
                            $upOrdersShop[$i] = $doOrders->execute('update '.C('DB_PREFIX').'orders_shop set status=11,close_time=now(),refund_num=refund_num+'.$rData[$i]['num'].',refund_price=refund_price+'.$rData[$i]['money'].',refund_score=refund_score+'.$rData[$i]['score'].',money=money-'.$rData[$i]['money'].',refund_express=refund_express+'.$rData[$i]['refund_express'].' where id='.$v['id']);
                        } else {
                            $upOrdersShop[$i] = $doOrders->execute('update '.C('DB_PREFIX').'orders_shop set refund_num=refund_num+'.$rData[$i]['num'].',refund_price=refund_price+'.$rData[$i]['money'].',refund_score=refund_score+'.$rData[$i]['score'].',money=money-'.$rData[$i]['money'].' where id='.$v['id']);
                        }
                        if ($upOrdersShop[$i] == false) {
                            $msg = '更新商家订单退款信息失败';
                            goto error;
                        }
                        
                        //更新订单商品数据
                        $upOrdersGoods[$i] = $doOrders->execute('update '.C('DB_PREFIX').'orders_goods set refund_num=refund_num+'.$rData[$i]['num'].',refund_price=refund_price+'.$rData[$i]['money'].',refund_score=refund_score+'.$rData[$i]['score'].' where id='.$rData[$i]['orders_goods_id']);
                        if ($upOrdersGoods[$i] == false) {
                            $msg = '更新订单商品数据失败';
                            goto error;
                        }
                    }
                }
            }
        } else {
            return ['code' => 1];
        }
        
//         log_add('withdrawal', serialize($rData));               //退款记录
//         log_add('withdrawal', serialize($rLogData));            //退款日志记录
//         log_add('withdrawal', serialize($erpParams));           //发送的erp的退款记录
//         log_add('withdrawal', serialize($acceptLogsDataId));    //退款同意日志
//         log_add('withdrawal', serialize($res));                 //erp退款日志
        $this->writeLogs([$rData, $rLogData, $erpParams, $acceptLogsData, $res]);
        $doOrders->commit();
        return ['code' => 1];
        error:
        $doOrders->rollback();
        $this->writeLogs([$rData, $rLogData, $erpParams, $acceptLogsData, $res]);
        return ['code' => 0, 'msg' => $msg];
    }
    
    /**
     * 取消售后及退款服务
     * @param unknown $shopId
     * @param unknown $sellerId
     */
    protected function canelRefund($shopId, $sellerId) {
        $doRefund = M('refund');
        $map = [
            'shop_id'   => $shopId,
            'seller_id' => $sellerId,
            'status'    => ['notin', '20,100'],
        ];
        $data = $doRefund->where($map)->field('id,r_no,uid,type')->order('id asc')->select();
        if ($data) {
            $canelLogsData  = [];   //取消售后及退款数据
            $canelLogsDataId= [];   //取消退款及售后数据ID
            $upRefund       = [];   //更新退款及售后状态
            $doRefundLogs   = M('refund_logs');
            $doRefund->startTrans();
            foreach ($data as $k => $v) {
                $canelLogsData[$k]['r_id']     =   $v['id'];
                $canelLogsData[$k]['r_no']     =   $v['r_no'];
                $canelLogsData[$k]['uid']      =   $v['uid'];
                $canelLogsData[$k]['status']   =   20;
                $canelLogsData[$k]['type']     =   $v['type'];
                $canelLogsData[$k]['remark']   =   '卖家强制关闭店铺';
                $canelLogsData[$k]['is_sys']   =   1;
                $canelLogsDataId[$k] = $doRefundLogs->add($canelLogsData[$k]);
                if ($canelLogsDataId[$k] == false) {
                    $msg = '添加卖家取消退款及售后是失败';
                    goto error;
                }
                $upRefund[$k] = $doRefund->where(['id' => $v['id']])->save(['status' => 20, 'cancel_time' => date('Y-m-d H:i:s', NOW_TIME), 'dotime' => date('Y-m-d H:i:s', NOW_TIME)]);
                if ($upRefund[$k] == false) {
                    $msg = '更新卖家取消售后及退款时失败';
                    goto error;
                }
            }
        } else {
            return ['code' => 1];
        }
        
        $doRefund->commit();
        return ['code' => 1];
        error:
        $doRefund->rollback();
        return  ['code' => 0, 'msg' => $msg];
    }
    
    /**
     * 写日志
     * @param unknown $param
     */
    private function writeLogs($param) {
        $cnt = count($param);
        for ($i=0;$i<$cnt;$i++) {
            log_add('withdrawal', serialize($param[$i]));
        }
    }
    
    /**
     * 订单
     * @param unknown $param
     * @param int $shopId 店铺ID
     */
    private function checkOrders() {
        $map = [
            'status' => '2,3'
        ];
        $data = M('orders_shop')->where(array_merge($this->_map, $map))->order('id desc')->field('s_no')->select();
        if($data) {
            return $data;
        }
        return null;
    }
    
    /**
     * 售后中
     * @param unknown $param
     */
    private function checkService() {
        $map = [
            'orders_status' => ['gt', 3],
            'status'        => ['notin', '100,20'],
        ];
        return $this->getRefundData($map);
    }
    
    /**
     * 退款中
     * @param unknown $param
     */
    private function checkRefund() {
        $map = [
            'orders_status' => ['lt', 3],
            'status'        => ['notin', '100,20'],
        ];
        return $this->getRefundData($map);
    }
    
    /**
     * 获取售后数据及
     * @param unknown $map
     */
    private function getRefundData($map) {
        $data = M('refund')->where(array_merge($this->_map, $map))->order('id desc')->field('r_no')->select();
        if($data) {
            return $data;
        }
        return null;
    }
}