<?php
/**
* 此文件为根据表单模板创建
*/
namespace Admin\Controller;
use Think\Controller;
class ZhaoshangController extends CommonModulesController {
	protected $name 			='招商';	//控制器名称
    protected $formtpl_id		=172;			//表单模板ID
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
     * 关于乐兑
     */
	public function about(){
	    $rs = M('zhaoshang')->find();
        $this->assign('rs',$rs);

	    $this->display();
    }

    public function about_save(){
        if(empty($_POST['about'])) $this->ajaxReturn(['status' => 'warning','msg' => '请输入内容！']);

        $do = M('zhaoshang');
        if(!$do->create()) $this->ajaxReturn(['status' => 'warning','msg' => $do->getError()]);

        if(false !== $do->save()) $this->ajaxReturn(['status' => 'success','msg' => '保存成功！']);
        else $this->ajaxReturn(['status' => 'warning','msg' => '保存失败！']);
    }

    /**
     * 入驻指南
     */
    public function guide(){
        $rs = M('zhaoshang')->find();
        $this->assign('rs',$rs);

        $this->display();
    }

    public function guide_save(){
        if(empty($_POST['guide'])) $this->ajaxReturn(['status' => 'warning','msg' => '请输入内容！']);

        $do = M('zhaoshang');
        if(!$do->create()) $this->ajaxReturn(['status' => 'warning','msg' => $do->getError()]);

        if(false !== $do->save()) $this->ajaxReturn(['status' => 'success','msg' => '保存成功！']);
        else $this->ajaxReturn(['status' => 'warning','msg' => '保存失败！']);
    }

    /**
     * 合同
     */
    public function agreement(){
        $rs = M('zhaoshang')->find();
        $this->assign('rs',$rs);

        $this->display();
    }

    public function agreement_save(){
        if(empty($_POST['agreement'])) $this->ajaxReturn(['status' => 'warning','msg' => '请输入内容！']);

        $do = M('zhaoshang');
        if(!$do->create()) $this->ajaxReturn(['status' => 'warning','msg' => $do->getError()]);

        if(false !== $do->save()) $this->ajaxReturn(['status' => 'success','msg' => '保存成功！']);
        else $this->ajaxReturn(['status' => 'warning','msg' => '保存失败！']);
    }

    /**
     * 邮寄合同地址
     */
    public function address(){
        $rs = M('zhaoshang')->find();
        $this->assign('rs',$rs);

        $this->display();
    }

    public function address_save(){
        if(empty($_POST['address'])) $this->ajaxReturn(['status' => 'warning','msg' => '请输入邮寄合同地址！']);

        $do = M('zhaoshang');
        if(!$do->create()) $this->ajaxReturn(['status' => 'warning','msg' => $do->getError()]);

        if(false !== $do->save()) $this->ajaxReturn(['status' => 'success','msg' => '保存成功！']);
        else $this->ajaxReturn(['status' => 'warning','msg' => '保存失败！']);
    }
}