<?php
/**
+----------------------------------------------------------------------
| RestFull API 
+----------------------------------------------------------------------
| 卖家订单管理
+----------------------------------------------------------------------
| Author: lazycat <673090083@qq.com>
+----------------------------------------------------------------------
*/

namespace Rest\Controller;
use Think\Controller\RestController;
class SupplierOrdersController extends CommonController {
	protected $action_logs = array('send_express','close','edit_price','orders_price_edit');
    public function index(){
    	redirect(C('sub_domain.www'));
    }

	/**
     * 获取卖家信息
     * Create by liangfeng
     * 2017-09-15
     */
	private function get_seller(){
		$id = C('cfg.supplier')['seller_id'];
		$rs = M('user')->cache(true)->field('id,level_id,nick,password_pay,is_auth,shop_type,erp_uid,shop_id')->find($id);
		return $rs;
	}
	
    /**
    * 卖家发货
    * @param string $_POST['openid']    用户openid
    * @param string $_POST['s_no']      订单号    
    * @param int    $_POST['express_company_id']    快递公司ID，如更改快递公司，传入此项
    * @param string $_POST['express_code']    快递单号
    * @param string $_POST['express_remark'] 发货备注
    */
    public function send_express(){
         //频繁请求限制,间隔300毫秒
        $this->_request_check();

        //必传参数检查
        $this->need_param=array('openid','s_no','express_code','express_company_id','sign');
        $this->_need_param();
        $this->_check_sign();

		$seller_info = $this->get_seller();
        //商家必须先设置过发货地址方可发货
//        $do=M('send_address');
//        if(!$do->where(array('uid'=>$this->uid))->field('id')->find()){
//            $this->apiReturn(206);
//        }

        $orders=new \Common\Controller\SellerOrdersController(array('s_no'=>I('post.s_no'),'seller_id'=>$seller_info['id']));
        $res=$orders->send_express(I('post.'));
        $this->apiReturn($res['code'],array('data'=>$res['data']),1,$res['msg']);
    }

    /**
     * 修改快递单号（未确认收货前可以修改）
     * @param string $_POST['openid']    用户openid
     * @param string $_POST['s_no']      订单号
     * @param int    $_POST['express_company_id']    快递公司ID，如更改快递公司，传入此项
     * @param string $_POST['express_code']    快递单号
     * @param string $_POST['express_remark'] 发货备注
     */

    public function edit_express(){
        //频繁请求限制,间隔300毫秒
        $this->_request_check();

        //必传参数检查
        $this->need_param=array('openid','s_no','express_code','express_company_id','sign');
        $this->_need_param();
        $this->_check_sign();
		
		$seller_info = $this->get_seller();

        //商家必须先设置过发货地址方可发货
//        $do=M('send_address');
//        if(!$do->where(array('uid'=>$this->uid))->field('id')->find()){
//            $this->apiReturn(206);
//        }

        $orders=new \Common\Controller\SellerOrdersController(array('s_no'=>I('post.s_no'),'seller_id'=>$seller_info['id']));
        $res=$orders->edit_express(I('post.'));
        $this->apiReturn($res['code'],array('data'=>$res['data']),1,$res['msg']);
    }

    /**
     * 获取订单发货快递
     * @param string $_POST['s_no'] 订单号
     * @param string $_POST['openid'] 卖家openid
     */
    public function get_orders_express(){
        //频繁请求限制,间隔300毫秒
        //$this->_request_check();

        //必传参数检查
        $this->need_param=array('openid','s_no','sign');
        $this->_need_param();
        $this->_check_sign();

        $rs = M('orders_shop')->where(['seller_id' => $this->uid,'s_no' => I('post.s_no')])->field('id,s_no,express_company_id,express_company,express_code,express_remark')->find();

        if($rs['express_company_id']) $this->apiReturn(1,['data' => $rs]);
        else $this->apiReturn(3);
    }

    /**
    * 卖家关闭订单
    * @param string $_POST['openid']    用户openid
    * @param string $_POST['s_no']      订单号    
    * @param string $_POST['reason']    关闭原因  
    */
    public function close(){
         //频繁请求限制,间隔300毫秒
        $this->_request_check();

        //必传参数检查
        $this->need_param=array('openid','s_no','reason','sign');
        $this->_need_param();
        $this->_check_sign();
        $seller_info = $this->get_seller();
        $orders=new \Common\Controller\SellerOrdersController(array('s_no'=>I('post.s_no'),'seller_id'=>$seller_info['id']));
        $res=$orders->s_close(I('post.reason'));
        $this->apiReturn($res['code'],array('data'=>$res['data']),1,$res['msg']);        

    }

    /**
    * 卖家改价，此方法已作废
    */
    public function edit_price(){
         //频繁请求限制,间隔300毫秒
        $this->_request_check();

        //必传参数检查
        $this->need_param=array('openid','s_no','pay_price','sign');
        $this->_need_param();
        $this->_check_sign();  

        $orders=new \Common\Controller\SellerOrdersController(array('s_no'=>I('post.s_no'),'seller_id'=>$this->uid));
        $res=$orders->edit_price(I('post.pay_price'));
        $this->apiReturn($res['code'],array('data'=>$res['data']),1,$res['msg']);        
    }

