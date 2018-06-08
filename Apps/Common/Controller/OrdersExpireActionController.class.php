<?php
/**
* 处理订单中超时未操作的流程
*/
namespace Common\Controller;
use Xs\xs;
use Xs\XSDocument;
import('Vendor.Xs.xs');
class OrdersExpireActionController extends OrdersExpireController {
    protected $project_goods    = 'goodscfg';   //迅搜商品项目
    protected $project_shop     = 'shop';       //迅搜店铺项目
    protected $project_keywords = 'keywords';   //迅搜关键词项目
    /**
    * 关闭超时未付款的订单
    */
    public function _close($s_no){
        $rs=M('orders_shop')->where(['s_no' => $s_no,'status' => 1,'next_time' => ['lt',date('Y-m-d H:i:s')]])->field('status')->find();
        if(!$rs) return ['code' => 100,'msg' => '错误的订单状态！'];  //状态不对，返回100后队列将会清除

        $orders= new OrdersController(['s_no' => $s_no]);
        $res=$orders->b_orders_shop_close('付款超时',0,1);
        if($res['code'] != 1) M('orders_shop')->where(['s_no' => $s_no])->setInc('is_problem',1);   //更新失败次数        //log_add('cron_orders',['atime'=>date('Y-m-d H:i:s'),'s_no'=>$s_no,'res'=>$res['code'],'function'=>__FUNCTION__]);
        return $res;
    }

    /**
    * 买家长时间未确认收货，默认自动确认收货
    */
    public function _confirm_orders($s_no){
        @set_time_limit(30);
        $rs=M('orders_shop')->where(['s_no' => $s_no,'status' => 3,'next_time' => ['lt',date('Y-m-d H:i:s')]])->field('status,uid')->find();
        if(!$rs) return ['code' => 100,'msg' => '错误的订单状态！'];  //状态不对，返回100后队列将会清除

        $res=A('Rest/Erp')->orders_confirm_auto($s_no,$rs['uid'],1);
        if($res['code'] !=1){   //ERP可能超时的弥补
            $status = A('Rest/Erp')->_check_orders_status($s_no);

            if($status->info->o_orderState > 2) {
                $res = $this->_orders_confirm_fix($s_no);
                if($res['code'] != 1) M('orders_shop')->where(['s_no' => $s_no])->setInc('is_problem',1);   //更新失败次数
                $res['msg'] = '与ERP状态不一致或ERP返回超时，已自动执行修复操作！';
            }else{
                $res = ['code' => 0,'msg' => '与ERP中的订单状态不一致！'];
            }

        }



        //log_add('cron_orders',['atime'=>date('Y-m-d H:i:s'),'s_no'=>$s_no,'res'=>$res['code'],'function'=>__FUNCTION__]);
        return $res;
    }

    /**
    * 未发货退款，卖家长时间未响应，默认同意退款
    */
    public function _refund_accept($r_no){
        $rs=M('refund')->where(['r_no' => $r_no,'status' => 1,'orders_status' => 2,'next_time' => ['lt',date('Y-m-d H:i:s')]])->field('seller_id,s_no')->find();
        if(!$rs) return ['code' => 100,'msg' => '错误的退款状态！'];  //状态不对，返回100后队列将会清除

        $orders= new SellerRefundController(['s_no' => $rs['s_no'],'seller_id'=>$rs['seller_id']]);
        $res=$orders->accept(['r_no' => $r_no],0,1);
        if($res['code'] != 1) M('refund')->where(['r_no' => $r_no])->setInc('is_problem',1);   //更新失败次数

        //log_add('cron_orders',['atime'=>date('Y-m-d H:i:s'),'r_no'=>$r_no,'res'=>$res['code'],'function'=>__FUNCTION__]);
        return $res;
    }

    /**
    * 已发货，退款，卖家长时间未响应，默认同意退款
    */
    public function _refund_accept2($r_no){
        $rs=M('refund')->where(['r_no' => $r_no,'status' => ['in','1,3'],'orders_status' => 3,'next_time' => ['lt',date('Y-m-d H:i:s')]])->field('seller_id,s_no')->find();
        if(!$rs) return ['code' => 100,'msg' => '错误的退款状态！'];  //状态不对，返回100后队列将会清除

        $orders= new SellerRefund2Controller(['s_no' => $rs['s_no'],'seller_id'=>$rs['seller_id']]);
        $res=$orders->accept(['r_no' => $r_no],0,1);
        if($res['code'] != 1) M('refund')->where(['r_no' => $r_no])->setInc('is_problem',1);   //更新失败次数

        //log_add('cron_orders',['atime'=>date('Y-m-d H:i:s'),'r_no'=>$r_no,'res'=>$res['code'],'function'=>__FUNCTION__]);
        return $res;        
    }

