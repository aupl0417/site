<?php
/**
+----------------------------------------------------------------------
| RestFull API 
+----------------------------------------------------------------------
| 订单中各流程的超时处理
| 数据多的情况下要用队列来处理
+----------------------------------------------------------------------
| Author: lazycat <673090083@qq.com>
| 2017-03-22 临时版本
+----------------------------------------------------------------------
*/
namespace Rest\Controller;
use Wap\Controller\CommonController;
use Common\Controller\OrdersExpireActionController;
use Common\Controller\TotalsController;
use Common\Builder\Queue;
//set_time_limit(120);  //不限时处理
class CronV2Controller extends OrdersExpireActionController {
    protected $sw       = array();  //保存事务执行结果
    protected $token;               //Rest2接口授权token
    public function _initialize() {
        parent::_initialize();

        //由于请求接口需要认证身份，所以此项是必须的
        if(IS_POST){
            $_POST = array_merge($_POST,C('cfg.api'));
        }else $_POST=C('cfg.api');

        //兼容Rest2接口
        $res = $this->doApi2('/Auth/token',$this->api_cfg);
        if($res['code'] == 1){
            $this->token = $res['data']['token'];
            $_POST['token'] = $this->token;
        }else{
            echo json_encode($res);
            exit();
        }

    }



