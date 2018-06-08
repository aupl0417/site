<?php
/**
 * ----------------------------------------------------------
 * RestFull API V2.0
 * ----------------------------------------------------------
 * 订单相关接口
 * ----------------------------------------------------------
 * Author:Lazycat <673090083@qq.com>
 * ----------------------------------------------------------
 * 2017-02-17
 * ----------------------------------------------------------
 */
namespace Rest2\Controller;
use Think\Controller\RestController;
use Think\Exception;

class OrdersController extends ApiController {
    protected $action_logs = array('_update_pay_ok','_orders_confirm','check_orders_paytype','check_ordres_in_erp');

    //各种订单状态
    protected $orders_status = array(
        0   => '已删除',
        1   => '已拍下',
        2   => '已付款',
        3   => '已发货',
        4   => '已收货',
        5   => '已评价',
        6   => '已归档',
        10  => '已关闭',
        11  => '退款完成',
    );

    /**
     * 订单支付前的检测
     * Create by Lazycat
     * 2017-02-17
     * --------------------------------------
     * @param string    $param['s_no']      商品家订单号
     * @param int       $param['is_sms']    库存积分不足时是否发送短信通知
     * @return array
     */
    public function _orders_pay_check($param,$ors=null){
        if(is_null($ors) || empty($ors)) {
            $ors = M('orders_shop')->where(['s_no' => $param['s_no']])->field('id,atime,status,s_no,o_id,o_no,shop_id,uid,seller_id,express_price_edit,goods_price_edit,pay_price,inventory_type,score,goods_num,coupon_id')->find();
        }
        //商家店铺是否处于正常营业状态
        $shop = D('Common/ShopUserRelation')->relation(true)->where(['id' => $ors['shop_id']])->field('id,uid,status,shop_name,shop_logo,mobile,qq,domain')->find();

        $shop['shop_url']   = shop_url($shop['id'],$shop['domain']);
        $data = ['orders' => $ors,'shop' => $shop];

        //if($ors['goods_price_edit'] * 100 != $ors['score']) return ['code' => 0,'msg' => '奖励积分存在异常！','data' => $data];

        if($shop['status'] != 1) return ['code' => 0,'msg' => '卖家店铺已暂停营业！','data' => $data];

        if($ors['uid'] != $this->user['id']) return ['code' => 0,'msg' => '您不是该订单的所有者！','data' => $data];
        if($ors['status'] != 1) return ['code' => 0,'msg' => '该订单不是待付款订单！','data' => $data];

        //检查商家库存积分
//        if($ors['inventory_type'] == 1){    //库存积分分账模式的订单
//            $res = A('Rest2/Erp')->_account(['userID' => $shop['erp_uid']]);
//            if($res['data']['a_storeScore'] < $ors['score']) {
//                //发送短信通知
//                if($param['is_sms'] == 1 && !empty($shop['mobile'])){
//                    $sms_data     = [];
//                    $sms_data['content']    = $this->sms_tpl(16,'{nick}',$this->user['nick']);
//                    $sms_data['mobile']     = $shop['mobile'];
//                    sms_send($sms_data);
//                }
//
//                return ['code' => 0,'msg' => '卖家库存积分不足！','data' => $data];
//            }
//        }

        //检查订单中商品属性
        $tmp = $this->_check_goods($param['s_no']);
        if($tmp['goods_status'] > 0) return ['code' => 0,'msg' => '订单中有部分商品存在异常（可能已下架或已变更属性）！','data' => $data];

        return ['code' => 1,'msg' => '订单状态正常！','data' => $data];
    }


