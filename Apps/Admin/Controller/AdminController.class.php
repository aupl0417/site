<?php
namespace Admin\Controller;
use Think\Controller;
class AdminController extends CommonModulesController {
	protected $name 			='雇员管理';	//控制器名称
    protected $formtpl_id		=50;			//表单模板ID
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
        //只能显示本部门的用户
        $data = [];
        if(!in_array($_SESSION['admin']['sid'],$this->admin_sid)) $data['map'] = ['sid' => session('admin.sid')];
    	$this->_index($data);
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
	* 批量更改类目
	*/
	public function sid_change_select($param=null){
		$result=$this->_sid_change_select();

		$this->ajaxReturn($result);		
	}
	/**
	* 雇员同步
	*/
	public function admin_sync(){
		$apiurl='/Erp/admin_sync';
		$data=[
			'start'	=>0,
			'limit'	=>1000,
		];
        //C('DEBUG_API',true);
		$res=$this->doApi($apiurl,$data);
		//print_r($this->api_cfg);
		$this->ajaxReturn($res);
	}
}