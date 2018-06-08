<?php
/**
+----------------------------------------------------------------------
| RestFull API 
+----------------------------------------------------------------------
| 商家处理退款 - 已发货 未收货退货退款
+----------------------------------------------------------------------
| Author: lazycat <673090083@qq.com>
+----------------------------------------------------------------------
*/

namespace Rest\Controller;
class SellerRefund2Controller extends CommonController{
	protected $action_logs = array('accept','notreceipt','reject','accept2');
	/**
	* 同意退款
	* @param string $_POST['openid']	用户openid
	* @param string $_POST['s_no']		订单号		
	* @param string $_POST['r_no']		退款单号
    * @param int    $_POST['address_id'] 卖家退货地址ID,当为退货时，此项必填
	*/
	public function accept(){
        //频繁请求限制,间隔300毫秒
        $this->_request_check();

        //必传参数检查
        $this->need_param=array('openid','s_no','r_no','password_pay','sign');
        //if(isset($_POST['address_id'])) $this->need_param[] = 'address_id';
        $this->_need_param();
        $this->_check_sign();

		$this->check_password_pay(I('post.password_pay'));
		
		$orders=new \Common\Controller\SellerRefund2Controller(['seller_id' => $this->uid,'s_no' => I('post.s_no')]);
		$res=$orders->accept(I('post.'));
		$this->apiReturn($res['code'],array('data'=>$res['data']),1,$res['msg']);
	}

    /**
     * 拒绝退款
     */
	public function notreceipt() {
	    //频繁请求限制,间隔300毫秒
	    $this->_request_check();
	    
	    //必传参数检查
	    $this->need_param=array('openid','s_no','r_no','reason','sign');
	    $this->_need_param();
	    $this->_check_sign();
	    
	    $orders=new \Common\Controller\SellerRefund2Controller(['seller_id' => $this->uid,'s_no' => I('post.s_no')]);
	    $res=$orders->reject(I('post.'));
	    $this->apiReturn($res['code'],array('data'=>$res['data']),1,$res['msg']);
	}
	
	
    /**
    * 拒绝退款
    * @param string $_POST['openid']    用户openid
    * @param string $_POST['s_no']      订单号     
    * @param string $_POST['r_no']      退款单号        
    * @param string $_POST['reason']    拒绝理由        
    */
    public function reject(){
        //频繁请求限制,间隔300毫秒
        $this->_request_check();

        //必传参数检查
        $this->need_param=array('openid','s_no','r_no','reason','sign');
        $this->_need_param();
        $this->_check_sign();
        
        $orders=new \Common\Controller\SellerRefund2Controller(['seller_id' => $this->uid,'s_no' => I('post.s_no')]);
        $res=$orders->reject(I('post.'));
        $this->apiReturn($res['code'],array('data'=>$res['data']),1,$res['msg']);
    }

    /**
    * 已收到退货且无异议，同意退款
    * @param string $_POST['openid']    用户openid
    * @param string $_POST['s_no']      订单号     
    * @param string $_POST['r_no']      退款单号  
    * @param string $_POST['password_pay']      安全密码  
    */
    public function accept2(){
        //频繁请求限制,间隔300毫秒
        $this->_request_check();

        //必传参数检查
        $this->need_param=array('openid','s_no','r_no','password_pay','sign');
        $this->_need_param();
        $this->_check_sign();
       
        $this->check_password_pay(I('post.password_pay'));

        $orders=new \Common\Controller\SellerRefund2Controller(['seller_id' => $this->uid,'s_no' => I('post.s_no')]);
        $res=$orders->accept2(I('post.r_no'));
        $this->apiReturn($res['code'],array('data'=>$res['data']),1,$res['msg']);
    }