    /**
    * 退款被拒绝后买家长时间未响应，默认取消退款
    */
    public function _refund_cancel($r_no){
        $rs=M('refund')->where(['r_no' => $r_no,'status' => ['in','2,4'],'orders_status' => 3,'next_time' => ['lt',date('Y-m-d H:i:s')]])->field('uid,s_no')->find();
        if(!$rs) return ['code' => 100,'msg' => '错误的退款状态！'];  //状态不对，返回100后队列将会清除

        $orders= new Refund2Controller(['s_no' => $rs['s_no'],'uid'=>$rs['uid']]);
        $res=$orders->cancel($r_no,0,1);
        if($res['code'] != 1) M('refund')->where(['r_no' => $r_no])->setInc('is_problem',1);   //更新失败次数

        //log_add('cron_orders',['atime'=>date('Y-m-d H:i:s'),'r_no'=>$r_no,'res'=>$res['code'],'function'=>__FUNCTION__]);
        return $res;
    }

    /**
    * 买家寄回退货，卖家长时间未确认，默认无异议自动退款
    */
    public function _refund_accept3($r_no){
        $rs=M('refund')->where(['r_no' => $r_no,'status' => 5,'orders_status' => 3,'next_time' => ['lt',date('Y-m-d H:i:s')]])->field('seller_id,s_no')->find();
        if(!$rs) return ['code' => 100,'msg' => '错误的退款状态！'];  //状态不对，返回100后队列将会清除


        $orders= new SellerRefund2Controller(['s_no' => $rs['s_no'],'seller_id'=>$rs['seller_id']]);
        $res=$orders->accept2($r_no,0,1);
        if($res['code'] != 1) M('refund')->where(['r_no' => $r_no])->setInc('is_problem',1);   //更新失败次数

        //log_add('cron_orders',['atime'=>date('Y-m-d H:i:s'),'r_no'=>$r_no,'res'=>$res['code'],'function'=>__FUNCTION__]);
        return $res;
    }

    /**
    * 超时未评价的订单
    */
    public function _buyer_rate($s_no){
        $rs=M('orders_shop')->where(['s_no' => $s_no,'status' => 4,'next_time' => ['lt',date('Y-m-d H:i:s')]])->field('status,uid')->find();
        if(!$rs) return ['code' => 100,'msg' => '错误的订单状态！']; //状态码!=4是，表示这个状态下不能评价，返回1后队列将会清除

        $n=0;
        //商品评价
        $goods = A('Rest/Orders')->_wait_rate_godos($rs['uid'],$s_no);
        foreach($goods as $val){
            $orders=new \Common\Controller\OrdersController(array('uid'=>$rs['uid']));
            $data = [
                'orders_goods_id'   =>$val['id'],
                'rate'              =>1,
                'content'           =>'默认好评！',
                'is_anonymous'      =>1,
                'is_sys'            =>1
            ];
            $res=$orders->b_goods_rate($data);
            if($res['code'] ==1) $n++;


        }

        //对卖家评价
        $data   = [
            'fraction_speed'    =>5,
            'fraction_service'  =>5,
            'fraction_desc'     =>5,
            'content'           =>'默认好评！',
            'is_sys'            =>1
        ];
        $orders=new \Common\Controller\OrdersController(array('s_no'=>$s_no,'uid'=>$rs['uid']));
        $res=$orders->b_shop_rate($data);
        if($res['code'] ==1) $n++;

        //log_add('cron_orders',['atime'=>date('Y-m-d H:i:s'),'s_no'=>$s_no,'res'=>$n,'function'=>__FUNCTION__]);

        if($n > 0) return ['code' => 1];
        else {
            M('orders_shop')->where(['s_no' => $s_no])->setInc('is_problem',1);   //更新失败次数
            return ['code' => 0];
        }
    }


    /**
    * 商品主图检测
    * @param int $id 商品ID
    */
    public function _goods_images_check($id){
        $do = M('goods');
        $rs = $do->where('id='.$id)->field('images')->find();

        $tmp=get_headers($rs['images']);
        if (preg_match("/200/", $tmp[0])){
            //文件存在
        }else{
            $do->where('id='.$id)->setField('status',3);
            log_add('cron_orders',['atime'=>date('Y-m-d H:i:s'),'goods_id'=>$id,'res'=>1,'function'=>__FUNCTION__]);
        }    

        return ['code' => 1];
    }

