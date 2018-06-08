<?php
/**
* 此文件为根据表单模板创建
*/
namespace Admin\Controller;
use Think\Controller;
class GoodsilleglController extends CommonModulesController {
	protected $name 			='商品违规';	//控制器名称
    protected $formtpl_id		=144;			//表单模板ID
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
	* 违规详情
	*/
	public function view(){
		$do=D('GoodsIlleglRelation');
		$rs=$do->relation(true)->where(['id' => I('get.id')])->find();
		$rs['shop_vr_status'] = M('shop_vr')->where(['goods_illegl_id'=>$rs['id']])->getField('status');
		$this->assign('rs',$rs);
		$this->display();
	}

	/** 
	* 保存审核记录
	*/
	public function logs_add(){
		if(I('post.remark')==''){
			$this->ajaxReturn(array('status'=>'warning','msg'=>'请输入备注！'));
		}

		$do=D('GoodsIlleglRelation');
		$rs=$do->relation(true)->where(array('id'=>I('post.illegl_id')))->find();	

		if($rs['status']==0 || $rs['status']==4) $this->ajaxReturn(['status' => 'warning','msg' => '已取消违规或已通过审核的违规记录不充许再次操作！']);


		$_POST['a_uid']=session('admin.id');

		$do->startTrans();
		if(!D('GoodsIlleglLogs')->create()){
			$msg=D('GoodsIlleglLogs')->getError();
			goto error;
		}

		if(!D('GoodsIlleglLogs')->add()) goto error;


		if(false===M('goods_illegl')->where(['id' => I('post.illegl_id')])->save(['status' => I('post.status'),'dotime' => date('Y-m-d H:i:s')])) goto error;

		//取消违规
		if(I('post.status')==0){
			/*
			if(!M('goods')->where(['id' => $rs['goods_id']])->save(['status' => 1])) goto error;

			$illegl_point=M('goods_illegl')->where(['uid'=>$rs['uid'],'status'=>['gt',0],'_string'=>'date_format(atime,"%Y")="'.date('Y').'"'])->sum('illegl_point');
			if(false===M('shop')->where(['id'=>$rs['shop_id']])->save(['illegl_point'=>$illegl_point])) goto error;			
			*/
		}elseif(I('post.status')==4){
			if(!M('goods')->where(['id' => $rs['goods_id']])->save(['status' => 1])) goto error;
		}


		success:
			$do->commit();
			$result['status']='success';
			$result['msg']='操作成功！';
			$this->ajaxReturn($result);
		error:
			$do->rollback();
			$result['status']='warning';
			$result['msg']='操作失败！'.$msg;
			$this->ajaxReturn($result);

	}	

}