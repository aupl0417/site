<?php
/**
* 此文件为根据表单模板创建
*/
namespace Admin\Controller;
use Think\Controller;
class ControlController extends CommonModulesController {
	protected $name 			='控制器';	//控制器名称
    protected $formtpl_id		=55;			//表单模板ID
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
		
		//列表字段
		$end=array('title'=>'操作','type'=>'html','html'=>'<div class="btn btn-sm btn-info btn-rad btn-trans m0" data-type="action-set" data-id="[id]">设置方法属性</div> <a href="'.__CONTROLLER__.'/edit/id/[id]" class="btn btn-sm btn-info btn-rad btn-trans m0">修改</a>','td_attr'=>'width="150" class="text-center"','norder'=>1);
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
	* 权限配置
	*/
	public function action_set(){
		$do=M($this->fcfg['table']);
		$rs=$do->where(array('id'=>I('get.id')))->find();
		
		$action_arr=array();
		if($rs['action']) $action_arr=eval($rs['action']);
		
		
		$file='./Apps/Admin/Controller/'.$rs['controller'].'Controller.class.php';
		if(is_file($file)){
			$body=file_get_contents($file);
			preg_match_all("/public function ([\s\S]*?)\(/ies",$body,$out);
			foreach($out[1] as $val){
				if($val) $action[$val]=$action_arr[$val];
			}
		}
		
		
		//dump($action);
		$this->assign('action',$action);
		
		$this->display();		
	}
	/**
	* 保存权限属性
	*/
	public function action_set_save(){
		$do=M($this->fcfg['table']);
		$action='return '.var_export(I('post.'),true).';';
		
		if($do->where(array('id'=>I('post.id')))->save(array('action'=>$action))){
			$this->ajaxReturn(array('status'=>'success','msg'=>'设置成功！'));
		}else{
			$this->ajaxReturn(array('status'=>'warning','msg'=>'设置失败！'));
		}
	}	
}