    /**
     * 检查订单中商品库存状态
     * Create by Lazycat
     * 2017-02-17
     */
    public function _check_goods($s_no){
        $orders_goods = M('orders_goods')->cache(true)->where(['s_no' => $s_no])->field('id,goods_id,attr_list_id,num')->select();
        $attr_ids   = arr_id(['plist' => $orders_goods,'field' => 'attr_list_id']);
        $goods_ids  = arr_id(['plist' => $orders_goods,'field' => 'goods_id']);
        $goods_ids  = array_unique($goods_ids);

        $attr_list  = M('goods_attr_list')->where(['id' => ['in',$attr_ids]])->getField('id,num',true);
        $goods_list = M('goods')->where(['id' => ['in',$goods_ids]])->getField('id,status',true);

        $res['goods_status']    = 0; //是否存在不正常的商品

        $list = array();
        foreach($orders_goods as $key => $val){
            $val['status']      = 1;
            $val['status_name'] = '正常';

            if($goods_list[$val['goods_id']] != 1){
                $val['status']      = 4;
                $val['status_name'] = '商品已下架！';
                $res['goods_status']++;
            }elseif(!isset($attr_list[$val['attr_list_id']])){
                $val['status']      = 2;
                $val['status_name'] = '库存属性已变更！';
                $res['goods_status']++;
            }elseif($val['num'] > $attr_list[$val['attr_list_id']]){
                $val['status']      = 3;
                $val['status_name'] = '库存不足！';
                $res['goods_status']++;
            }

        }

        return $res;
    }


    /**
     * 支付成功后更新订单状态
     * Create by Lazycat
     * 2017-02-18
     * @param array $ors 订单信息
     * @param int $paytype 支付方式
     * @param int $is_fix  0为正常模式，1为修复模式，当订单有错误时进行些模式进行修正
     */
    public function _update_pay_ok($paytype,$ors,$shop=null,$is_fix=0){
        //判断是否已支付,如果已经支付，则直接返回
        if(M('orders_shop')->where(['id' => $ors['id'],'status' => 2])->count() > 0) goto success;

        $do=M();
        $do->startTrans();  //事务开始

        $map = ['id' => $ors['id'],'status' => 1];
        if($is_fix == 1) $map = ['id' => $ors['id']];   //修复模式不检测订单状态

        //更新订单状态
        if(!$this->sw[] = M('orders_shop')->where($map)->save(['pay_time' => date('Y-m-d H:i:s'),'status' => 2,'pay_type' => $paytype, 'money' => $ors['pay_price']])) {
            $msg = '更新订单状态失败！';
            goto error;
        }

        //写入日志
        $logs_data=array(
            'o_id'		=> $ors['o_id'],
            'o_no'		=> $ors['o_no'],
            's_id'		=> $ors['id'],
            's_no'		=> $ors['s_no'],
            'status'	=> 2,
            'remark'	=> '买家已付款'
        );

        if(!$this->sw[] = D('Common/OrdersLogs')->create($logs_data)){
            $msg = D('Common/OrdersLogs')->getError();
            goto error;
        }
        if(!$this->sw[] = D('Common/OrdersLogs')->add()) {
            $msg = '创建订单日志失败！';
            goto error;
        }

        //更新库存，有部分库存属可能已列新，所以不列入事务是否一定要执行成功
        $orders_goods = M('orders_goods')->cache(true)->where(['s_id' => $ors['id']])->field('id,goods_id,attr_list_id,num')->select();
        $num        = 0;
        $goods_ids  = [];
        foreach($orders_goods as $i => $val){
            $goods_ids[]    = $val['goods_id'];
            $num 	        +=	$val['num'];

            //更新销量
            $do->execute('update '.C('DB_PREFIX').'goods_attr_list set num=num-'.$val['num'].',sale_num=sale_num+'.$val['num'].' where id='.$val['attr_list_id']);
            $do->execute('update '.C('DB_PREFIX').'goods set num=num-'.$val['num'].',sale_num=sale_num+'.$val['num'].' where id='.$val['goods_id']);
        }
        $goods_ids = array_unique($goods_ids);

        //更新店铺销量
        if(!$this->sw[] = M('shop')->where(['id' => $ors['shop_id']])->setInc('sale_num',$num)) goto error;

        $do->commit();  //提交事务
        shop_pr($ors['shop_id']);	//更新店铺PR
        goods_pr($goods_ids);       //更新宝贝PR

        //发短信通知
        if(isset($shop['mobile']) && !empty($shop['mobile'])){
            $sms_data['content'] = $this->sms_tpl(14,
                ['{nick}','{orderno}','{money}','{goods_num}'],
                [$this->user['nick'],$ors['s_no'],$ors['pay_price'],$ors['goods_num']]
            );

            if(!strstr($sms_data['content'],'test') && !strstr($sms_data['content'],'测试')) sms_send($sms_data);
        }
		
		//发送订单消息
		$msg_data = ['tpl_tag'=>'orders_pay','s_no'=>$ors['s_no']];
		tag('send_msg',$msg_data);

        success:
        return ['code' => 1,'msg' => '订单更新成功！'];

        error:
        $do->rollback();
        return ['code' => 0,'msg' => '订单更新失败！'];
    }