    /**
     * 售后 - 买家申请售后，卖家未响应默认拒绝
     */
    public function _service_seller_reject($r_no) {
        $do     =   M('refund');
        $map    =   [
            'r_no'          =>  $r_no,
            'status'        =>  1,
            'orders_status' => ['in','4,5'],
            'next_time'     => ['lt',date('Y-m-d H:i:s')],
        ];
        $rs = $do->where($map)->field('id,r_no,s_no,uid,seller_id,type,num,orders_goods_id')->find();

        if (!$rs) {
            //log_add('cron_service_error',['atime'=>date('Y-m-d H:i:s'),'data'=>$s_no, 'params' => $do->getLastSql(), 'function'=>__FUNCTION__]);
            return ['code' => 100 ,'msg' => '错误的售后状态！'];
        }


        $do=M();
        $do->startTrans();

        if(!$this->sw[]=M('refund')->where(['id' => $rs['id']])->save(['status' => 2,'dotime' => date('Y-m-d H:i:s'),'next_time' => date('Y-m-d H:i:s',time() + C('cfg.orders')['refund_express']),'is_problem' => 0])) goto error;

        //日志数据
        $logs=[
            'r_id'          =>$rs['id'],
            'r_no'          =>$rs['r_no'],
            'uid'           =>$rs['seller_id'],
            'status'        =>2,
            'type'          =>$rs['type'],
            'remark'        =>'卖家响应超时，默认拒绝售后！', //卖家拒绝退货
            'images'        =>'', //拒绝图片
        ];

        if(!$this->sw[]=D('Common/RefundLogs')->create($logs)){
            $msg=D('Common/RefundLogs')->getError();
            goto error;
        }

        if(!$this->sw[]=D('Common/RefundLogs')->add()) goto error;

        $do->commit();
        //log_add('cron_service',['atime'=>date('Y-m-d H:i:s'),'data'=>$logs, 'params' => $rs, 'function'=>__FUNCTION__]);

        return ['code' => 1];

        error:
        $do->rollback();
        M('refund')->where(['r_no' => $r_no])->setInc('is_problem',1);   //更新失败次数
        //log_add('cron_service_error',['atime'=>date('Y-m-d H:i:s'),'data'=>$logs, 'params' => $rs, 'function'=>__FUNCTION__]);
        return ['code' => 0];
    }

    /**
     * 买家长时间不操作则自动设为收货状态
     * @param unknown $param
     */
    public function _service_buyer_confirm($r_no) {
        $do     =   M('refund');
        $map    =   [
            'r_no'          =>  $r_no,
            'status'        =>  6,
            'orders_status' => ['in','4,5'],
            'next_time'     => ['lt',date('Y-m-d H:i:s')],
        ];
        $rs = $do->where($map)->field('id,r_no,s_no,uid,type,num,orders_goods_id')->find();
        if (!$rs) {
            //log_add('cron_service_error',['atime'=>date('Y-m-d H:i:s'),'data'=>$r_no, 'params' => $do->getLastSql(), 'function'=>__FUNCTION__]);
            return ['code' => 100 ,'msg' => '错误的售后状态！'];
        }
        $do->startTrans();
        $sw1    =   $do->where($map)->save(['dotime' => NOW_TIME, 'accept_time' => NOW_TIME, 'status' => 100]); //售后已完成
        if (!$sw1) goto error;
        //日志数据
        $logs   =   [
            'r_id'          =>$rs['id'],
            'r_no'          =>$rs['r_no'],
            'uid'           =>$rs['uid'],
            'status'        =>100,
            'type'          =>$rs['type'],
            'remark'        =>'买家长时间未操作，系统自动操作完成', //买家取消退款！
            'is_sys'        =>1,
        ];
        $sw2    =   M('refund_logs')->add($logs);
        if (!$sw2) goto error;
        $sw3    =   M('orders_shop')->where(['s_no' => $rs['s_no']])->setInc('service_num', $rs['num']);
        if (!$sw3) goto error;
        
        $sw4    =   M('orders_goods')->where(['id' => $rs['orders_goods_id']])->setInc('service_num', $rs['num']);
        if (!$sw4) goto error;
        //log_add('cron_service',['atime'=>date('Y-m-d H:i:s'),'data'=>$logs, 'params' => $rs, 'function'=>__FUNCTION__]);
        $do->commit();
        return ['code' => 1];
        error:
            $do->rollback();
            M('refund')->where(['r_no' => $r_no])->setInc('is_problem',1);   //更新失败次数
            //log_add('cron_service_error',['atime'=>date('Y-m-d H:i:s'),'data'=>$logs, 'params' => $rs, 'function'=>__FUNCTION__]);
            return ['code' => 0];
    }
    
