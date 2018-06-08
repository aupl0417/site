<?php
/**
* 此文件为根据表单模板创建
*/
namespace Admin\Controller;
use Think\Controller;
use Think\Exception;

class Luckdraw1applyController extends CommonModulesController {
	protected $name 			='申请管理';	//控制器名称
    protected $formtpl_id		=232;			//表单模板ID
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
        $btn=array('title'=>'操作','type'=>'html','html'=>'<div data-url="'.__CONTROLLER__.'/view/id/[id]" data-id="[id]" class="btn btn-sm btn-primary btn-rad btn-trans btn-block m0 btn-view">审核</div>','td_attr'=>'width="100" class="text-center"','norder'=>1);
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
     * subject: 申请详情
     * api: view
     * author: Mercury
     * day: 2017-04-22 14:07
     * [字段名,类型,是否必传,说明]
     */
    public function view()
    {
        $id = I('get.id');
        $model = D('Luckdraw1apply232View');
        $data  = $model->where(['id' => $id])->find();
        $data['logs'] = M('luckdraw1_apply_log')->where(['apply_id' => $id])->order('id desc')->select();
        if ($data['coupons']) $data['coupons'] = unserialize($data['coupons']);
        $this->assign('rs', $data);
        $this->display();
	}

    /**
     * subject: 审核
     * api: post
     * author: Mercury
     * day: 2017-05-12 16:30
     * [字段名,类型,是否必传,说明]
     */
    public function post()
    {
        if (IS_POST) {
            //状态，优惠券创建，dotime
            $data = I('post.');
            try {
                $model = M('Luckdraw1_apply');
                $model->startTrans();
                $data['dotime'] =   date('Y-m-d H:i:s');
                if ($data['status'] == 2) { //通过
                    $coupon = $model->where(['id' => $data['id']])->field('coupons,uid,shop_id,luckdraw_id')->find();
                    $tmp = unserialize($coupon['coupons']);
                    if (!is_array($tmp)) throw new Exception('优惠券信息有问题');
                    foreach ($tmp as $k => $v) {
                        $cData = [
                            'ip'    =>  get_client_ip(),
                            'b_no'  =>  $this->create_orderno('YT'),
                            'uid'   =>  $coupon['uid'],
                            'price' =>  $v['price'],
                            'num'   =>  C('CFG.luckdraw')['luckdraw_coupon_num'],
                            'sday'  =>  date('Y-m-d'),
                            'eday'  =>  date('Y-m-d', strtotime('+7 day')),
                            'type'  =>  1,
                            'use_type'  =>  2,
                            'shop_id'   =>  $coupon['shop_id'],
                            'min_price' =>  $v['min_price'],
                            'channel'   =>  2,
                            'max_num'   =>  1,
                        ];
                        $tmp[$k]['cid'] = M('coupon_batch')->add($cData);
                        if ($tmp[$k]['cid'] == false) throw new Exception('创建优惠券失败');
                    }
                    $data['coupons'] = serialize($tmp);
                } elseif ($data['status'] == 3) { //拒绝
                    if (!$data['reason']) throw new Exception('原因不能为空');
                }
                if ($model->save($data) == false) throw new Exception('审核失败');
                $lodData = [
                    'a_nick'    =>  session('admin.username'),
                    'status'    =>  $data['status'],
                    'reason'    =>  $data['reason'] ? : '审核通过',
                    'apply_id'  =>  $data['id'],
                ];
                if (M('luckdraw1_apply_log')->add($lodData) == false) throw new Exception('日志添加失败');
                $model->commit();
                $this->ajaxReturn(['status' => 'success', 'msg' => '操作成功']);
            } catch (Exception $e) {
                $model->rollback();
                $this->ajaxReturn(['status' => 'warning', 'msg' => $e->getMessage()]);
            }
        }
	}
}