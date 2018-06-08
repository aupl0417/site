<?php
/**
* 此文件为根据表单模板创建
*/
namespace Admin\Controller;
use Think\Controller;
use Think\Exception;

class ShopauthreplyController extends CommonModulesController {
	protected $name 			='子账号申请';	//控制器名称
    protected $formtpl_id		=215;			//表单模板ID
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
     * subject: 详情
     * api: view
     * author: Mercury
     * day: 2017-03-25 14:10
     * [字段名,类型,是否必传,说明]
     */
	public function view() {
	    $id = I('get.id', 0, 'int');
	    if ($id > 0) {
            $rs = $this->getData($id);
            $this->assign('rs', $rs);
            $this->display();
        }
    }

    /**
     * subject: 审核
     * api: post
     * author: Mercury
     * day: 2017-03-25 14:12
     * [字段名,类型,是否必传,说明]
     */
    public function post()
    {
        if (IS_POST) {
            try {
                $id = I('post.id', 0, 'int');
                if ($id <= 0 ) throw new Exception('非法操作');
                $rs     = $this->getData($id);
                if ($rs == false) throw new Exception('非法操作');
                $status = I('post.status');
                $reason = I('post.reason2');
                $model  = M('shop_auth_account_num_reply');
                $model->startTrans();
                switch ($status) {
                    case 1:     //待审核
                        break;
                    case 2:     //已通过
                        if (M('shop')->where(['id' => $rs['shop_id']])->setInc('max_sub_user', $rs['num']) == false) throw new Exception('新增名额失败');
                        break;
                    case 3:     //已拒绝
                        if ($reason == '') throw new Exception('拒绝原因不能为空');
                        break;
                }
                if ($model->where(['id' => $id])->save(['status' => $status, 'reason2' => $reason, 'dotime' => date('Y-m-d H:i:s', NOW_TIME)]) === false) throw new Exception('更新数据失败');
                $model->commit();
                $this->ajaxReturn(['status' => 'success', 'msg' => '操作成功']);
            } catch (Exception $e) {
                $model->rollback();
                $this->ajaxReturn(['status' => 'warning', 'msg' => $e->getMessage()]);
            }
        }
    }

    /**
     * subject: 获取数据
     * api: getData
     * author: Mercury
     * day: 2017-03-25 14:11
     * [字段名,类型,是否必传,说明]
     * @param $id
     * @return mixed
     */
    private function getData($id)
    {
        $model = D('Shopauthaccountnumreply215View');
        $data = $model->where(['id' => $id])->find();
        return $data;
    }
}