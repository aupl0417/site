<?php
/**
* 此文件为根据表单模板创建
*/
namespace Admin\Controller;
use Think\Controller;
class ServicehistoryController extends CommonModulesController {
	protected $name 			='申诉历史';	//控制器名称
    protected $formtpl_id		=177;			//表单模板ID
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
        $map = [
            'status' => 10,    
        ];
    	$this->_index(['map' => $map]);
    	$btn=array('title'=>'操作','type'=>'html','html'=>'<div data-url="'.__CONTROLLER__.'/view/id/[id]" data-id="[id]" class="btn btn-sm btn-primary btn-rad btn-trans btn-block m0 btn-view">详情</div>','td_attr'=>'width="100" class="text-center"','norder'=>1);
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
	 * 退款详情
	 */
	public function view(){
	    $do=D('Common/RefundRelation');
	
	    $rs=$do->relation(true)->where(['id' => I('get.id')])->find();
	
	    $rs['logs']=D('Common/RefundLogsRelation')->relation(true)->where(['r_id' => $rs['id']])->order('id desc')->select();
	
	    $this->assign('rs',$rs);
	    $this->display();
	}
}