    /**
     * 卖家长时间不操作，则自动设为同意售后
     * @param unknown $param
     */
    public function _service_seller_accept($r_no) {
        $do     =   M('refund');
        $map    =   [
            'r_no'          =>  $r_no,
            'status'        =>  1,
            'orders_status' => ['in','4,5'],
            'next_time'     => ['lt',date('Y-m-d H:i:s')],
        ];
        $rs = $do->where($map)->field('id,s_no,r_no,seller_id,type')->find();
        if (!$rs) {
            //log_add('cron_service_error',['atime'=>date('Y-m-d H:i:s'),'data'=>$r_no, 'params' => $do->getLastSql(), 'function'=>__FUNCTION__]);
            return ['code' => 100 ,'msg' => '错误的售后状态！'];
        }
        $do->startTrans();
        $sw1    =   $do->where($map)->save(['dotime' => NOW_TIME, 'status' => 3]); //售后已完成
        if (!$sw1) goto error;
        //日志数据
        $logs   =   [
            'r_id'          =>$rs['id'],
            'r_no'          =>$rs['r_no'],
            'uid'           =>$rs['seller_id'],
            'status'        =>3,
            'type'          =>$rs['type'],
            'remark'        =>'卖家长时间未操作，系统自动操作同意售后', //买家取消退款！
            'is_sys'        =>1,
        ];
        $sw2    =   M('refund_logs')->add($logs);
        if(!$sw2) goto error;
        //log_add('cron_service',['atime'=>date('Y-m-d H:i:s'),'data'=>$logs, 'params' => $rs, 'function'=>__FUNCTION__]);
        $do->commit();
        return ['code' => 1];
        error:
            $do->rollback();
            M('refund')->where(['r_no' => $r_no])->setInc('is_problem',1);   //更新失败次数
            //log_add('cron_service_error',['atime'=>date('Y-m-d H:i:s'),'data'=>$logs, 'params' => $rs, 'function'=>__FUNCTION__]);
            return ['code' => 0];
    }
    
    /**
     * 卖家长时间不操作，则设为已收到售后商品
     * @param unknown $param
     */
    public function _service_seller_confirm($r_no) {
        $do     =   M('refund');
        $map    =   [
            'r_no'          =>  $r_no,
            'status'        =>  4,
            'orders_status' => ['in','4,5'],
            'next_time'     => ['lt',date('Y-m-d H:i:s')],
        ];
        $rs = $do->where($map)->field('id,s_no,r_no,seller_id,type')->find();
        if (!$rs) {
            //log_add('cron_service_error',['atime'=>date('Y-m-d H:i:s'),'data'=>$r_no, 'params' => $do->getLastSql(), 'function'=>__FUNCTION__]);
            return ['code' => 100 ,'msg' => '错误的售后状态！'];
        }
        $do->startTrans();
        $sw1    =   $do->where($map)->save(['dotime' => NOW_TIME, 'status' => 5]); //售后已完成
        if (!$sw1) goto error;
        //日志数据
        $logs   =   [
            'r_id'          =>$rs['id'],
            'r_no'          =>$rs['r_no'],
            'uid'           =>$rs['seller_id'],
            'status'        =>5,
            'type'          =>$rs['type'],
            'remark'        =>'卖家长时间未操作，系统自动操作收到售后商品', //买家取消退款！
            'is_sys'        =>1,
        ];
        $sw2    =   M('refund_logs')->add($logs);
        if(!$sw2) goto error;
        //log_add('cron_service',['atime'=>date('Y-m-d H:i:s'),'data'=>$logs, 'params' => $rs, 'function'=>__FUNCTION__]);
        $do->commit();
        return ['code' => 1];
        error:
            $do->rollback();
            M('refund')->where(['r_no' => $r_no])->setInc('is_problem',1);   //更新失败次数
            //log_add('cron_service_error',['atime'=>date('Y-m-d H:i:s'),'data'=>$logs, 'params' => $rs, 'function'=>__FUNCTION__]);
            return ['code' => 0];
    }
    
