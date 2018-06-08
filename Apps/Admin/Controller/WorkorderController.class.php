<?php
/**
* 此文件为根据表单模板创建
*/
namespace Admin\Controller;
use Think\Controller;
class WorkorderController extends CommonModulesController {
	protected $name 			='工单管理';	//控制器名称
    protected $formtpl_id		=151;			//表单模板ID
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
    	$btn=array('title'=>'操作','type'=>'html','html'=>'<a href="'.__CONTROLLER__.'/edit/id/[id]" class="btn btn-sm btn-info btn-rad btn-trans btn-block m0">修改</a> <div data-url="'.__CONTROLLER__.'/view/id/[id]" data-id="[id]" class="btn btn-sm btn-primary btn-rad btn-trans btn-block m0 btn-view">详情</div>','td_attr'=>'width="100" class="text-center"','norder'=>1);
    	$this->assign('fields',$this->plist(null,$btn));
		$this->display();
    }

    /**
     * 详情
     */
    public function view(){

    	$status = (new \Common\Controller\WorkorderController)->status;

    	$one = D('Common/WorkorderRelation')->relation(true)->find(I('id'));
    	$one['status_name'] = $status[$one['status']];
    	

    	$logs = D('WorkorderLogsRelation')->where(['w_no' => $one['w_no']])->relation(true)->select();

    	$this->assign('data',$one);
    	$this->assign('logs',$logs);
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
	* 添加日志
	*/
	public function logs_add(){
		
		if(I('post.remark')=='') $this->ajaxReturn(['status' => 'warning','msg' =>'请输入留言或备注！']);

		$rs = M('workorder')->where(['id' => I('post.w_id')])->find();
		$data 	=	[
			'ip'		=> get_client_ip(),
			'atime'		=> date('Y-m-d H:i:s'),
			'uid'		=> 0,
			'w_no'		=> $rs['w_no'],
			'workorder_status'	=> $rs['status'],
			'work_id'		=>session('admin.id'),
			'content'	=> I('post.remark'),
		];

		$insid = M('workorder_logs')->add($data);
		if($insid){
			$this->ajaxReturn(['status' => 'success','msg' =>'操作成功！']);
		}else{
			$this->ajaxReturn(['status' => 'warning','msg' =>'操作失败！']);
		}
	}

}