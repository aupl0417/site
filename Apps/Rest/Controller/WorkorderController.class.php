<?php
/**
+----------------------------------------------------------------------
| RestFull API 
+----------------------------------------------------------------------
| 工单管理
+----------------------------------------------------------------------
| Author: 李博
+----------------------------------------------------------------------
*/

namespace Rest\Controller;
use Common\Controller\WorkorderController as Wo;
class WorkorderController extends CommonController
{

	protected $action_logs = array('create','user_handle','delete');
	/**
	 * 获取工单列表
	 * @apram string 	$param['openid']
	 */
	public function plist(){
		$this->need_param = array('openid');
		$this->_need_param();
        $this->_check_sign();

        $list = (new Wo())->user_list(['uid' => $this->uid]);
        if($list['code'] == 1){
        	$this->apiReturn(1, ['data' => $list['data']]);
        }else{
        	$this->apiReturn($list['code'], ['msg' => $list['msg']]);
        }
	}

	/**
	 * 类型列表
	 */
	public function type_list(){
		$this->apiReturn(1, ['data' => (new Wo())->type_list()]);
	}

	/**
	 * 接收短信时间段列表
	 */
	public function sms_list(){
		$this->apiReturn(1, ['data' => (new Wo())->sms_list()]);
	}


	/**
	 * 创建工单
	 * @param int 		$param['openid']			买家/买家 openid
	 * @param string 	$param['title']				问题
	 * @param string 	$param['content']			问题描述
	 * @param int 		$param['type']				类型
	 * @param int 		$param['type2']				子类型
	 * @param int  		$param['mobile']			手机
	 * @param string 	$param['email']				邮箱 非必填
	 * @param int 		$param['smstime']			手机接收短信时段
	 * @param string 	$param['accessory']			附件 非必填
	 */
	public function create(){

		$this->need_param = array('openid','title','content','type','type2','mobile','smstime');
		if($_POST['accessory']){
			$this->need_param[] = 'accessory';
		}
		$this->_need_param();
        $this->_check_sign();

        $data['uid'] 		= $this->uid;
        $data['title'] 		= I('post.title');
        $data['content'] 	= I('post.content');
        $data['type'] 		= I('post.type');
        $data['type2'] 		= I('post.type2');
        $data['mobile'] 	= I('post.mobile');
        $data['email'] 		= I('post.email');
        $data['smstime'] 	= I('post.smstime');
		$data['accessory'] 	= I('post.accessory');

		$check = new \Org\Util\CheckData($data['mobile']);
		if(! $check->is_mobile()){
			$this->apiReturn(47, ['msg' => '手机号码格式错误']);
		}

		$result = (new Wo())->create($data);
		if($result['code'] == 1){
			$this->apiReturn(1);
		}else{
			$this->apiReturn($result['code'], ['msg' => $result['msg']]);
		}

	}

	/**
	 * 用户 处理工单
	 * @param $param['openid']
	 * @param $param['w_no']
	 * @param $param['content']
	 */
	public function user_handle(){

		$this->need_param = array('openid','w_no','content');
		$this->_need_param();
        $this->_check_sign();

        $data['uid'] 			= $this->uid;
        $data['w_no'] 			= I('post.w_no');
        $data['content'] 		= I('post.content');
        # $data['accessory'] 		= I('post.accessory');

        $result = (new Wo())->user_handle($data);

        if($result['code'] == 1){
			$this->apiReturn(1);
		}else{
			$this->apiReturn($result['code'], ['msg' => $result['msg']]);
		}

	}
	/**
	 * 工单详情
	 */
	public function view(){
		$this->need_param = array('openid','w_no');
		$this->_need_param();
        $this->_check_sign();

        $res = (new Wo())->view(['w_no' => I('post.w_no')]);
        if($res['code'] == 1){
        	$this->apiReturn(1, ['data' => $res['data']]);
        }else{
        	$this->apiReturn($res['code'],['msg' => $res['msg']]);
        }
	}

	/**
	 * 删除工单
	 */
	public function delete(){
		$this->need_param = array('openid','w_no');
		$this->_need_param();
        $this->_check_sign();

        $res = (new Wo())->delete(['w_no' => I('post.w_no')]);
        if($res['code'] == 1){
        	$this->apiReturn(1);
        }else{
        	$this->apiReturn(0);
        }
	}

}