    /**
     * 卖家拒绝售后，买家7天内未操作，则取消售后
     * @param unknown $s_no
     */
    public function _service_seller_refuse($r_no) {
        $do     =   M('refund');
        $map    =   [
            'r_no'          =>  $r_no,
            'status'        =>  2,
            'orders_status' => ['in','4,5'],
            'next_time'     => ['lt',date('Y-m-d H:i:s')],
        ];
        $rs = $do->where($map)->field('id,r_no,s_no,uid,type')->find();
        if (!$rs) {
            //log_add('cron_service_error',['atime'=>date('Y-m-d H:i:s'),'data'=>$r_no, 'params' => $do->getLastSql(), 'function'=>__FUNCTION__]);
            return ['code' => 100 ,'msg' => '错误的售后状态！'];
        }
        $do->startTrans();
        $sw1    =   $do->where($map)->save(['dotime' => NOW_TIME, 'cancel_time' => NOW_TIME, 'status' => 20]); //售后已完成
        if (!$sw1) goto error;
        //日志数据
        $logs   =   [
            'r_id'          =>$rs['id'],
            'r_no'          =>$rs['r_no'],
            'uid'           =>$rs['uid'],
            'status'        =>20,
            'type'          =>$rs['type'],
            'remark'        =>'买家长时间未操作，系统自动操取消售后', //买家取消退款！
            'is_sys'        =>1,
        ];
        $sw2    =   M('refund_logs')->add($logs);
        if(!$sw2) goto error;
        //log_add('cron_service',['atime'=>date('Y-m-d H:i:s'),'data'=>$logs, 'params' => $rs, 'function'=>__FUNCTION__]);
        $do->commit();
        return ['code' => 1];
        error:
        $do->rollback();
        M('refund')->where(['r_no' => $r_no])->setInc('is_problem',1);   //更新失败次数
        //log_add('cron_service_error',['atime'=>date('Y-m-d H:i:s'),'data'=>$logs, 'params' => $rs, 'function'=>__FUNCTION__]);
        return ['code' => 0];
    }
    
    /**
     * 售后买家长时间不发货，则将售后设为关闭
     * @param unknown $param
     */
    public function _service_buyer_express($r_no) {
        $do     =   M('refund');
        $map    =   [
            'r_no'          =>  $r_no,
            'status'        =>  3,
            'orders_status' => ['in','4,5'],
            'next_time'     => ['lt',date('Y-m-d H:i:s')],
        ];
        $rs = $do->where($map)->field('id,r_no,s_no,uid,type')->find();
        if (!$rs) {
            //log_add('cron_service_error',['atime'=>date('Y-m-d H:i:s'),'data'=>$r_no, 'params' => $do->getLastSql(), 'function'=>__FUNCTION__]);
            return ['code' => 100 ,'msg' => '错误的售后状态！'];
        }
        $do->startTrans();
        $sw1    =   $do->where($map)->save(['dotime' => NOW_TIME, 'cancel_time' => NOW_TIME, 'status' => 20]); //售后已完成
        if (!$sw1) goto error;
        //日志数据
        $logs   =   [
            'r_id'          =>$rs['id'],
            'r_no'          =>$rs['r_no'],
            'uid'           =>$rs['uid'],
            'status'        =>20,
            'type'          =>$rs['type'],
            'remark'        =>'买家长时间未操作，系统自动操取消售后', //买家取消退款！
            'is_sys'        =>1,
        ];
        $sw2    =   M('refund_logs')->add($logs);
        if(!$sw2) goto error;
        //log_add('cron_service',['atime'=>date('Y-m-d H:i:s'),'data'=>$logs, 'params' => $rs, 'function'=>__FUNCTION__]);
        $do->commit();
        return ['code' => 1];
        error:
            $do->rollback();
            M('refund')->where(['r_no' => $r_no])->setInc('is_problem',1);   //更新失败次数
            //log_add('cron_service_error',['atime'=>date('Y-m-d H:i:s'),'data'=>$logs, 'params' => $rs, 'function'=>__FUNCTION__]);
            return ['code' => 0];
    }


    /**
    * 商品主图搬家
    * @param int $id 商品ID
    */
    public function _goods_images($id){
        $do = M('goods');
        $rs = $do->where('id='.$id)->field('images')->find();
		
		if(!strstr($rs['images'],'ledui365.net')){
			$res = $this->doApi('/Upload/upload_remote',['url' => $rs['images']]);
			if($res->code == 1){
				$do->where('id='.$id)->setField('images',$res->data->url);
				log_add('remote_images',['atime'=>date('Y-m-d H:i:s'),'goods_id'=>$id,'tables'=>'goods','before_images' => $rs['images'],'change_images' =>$res->data->url,'function'=>__FUNCTION__]);
				return ['code' => 1,'msg' => '搬家成功！'];
			}else{  //失败不记录日志
				return ['code' => 0,'msg' => '搬家失败！'];
			}
		}
        return ['code' => 1,'msg' => '已搬家！'];
    }

