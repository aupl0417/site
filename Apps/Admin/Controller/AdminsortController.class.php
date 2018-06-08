<?php
/**
* 此文件为根据表单模板创建
*/
namespace Admin\Controller;
use Think\Controller;
class AdminsortController extends CommonModulesController {
	protected $name 			='权限分组';	//控制器名称
    protected $formtpl_id		=52;			//表单模板ID
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
        if(!in_array($_SESSION['admin']['sid'],$this->admin_sid)) $data['map']['uid'] = $_SESSION['admin']['id'];
    	$this->_index($data);
		
		//列表字段
		$end=array('title'=>'操作','type'=>'html','html'=>'<div class="btn btn-sm btn-info btn-rad btn-trans m0" data-type="action-set" data-id="[id]">设置权限</div> <a href="'.__CONTROLLER__.'/edit/id/[id]" class="btn btn-sm btn-info btn-rad btn-trans m0">修改</a>','td_attr'=>'width="150" class="text-center"','norder'=>1);
		$this->assign('fields',$this->plist(null,$end));
		
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
        $_POST['uid'] = $_SESSION['admin']['id'];
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
	* 排序
	*/
	public function setsort(){
		$result=$this->_setsort();
		$this->ajaxReturn($result);
	}

	/**
	* 类目转移
	*/
	public function sid_change_select(){
		$result=$this->_sid_change_select();
		$this->ajaxReturn($result);
	}
	
	/**
	* 设置权限
	*/
	public function action_set(){
		$do=M($this->fcfg['table']);
		$rs=$do->where(array('id'=>I('get.id')))->find();
		$rs['menuid']=@explode(',',$rs['menuid']);
		if($rs['action']) $rs['action']=eval(html_entity_decode($rs['action']));
		
		//菜单
        $sql = 'status=1 and id in ('.C('admin.menuid').')';
        if(session('admin.sid') == 1) $sql = 'status=1';    //管理员
		$menulist=get_category(array('table'=>'admin_menu','level'=>3,'sql'=>$sql,'field'=>'id,name'));
		$this->assign('menulist',$menulist);
		
		$tmp    = eval(html_entity_decode(C('admin.action')));
        $action = [];
        foreach($tmp as $val){
            $val        = explode(':',$val);
            $action[]   = $val[0];
        }
        //dump($action);
        $map = ['status' =>1,'controller' => ['in',$action]];
        if(session('admin.sid') == 1) $map = ['status' => 1];   //管理员
        //模块
		$modules=CURD(array('table'=>'controller','field'=>'id,controller_name,controller','map' => $map));
		$this->assign('modules',$modules);
		//dump($modules);

		$this->assign('rs',$rs);
		$this->display();		
	}

	/**
	* 保存权限设置
	*/
	public function action_set_save(){
		$data['menuid']=@implode(',',I('post.menuid'));		
		$data['action']='return '.var_export(I('post.action'),true).';';


		$do=D($this->fcfg['verify_model']);
		if($do->where('id='.I('post.id'))->save($data)){
			$result['status']='success';
			$result['msg']='操作成功！';
		}else{
			$result['status']='warning';
			$result['msg']='操作失败！';			
		}

		$this->ajaxReturn($result);
	}

}