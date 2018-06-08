<?php
/**
* 此文件为根据表单模板创建
*/
namespace Admin\Controller;
use Think\Controller;
class BrandextController extends CommonModulesController {
	protected $name 			='品牌推广';	//控制器名称
    protected $formtpl_id		=146;			//表单模板ID
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
    	$btn=array('title'=>'操作','type'=>'html','html'=>'<a href="'.__CONTROLLER__.'/edit/id/[id]" class="btn btn-sm btn-info btn-rad btn-trans btn-block m0">修改</a> <div data-url="'.__CONTROLLER__.'/view/id/[id]" data-id="[id]" class="btn btn-sm btn-primary btn-rad btn-trans btn-block m0 btn-view">审核</div>','td_attr'=>'width="100" class="text-center"','norder'=>1);
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
	* 详情
	*/
	public function view(){
		$do=D('BrandExtRelation');
		$rs=$do->relation(true)->where(array('id'=>I('get.id')))->find();

		$category=explode(',',$rs['category_id']);
		foreach($category as $val){
			$rs['category_name'][]=nav_sort(['table' => 'goods_category','field'=>'id,sid,category_name','id'=>$val,'key'=>'category_name']);
		}

		//dump($rs);
		$this->assign('rs',$rs);
		$this->display();
	}	

	/** 
	* 保存审核记录
	*/
	public function logs_add(){
		if(I('post.status')==2 && I('post.reason')==''){
			$this->ajaxReturn(array('status'=>'warning','msg'=>'请输入拒绝原因！'));
		}

		$do=D('BrandExtRelation');
		$rs=$do->relation(true)->where(array('id'=>I('post.brand_ext_id')))->find();	


		$do=D('BrandExtLogs');
		$_POST['a_uid']=session('admin.id');

		$do->startTrans();

		if($sw1=$do->create()) $sw1=$do->add();
		else{
			$msg[]=$do->getError();
			goto error;
		}


		if($rs['status']!=I('post.status')){
			if(!M('brand_ext')->where(array('id'=>I('post.brand_ext_id')))->save(array('status'=>I('post.status'),'dotime'=>date('Y-m-d H:i:s')))) goto error;
		}


		success:
			$do->commit();
			$result['status']='success';
			$result['msg']='操作成功！';
			$this->ajaxReturn($result);
		error:
			$do->rollback();
			$result['status']='warning';
			$result['msg']='操作失败！'.@implode('<br>',$msg);
			$this->ajaxReturn($result);

	}		
}