    /**
     * 统计买家各状态订单
     * create by Lazycat
     * 2017-02-20
     */
    public function _buyer_orders_count($uid){
        $count  = [];
        $allnum = 0;
        foreach($this->orders_status as $key => $val){
            $tmp['status']      = $key;
            $tmp['status_name'] = $val;

            $map = [];
            $map['uid']         = $uid;
            $map['status']      = $key;
            if($key == 4) $map['_string'] = 'refund_price < goods_price_edit';
            $tmp['count']       = M('orders_shop')->where($map)->count();

            $count[]            = $tmp;

            $allnum += $tmp['count'];
        }

        return ['allnum' => $allnum,'count' => $count];
    }

    /**
     * 售后统计
     * Create by Lazycat
     * 2017-02-24
     */
    public function _buyer_service_count($uid){
        $result['service_in']       = M('refund')->where(['uid' => $uid,'status' => ['not in','20,100'],'orders_status' => ['in', '4,5']])->count();  //售后中
        $result['service_finish']   = M('refund')->where(['uid' => $uid,'status' => 100,'orders_status' => ['in', '4,5']])->count();  //已完成的售后
        $result['service_allnum']   = M('refund')->where(['uid' => $uid,'orders_status' => ['in', '4,5']])->count();  //所有售后

        return $result;
    }
    /**
     * 买家退货
     * Create by Lazycat
     * 2017-02-24
     */
    public function _buyer_refund_count($uid){
        $result['refund_in']        = M('refund')->where(['uid' => $uid,'status' => ['not in','20,100'],'orders_status' => ['lt', 4]])->count();  //退款中
        $result['refund_finish']    = M('refund')->where(['uid' => $uid,'status' => 100,'orders_status' => ['lt', 4]])->count();      //退款完成
        $result['refund_allnum']    = M('refund')->where(['uid' => $uid,'orders_status' => ['lt', 4]])->count();      //所有退款

        return $result;
    }

    /**
     * 卖家退货/售后统计
     * Create by Lazycat
     * 2017-02-24
     */
    public function _seller_other_count($uid){
        $result['refundin']     = M('refund')->where(['seller_id' => $uid,'status' => ['not in','20,100'],'orders_status' => ['lt', 4]])->count();  //退款中
        $result['finish']       = M('refund')->where(['seller_id' => $uid,'status' => ['in','20,100'],'orders_status' => ['lt', 4]])->count();      //退款完成
        $result['allnum']       = M('refund')->where(['seller_id' => $uid,'orders_status' => ['lt', 4]])->count();      //所有退款

        return $result;
    }

    /**
     * 统计卖家各状态订单
     * create by Lazycat
     * 2017-02-20
     */
    public function _seller_orders_count($uid){
        $count  = [];
        $allnum = 0;
        foreach($this->orders_status as $key => $val){
            $tmp['status']  = $key;
            $tmp['status_name'] = $val;

            $map = [];
            $map['seller_id']   = $uid;
            $map['status']      = $key;
            if($key == 4) $map['_string'] = 'refund_price < goods_price_edit';
            $tmp['count']   = M('orders_shop')->where($map)->count();

            $count[]        = $count;
            $allnum += $tmp['count'];
        }

        return ['allnum' => $allnum,'count' => $count];
    }

