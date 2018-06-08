<?php
/**
+----------------------------------------------------------------------
| RestFull API 
+----------------------------------------------------------------------
| 订单中各流程的超时处理
| 数据多的情况下要用队列来处理
+----------------------------------------------------------------------
| Author: lazycat <673090083@qq.com>
+----------------------------------------------------------------------
*/
namespace Rest\Controller;
use Wap\Controller\CommonController;
use Common\Controller\OrdersExpireActionController;
set_time_limit(0);  //不限时处理
class CronController extends OrdersExpireActionController {
    public function _initialize() {
        parent::_initialize();

        //由于请求接口需要认证身份，所以此项是必须的
        if(IS_POST){
            $_POST = array_merge($_POST,C('cfg.api'));
        }else $_POST=C('cfg.api');
    }


    /**
    * 获取任务item
    */
    public function item(){
        $type = I('post.type');
        if(empty($type)) exit();
        
        $list=$this->$type();
        echo json_encode($list);
    }

    /**
    * 执行任务
    */
    public function job(){
        switch (I('post.type')) {
            //关闭超时未付款的订单
            case 'buyer_add_orders': 
                $res = $this->_close(I('post.val'));
            break;
            
            //买家长时间未确认收货，默认自动确认收货
            case 'buyer_confirm_orders':
                $res = $this->_confirm_orders(I('post.val'));
            break;

            //未发货退款，卖家长时间未响应，默认同意退款
            case 'seller_send_express':
                $res = $this->_refund_accept(I('post.val'));
            break;

            //已发货，退款，卖家长时间未响应，默认同意退款
            case 'buyer_refund_add':
                $res = $this->_refund_accept2(I('post.val'));
            break;

            //退款被拒绝后买家长时间未响应，默认取消退款
            case 'seller_not_accept':
                $res = $this->_refund_cancel(I('post.val'));
            break;

            //修改退款后卖家长时间未响应，默认为同意退款
            case 'buyer_refund_edit':
                $res = $this->_refund_accept2(I('post.val'));
            break;

            //卖家同意退货，买家长时间未响应默认取消退款
            case 'seller_accept':
                $res = $this->_refund_cancel(I('post.val'));
            break;

            //买家寄回退货，卖家长时间未确认，默认无异议自动退款
            case 'buyer_send_express':
                $res = $this->_refund_accept3(I('post.val'));
            break;
        }

        echo json_encode($res);
    }





    public function index(){
        $this->close();
        $this->confirm_orders();
        $this->refund_accept();
        $this->refund_accept2();
        $this->refund_cancel();
        $this->refund_accept3();
        $this->refund_cancel2();
        $this->refund_accept4();
    }
    /**
    * 关闭超时未付款的订单
    */
    public function close(){
        $list=$this->buyer_add_orders();
        //dump($list);
        foreach($list as $val){
            dump($val);
            //$this->_close($val);
            usleep(100);
        }
    }

    /**
    * 买家长时间未确认收货，默认自动确认收货
    */
    public function confirm_orders(){
        $list=$this->buyer_confirm_orders();
        foreach($list as $val){
            dump($val);
            //$this->_confirm_orders($val);
            usleep(100);
        }        
    }

    /**
    * 未发货退款，卖家长时间未响应，默认同意退款
    */
    public function refund_accept(){        
        $list=$this->seller_send_express();
        foreach($list as $val){
            dump($val);
            //$this->_refund_accept($val);
            usleep(100);
        }        
    }

    /**
    * 已发货，退款，卖家长时间未响应，默认同意退款
    */
    public function refund_accept2(){
        $list=$this->buyer_refund_add();
        foreach($list as $val){
            dump($val);
            //$this->_refund_accept2($val);
            usleep(100);
        }           
    }

    /**
    * 退款被拒绝后买家长时间未响应，默认取消退款
    */
    public function refund_cancel(){
        $list=$this->seller_not_accept();
        foreach($list as $val){
            dump($val);
            //$this->_refund_cancel($val);
            usleep(100);
        }   
    }

    /**
    * 修改退款后卖家长时间未响应，默认为同意退款
    */
    public function refund_accept3(){
        $list=$this->buyer_refund_edit();
        foreach($list as $val){
            dump($val);
            //$this->_refund_accept2($val);
            usleep(100);
        }           
    }  

    /**
    * 卖家同意退货，买家长时间未响应默认取消退款
    */
    public function refund_cancel2(){
        $list=$this->seller_accept();
        foreach($list as $val){
            dump($val);
            //$this->_refund_cancel($val);
            usleep(100);
        }           
    }  

    /**
    * 买家寄回退货，卖家长时间未确认，默认无异议自动退款
    */
    public function refund_accept4(){
        $list=$this->buyer_send_express();
        foreach($list as $val){
            dump($val);
            //$this->_refund_accept3($val);
            usleep(100);
        }           
    }  


}