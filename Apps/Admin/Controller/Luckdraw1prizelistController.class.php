<?php
/**
* 此文件为根据表单模板创建
*/
namespace Admin\Controller;
use Think\Controller;
class Luckdraw1prizelistController extends CommonModulesController {
	protected $name 			='所有奖品';	//控制器名称
    protected $formtpl_id		=235;			//表单模板ID
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
        $btn=array('title'=>'操作','type'=>'html','html'=>'<a href="'.__CONTROLLER__.'/view/id/[id]" data-id="[id]" class="btn btn-sm btn-success btn-rad btn-trans btn-block m0">修改</a><div data-url="'.__CONTROLLER__.'/view/id/[id]" data-id="[id]" class="btn btn-sm btn-primary btn-rad btn-trans btn-block m0 btn-view">详情</div>','td_attr'=>'width="100" class="text-center"','norder'=>1);
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

    public function view()
    {
        $id = I('get.id');
        $model = D('Luckdraw1prizelist235View');
        $data = $model->find($id);
        if ($data) {
            switch ($data['type_id']) {
                case 1:     //代金券
                case 2:     //优惠券
                    if ($data['value']) {   //正在进行的代金券及优惠券
                        $data['coupon'] = M('coupon_batch')->cache(true)->where(['id' => ['in', trim($data['value'], ',')]])->order('id asc')->select();
                        if ($data['coupon']) {
                            foreach ($data['coupon'] as &$v) {
                                if ($v['shop_id']) {
                                    $v['shop_name'] = M('shop')->where(['id' => $v['shop_id']])->cache(true)->getField('shop_name');
                                }
                            }
                            unset($v);
                        }
                    }
                    if ($data['r_value']) { //已经移除的代金券及优惠券
                        $data['r_coupon'] = M('coupon_batch')->cache(true)->where(['id' => ['in', trim($data['value'], ',')]])->order('id asc')->select();
                        if ($data['r_coupon']) {
                            foreach ($data['r_coupon'] as &$v) {
                                if ($v['shop_id']) {
                                    $v['shop_name'] = M('shop')->where(['id' => $v['shop_id']])->cache(true)->getField('shop_name');
                                }
                            }
                            unset($v);
                        }
                    }
                    break;
                case 3:     //积分
                    break;
            }
        }
        $this->assign('rs', $data);
        $this->display();
	}
}