    /**
    * 卖家订单统计
    * @param string $_POST['openid']    用户openid    
    */
    public function orders_count(){
         //频繁请求限制,间隔300毫秒
        $this->_request_check();

        //必传参数检查
        $this->need_param=array('openid','sign');
        $this->_need_param();
        $this->_check_sign();  

        $orders=new \Common\Controller\SellerOrdersController(array('seller_id'=>$this->uid));
        $res=$orders->s_count();
        $this->apiReturn($res['code'],array('data'=>$res['data']),1,$res['msg']);
    }   

	/**
	* 获取订单处理日志
    * @param string $_POST['openid']    用户openid
    * @param string $_POST['s_no']      订单号
	*/
	public function orders_logs(){
        //频繁请求限制,间隔300毫秒
        $this->_request_check();

        //必传参数检查
        $this->need_param=array('openid','s_no','sign');
        $this->_need_param();
        $this->_check_sign();

		$seller_info = $this->get_seller();
		
        $orders=new \Common\Controller\OrdersController(array('seller_id'=>$seller_info['id'],'s_no'=>I('post.s_no')));
        $res=$orders->orders_logs(1);
        $this->apiReturn($res['code'],array('data'=>$res['data']),1,$res['msg']);  		
	}


    /**
    * 订单列表
    * @param string $_POST['openid']    用户openid
    */
    public function orders_list(){
        //频繁请求限制,间隔300毫秒
        $this->_request_check();

        //必传参数检查
        $this->need_param=array('openid','sign');
        $this->_need_param();
        $this->_check_sign();  

		$seller_info = $this->get_seller();
		
        $orders=new \Common\Controller\SellerOrdersController(array('seller_id'=>$seller_info['id']));
		
		$data = I('post.');
		$data['supplier_id'] = $this->uid;
        $res=$orders->s_orders_list($data);
        $this->apiReturn($res['code'],array('data'=>$res['data']),1,$res['msg']);                     
    }

    /**
    * 订单详情
    * @param string $_POST['openid']    用户openid
    * @param string $_POST['s_no']      订单号    
    */
    public function view(){
        //频繁请求限制,间隔300毫秒
        $this->_request_check();

        //必传参数检查
        $this->need_param=array('openid','s_no','sign');
        $this->_need_param();
        $this->_check_sign();  
		
		$seller_info = $this->get_seller();

        $orders=new \Common\Controller\SellerOrdersController(array('seller_id'=>$seller_info['id'],'s_no'=>I('post.s_no')));
        $res=$orders->s_view();
        $this->apiReturn($res['code'],array('data'=>$res['data']),1,$res['msg']);          
    }

    /**
    * 订单详情
    * @param string $_POST['openid']    用户openid
    * @param string $_POST['s_no']      订单号    
    */
    public function orders_goods(){
        //频繁请求限制,间隔300毫秒
        $this->_request_check();

        //必传参数检查
        $this->need_param=array('openid','s_no','sign');
        $this->_need_param();
        $this->_check_sign();  

        $orders=new \Common\Controller\SellerOrdersController(array('seller_id'=>$this->uid,'s_no'=>I('post.s_no')));
        $res=$orders->orders_goods();
        $this->apiReturn($res['code'],array('data'=>$res['data']),1,$res['msg']);          
    }

    /**
    * 修改订单价格
    * @param string $_POST['openid']    用户openid
    * @param string $_POST['s_no']      订单号
    * @param float  $_POST['express_price'] 运费
    * @param float  $_POST['goods_price']  改后的商品价格
    */
    public function orders_price_edit(){
        //频繁请求限制,间隔300毫秒
        $this->_request_check();


        //必传参数检查
        $this->need_param=array('openid','s_no','express_price','goods_price','sign');
        $this->_need_param();
        $this->_check_sign();    

        $orders=new \Common\Controller\SellerOrdersController(array('seller_id'=>$this->uid,'s_no'=>I('post.s_no')));
        $res=$orders->orders_price_edit(I('post.'));
        $this->apiReturn($res['code'],'',1,$res['msg']);              
    }

    /**
     * 添加订单备注
     * @param string $_POST['openid'] 用户openid
     * @param string $_POST['s_no'] 订单号
     * @param string $_POST['remark'] 备注内容，255字以内
     * @param string $_POST['color'] 备注颜色，如：red,green,#EE22ff
     */
    public function remark_add(){
        //频繁请求限制,间隔300毫秒
        $this->_request_check();

        //必传参数检查
        $this->need_param=array('openid','s_no','seller_remark','seller_remark_color');
        $this->_need_param();
        $this->_check_sign();

        $orders=new \Common\Controller\SellerOrdersController(array('s_no'=>I('post.s_no'),'seller_id'=>$this->uid));
        $res=$orders->s_remark_add(I('post.'));
        $this->apiReturn($res['code'],array('data'=>$res['data']),1,$res['msg']);
    }


}