    /**
     * 执行任务
     * Create by Lacycat
     * 2017-03-22
     */
    public function job(){
        G('begin');
        switch (I('post.type')) {
            //待关闭订单
            case 'orders_close':
                $res = $this->_close(I('post.val'));
                break;

            //待确认收货订单
            case 'orders_confirm':
            case 'orders_confirm_refund_finished':
                $res = $this->_orders_confirm_v2(I('post.val'));
                break;

            //待评价订单
            case 'orders_rate':
                $res = $this->_buyer_rate(I('post.val'));
                break;

            //待归档订单
            case 'orders_history':

                break;

            //================== 退款处理 ===================

            //未发货退款 - 买家申请退款
            case 'refund_buyer_not_express':
                $res = $this->_refund_accept(I('post.val'));
                break;

            //已发货退款 - 买家申请退款/买家修改退款，卖家响应超时时默认同意退款
            case 'refund_buyer_add':
            case 'refund_buyer_edit':
                $res = $this->_refund_accept2(I('post.val'));
                break;

            //已发货退款 - 买家寄回商品，卖家响应超时时默认同意退款
            case 'refund_buyer_send_express':
                $res = $this->_refund_accept3(I('post.val'));
                break;

            //已发货退款 - 卖家拒绝退款/卖家同意退货，买家响应超时是默认取消退款
            case 'refund_seller_reject':
            case 'refund_seller_accept':
                $res = $this->_refund_cancel(I('post.val'));
                break;

            //================ 售后处理 ===================

            //售后 - 买家申请售后，买申请后卖家未响应默认拒绝
            case 'service_buyer_add':
                $res = $this->_service_seller_reject(I('post.val'));
                break;

            //售后 - 卖家拒绝售后，买家响应超时默认取消售后
            case 'service_seller_reject':
                $res = $this->_service_seller_refuse(I('post.val'));
                break;

            //售后 - 卖家同意售后，买家响应超时默认取消售后
            case 'service_seller_accept':
                $res = $this->_service_buyer_express(I('post.val'));
                break;

            //售后 - 买家寄回商品，卖家响应超时默认为已收到待售后处理的商品
            case 'service_buyer_send_express':
                $res = $this->_service_seller_confirm(I('post.val'));
                break;

            //售后 - 卖家寄出商品(售后完成)，买家响应超时默认买家已认同卖家的售后服务已完成
            case 'service_seller_finished':
                $res = $this->_service_buyer_confirm(I('post.val'));
                break;

            //============== 商品处理 ===================

            //商品索引更新
            case 'goods_update':
                $res = $this->_goods_to_index(I('post.val'));
                break;

            //商品主图搬家
            case 'goods_images':
                $res = $this->_goods_images(I('post.val'));
                break;

            //商品库存主图搬家
            case 'goods_attr_list_images':
                $res = $this->_goods_attr_list_images(I('post.val'));
                break;

            //商品库存属性图片搬家
            case 'goods_attr_value_images':
                $res = $this->_goods_attr_value_images(I('post.val'));
                break;

            //店铺索引更新
            case 'shop_update':
                $res = $this->_shop_to_index(I('post.val'));
                break;

            //店铺统计营业额
            case 'shop_total':
                $res = $this->_shop_total(I('post.val'));
                break;

            //=========== 话费流量 ================

            //话费、流量充值处理（当付款成功，但提交到充值平台未被正确接收处理的，队列将重新发起请求）
            case 'mobile_orders_repost':
                $res = $this->_mobile_orders_repost(I('post.val'));
                break;

            //话费、流量充值（当提交至充值平台超过1小时用无果后，表明充值失败，直接将款退给用户）
            case 'mobile_orders_refund':
                $res = $this->_mobile_orders_refund(I('post.val'));
                break;

            //话费订单 - 关闭订单
            case 'mobile_orders_close':
                $res = $this->_mobile_orders_close(I('post.val'));
                break;

            //话费订单 - 确认收货
            case 'mobile_orders_confirm':
                $res = $this->_mobile_orders_confirm(I('post.val'));
                break;

            //话费订单 - 退款（商家超时未处理，默认同意退款）
            case 'mobile_orders_refund_add':
                $res = $this->_mobile_orders_refund_add(I('post.val'));
                break;

            //话费订单 - 被拒绝退款（超时自动取消退款）
            case 'mobile_orders_refund_reject':
                $res = $this->_mobile_orders_refund_reject(I('post.val'));
                break;



        }

        //============ 以下为日志内容格式化 ===============
        $job_name   = [
            'orders_close'              => '待关闭订单',
            'orders_confirm'            => '待确认收货订单',
            'orders_confirm_refund_finished' => '待确认收货订单(退款已取消或完成)',
            'orders_rate'               => '待评价订单',
            'orders_history'            => '待归档订单',

            'refund_buyer_not_express'  => '未发货退款 - 买家申请退款，卖家响应超时默认同意退款',
            'refund_buyer_add'          => '已发货退款 - 买家申请退款，卖家响应超时时默认同意退款',
            'refund_buyer_edit'         => '已发货退款 - 买家修改退款，卖家响应超时时默认同意退款',
            'refund_buyer_send_express' => '已发货退款 - 买家寄回商品，卖家响应超时时默认同意退款',
            'refund_seller_reject'      => '已发货退款 - 卖家拒绝退货退款，买家响应超时是默认取消退款',
            'refund_seller_accept'      => '已发货退款 - 卖家同意退货，买家响应超时是默认取消退款',

            'service_buyer_add'         => '售后 - 买家申请售后，买申请后卖家未响应默认拒绝',
            'service_seller_reject'     => '售后 - 卖家拒绝售后，买家响应超时默认取消售后',
            'service_seller_accept'     => '售后 - 卖家同意售后，买家响应超时默认取消售后',
            'service_buyer_send_express'=> '售后 - 买家寄回商品，卖家响应超时默认为已收到待售后处理的商品',
            'service_seller_finished'   => '售后 - 卖家寄出商品(售后完成)，买家响应超时默认买家已认同卖家的售后服务已完成',

            'goods_update'              => '商品索引更新',
            'goods_images'              => '商品主图搬家',
            'goods_attr_list_images'    => '商品库存主图搬家',
            'goods_attr_value_images'   => '商品库存属性图片搬家',
            'shop_update'               => '店铺索引更新',
            'shop_total'                => '店铺统计营业额',
            'mobile_orders_repost'    => '话费、流量充值重新提交到充值平台',
            'mobile_orders_refund'    => '话费、流量充值退款',
            'mobile_orders_close'       => '话费、流量订单 - 超时关闭',
            'mobile_orders_confirm'     => '话费、流量订单 - 确认收货',
            'mobile_orders_refund_add'  => '话费、流量订单 - 退款（卖家响应超时默认同意退款）',
            'mobile_orders_refund_reject' => '话费、流量订单 - 退款被拒绝（超时自动取消退款）',
        ];

        //日志表归类
        $logs_table = [
            'cron_orders'           => ['orders_close','orders_confirm','orders_confirm_refund_finished','orders_rate','orders_history'],
            'cron_refund'           => ['refund_buyer_not_express','refund_buyer_add','refund_buyer_edit','refund_buyer_send_express','refund_seller_reject','refund_seller_accept'],
            'cron_service'          => ['service_buyer_add','service_seller_reject','service_seller_accept','service_buyer_send_express','service_seller_finished'],
            'cron_images_remote'    => ['goods_images','goods_attr_list_images','goods_attr_value_images'],
            'cron_goods_update'     => ['goods_update'],
            'cron_shop_update'      => ['shop_update'],
            'cron_shop_total'       => ['shop_total'],
            'cron_mobile_recharge'  => ['mobile_orders_repost','mobile_orders_refund','mobile_orders_close','mobile_orders_confirm','mobile_orders_refund_add','mobile_orders_refund_reject'],
        ];

        $msg = [
            0       => '操作失败！',
            1       => '操作成功！',
            100     => '状态错误或已处理过！',
        ];

        $table = '';
        foreach ($logs_table as $key => $val){
            if(in_array(I('post.type'),$val)) {
                $table = $key;
                break;
            }
        }

        if($table) {
            $logs_data = [
                'job_name'      => $job_name[I('post.type')],
                'method'        => I('post.type'),
                'val'           => I('post.val'),
                'code'          => $res['code'],
                'msg'           => $res['msg'] ? $res['msg'] : $msg[$res['code']],
                'dotime'        => G('begin','end'),
                'atime'         => date('Y-m-d H:i:s'),
            ];
            log_add($table, $logs_data);
        }

        echo json_encode($res);
    }