    /**
    * 库存主图搬家
    * @param int $id 商品ID
    */
    public function _goods_attr_list_images($id){
        $do = M('goods_attr_list');
        $rs = $do->where('id='.$id)->field('images')->find();
		if(!strstr($rs['images'],'ledui365.net')){
        $res = $this->doApi('/Upload/upload_remote',['url' => $rs['images']]);
			if($res->code == 1){
				$res = $do->where('id='.$id)->setField('images',$res->data->url);
				log_add('remote_images',['atime'=>date('Y-m-d H:i:s'),'goods_id'=>$id,'tables'=>'goods_attr_list','before_images' => $rs['images'],'change_images' =>$res->data->url,'function'=>__FUNCTION__]);
				return ['code' => 1,'msg' => '搬家成功！'];
			}else{  //失败不记录日志
				return ['code' => 0,'msg' => '搬家失败！'];
			}
		}
        return ['code' => 1,'msg' => '已搬家！'];
    }

    /**
     * 库存属性搬家
     * @param int $id 商品ID
     */
    public function _goods_attr_value_images($id){
        $do = M('goods_attr_value');
        $rs = $do->where('id='.$id)->field('attr_images,attr_album')->find();
        if(!strstr($rs['attr_images'],'ledui365.net')){
            $res = $this->doApi('/Upload/upload_remote',['url' => $rs['attr_images']]);

            $attr_album = array();
            if($rs['attr_album']){
                $att_tmp = explode(',',$rs['attr_album']);
                foreach($att_tmp as $val){
                    $tmp = $this->doApi('/Upload/upload_remote',['url' => $val]);
                    if($tmp->code == 1) $attr_album[] = $tmp->data->url;
                }
            }

            if($res->code == 1){
                $res = $do->where('id='.$id)->save(['attr_images' => $res->data->url,'attr_album' => @implode(',',$attr_album)]);
                log_add('remote_images',['atime'=>date('Y-m-d H:i:s'),'goods_id'=>$id,'tables'=>'goods_attr_value','before_images' => $rs['attr_images'],'change_images' =>$res->data->url,'before_album' => $rs['attr_album'],'change_album' =>@implode(',',$attr_album),'function'=>__FUNCTION__]);
                return ['code' => 1,'msg' => '搬家成功！'];
            }else{  //失败不记录日志
                return ['code' => 0,'msg' => '搬家失败！'];
            }
        }
        return ['code' => 1,'msg' => '已搬家！'];
    }


    /**
     * 当商品下架或删除时直接从索引中删除，每隔一分钟执行一次
     */
    public function _xs_remove_goods_index(){
        $xs = new xs($this->project_goods);
        $index = $xs->index;

        $map['_string'] = '(status!=1 and etime>"'.date('Y-m-d H:i:s',time() - $this->expire_time).'") or (status=1 and etime>"'.date('Y-m-d H:i:s',time() - $this->expire_time).'" and num=0)';

        $do = M('goods');
        $count = $do->where($map)->count();

        $pagesize = 100;
        $page = ceil($count / $pagesize);

        for($i=0;$i<$page;$i++){
            $ids = $do->where($map)->page($i)->limit($pagesize)->getField('id',true);
            $index->del($ids);
        }
        return true;
    }


    /**
     * 当店铺暂停营或关闭时直接从索引中删除，每隔一分钟执行一次
     */
    public function _xs_remove_shop_index(){
        $xs = new xs($this->project_shop);
        $index = $xs->index;

        $map['_string'] = '(status!=1 and etime>"'.date('Y-m-d H:i:s',time() - $this->expire_time).'") or (status=1 and etime>"'.date('Y-m-d H:i:s',time() - $this->expire_time).'" and goods_num=0)';

        $do = M('shop');
        $count = $do->where($map)->count();

        $pagesize = 100;
        $page = ceil($count / $pagesize);

        for($i=0;$i<$page;$i++){
            $ids = $do->where($map)->page($i)->limit($pagesize)->getField('id',true);
            $index->del($ids);
        }
        return ['code' => 1];
    }

    /**
     * 获取最近新增或更新商品的记录
     */
    public function _goods_to_index($id){
        $xs = new xs($this->project_goods);
        $index = $xs->index;
        $doc = new XSDocument;

        $res = $this->goods_item($id);

        if($res['code'] == 1){
            $doc->setFields($res['data']);
            $index->update($doc);
        }else {
            $index->del($id);
        }

        return ['code' => 1];
    }


    /**
     * 获取最近新增或更新的店铺记录
     */
    public function _shop_to_index($id){
        $xs = new xs($this->project_shop);
        $index = $xs->index;
        $doc = new XSDocument;

        $res = $this->shop_item($id);
        if($res['code'] == 1) {
            $doc->setFields($res['data']);
            $index->update($doc);
        }else{
            $index->del($id);
        }


        return ['code' => 1];
    }