    /**
     * 查询物流信息
     * Create by Lazycat
     * 2017-02-21
     * @param string $param['s_no'] 订单号
     * @param int   $param['express_company_id']    快递公司ID
     * @param string $param['express_code']         运单号
     * @param date  $param['express_time']          发货时间
     */
    public function _logistics_info($param){
        $cache_name = 'query_express_'.$param['s_no'];
        $rs         = S($cache_name);
        if($rs){
            return ['code' => 1,'data' => $rs];
        }

        //测试数据
        //$param['express_company_id']    = 323;
        //$param['express_code'] = '428241426874';

        $rs = M('express_company')->cache(true)->where(['id' => $param['express_company_id']])->field('id,company,sub_name,logo,website,tel,code')->find();

        if($rs){
            $rs['s_no']         =   $param['s_no'];
            $rs['express_code'] =   $param['express_code'];
            $rs['express_time'] =   $param['express_time'];
            $url    =   'https://www.kuaidi100.com/query?type='.$rs['code'].'&postid='.$rs['express_code'];
            $res    =   $this->curl_get($url);
            $res    =   json_decode($res,1);

            if($res) {
                $rs['history']  = $res['data'];
                S($cache_name, $rs);
            }
            return ['code' => 1,'data' => $rs];
        }

        error:
        return ['code' => 0,'msg' => '找不到物流记录！'];

    }
    /**
     * 通过阿里云查询物流信息
     * Create by Lazycat
     * 2017-02-21
     * @param string $param['s_no'] 订单号
     * @param int   $param['express_company_id']    快递公司ID
     * @param string $param['express_code']         运单号
     * @param date  $param['express_time']          发货时间
     */
    public function _logistics_info_aliyun($param){
        //获取后台配置
		$data = getSiteConfig('logistics');
		
 		$cache_name = 'query_express_'.$param['s_no'];
        $rs         = S($cache_name);
        if($rs){
            return ['code' => 1,'data' => $rs];
        } 

        //测试数据
        // $param['express_company_id']    = 323;
        // $param['express_code'] = '428241426874';

        $rs = M('express_company')->cache(true)->where(['id' => $param['express_company_id']])->field('id,company,sub_name,logo,website,tel,code')->find();

        if($rs){
			$method = "GET";
			$appcode = $data['appcode'];
			$headers = array();
			array_push($headers, "Authorization:APPCODE " . $appcode);
			$querys = "com=".$rs['code']."&nu=".$param['express_code'];
			$bodys = "";
			$url = $data['apiurl'] . "?" . $querys;

			$curl = curl_init();
			curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
			curl_setopt($curl, CURLOPT_URL, $url);
			curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
			curl_setopt($curl, CURLOPT_FAILONERROR, false);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($curl, CURLOPT_HEADER, false);
			if (1 == strpos("$".$host, "https://"))
			{
				curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
				curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
			}
			$result = json_decode(curl_exec($curl),true);
			$result['logo'] = $rs['logo'];
			if($result['showapi_res_code'] !=0){
				$result['showapi_res_body']['expTextName'] = $rs['sub_name'];
				$result['showapi_res_body']['updateStr'] = $param['express_time'];
				$result['showapi_res_body']['mailNo'] = $param['express_code'];
				$result['showapi_res_body']['msg'] = $result['showapi_res_error'];
			}
			if($result['showapi_res_body']['ret_code'] != 0){
				$result['showapi_res_body']['expTextName'] = $rs['sub_name'];
				$result['showapi_res_body']['updateStr'] = $param['express_time'];
				$result['showapi_res_body']['mailNo'] = $param['express_code'];
			}
			return ['code' => 1,'data' => $result];
/* 			
            $rs['s_no']         =   $param['s_no'];
            $rs['express_code'] =   $param['express_code'];
            $rs['express_time'] =   $param['express_time'];
            $url    =   'https://www.kuaidi100.com/query?type='.$rs['code'].'&postid='.$rs['express_code'];
            $res    =   $this->curl_get($url);
            $res    =   json_decode($res,1);

            if($res) {
                $rs['history']  = $res['data'];
                S($cache_name, $rs);
            }
            return ['code' => 1,'data' => $rs]; */
        }

        error:
        return ['code' => 0,'msg' => '找不到物流记录！'];

    }
    /**
     * @param array $ors  订单资料
     * @param int $is_sys   是否系统自动执行
     * @return array
     */
    public function _orders_confirm($ors,$is_sys=0){
        //退款列表
        $refund = M('refund')->where(['s_id' => $ors['id'],'status' => ['not in','20,100']])->field('id,r_no,uid,type')->select();

        $do = M();
        $do->startTrans();  //事务开始

        //如果存在着退款，即将退款取消
        if($refund){
            if(!$this->sw[] = M('refund')->where(['s_id' => $ors['id'],'status' => ['not in','20,100']])->save(['status' => 20,'cancel_time' => date('Y-m-d H:i:s')])) goto error;
            //日志
            foreach($refund as $val){
                //日志数据
                $logs=[
                    'r_id'          => $val['id'],
                    'r_no'          => $val['r_no'],
                    'uid'           => $val['uid'],
                    'status'        => 20,
                    'type'          => $val['type'],
                    'remark'        => '买家确认收货，默认取消退款！', //买家取消退款！
                ];

                if(!$this->sw[] = D('Common/RefundLogs')->create($logs)){
                    $msg = D('Common/RefundLogs')->getError();
                    goto error;
                }

                if(!$this->sw[] = D('Common/RefundLogs')->add()) {
                    $msg = '取消退款失败！';
                    goto error;
                }
            }
        }


        //更新订单
        if(!$this->sw[] = M('orders_shop')->where(['id' => $ors['id'],'status' => 3])->save(['status' => 4,'receipt_time' => date('Y-m-d H:i:s'),'next_time' => date('Y-m-d H:i:s',time() + C('cfg.orders')['rate_add']),'is_problem' => 0])){
            $msg = '更新订单状态失败！';
            goto error;
        }

        //订单日志
        $logs_data=array(
            'o_id'		=> $ors['o_id'],
            'o_no'		=> $ors['o_no'],
            's_id'		=> $ors['id'],
            's_no'		=> $ors['s_no'],
            'status'	=> 4,
            'remark'	=> '买家确认收货',
            'is_sys'	=> $is_sys,
        );

        if(!$this->sw[]=D('Common/OrdersLogs')->create($logs_data)){
            $msg = D('Common/OrdersLogs')->getError();
            goto error;
        }

        if(!$this->sw[] = D('Common/OrdersLogs')->add()){
            $msg = '写入订单日志失败！';
            goto error;
        }

        $do->commit();
		
		//发送订单消息
		$msg_data = ['tpl_tag'=>'orders_confirm','s_no'=>$ors['s_no']];
		tag('send_msg',$msg_data);
		
        return ['code' => 1,'msg' => '确认收货成功！'];

        error:
        $do->rollback();
        return ['code' => 0,'msg' => !empty($msg) ? $msg : '确认收货失败！'];

    }

