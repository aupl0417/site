<?php
/**
* 此文件为根据表单模板创建
*/
namespace Admin\Controller;
use Think\Controller;
class OrdersController extends CommonModulesController {
	protected $name 			='订单管理';	//控制器名称
    protected $formtpl_id		=124;			//表单模板ID
    protected $fcfg				=array();		//表单配置
    protected $do 				='';			//实例化模型
    protected $map				=array();		//where查询条件

    /**
    * 初始化
    */
    public function _initialize() {
    	parent::_initialize();

    	//初始化表单模板
    	$this->_initform();

    	//dump($this->fcfg);

    }

    /**
    * 列表
    */
    public function index($param=null){
    	//$this->_index();
		if(in_array(I('get.status'),array(1,''))){
			$this->assign('search',1);
			$this->orders();
		}else{
			$this->orders_shop();
		}

    }
	
	/**
	* 订单合并模式
	*/
	public function orders(){
		$map=$this->map;
    	$pagelist=pagelist(array(
    			'table'		=>'OrdersBuyerRelation',
    			'do'		=>'D',
    			'relation'	=>true,
    			'pagesize'	=>20,
				'map'		=>$map,
    			'order'		=>'id desc',
    		));

    	foreach($pagelist['list'] as $key=>$val){
	        $pagelist['list'][$key]['province']    =get_key_by_list(array('table'=>'area','field'=>'id,a_name','key_val'=>$val['province'],'cache_name'=>'table_area'));
	        $pagelist['list'][$key]['city']        =get_key_by_list(array('table'=>'area','field'=>'id,a_name','key_val'=>$val['city'],'cache_name'=>'table_area'));
	        $pagelist['list'][$key]['district']    =get_key_by_list(array('table'=>'area','field'=>'id,a_name','key_val'=>$val['district'],'cache_name'=>'table_area'));
	        $pagelist['list'][$key]['town']        =get_key_by_list(array('table'=>'area','field'=>'id,a_name','key_val'=>$val['town'],'cache_name'=>'table_area'));    		
	        foreach($pagelist['list'][$key]['orders_shop'] as $skey=>$v){
	            $pagelist['list'][$key]['orders_shop'][$skey]['seller']=D('Common/ShopUserRelation')->relation(true)->where(array('uid'=>$v['seller_id']))->field('uid,shop_name,shop_logo,mobile,qq,wang')->find();
	            $pagelist['list'][$key]['orders_shop'][$skey]['orders_goods']=M('orders_goods')->where(array('s_id'=>$v['id']))->field('etime,ip',ture)->select();
				$pagelist['list'][$key]['orders_shop'][$skey]['express']=M('express_company')->where(array('id'=>$v['express_company_id']))->field('id,company,sub_name,logo,code')->find();
	        }	        
    	}

    	//dump($pagelist);

    	$this->assign('pagelist',$pagelist);	
		$this->display();
	}
	
	/**
	* 商家订单模式
	*/
	public function orders_shop(){
		$map=$this->map;
    	$pagelist=pagelist(array(
    			'table'		=>'OrdersShopRelation',
    			'do'		=>'D',
    			'relation'	=>true,
    			'pagesize'	=>20,
				'map'		=>$map,
    			'order'		=>'id desc',
    		));
    	$area 	=	$this->cache_table('area');
    	foreach($pagelist['list'] as $key=>$val){
	        $pagelist['list'][$key]['province']    =$area[$val['province']];
	        $pagelist['list'][$key]['city']        =$area[$val['city']];
	        $pagelist['list'][$key]['district']    =$area[$val['district']];
	        $pagelist['list'][$key]['town']        =$area[$val['town']];
    	}

    	//dump($pagelist);

    	$this->assign('pagelist',$pagelist);
		$this->display('orders_shop');
	}

    /**
    * 添加记录
    */
    public function add($param=null){
    	$this->display();
    }
	
	/**
	* 保存新增记录
	*/
	public function add_save($param=null){
		$result=$this->_add_save();

		$this->ajaxReturn($result);
	}

	/**
	* 修改记录
	*/
	public function edit($param=null){
		$this->_edit();
		$this->display();
	}

	/**
	* 保存修改记录
	*/
	public function edit_save($param=null){
		$result=$this->_edit_save();

		$this->ajaxReturn($result);
	}

	/**
	* 删除选中记录
	*/
	public function delete_select($param=null){
		$result=$this->_delete_select();
		$this->ajaxReturn($result);
	}

	/**
	* 批量更改状态
	*/
	public function active_change_select($param=null){
		$result=$this->_active_change_select();

		$this->ajaxReturn($result);		
	}
	
	/**
	* 发货
	*/
	public function send_express(){
		$rs=M('orders_shop')->where(array('s_no'=>I('get.s_no')))->field('s_no,seller_id,express_company_id,express_code')->find();
		$this->assign('rs',$rs);
		//dump($rs);
		$this->display();
	}
	
	public function send_express_save(){
		$orders=new \Common\Controller\SellerOrdersController(array('s_no'=>I('post.s_no'),'seller_id'=>I('post.seller_id')));
		$res=$orders->send_express(I('post.express_code'),I('post.express_company_id'));
		//dump($res);
		
		$result['status']=$res['code']==1?'success':'warning';
		$result['msg']=$res['msg'];
		
		$this->ajaxReturn($result);
	}
	
	/**
	* 改价
	*/
	public function edit_price(){
		$rs=M('orders_shop')->where(array('s_no'=>I('get.s_no')))->field('s_no,seller_id,pay_price,total_price')->find();
		$this->assign('rs',$rs);
		//dump($rs);
		$this->display();
	}	
	
	public function edit_price_save(){
		$orders=new \Common\Controller\SellerOrdersController(array('s_no'=>I('post.s_no'),'seller_id'=>I('post.seller_id')));
		$res=$orders->edit_price(I('post.pay_price'));
		//dump($res);
		
		$result['status']=$res['code']==1?'success':'warning';
		$result['msg']=$res['msg'];
		
		$this->ajaxReturn($result);
	}
	/**
	* 物流跟踪
	*/
	public function query_express(){
		$orders=new \Common\Controller\OrdersController(array('s_no'=>I('get.s_no')));
		$res=$orders->query_express_aliyun();
		//dump($res);
		//dump($res);
		$this->assign('rs',$res);
		$this->display();
	}
	
	/**
	* 关闭订单
	*/
	public function orders_close(){
		$this->display();
	}
	public function orders_close_save(){
		$orders=new \Common\Controller\OrdersController(array('o_no'=>I('post.o_no')));
		$res=$orders->b_close(I('post.reason'));	
		$result['status']=$res['code']==1?'success':'warning';
		$result['msg']=$res['msg'];
		
		$this->ajaxReturn($result);		
	}
	
	/**
	* 订单日志
	*/
	public function orders_logs(){
		$orders=new \Common\Controller\OrdersController(array('s_no'=>I('get.s_no')));
		$res=$orders->orders_logs(0);	
		//dump($res);

		$this->assign('list',$res);
		$this->display();
	}
}