    /**
     * 获取商品信息用于加入索引
     */
    public function goods_item($id){
        $do = D('Common/GoodsRelation');
        $field = 'id,atime,status,category_id,goods_name,sub_name,brand_id,images,shop_id,uptime,price,price_max,num,sale_num,rate_num,fav_num,view,seller_id,fraction,score_ratio,is_best,is_love,code,free_express,is_self,express_tpl_id,activity_id,officialactivity_join_id,officialactivity_price,pr,pr_extra,is_daigou,is_display';
        $rs = $do->relation(true)->where(['id' => $id])->field($field)->find();

        if($rs['status'] != 1 || $rs['is_display'] != 1 || $rs['num'] <1 || $rs['shop']['status'] != 1) return ['code' => 0,'msg' => '商品处于异常状态！'];

        //dump($rs);
        $data  = [];
        $field = explode(',',$field);
        foreach($field as $val){
            $data[$val] = $rs[$val];
        }
        $data['pr'] += $rs['pr_extra'];
        $data['pr'] += (time() - strtotime($data['uptime']))/86400;
        //$data['atime']  = strtotime($data['atime']);
        $data['category_name']  = nav_sort(['table' => 'goods_category','icon' => ',','field' => 'id,sid,category_name','id' => $rs['category_id'],'key' => 'category_name','cache_name' => 'nav_sort_goods_category_'.$rs['category_id']]);

        if($rs['brand_id'] > 0) $data['brand_name'] = M('brand')->where(['id' => $rs['brand_id']])->getField('b_name');
        else $data['brand_name'] = '其它';

        //$area = $this->cache_table('area');
        $express = M('express_tpl')->where(['id' => $rs['express_tpl_id']])->field('province,city')->find();
        $area = M('area')->cache(true)->where(['id' => ['in',[$express['province'],$express['city']]]])->getField('id,a_name',true);
        $data['city']   = $area[$express['province']] . ' ' . $area[$express['city']];
        $data['city_id']= $express['city'];

        //类目
        $upsid = upsid(['table' => 'goods_category','id' => $rs['category_id']]);
        $data['first_category_id']  = $upsid[0];
        $data['second_category_id'] = $upsid[1];
        //$data['three_category_id']  = $upsid[2];

        //属性
        foreach ($rs['attr_list'] as $val){
            $data['attr'][]         = $val['attr'];
            $data['attr_id'][]      = $val['attr_id'];
            $data['attr_name'][]    = $val['attr_name'];
        }
        $data['attr']       = implode(',',$data['attr']);
        $data['attr_id']    = implode(',',$data['attr_id']);
        $data['attr_name']  = implode(',',$data['attr_name']);
        $data['attr_list']  = serialize($rs['attr_list']);

        $data['url']        = C('sub_domain.item').'/goods/' . $rs['attr_list'][0]['id'] . '.html';

        //参数
        $data['option']     = M('goods_param')->cache(true)->where(['goods_id' => $id])->getField('param_value',true);
        $data['option']     = @implode(' ',$data['option']);

        //店铺信息
        $shop_type = $this->cache_table('shop_type');
        $data['shop_id']    = $rs['shop']['id'];
        $data['nick']       = $rs['seller']['nick'];
        $data['shop_name']  = $rs['shop']['shop_name'];
        $data['shop_url']   = shop_url($rs['shop']['id'],$rs['shop']['domain']);
        $data['qq']         = $rs['shop']['qq'];
        $data['type_id']    = $rs['shop']['type_id'];
        $data['type_name']  = $shop_type[$data['type_id']];

        //个人店铺商品PR降15分
        if($data['type_id'] == 6) $data['pr']   -= 15;

        //dump($data);
        return ['code' => 1,'data' => $data,'msg' => '获取资料成功！'];

    }

    /**
     * 店铺详情
     */
    public function shop_item($id){
        $do = M('shop');
        $rs = $do->where(['id' => $id])->field('id,status,uid,shop_name,shop_level,shop_logo,about,scope,type_id,province,city,domain,qq,mobile,fav_num,pr,goods_num,sale_num,fraction_speed,fraction_service,fraction_desc,fraction')->find();

        if($rs['status'] != 1){
            return ['code' => 0,'msg' => '店铺不在正常营业状态！'];
        }

        $area = M('area')->cache(true)->where(['id' => ['in',[$rs['province'],$rs['city']]]])->getField('id,a_name',true);
        $rs['city_name']    = $area[$rs['province']] . ' ' . $area[$rs['city']];

        $shop_type = $this->cache_table('shop_type');
        $rs['type_name']    = $shop_type[$rs['type_id']];

        return ['code' => 1,'data' => $rs,'msg' => '获取资料成功！'];
    }

