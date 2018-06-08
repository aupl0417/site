<?php
/**
* 此文件为根据表单模板创建
*/
namespace Admin\Controller;
use Think\Controller;
class AppealController extends CommonModulesController {
	protected $name 			='申诉管理';	//控制器名称
    protected $formtpl_id		=171;			//表单模板ID
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
            'status'    =>  10,
        ];
    	$this->_index(['map' => $map]);
    	$btn=array('title'=>'操作','type'=>'html','html'=>'<div data-url="'.__CONTROLLER__.'/view/id/[id]" data-id="[id]" class="btn btn-sm btn-primary btn-rad btn-trans btn-block m0 btn-view">仲裁</div>','td_attr'=>'width="100" class="text-center"','norder'=>1);
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
	    
	    
	    foreach ($rs['logs'] as $i => &$val) {
            if($val['remark']) $rs['logs'][$i]['remark'] = html_entity_decode($val['remark']);
            if($val['images']) $rs['logs'][$i]['images'] = explode(',',rtrim($val['images'], ','));
            if(!empty($val['express_company_id']) && !empty($val['express_code'])) {
                $rs['express'][$i]['express_company']      =   M('express_company')->where(['id' => $val['express_company_id']])->getField('company');
                $rs['express'][$i]['express_company_id']   =   $val['express_company_id'];
                $rs['express'][$i]['express_code']         =   $val['express_code'];
            }
            //是否有退货地址
            if($val['status']==4){
                $rs['address']=html_entity_decode($val['remark']);
            }
	    }
	    $rs['express'] =   array_values($rs['express']);
	    $this->assign('statusName', $this->getStatusName($rs['orders_status']));
	    $this->assign('rs',$rs);
	    $this->display();
	}
	
	/**
	 * 添加日志
	 */
	public function logs_add(){
	    if(I('post.referee')=='') $this->ajaxReturn(['status' => 'warning','msg' =>'请选择判决结果！']);
	    if(I('post.remark')=='') $this->ajaxReturn(['status' => 'warning','msg' =>'请输入留言或备注！']);
	    $do    =   M('refund');
	    $rs    =   $do->where(['id' => I('post.r_id'), 'status' => 10])->find();
	    if (!$rs) $this->ajaxReturn(['code' => 0, 'msg' => '申诉不存在！']);
	    $status=   M('refund_logs')->where(['r_no' => $rs['r_no'], '_string' => 'status != 10'])->order('id desc')->getField('status');
	    $remark=   null;
	    $do->startTrans();
	    if (isset($_POST['referee']) && !empty(I('post.referee'))) {
	        $referee   =   I('post.referee');
	        switch ($referee) {
	            case 1:    //原判
	                if ($status) {
	                    $sw1   =   $do->where(['id' => $rs['id']])->save(['status' => $status, 'dotime' => date('Y-m-d H:i:s', NOW_TIME)]);
	                    if ($sw1 == false) goto error;
	                }
	                $remark    =   '雇员判决结果：维持原判';
	                break;
	            case 20:   //卖家赢
	                if ($rs['orders_status'] > 3) {
	                    switch ($status) {
	                        case 2:    //卖家拒绝后判定卖家赢
	                            $status=   20;
	                            $sw1   =   $do->where(['id' => $rs['id']])->save(['status' => $status, 'cancel_time' => date('Y-m-d H:i:s', NOW_TIME), 'dotime' => date('Y-m-d H:i:s', NOW_TIME)]);
	                            if ($sw1 == false) goto error;
	                            $remark    =   '雇员判决结果：取消售后';
	                            break;
	                        case 4:
	                            $status=   20;//卖家未收到货，判定卖家赢
	                            $sw1   =   $do->where(['id' => $rs['id']])->save(['status' => $status, 'cancel_time' => date('Y-m-d H:i:s', NOW_TIME), 'dotime' => date('Y-m-d H:i:s', NOW_TIME)]);
	                            if ($sw1 == false) goto error;
	                            $remark    =   '雇员判决结果：取消售后';
	                            break;
	                        case 5:    //买家嫌弃卖家操作太慢判定卖家赢
	                            $status=   5;
	                            $sw1   =   $do->where(['id' => $rs['id']])->save(['status' => $status, 'dotime' => date('Y-m-d H:i:s', NOW_TIME)]);
	                            if ($sw1 == false) goto error;
	                            $remark    =   '雇员判决结果：维持原判';
	                            break;
	                        case 6:    //买家未收到售后商品判定买家赢
	                            $status=   100;
	                            $sw1   =   $do->where(['id' => $rs['id']])->save(['status' => $status, 'accept_time' => date('Y-m-d H:i:s', NOW_TIME), 'dotime' => date('Y-m-d H:i:s', NOW_TIME)]);
	                            if ($sw1 == false) goto error;
	                            $remark    =   '雇员判决结果：完成售后';
	                            $sw2 = M('orders_shop')->where(['s_no' => $rs['s_no']])->setInc('service_num', $rs['num']);
	                            if ($sw2 == false) goto error;
	                            $sw4 = M('orders_goods')->where(['id' => $rs['orders_goods_id']])->setInc('service_num', $rs['num']);
	                            if ($sw4 == false) goto error;
	                            break;
	                    }
	                    
	                } else {
	                    $status=   20;
	                    $sw1   =   $do->where(['id' => $rs['id']])->save(['status' => $status, 'cancel_time' => date('Y-m-d H:i:s', NOW_TIME), 'dotime' => date('Y-m-d H:i:s', NOW_TIME)]);
	                    if ($sw1 == false) goto error;
	                    $remark    =   '雇员判决结果：取消本次申请';
	                }
	                break;
	            case 100:  //买家赢
	                if ($rs['orders_status'] < 4) {
	                    switch ($status) {
	                        case 2:    //拒绝退货退款
	                               if ($rs['type'] == 1) { //退货并退款
	                                   $remark=   '雇员判决结果：卖家同意退货并退款';
	                                   //加上卖家收货地址！
	                                   //取卖家常用退货地址
	                                   $area=$this->cache_table('area');
	                                   $address=M('send_address')->where(['uid' => $rs['seller_id']])->order('is_default desc')->find();
	                                   $address_str=$area[$address['province']].' '.$area[$address['city']].' '.$area[$address['district']].' '.$area[$address['town']].' '.$address['street'].($address['postcode']?'('.$address['postcode'].')':'').'，'.$address['linkname'].'，'.$address['mobile'].($address['tel']?'，'.$address['tel']:'');
	                                   //$remark.= $address_str;
	                                   $status=    4;
	                                   $sw1   =    $do->where(['id' => $rs['id']])->save(['status' => $status, 'accept_time' => date('Y-m-d H:i:s', NOW_TIME), 'dotime' => date('Y-m-d H:i:s', NOW_TIME)]);
                                       $msg   = 'sw1';
	                                   if (!$sw1) goto error;
	                               } else if ($rs['type'] == 2) {  //只退款
	                                   $remark=   '雇员判决结果：卖家同意退款';
	                                   $ob    =   new \Common\Controller\RefereeController(['s_no' => $rs['s_no'], 'r_no' => $rs['r_no'], 'id' => $rs['id'], 'seller_id' => $rs['seller_id'], 'status' => $status]);
	                                   $res   =   $ob->run();
	                                   if ($res['code'] == 0) {
	                                       $mag   =   $res['msg'];
	                                       goto error;
	                                   }
	                                   $status=    100;
//	                                   $sw1   =    $do->where(['id' => $rs['id']])->save(['status' => $status, 'accept_time' => date('Y-m-d H:i:s', NOW_TIME), 'dotime' => date('Y-m-d H:i:s', NOW_TIME)]);
//                                       writeLog($do->getLastSql());
//	                                   $mag   =    'sw1';
//	                                   if (!$sw1) goto error;
	                               }
	                            break;
	                        case 5:    //退货时卖家未收到货
	                            $remark=   '雇员判决结果：卖家同意退款';
	                            $ob    =   new \Common\Controller\RefereeController(['s_no' => $rs['s_no'], 'r_no' => $rs['r_no'], 'id' => $rs['id'], 'seller_id' => $rs['seller_id'], 'status' => $status]);
	                            $res   =   $ob->run();
	                            if ($res['code'] == 0) {
	                                $mag   =   $res['msg'];
	                                goto error;
	                            }
	                            $status=   100;
	                            //$sw1   =   $do->where(['id' => $rs['id']])->save(['status' => $status, 'accept_time' => date('Y-m-d H:i:s', NOW_TIME), 'dotime' => date('Y-m-d H:i:s', NOW_TIME)]);
	                            //writeLog($do->getLastSql());
	                            //$mag   =   'sw1';
	                            //if (!$sw1) goto error;
	                            break;
	                    }
	                } elseif ($rs['orders_status'] > 3) {  //售后申请
	                    switch ($status) {
	                        case 2:    //卖家拒绝售后
	                            $status=   3;
	                            $remark=   '雇员判决结果：卖家同意售后';
	                            //加入卖家售后地址
	                            $area=$this->cache_table('area');
	                            $address=M('send_address')->where(['uid' => $rs['seller_id']])->order('is_default desc')->find();
	                            $address_str=$area[$address['province']].' '.$area[$address['city']].' '.$area[$address['district']].' '.$area[$address['town']].' '.$address['street'].($address['postcode']?'('.$address['postcode'].')':'').'，'.$address['linkname'].'，'.$address['mobile'].($address['tel']?'，'.$address['tel']:'');
	                            $remark.= $address_str;
	                            $sw1   =   $do->where(['id' => $rs['id']])->save(['status' => $status, 'dotime' => date('Y-m-d H:i:s', NOW_TIME)]);
	                            if ($sw1 == false) goto error;
	                            break;
	                        case 4:    //卖家未收到商品
	                            $status=   5;
	                            $remark=   '雇员判决结果：卖家确认收货';
	                            $sw1   =   $do->where(['id' => $rs['id']])->save(['status' => $status, 'dotime' => date('Y-m-d H:i:s', NOW_TIME)]);
	                            if ($sw1 == false) goto error;
	                            break;
	                        case 5:    //卖家操作太慢
	                            $status=   5;
	                            $remark=   '雇员判决结果：警告1次';
	                            $sw1   =   $do->where(['id' => $rs['id']])->save(['status' => $status, 'dotime' => date('Y-m-d H:i:s', NOW_TIME)]);
	                            if ($sw1 == false) goto error;
	                            break;
	                        case 6:    //买家未收到货
	                            $status=   6;
	                            $remark=   '雇员判决结果：警告1次';
	                            $sw1   =   $do->where(['id' => $rs['id']])->save(['status' => $status, 'dotime' => date('Y-m-d H:i:s', NOW_TIME)]);
	                            if ($sw1 == false) goto error;
	                            break;
	                    }
	                }
	                break;
	        }
	    }
	    if (!is_null($remark)) {
	        $remark   .=  '<br />'.I('post.remark');
	    } else {
	        $remark    =   I('post.remark');
	    }
	     
	    $data  =   [
	        'ip'		=>get_client_ip(),
	        'atime'		=>date('Y-m-d H:i:s'),
	        'r_id'		=>$rs['id'],
	        'r_no'		=>$rs['r_no'],
	        'status'	=>!is_null($status) ? $status : $rs['status'],
	        'type'		=>$rs['type'],
	        'a_uid'		=>session('admin.id'),
	        'remark'	=>$remark,
	    ];
	    if ($address_str) $data['address'] = $address_str;
	    $sw3   =   M('refund_logs')->add($data);
	    $msg   = 'sw3';
	    if (!$sw3) goto error;
	    
	    $do->commit();
	    $this->ajaxReturn(['status' => 'success','msg' =>'操作成功！']);
	    error:
	       $do->rollback();
	    $this->ajaxReturn(['status' => 'warning','msg' =>'操作失败！' . $mag]);
	}
	
	
	public function express() {
	    $express_company_id    =   I('get.company');
	    $express_code          =   I('get.code');
	    $res                   =   $this->curl('/Express/query_express2', ['company_id' => $express_company_id, 'express_code' => $express_code], 1);
	    $this->assign('rs', $res);
	    $this->display();
	}
	
	private function getStatusName($orders_status) {
	    $data  =   [];
	    switch ($orders_status) {
	        case 2:
	            $data=[
	               [1,'退款中'],
	               [2,'卖家拒绝退款'],
	               [3,'买家修改退款'],
	               [4,'卖家同意退款退货'],
	               [5,'买家寄回退货'],
	               [20,'取消退款'],
	               [100,'退款成功']
	            ];
	            break;
	        case 3:
	            $data=[
	               [1,'退款中'],
	               [2,'卖家拒绝退款'],
	               [3,'买家修改退款'],
	               [4,'卖家同意退款退货'],
	               [5,'买家寄回退货'],
	               [20,'取消退款'],
	               [100,'退款成功']
	            ];
	            break;
	        default:
	            $data=[
	               [1,'售后中'],
	               [2,'卖家拒绝售后'],
	               [3,'卖家同意售后'],
	               [4,'买家寄回商品'],
	               [5,'卖家收到商品'],
	               [6,'卖家寄出商品'],
	               [20,'取消售后'],
	               [100,'售后完成']
	           ];
	            break;
	    }
	    return $data;
	}
}