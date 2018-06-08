<?php
/**
* 此文件为根据表单模板创建
*/
namespace Admin\Controller;
use Think\Controller;
class MobileordersController extends CommonModulesController {
	protected $name 			='话费流量订单';	//控制器名称
    protected $formtpl_id		=257;			//表单模板ID
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
    	$this->_index();
        $btn=array('title'=>'操作','type'=>'html','html'=>'<a href="'.__CONTROLLER__.'/edit/id/[id]" class="btn btn-sm btn-info btn-rad btn-trans btn-block m0">修改</a> <div data-url="'.__CONTROLLER__.'/view/id/[id]/uid/[uid]/s_no/[s_no]" data-id="[id]" class="btn btn-sm btn-primary btn-rad btn-trans btn-block m0 btn-view">详情</div>','td_attr'=>'width="100" class="text-center"','norder'=>1);
        $this->assign('fields',$this->plist(null,$btn));
		$this->display();
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
     * 订单详情
     * Create by lazycat
     * 2017-05-15
     */

	public function view(){
        $data['openid'] = M('user')->where(['id' => I('get.uid')])->getField('openid');
        $data['s_no']   = I('get.s_no');

        $res = $this->doApi2('/MobileRecharge/view',$data);

        $res['data']['user']    = M('user')->where(['id' => $res['data']['uid']])->field('nick,mobile')->find();
        $res['data']['seller']  = M('user')->where(['id' => $res['data']['seller_id']])->field('nick,mobile')->find();
        $res['data']['shop']    = M('shop')->where(['id' => $res['data']['shop_id']])->field('shop_name,shop_logo,domain,qq,wang')->find();
        $res['data']['erp_status']       = $this->doApi2('/Erp/orders_in_erp_status',['s_no' => $res['data']['s_no']]);

        //dump($res);

        $this->assign('rs',$res['data']);
        $this->display();
    }

}