	/**
	* 退款详情
	* @param string $_POST['openid']	用户openid	
	* @param string $_POST['r_no']	退款单号
	*/
	public function view(){
        //频繁请求限制,间隔300毫秒
        $this->_request_check();

        //必传参数检查
        $this->need_param=array('openid','r_no','sign');
        $this->_need_param();
        $this->_check_sign();

        $type_name	=['','退货并退款','只退款','只退运费'];
        $status_name=[1=>'退款中',2=>'卖家拒绝退款',3=>'买家修改退款',4=>'卖家同意退款退货',5=>'买家寄回退货',20=>'取消退款',100=>'退款成功'];

        $do=D('Common/RefundRelation');
        $rs=$do->relation(['uid','orders_goods'])->where(['r_no' => I('post.r_no'),'seller_id' => $this->uid])->field('etime,ip',true)->find();

        if(!$rs) $this->apiReturn(3);
        $rs['can_money']    =number_format($rs['money'] - $rs['activity_money'], 2);
        $rs['orders_goods']	=imgsize_list($rs['orders_goods'],'images',160);
        $rs['type_name']	=$type_name[$rs['type']];
        $rs['status_name']	=$status_name[$rs['status']];        
        $this->apiReturn(1,['data' => $rs]);
	}

	/**
	* 列出某订单中申请退款的商品
	* @param string $_POST['openid']	用户openid	
	* @param string $_POST['s_no']		订单号	
	*/
	public function item_list(){
        //频繁请求限制,间隔300毫秒
        $this->_request_check();

        //必传参数检查
        $this->need_param=array('openid','s_no','sign');
        $this->_need_param();
        $this->_check_sign();	

        $type_name	=['','退货并退款','只退款','只退运费'];
        $status_name=[1=>'退款中',2=>'卖家拒绝退款',3=>'买家修改退款',4=>'卖家同意退款退货',5=>'买家寄回退货',20=>'取消退款',100=>'退款成功'];

        $do=D('Common/RefundRelation');
        $list=$do->relation(['uid','orders_goods'])->where(['s_no' => I('post.s_no'),'seller_id' => $this->uid])->field('etime,ip',true)->select();

        if(!$list) $this->apiReturn(3);

        foreach($list as $i => $val){
	        $list[$i]['orders_goods']	=imgsize_list($val['orders_goods'],'images',160);
	        $list[$i]['type_name']		=$type_name[$val['type']];
	        $list[$i]['status_name']	=$status_name[$val['status']];           	
        }
     
        $this->apiReturn(1,['data' => $list]);        	
	}	

    /**
    * 退款商品列表
	* @param string $_POST['openid']	用户openid    
    */
    public function refund_list(){
        //频繁请求限制,间隔2秒
        $this->_request_check();

        //必传参数检查
        $this->need_param=array('openid','sign');
        $this->_need_param();
        $this->_check_sign();

        $type_name	=['','退货并退款','只退款','只退运费'];
        $status_name=[1=>'退款中',20=>'取消退款',100=>'退款成功'];

        $map['seller_id']	= $this->uid;
        $pagesize=I('post.pagesize')?I('post.pagesize'):12;        

        $order=I('post.order')?I('post.order'):'id desc';
        if(I('post.sort')){
            $order=str_replace('-', ' ', I('post.sort'));
        }

        $pagelist=pagelist(array(
                'table'     		=>'Common/RefundRelation',
                'do'        		=>'D',
                'map'       		=>$map,
                'order'     		=>'atime desc',
                //'fields'    =>'',
                'order'     		=>$order,
                'pagesize'  		=>$pagesize,
                'relation'  		=>['uid','orders_goods'],
                'action'            =>I('post.action'),
                'query'             =>I('post.query')?query_str_(I('post.query')):'',
                'p'                 =>I('post.p'),
				//'cache_name'        =>md5(implode(',',$_POST).__SELF__),
                //'cache_time'        =>C('CACHE_LEVEL.L'),

            ));


        if($pagelist['list']){
        	foreach($pagelist['list'] as $i => $val){
		        $pagelist['list'][$i]['orders_goods']	=imgsize_list($val['orders_goods'],'images',160);
		        $pagelist['list'][$i]['shop']			=imgsize_list($val['shop'],'shop_logo',100);
		        $pagelist['list'][$i]['type_name']		=$type_name[$val['type']];
		        $pagelist['list'][$i]['status_name']	=$status_name[$val['status']];        		
        	}

            $this->apiReturn(1,array('data' => $pagelist));
        }else{
            $this->apiReturn(3);
        }
    }	

}