<?php
/**
 * -------------------------------------------------
 * 买家中心 - 设置
 * -------------------------------------------------
 * Create by Lazycat <673090083@qq.com>
 * -------------------------------------------------
 * 2017-01-14
 * -------------------------------------------------
 */
namespace Mobile\Controller;
use Think\Controller;
class SettingController extends CommonController {

    public function _initialize()
    {
        parent::_initialize(); // TODO: Change the autogenerated stub

        $this->check_logined();
    }

    /**
     * 设置首页
     */
    public function index(){
//		$account = $this->doApi2('/Erp/account',['erp_uid' => session('user.erp_uid')]);
//        $this->assign('account',$account['data']);
        $this->display();
    }

    /**
     * 个人资料
     */
    public function user_info(){
        $res = '';//$this->doApi2('/Erp/user_info',['openid' => session('user.openid')]);
        $this->assign('user',$res['data']);

        //var_dump($res);
        $this->display();
    }


	/**
     * 消息接收设置
     * Create by liangfeng
     * 2017-05-18
     */
    public function msg_setting(){
		$res = $this->doApi2('/User/getuser',['openid' => session('user.openid')]);
		$is_receive_msg = $res['data']['is_receive_msg'];
		
		$res = $this->doApi2('/Notice/get_msg_category');
		$categorys = $res['data'];
		if($is_receive_msg == 1){
			foreach($categorys as $k => $v){
				$categorys[$k]['is_receive'] = 1;
			}
		}else if($is_receive_msg == 0){
			foreach($categorys as $k => $v){
				$categorys[$k]['is_receive'] = 0;
			}
		}else{
			foreach($categorys as $k => $v){
				foreach($is_receive_msg as $ke => $va){
					if($ke == $v['id']){
						$categorys[$k]['is_receive'] = $va;
					}
				}
			}
		}
		$this->assign('categorys',$categorys);
        $this->display();
    }
	/**
     * 保存消息接收
     * Create by liangfeng
     * 2017-05-18
     */
	public function ajax_save_msg_setting(){
		if(isset($_POST['categorys'])){
			$res = $this->doApi2('/Notice/get_msg_category');
			$categorys = $res['data'];

			foreach($categorys as $v){
				$new_is_receive_msg[$v['id']] = '0';
				foreach(I('post.categorys') as $va){
					if($v['id'] == $va){
						$new_is_receive_msg[$v['id']] = '1';
						break;
					}
				}
			}
			$data['is_receive_msg'] = json_encode($new_is_receive_msg);			
			$re = M('user')->where(['id'=>$_SESSION['user']['id']])->data($data)->save();
			if($re!==false){
				$res = ['code'=>1,'msg'=>'修改成功'];
			}else{
				$res = ['code'=>0,'msg'=>'操作失败'];
			}
		}else{
			$res = ['code'=>0,'msg'=>'操作失败'];
		}
		$this->ajaxReturn($res);
		
	}

}