    /**
     * subject: 检查某个订单与ERP数据对比是否正常，如有异常将自动修复
     * api: /Orders/check_ordres_in_erp
     * author: lazycat
     * day: 2017-03-28
     * content: 主要针对ERP接口返回超时后订单没更改状态的情况，此为工具类接口
     *
     * [字段名,类型,是否必传,说明]
     * param: s_no,string,1,订单号
     */
    public function check_ordres_in_erp(){
        $this->check('s_no',false);

        $res = $this->_check_ordres_in_erp($this->post);
        $this->apiReturn($res);
    }

    public function _check_ordres_in_erp($param){
        $ors = M('orders_shop')->where(['s_no' => $param['s_no']])->field('id,s_no,o_id,o_no,uid,seller_id,shop_id,status,goods_num,pay_price')->find();
        if(empty($ors)) return ['code' => 0,'msg' => '找不到订单记录！'];

        $this->user = $this->_user(['id' => $ors['uid']]);

        $res = A('Rest2/Erp')->_orders_in_erp_status($param['s_no']);
        if($res['code'] != 1) return ['code' => 0,'msg' => '获取ERP订单状态失败！'];

        $paytype = 1;   //余额
        switch($res['data']['o_payType']){
            case 2: //唐宝
                $paytype = 2;
                break;
            case 3: //微信
                $paytype = 3;
                break;
            case 5: //支付宝
                $paytype = 5;
                break;
            case 7: //网银
                $paytype = 7;
                break;
        }

        switch ($ors['status']){
            //状态为未付款，实际ERP中已付款
            case 1:
            case 10:
            case 20:
                if($res['data']['o_orderState'] > 0){
                    $ret = $this->_update_pay_ok($paytype,$ors,null,1);

                    $logs_data = [
                        'atime'         => NOW_TIME,
                        'subject'       => 'ERP已付款成功但商城状态还是未付款',
                        's_no'          => $ors['s_no'],
                        'status'        => $ors['status'],
                        'res'           => $ret['code'],
                        'fix_status'    => $ret['code'] == 1 ? '修复成功！' : $ret['msg'],
                    ];
                    log_add('orders_fix',$logs_data);
                    return ['code' => 1,'data' => $ors];
                }
                break;

            //ERP中已收货，商城未更改状态
            case 3:
                if($res['data']['o_orderState'] > 2){
                    $ret = $this->_orders_confirm($ors);
                    $logs_data = [
                        'atime'         => NOW_TIME,
                        'subject'       => 'ERP已确认收货但商城状态还未收货',
                        's_no'          => $ors['s_no'],
                        'status'        => $ors['status'],
                        'res'           => $ret['code'],
                        'fix_status'    => $ret['code'] == 1 ? '修复成功！' : $ret['msg'],
                    ];
                    log_add('orders_fix',$logs_data);
                    return ['code' => 1,'data' => $ors];
                }
                break;
        }

        success:
        return ['code' => 10,'msg' => '订单状态正常！'];
    }