    /**
     * 自动报名秒杀活动（由于暂时没人维护秒杀模块，启用些自动功能）
     * Create by Lazycat
     * 2017-04-02
     */
    public function auto_activity(){
        $_POST['token'] = $this->token;
        $res = $this->doApi2('/Miaosha/auto_activity',$_POST);
        echo json_encode($res);
    }

    /**
     * 确认收货
     * Create by lazycat
     * 2017-04-14
     */
    public function _orders_confirm_v2($s_no){
        $rs = M('orders_shop')->where(['s_no' => $s_no,'status' => 3,'next_time' => ['lt',date('Y-m-d H:i:s')]])->field('status,uid')->find();
        if(!$rs) return ['code' => 100,'msg' => '错误的订单收货状态！'];  //状态不对，返回100后队列将会清除

        $data['uid']       = $rs['uid'];
        $data['s_no']      = $s_no;
        $res = A('Rest2/Erp')->_erp_orders_confirm($data);
        return $res;
    }

    /**
     * 已评价的记录进行刷单检测
     * Create by lazycat
     * 2017-04-29
     */
    public function _check_orders_shuadan($s_no){
        $res = A('Rest2/ToolsRate')->_check_orders_shuadan(['s_no' => $s_no]);
        return $res;
    }

    /**
     * 话费、流量充值重新提交到充值平台
     * Create by lazycat
     * 2017-05-11
     */
    public function _mobile_orders_repost($s_no){
        $rs = M('mobile_orders')->where(['s_no' => $s_no])->field('status,uid,transtat,return_status,next_time')->find();
        if(empty($rs)) return ['code' => 100,'msg' => '订单不存在！'];
        if($rs['status'] != 2) return ['code' => 100,'msg' => '订单状态错误！'];
        if(in_array($rs['transtat'],[1,3,4,10,18]) || in_array($rs['return_status'],[1,10,28,29])) return ['code' => 100,'msg' => '该订单已被成功处理，无须再次提交！'];
        if($rs['next_time'] < date('Y-m-d H:i:s')) return ['code' => 100,'msg' => '已超过重复提交的处理时间！'];

        $data['s_no']   = $s_no;
        $data['uid']    = $rs['uid'];
        $data['repost'] = 1;
        $res = A('Rest2/MobileRecharge')->_recharge($data);
        $res['code'] = $res['code'] == 1 ? $res['code'] : 100;  //1或100都会直接删除队列
        return $res;
    }

