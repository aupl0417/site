<?php
/**
* 此文件为根据表单模板创建
*/
namespace Admin\Controller;
use Think\Controller;
class ExpressController extends CommonModulesController {
	protected $name 			='运费模板';	//控制器名称
    protected $formtpl_id		=118;			//表单模板ID
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
	* 自定义地区运费
	*/
	public function express_area(){
		$rs=$this->do->where('id='.I('get.express_id'))->find();
		$this->assign('rs',$rs);

		$do=M('express_area');
		$list=$do->where(array('express_id'=>I('get.express_id')))->select();

		foreach($list as $key=>$val){
			$citys=M('area')->where(array('id'=>array('in',$val['city_ids'])))->getField('a_name',true);
			//dump($citys);
			$list[$key]['citys']=implode(',',$citys);
		}

		$this->assign('list',$list);

		$this->display();
	}

	public function express_area_add(){
		$rs=$this->do->where('id='.I('get.express_id'))->find();
		$this->assign('rs',$rs);

		$do=M('express_area');
		$list=$do->where(array('express_id'=>I('get.express_id')))->getField('city_ids',true);
		$this->assign('disable_value',@implode(',',$list));

		$this->display();		
	}

	/**
	* 保存自定义运费
	*/
	public function express_area_add_save(){
		$do=D('ExpressArea');
		$_POST['city_ids']=@implode(',',I('post.city_ids'));

		if($do->create()){
			$do->add();
			$result['status']='success';
			$result['msg']='操作成功！';			
		}else{
			$result['status']='warning';
			$result['msg']='操作失败！'.$do->getError();
		}

		$this->ajaxReturn($result);
	}

	/**
	* 删除自定义运费
	*/
	public function express_area_delete(){
		$do=M('express_area');

		if($do->where('id='.I('post.id'))->delete()){
			$result['status']='success';
			$result['msg']='操作成功！';			
		}else{
			$result['status']='warning';
			$result['msg']='操作失败！';
		}

		$this->ajaxReturn($result);
	}

	/**
	* 修改自定义运费
	*/
	public function express_area_edit(){
		$do=D('ExpressAreaRelation');
		$rs=$do->relation(true)->where('id='.I('get.id'))->find();

		$do=M('express_area');
		$list=$do->where(array('express_id'=>$rs['express_id'],'id'=>array('neq',I('get.id'))))->getField('city_ids',true);
		$this->assign('disable_value',@implode(',',$list));

		$this->assign('rs',$rs);
		$this->display();
	}

	/**
	* 保存自定义运费
	*/
	public function express_area_edit_save(){
		$do=D('ExpressArea');
		$_POST['city_ids']=@implode(',',I('post.city_ids'));

		if($do->create()){
			$do->save();
			$result['status']='success';
			$result['msg']='操作成功！';			
		}else{
			$result['status']='warning';
			$result['msg']='操作失败！'.$do->getError();
		}

		$this->ajaxReturn($result);
	}	
}