    /**
     * 支付方式较对，修复PC端接入收银台后产生支付方式不一致的问题
     * Create by Lazycat
     * 2017-03-29
     */
    public function check_orders_paytype(){
        $this->check('s_no',false);

        $res = $this->_check_orders_paytype($this->post);
        $this->apiReturn($res);
    }

    public function _check_orders_paytype($param){
        $ors = M('orders_shop')->where(['s_no' => $param['s_no']])->field('id,status,s_no,pay_type')->find();
        if(empty($ors)) return ['code' => 0,'msg' => '找不到订单记录！'];

        if(in_array($ors['status'],[0,1,10])) return ['code' => 0,'msg' => '未付款订单不支持检测！'];

        $res = A('Rest2/Erp')->_orders_in_erp_status($param['s_no']);
        if($res['code'] != 1) return ['code' => 0,'msg' => '获取ERP订单状态失败！'];

        $paytype = 1;   //余额
        switch($res['data']['o_payType']){
            case 2: //唐宝
                $paytype = 2;
                break;
            case 3: //微信
                $paytype = 3;
                break;
            case 5: //支付宝
                $paytype = 5;
                break;
            case 7: //网银
                $paytype = 7;
                break;
        }

        if($ors['pay_type'] != $paytype){
            $ret = M('orders_shop')->where(['id' => $ors['id']])->setField('pay_type',$paytype);
            $logs_data = [
                'atime'         => NOW_TIME,
                'subject'       => '支付方式不一致（商城='.$ors['pay_type'].'，ERP='.$paytype.'）',
                's_no'          => $ors['s_no'],
                'status'        => $ors['status'],
                'res'           => $ret ? 1 : 0,
                'fix_status'    => $ret ? '修复成功！' : $ret['msg'],
            ];
            log_add('orders_fix',$logs_data);
            return ['code' => 1,'data' => $ors];
        }

        success:
        return ['code' => 10,'msg' => '订单状态正常！'];

    }