    /**
     * 促销活动结束
     */
    public function _activity_over($id) {
        $code = 0;
        $map  = [
            'id'    => $id,
            'status'=> 1,
        ];
        $data = [
            'status'    => 2,
            'over_time' => date('Y-m-d H:i:s', NOW_TIME),
            'is_sys'    => 1,
        ];
        $do = M('activity');
        if ($do->where($map)->save($data)) {
            $code = 1;
        }
        return ['code' => $code];
    }

    /**
     * 促销活动开始
     */
    public function _activity_start($id) {
        $code = 0;
        $map  = [
            'id'    => $id,
            'status'=> 0,
        ];
        $do = M('activity');
        if ($do->where($map)->setInc('status', 1)) {
            $code = 1;
        }
        return ['code' => $code];
    }

    /**
     * 优惠券批次结束
     */
    public function _coupon_batch_over($id) {
        $code = 0;
        $map  = [
            'id'    => $id,
            'status'=> 1,
        ];

        $data = [
            'status'=> 2,
            'is_sys'=> 1,
        ];

        $do = M('coupon_batch');
        if ($do->where($map)->save($data)) $code = 1;
        return ['code' => $code];
    }

    /**
     * 未支付订单检测
     * @param $id s_no
     */
    public function _orders_nopay_check($id) {
        $code = 0;
        //erp code...
        $ret  = $this->curl('/Erp/check_orders_status',['s_no' => $id], 1);
        if ($ret['code'] == 1) {
            $resData = [];
            $resData = $ret['data'];
            log_add('check_orders', array_merge($resData, ['atime' => date('Y-m-d H:i:s', NOW_TIME), 's_no' => $id]));
            if ($resData['o_orderState'] == 1) {
                $map  = [
                    'status'    =>  1,
                    's_no'      =>  $id,
                ];
                $data = [
                    'pay_price' =>  $resData['o_totalMoney'],
                    'score'     =>  $resData['o_totalScore'],
                    'is_pay'    =>  1,
                    'status'    =>  2,
                    'pay_time'  =>  date('Y-m-d H:i:s', NOW_TIME),
                    'pay_type'  =>  $resData['o_payType'],
                ];
                $do   = M('orders_shop');
                $flag = $do->where($map)->save($data);
                if ($flag) {
                    $code = 1;
                }
            } else {
                $code = 1;
            }
        } else if ($ret['code'] == 0) {
            $code = 1;
        }
        return ['code' => $code];
    }

    /**
     * 未确认收货订单检测
     * @param $id s_no
     */
    public function _orders_accept_check($id) {
        $code = 0;
        $ret  = $this->curl('/Erp/check_orders_status',['s_no' => $id], 1);
        if ($ret['code'] == 1) {
            $resData = [];
            $resData = $ret['data'];
            log_add('check_orders', array_merge($resData, ['atime' => date('Y-m-d H:i:s', NOW_TIME), 's_no' => $id]));
            if ($resData['o_orderState'] > 2) {
                $map  = [
                    'status'    =>  3,
                    's_no'      =>  $id,
                ];
                $data = [
                    'status'        =>  4,
                    'receipt_time'  =>  date('Y-m-d H:i:s', NOW_TIME),
                ];
                $do   = M('orders_shop');
                $flag = $do->where($map)->save($data);
                if ($flag) {
                    $code = 1;
                }
            } else {
                $code = 1;
            }
        } else if ($ret['code'] == 0) {
            $code = 1;
        }
        return ['code' => $code];
    }

    /**
     * 店铺每日统计
     * Create by Lazycat
     * 2017-03-22
     */
    public function _shop_total($shop_id){
        $t      = new TotalsController();
        $res    = $t->shop_totals($shop_id);
        return ['code' => 1,'msg' => '店铺[ID='.$shop_id.']已统计'];
    }

    /**
     * 确认收货订单修复，当ERP接口返回超时时需要执行此方法进行弥补
     * @param array $ors  订单资料
     * @param int $is_sys   是否系统自动执行
     * @return array
     */
    public function _orders_confirm_fix($s_no,$is_sys=1){
        $ors = M('orders_shop')->where(['s_no' => $s_no,'status' => 3])->field('id,o_id,o_no,s_no,uid,seller_id')->find();
        if(empty($ors)) return ['code' => 0,'msg' => '错误的订单状态！'];

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
        if(!$this->sw[] = M('orders_shop')->where(['id' => $ors['id']])->save(['status' => 4,'receipt_time' => date('Y-m-d H:i:s'),'next_time' => date('Y-m-d H:i:s',time() + C('cfg.orders')['rate_add']),'is_problem' => 0])){
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
        return ['code' => 1,'msg' => '确认收货成功！'];

        error:
        $do->rollback();
        return ['code' => 0,'msg' => !empty($msg) ? $msg : '确认收货失败！'];

    }
}