    /**
     * 话费、流量充值退款
     * Create by lazycat
     * 2017-05-11
     */
    public function _mobile_orders_refund($s_no){
        $rs = M('mobile_orders')->where(['s_no' => $s_no])->field('status,uid,transtat,return_status,next_time')->find();
        if(empty($rs)) return ['code' => 100,'msg' => '订单不存在！'];
        if($rs['next_time'] > date('Y-m-d H:i:s')) return ['code' => 100,'msg' => '未达到自动退款处理时间！'];

        $data['s_no']   = $s_no;
        $data['uid']    = $rs['uid'];
        //$data['auto']   = 1;
        $res = A('Rest2/MobileRecharge')->_auto_refund($data);
        $res['code'] = $res['code'] == 1 ? $res['code'] : 100;  //1或100都会直接删除队列
        return $res;
    }

    /**
     * 话费、流量订单 - 超时关闭
     */
    public function _mobile_orders_close($s_no){
        $rs = M('mobile_orders')->where(['s_no' => $s_no])->field('status,uid,next_time')->find();
        if($rs['status'] != 1) return ['code' => 0,'msg' => '未付款订单才可以关闭！'];
        if($rs['next_time'] > date('Y-m-d H:i:s')) return ['code' => 0,'msg' => '未达到自动关闭时间期限！'];

        $data['s_no']   = $s_no;
        $data['uid']    = $rs['uid'];

        $res = A('Rest2/MobileRecharge')->_orders_close($data);
        return $res;
    }

    /**
     * 话费、流量订单 - 确认收货
     */
    public function _mobile_orders_confirm($s_no){
        $rs = M('mobile_orders')->where(['s_no' => $s_no])->field('status,uid,next_time')->find();
        if($rs['status'] != 3) return ['code' => 0,'msg' => '已发货订单才可以确认收货！'];
        if($rs['next_time'] > date('Y-m-d H:i:s')) return ['code' => 0,'msg' => '未达到自动确认收货时间期限！'];

        $data['s_no']   = $s_no;
        $data['uid']    = $rs['uid'];

        $res = A('Rest2/Erp')->_erp_mobile_orders_confirm($data);
        return $res;
    }


    /**
     * 话费、流量订单 - 退款（卖家响应超时默认同意退款）
     */
    public function _mobile_orders_refund_add($r_no){
        $rs = M('mobile_orders_refund')->where(['r_no' => $r_no])->field('status,uid,next_time')->find();
        if(!in_array($rs['status'],[1,2])) return ['code' => 0,'msg' => '退款中的状态才可以同意退款！'];
        if($rs['next_time'] > date('Y-m-d H:i:s')) return ['code' => 0,'msg' => '未达到自动退款时间期限！'];

        $data['s_no']   = $s_no;
        $data['uid']    = $rs['uid'];

        $res = A('Rest2/Erp')->_mobile_recharge_refund($data);
        return $res;
    }

    /**
     * 话费、流量订单 - 退款被拒绝（超时自动取消退款）
     */
    public function _mobile_orders_refund_reject($r_no){
        $rs = M('mobile_orders_refund')->where(['r_no' => $r_no])->field('status,uid,next_time')->find();
        if($rs['status'] != 2) return ['code' => 0,'msg' => '被拒绝的订单才可以取消退款！'];
        if($rs['next_time'] > date('Y-m-d H:i:s')) return ['code' => 0,'msg' => '未达到自动取消退款时间期限！'];

        $data['s_no']   = $s_no;
        $data['uid']    = $rs['uid'];

        $res = A('Rest2/MobileRecharge')->_refund_cancel($data);
        return $res;
    }


}