    /**
     * 防止重复支付问题
     * 进入支付流程，1分钟内不可以修改价格或使用其它浏览器或APP进行支付
     * Create by lazycat
     * 2017-04-07
     */
    public function paying(){
        $this->check($this->_field('device_id','s_no'),false);

        $res = $this->_paying($this->post);
        $this->apiReturn($res);
    }

    public function _paying($param){
        $device_id  = $param['device_id'] ? $param['device_id'] : $this->token['data']['device_id'];
        $cache_name = 'paying_'.$param['s_no'];
        $data = S($cache_name);

        if($data){
            if($data['device_id'] != $device_id){
                return ['code' => 0,'msg' => '您已在其它设备上进入了支付流程，请1分钟后再重新偿试！'];
            }elseif(time() - $data['atime'] < 5){ //提交时间隔小于5秒
                return ['code' => 0,'msg' => '请不要频繁提交，如支付遇到问题请稍候再试！'];
            }

            S($cache_name,['device_id' => $device_id,'atime' => time()],60);
            return ['code' => 1];
        }else{
            S($cache_name,['device_id' => $device_id,'atime' => time()],60);
            return ['code' => 1];
        }
    }

    /**
     * subject: 确认收货
     * api: receive
     * author: Mercury
     * day: 2017-06-30 10:04
     * [字段名,类型,是否必传,说明]
     */
    public function receive()
    {
        $this->check('s_no', false);
        $model = M('orders_shop');
        $model->startTrans();
        try {

            $map    = ['uid' => $this->user['id'], 's_no' => $this->post['s_no']];


			$orders = $model->where($map)->field('id,o_id,o_no,s_no,score_type,express_price,supplier_id')->find();
			

            $res = A('Erp')->_orders_confirm2(['s_no'=>$this->post['s_no'],'score_type'=>$orders['score_type']]);
            if($res['code'] != 1) throw new Exception($res['msg']);
            $cData  = [
                'status'        => 4,
                'receipt_time'  => date('Y-m-d H:i:s', NOW_TIME)
            ];
            if (M('orders_shop')->where($map)->save($cData) == false) throw new Exception('操作失败');
			
			
			//供货商增加金额
			if($orders['supplier_id'] > 0){
				$money = 0;
				$sale_money = M('supplier_user')->where(['uid'=>$orders['supplier_id']])->getField('sale_money');
				if($orders['score_type'] == 2){
					//现金计算商品价格
					$orders_goods_list = M('orders_goods')->field('total_price_edit,score_ratio')->where(['s_no'=>$orders['s_no']])->select();
					//writelog($orders_goods_list);
					foreach($orders_goods_list as $v){
						//价格比例
						$ratio = (($v['score_ratio']*12)*0.01);
						$money += $v['total_price_edit']*(1-$ratio);
					}
					$money += $orders['express_price'];
					
				}else{
					//金积分银积分直接计算成本价
					$money = M('orders_goods')->where(['s_no'=>$orders['s_no']])->sum('price_purchase');
				}
				$money = round($money,2);
				$sale_money += $money;
				if(M('supplier_user')->where(['uid'=>$orders['supplier_id']])->save(['sale_money'=>$sale_money]) === false) throw new Exception('操作失败');
			}
			

            $logs   = [
                'ip'    => get_client_ip(),
                'o_id'  => $orders['o_id'],
                'o_no'  => $orders['o_no'],
                's_id'  => $orders['id'],
                's_no'  => $this->post['s_no'],
                'status'=> 4,
                'remark'=> '买家确认收货',
            ];
            if (false == M('orders_logs')->add($logs)) throw new Exception('添加日志失败');
            $model->commit();
            $returnData = ['code' => 1];
        } catch (Exception $e) {
            $model->rollback();
            $returnData = ['code' => 0, 'msg' => $e->getMessage()];
        }
        $this->apiReturn($returnData);
    }
}