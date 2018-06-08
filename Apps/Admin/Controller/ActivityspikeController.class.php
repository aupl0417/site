<?php
/**
* 此文件为根据表单模板创建
*/
namespace Admin\Controller;
use Think\Controller;
class ActivityspikeController extends CommonModulesController {
	protected $name 			='累积升级';	//控制器名称
    protected $formtpl_id		=170;			//表单模板ID
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
        $map    =   [
            'pay_type'  =>  ['in', '1,3,4'],
            'atime'     =>  ['lt', '2016-09-23 00:00:00'],
            'status'    =>  ['in', '4,5'],
        ];
        //dump($map);
    	$this->_index(['map' => $map]);
    	foreach ($this->_data['list'] as $k=>&$v) {
    	    $v['activity']   =   M('activity_participate')->field('id,type_id,status')->where(['s_no' => $v['s_no'], 'type_id' => 7, 'status' => 2])->find();
    	    if (empty($v['activity'])) unset($this->_data['list'][$k]);
    	}
    	unset($v,$k);
    	$btn=array('title'=>'操作','type'=>'html','html'=>'<div data-url="'.__CONTROLLER__.'/view/id/[id]" data-id="[id]" class="btn btn-sm btn-primary btn-rad btn-trans btn-block m0 btn-view">操作</div>','td_attr'=>'width="100" class="text-center"','norder'=>1);
    	$this->assign('fields',$this->plist(null,$btn));
    	$this->assign('pagelist', $this->_data);
		$this->display();
    }
    
    /**
     * 补回列表
     */
    public function makeup() {
        $map    =   [
            'pay_type'      =>  ['in', '1,3,4'],
            //'receipt_time'  =>  ['between', '2016-09-26 00:00:00,2016-09-29 15:00:00'],
            //'atime'  =>  ['between', '2016-09-26 00:00:00,2016-09-29 15:00:00'],
            'atime'  =>  ['between', '2016-11-08 18:00:00,2016-11-12 15:00:00'],
            'status'        =>  ['in', '4,5'],
        ];
        //dump($map);
    	$this->_index(['map' => $map]);
    	foreach ($this->_data['list'] as $k=>&$v) {
    	    $v['activity']   =   M('activity_participate')->field('id,type_id,status')->where(['s_no' => $v['s_no'], 'type_id' => 7])->find();
    	    if (!empty($v['activity'])) unset($this->_data['list'][$k]);
    	}
    	unset($v,$k);
    	$btn=array('title'=>'操作','type'=>'html','html'=>'<div data-url="'.__CONTROLLER__.'/view1/id/[id]" data-id="[id]" class="btn btn-sm btn-primary btn-rad btn-trans btn-block m0 btn-view">操作</div>','td_attr'=>'width="100" class="text-center"','norder'=>1);
    	$this->assign('fields',$this->plist(null,$btn));
    	$this->assign('pagelist', $this->_data);
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
	
	public function view() {
	    $rs    =   $this->getData(I('get.id'));
	    if ($rs == false) return false;
	    $this->assign('rs', $rs);
	    $this->display();
	}
	
	public function view1() {
	    $map    =   [
            'pay_type'      =>  ['in', '1,3,4'],
            //'receipt_time'  =>  ['between', '2016-09-26 00:00:00,2016-09-29 15:00:00'],
	        //'atime'  =>  ['between', '2016-09-26 00:00:00,2016-09-29 15:00:00'],
	        'atime'  =>  ['between', '2016-11-08 18:00:00,2016-11-12 15:00:00'],
            'status'        =>  ['in', '4,5'],
	        'id'            =>  I('get.id'),
        ];
	    $rs    =   $this->getData(null, $map);
	    if ($rs == false) return false;
	    $this->assign('rs', $rs);
	    $this->display();
	}
	
	public function post() {
	    $rs    =   $this->getData(I('post.id'));
	    if ($rs == false) $this->ajaxReturn(['code' => 0, 'msg' => '订单错误']);
	    $data  =   [
	       'orderID'   =>  $rs['s_no'],
	       'money'     =>  $rs['goods_price_edit'],
	       'securityID'=>  'ASDSQWES2564DSA54SA21DAS',
	    ];
	    $res   =   $this->curl('/Erp/addSpecialMoney', $data, 1);
	    if ($res['code'] == 1) {
	        M('activity_participate')->where(['s_no' => $rs['s_no'], 'type_id' => 4])->save(['status' => 2]);
	        M('activity_participate')->where(['s_no' => $rs['s_no'], 'id' => $rs['activity']['id']])->save(['status' => 1]);
	    }
	    $this->ajaxReturn($res);
	}
    
	public function post1() {
	    $map    =   [
	        'pay_type'      =>  ['in', '1,3,4'],
	        //'receipt_time'  =>  ['between', '2016-09-26 00:00:00,2016-09-29 15:00:00'],
	        //'atime'  =>  ['between', '2016-09-26 00:00:00,2016-09-29 15:00:00'],
	        'atime'  =>  ['between', '2016-11-08 18:00:00,2016-11-12 15:00:00'],
	        'status'        =>  ['in', '4,5'],
	        'id'            =>  I('post.id'),
	    ];
	    $rs    =   $this->getData(null, $map);
	    if ($rs == false) $this->ajaxReturn(['code' => 0, 'msg' => '订单错误']);
	    $do    =   M('activity_participate');
	    $do->startTrans();
	    $activityId  =   M('activity')->where(['shop_id' => $rs['shop_id'], 'status' => 2])->order('id desc')->getField('id');
	    $aData =   [
	        'uid'                  =>  $rs['uid'],
	        'activity_id'          =>  $activityId,
	        's_no'                 =>  $rs['s_no'],
	        'status'               =>  1,
	        'calc_before_money'    =>  $rs['pay_price'],
	        'calc_after_money'     =>  $rs['pay_price'],
	        'full_value'           =>  $rs['goods_price_edit'],
	        'type_id'              =>  7,
	        'shop_id'              =>  $rs['shop_id'],
	        'remark'               =>  '享受了消费金额累积升级活动',
	    ];
	    
	    $sw1 = M('activity_participate')->add($aData);
	    if (!$sw1) {
	        $msg = '$sw1失败';
	        goto error;
	    }
	    
	    $sql = 'update `ylh_activity` SET `sale_num` = sale_num+1, `payment_num` = payment_num+1' . ' WHERE `id`=' . $activityId;

	    if (false == $do->execute($sql)) {
	        $msg = '$sql失败';
	        goto error;
	    }
	    
	    $data  =   [
	        'orderID'   =>  $rs['s_no'],
	        'money'     =>  $rs['goods_price_edit'],
	        'securityID'=>  'ASDSQWES2564DSA54SA21DAS',
	    ];
	    $res   =   $this->curl('/Erp/addSpecialMoney', $data, 1);
	    if ($res['code'] != 1) {
	        $msg = 'res__' . $res['msg'] . $res['info'];
	        goto error;
	    }
	    
	    $do->commit();
	    $this->ajaxReturn(['code' => 1, 'msg' => '操作成功']); 
	    error:
	       $do->rollback();
	       $this->ajaxReturn(['code' => 0, 'msg' => $msg]);
	}
	
	
	private function getData($id = null, $map = []) {
	    $do=D($this->fcfg['modelname']);
	    if (empty($map)) {
	        $map    =   [
	            'pay_type'  =>  ['in', '1,3,4'],
	            'atime'     =>  ['lt', '2016-09-23 00:00:00'],
	            'status'    =>  ['gt', 3],
	            'id'        =>  $id
	        ];
	    }
 	    $rs=$do->relation(true)->where($map)->find();
 	    if (!$rs) return false;
 	    if (!is_null($id)) {
 	        $rs['activity']=M('activity_participate')->field('id,type_id,status')->where(['s_no' => $rs['s_no'], 'type_id' => 7, 'status' =>2])->find();
 	        if (empty($rs['activity'])) return false;
 	    }
	    return $rs;
	}
}