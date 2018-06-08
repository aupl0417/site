<?php
namespace Common\Controller;

use Think\Controller;
/**
 * 工单管理
 */

class WorkorderController extends Controller
{

	public $status = array(
		0 => '待处理',
		1 => '工单完成',
		2 => '处理中',
	);

	public $smstime = array(
		0 => '任何时间',
		1 => '9:00-18:00',
		2 => '从不接收',
	);



	/**
	 * 用户 我的工单列表
	 * @param int 		$param['uid']
	 */
	public function user_list($param){
		
		$res = array();
		# $res = M('workorder')->where(['uid' => $param['uid']])->order('id desc')->select();
		$res = D('Common/WorkorderView')->where(['uid' => $param['uid']])->order('id desc')->select();
		if(! empty($res)){
			$ts = $this->type_list();
			foreach ($res as $ko => $vo) {
				$res[$ko]['type_name'] = $ts[$vo['type']]['name'];
				$res[$ko]['type2_name'] = $ts[$vo['type']]['child'][$vo['type2']]['name'];
				$res[$ko]['status_name'] = $this->status[$vo['status']];
				$res[$ko]['smstime_name'] = $this->smstime[$vo['smstime']];
			}
			return ['code' =>1, 'data' => $res];
		}else{
			return ['code' => 3, 'msg' => '没有工单记录'];
		}
	}



	/**
	 * 雇员 工单列表 查找全部
	 */
	public function work_list($param){
		$res = array();
		# $res = M('workorder')->order('id desc')->select();
		$res = D('Common/WorkorderView')->order('id desc')->select();
		if(! empty($res)){
			$ts = $this->type_list();
			foreach ($res as $ko => $vo) {
				$res[$ko]['type_name'] = $ts[$vo['type']]['name'];
				$res[$ko]['type2_name'] = $ts[$vo['type']]['child'][$vo['type2']]['name'];
				$res[$ko]['status_name'] = $this->status[$vo['status']];
				$res[$ko]['smstime_name'] = $this->smstime[$vo['smstime']];
			}
			return ['code' =>1, 'data' => $res];
		}else{
			return ['code' => 3, 'msg' => '没有工单记录'];
		}
		
	}

	/**
	 * 类型列表
	 */
	public function type_list(){
		$ts = M('workorder_type')->field('id,pid,name')->select();
		$ts_n = array();
		$ts_0 = array();
		foreach ($ts as $vo) {
			if($vo['pid'] == 0){
				$ts_0[$vo['id']] = $vo;
			}else{
				$ts_n[$vo['id']] = $vo;
			}
		}
		$ts = array();
		foreach ($ts_0 as $vo) {
			foreach ($ts_n as$v) {
				$ts_0[$v['pid']]['child'][$v['id']] = $v;
			}
		}
		return $ts_0;
	}






	/**
	 * 创建工单
	 * @param int 		$param['uid']				买家/买家 id
	 * @param string 	$param['title']				问题
	 * @param string 	$param['content']			问题描述
	 * @param int 		$param['type']				类型
	 * @param int 		$param['type2']				子类型
	 * @param int  		$param['mobile']			手机
	 * @param string 	$param['email']				邮箱
	 * @param int 		$param['smstime']			手机接收短信时段
	 * @param string 	$param['accessory']			附件
	 */
	public function create($param){
		$param['status'] = 0;
		$param['w_no'] = $this->create_orderno();

		$msg = array();
		M()->startTrans();
		
		if(! D('Common/Workorder')->create($param) || ! $id = D('Common/Workorder')->add()){
			$msg[] = D('Common/Workorder')->getError();
			goto error;
		}

		$data['w_no'] 				= $param['w_no'];
		$data['uid'] 				= $param['uid'];
		$data['work_id'] 			= 0;
		$data['workorder_status'] 	= 0;
		$data['content'] 			= $param['content'];
		$data['accessory'] 			= $param['accessory'];
		$log = $this->logs($data);
		if($log !== true){
			$msg[] = $log;
			goto error;
		}
		success:
			M()->commit();
			return ['code' => 1];
		error:
			M()->rollback();
			return ['code' => 0, 'msg' => $msg];
	}


	/**
	 * 用户 处理工单
	 * @param $param['w_no']
	 * @param $param['uid']
	 * @param $param['content']
	 */
	public function user_handle($param){

		$param['work_id'] = 0;

		# 工单是否存在
		$one = M('workorder')->field('id,status')->where(['w_no' => $param['w_no']])->find();
		if(! isset($one['id'])){
			return ['code' => 3, 'msg' => '没有找到该工单'];
		}
		# 是否状态已完成
		if($one['status'] == 1){
			return ['code' => 0, 'msg' => '该工单已处理完,不能再修改'];
		}
		
		$param['workorder_status'] = $one['status'];
		# 处理结果
		$r = $this->logs($param);
		if($r === true){
			return ['code' => 1];
		}else{
			return ['code' => 0,'msg' => $msg];
		}
	}

	/**
	 * 雇员 处理工单
	 * @param $param['w_no']
	 * @param $param['work_id']
	 * @param $param['content']
	 */
	public function work_handle($param){
		$param['uid'] = 0;
		# 工单是否存在
		$one = M('workorder')->field('id,status')->where(['w_no' => $param['w_no']])->find();
		if(! isset($one['id'])){
			return ['code' => 3, 'msg' => '没有找到该工单'];
		}
		# 是否状态已完成
		if($one['status'] == 1){
			return ['code' => 0, 'msg' => '该工单已处理完,不能再修改'];
		}
		M()->startTrans();
		$msg = array();
		# 有存在处理完成的情况
		if($param['status'] == 1){
			$r1 = M('workorder')->where(['w_no' => $param['w_no']])->data(['status' => 1])->save();
			if($r1 === false){
				$msg[] = M('workorder')->getError();
				goto error;
			}
		}

		$param['workorder_status'] = $one['status'];
		# 处理记录
		$r2 = $this->logs($param);
		if($r2 !== true){
			$msg[] = $r2;
			goto error;
		}

		success:
			M()->commit();
			return ['code' => 1];
		error:
			M()->rollback();
			$msg[] = $r2;
			return ['code' => 0, 'msg' => $msg];
	}


	/**
	 * 工单详情
	 * @param string $param['w_no']
	 */
	public function view($param){
		$one = M('workorder')->where(['w_no' => $param['w_no']])->find();
		if(! isset($one['id'])){
			return ['code' => 3, 'msg' => '没有找到记录'];
		}

		
		$one['status_name'] = $this->status[$one['status']];

		$logs = D('Common/WorkorderLogsRelation')->relation(true)->where(['w_no' => $one['w_no']])->select();
		
		$ts = $this->type_list();
		foreach ($logs as $ko => $vo){
			$logs[$ko]['status_name'] = $this->status[$vo['workorder_status']];
		}
		return ['code' => 1, 'data' => ['data' => $one, 'logs' => $logs]];
	}



	/**
	 * 删除工单
	 * @param string $param['w_no']
	 */
	public function delete($param){

		if(M('workorder')->where(['w_no' => $param['w_no']])->delete()){
			return ['code' => 1];
		}else{
			return ['code' => 0];
		}
	}





	/**
	 * 工单日志
	 */
	public function logs($data){

		$res =  M('workorder_logs')->data($data)->add();

		if($res){
			return true;
		}else{
			return M('workorder_logs')->getError();
		}
	}



	public function sms_list(){
		return $this->smstime;
	}


}