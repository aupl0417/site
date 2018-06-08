<?php
/**
 * ----------------------------------------------------------
 * RestFull API V2.0
 * ----------------------------------------------------------
 * 卖家订单管理
 * ----------------------------------------------------------
 * Author:liangfeng 
 * ----------------------------------------------------------
 * 2017-03-22
 * ----------------------------------------------------------
 */
namespace Rest2\Controller;
class SellerOrdersController extends ApiController{
	protected $action_logs = array();
	/**
     * subject: 订单列表
     * api: /SellerOrders/orders_list
     * author: liangfeng
     * day: 2017-03-22
     *
     * [字段名,类型,是否必传,说明]
     * param: openid,int,1,用户openid
     * param: status,int,0,订单状态
     * param: s_no,int,0,订单号
	 * param: pagesize,int,0,每页显示数
	 * param: p,int,0,页码
     * param: sday,string,0,起始日期 xxxx-xx-xx
     * param: eday,string,0,结束日期 xxxx-xx-xx
     * param: goods_name,string,0,搜索的商品名称
     * param: nick,string,0,搜索的买家名称
     */
    public function orders_list(){
        $this->check($this->_field('openid'),false);
        $res = $this->_orders_list($this->post);
        $this->apiReturn($res);
    }
	public function _orders_list($param){
		$orders=new \Common\Controller\SellerOrdersController(array('seller_id'=>$this->user['id']));
        $res=$orders->s_orders_list($param);
		return ['code' => $res['code'],'data'=>$res['data'],'msg'=>$res['msg']];
	}
	/**
     * subject: 订单详情
     * api: /SellerOrders/view
     * author: liangfeng
     * day: 2017-03-23
     *
     * [字段名,类型,是否必传,说明]
     * param: openid,int,1,用户openid
     * param: s_no,int,1,订单号
     */
    public function view(){
        $this->check($this->_field('openid','s_no'),false);
        $res = $this->_view($this->post);
        $this->apiReturn($res);
    }
	public function _view($param){
		$orders=new \Common\Controller\SellerOrdersController(array('seller_id'=>$this->user['id'],'s_no'=>$param['s_no']));
        $res=$orders->s_view();
		return ['code' => $res['code'],'data'=>$res['data'],'msg'=>$res['msg']];      
	}
	/**
     * subject: 订单改价-列出改价商品
     * api: /SellerOrders/orders_goods
     * author: liangfeng
     * day: 2017-03-23
     *
     * [字段名,类型,是否必传,说明]
     * param: openid,int,1,用户openid
     * param: s_no,int,1,订单号
     */
    public function orders_goods(){
        $this->check($this->_field('openid','s_no'),false);
        $res = $this->_orders_goods($this->post);
        $this->apiReturn($res);
    }
	public function _orders_goods($param){
		$orders=new \Common\Controller\SellerOrdersController(array('seller_id'=>$this->user['id'],'s_no'=>$param['s_no']));
        $res=$orders->orders_goods();
		return ['code' => $res['code'],'data'=>$res['data'],'msg'=>$res['msg']];      
	}
	/**
     * subject: 订单改价-修改订单价格
     * api: /SellerOrders/orders_price_edit
     * author: liangfeng
     * day: 2017-03-23
     *
     * [字段名,类型,是否必传,说明]
     * param: openid,int,1,用户openid
     * param: s_no,int,1,订单号
     * param: express_price,float,1,运费
     * param: goods_price,float,1,商品金额
     */
    public function orders_price_edit(){
        $this->check($this->_field('openid','s_no','express_price','goods_price'),false);
        $res = $this->_orders_price_edit($this->post);
        $this->apiReturn($res);
    }
	public function _orders_price_edit($param){
		$orders=new \Common\Controller\SellerOrdersController(array('seller_id'=>$this->user['id'],'s_no'=>$param['s_no']));
        $res=$orders->orders_price_edit($param);
		return ['code' => $res['code'],'data'=>$res['data'],'msg'=>$res['msg']];
	}
	/**
     * subject: 卖家发货
     * api: /SellerOrders/send_express
     * author: liangfeng
     * day: 2017-03-23
     *
     * [字段名,类型,是否必传,说明]
     * param: openid,string,1,用户openid
     * param: s_no,string,1,订单号
     * param: express_code,string,1,快递单号
     * param: express_company_id,int,1,快递公司id
     * param: express_remark,string,0,备注
     */
    public function send_express(){
        $this->check($this->_field('openid','s_no','express_code','express_company_id'),false);
        $res = $this->_send_express($this->post);
        $this->apiReturn($res);
    }
	public function _send_express($param){
		//商家必须先设置过发货地址方可发货
        if(!M('send_address')->where(array('uid'=>$this->user['id']))->field('id')->find()){
			return ['code' => 206,'msg'=>C('error_code')[206]];
        }
        $orders=new \Common\Controller\SellerOrdersController(array('seller_id'=>$this->user['id'],'s_no'=>$param['s_no']));
        $res=$orders->send_express($param);
		return ['code' => $res['code'],'data'=>$res['data'],'msg'=>$res['msg']];
	}
	/**
     * subject: 卖家修改发货信息
     * api: /SellerOrders/edit_express
     * author: liangfeng
     * day: 2017-03-23
     *
     * [字段名,类型,是否必传,说明]
     * param: openid,string,1,用户openid
     * param: s_no,string,1,订单号
     * param: express_code,string,1,快递单号
     * param: express_company_id,int,1,快递公司id
     * param: express_remark,string,0,备注
     */
    public function edit_express(){
        $this->check($this->_field('openid','s_no','express_code','express_company_id'),false);
        $res = $this->_edit_express($this->post);
        $this->apiReturn($res);
    }
	public function _edit_express($param){
		//商家必须先设置过发货地址方可发货
        if(!M('send_address')->where(array('uid'=>$this->user['id']))->field('id')->find()){
			return ['code' => 206,'msg'=>C('error_code')[206]];
        }
        $orders=new \Common\Controller\SellerOrdersController(array('seller_id'=>$this->user['id'],'s_no'=>$param['s_no']));
        $res=$orders->edit_express($param);
		return ['code' => $res['code'],'data'=>$res['data'],'msg'=>$res['msg']];
	}
	/**
     * subject: 卖家关闭订单
     * api: /SellerOrders/close
     * author: liangfeng
     * day: 2017-03-23
     *
     * [字段名,类型,是否必传,说明]
     * param: openid,int,1,用户openid
     * param: s_no,int,1,订单号
     * param: reason,string,1,原因
     */
    public function close(){
        $this->check($this->_field('openid','s_no','reason'),false);
        $res = $this->_close($this->post);
        $this->apiReturn($res);
    }
	public function _close($param){
		$orders=new \Common\Controller\SellerOrdersController(array('seller_id'=>$this->user['id'],'s_no'=>$param['s_no']));
        $res=$orders->s_close($param['reason']);
		return ['code' => $res['code'],'data'=>$res['data'],'msg'=>$res['msg']];
	}
}