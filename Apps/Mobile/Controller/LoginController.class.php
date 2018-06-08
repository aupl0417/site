<?php
/**
 * -------------------------------------------------
 * 用户登录、注册
 * -------------------------------------------------
 * Create by Lazycat <673090083@qq.com>
 * -------------------------------------------------
 * 2017-01-13
 * -------------------------------------------------
 */
namespace Mobile\Controller;
use Think\Controller;
class LoginController extends CommonController {
    public function index(){
		$this->display();
    }
	
	public function check_login(){
		if(I('post.code')){
			$this->checkVerify(I('post.code'));
		}
        $_POST['password']  = $this->password(I('post.password'));
        $res = $this->doApi2('/Erp/check_login',I('post.'));

        if($res['code'] == 1){
            session('user',$res['data']);
            S(md5(session_id()),$res['data']['erp_uid'],3600); //用于ERP快捷登录商城

            //cookie记录用户登录
            $this->cookie_user_encrypt($res['data']);
        }

        $this->ajaxReturn(['code' => $res['code'],'msg' => $res['msg']]);
	}
    /**
     * 注册类型
     */
    public function register(){
        $this->display();
    }

    /**
     * 个人注册
     */

    public function register_person(){
        $res = $this->doApi2('/Help/agreement');
        $this->assign('rs',$res['data']);

        $this->display();
    }

    public function register_person_save(){
		$_POST['password'] = $this->password(I('post.password'));

        $res = $this->doApi2('/Erp/register_person',I('post.'));
        if($res['code'] == 1){
            session('user',$res['data']);
            S(md5(session_id()),$res['data']['erp_uid'],3600); //用于ERP快捷登录商城

            //cookie记录用户登录
            $this->cookie_user_encrypt($res['data']);
        }

        $this->ajaxReturn($res);
    }

    /**
     * 企业注册
     */
    public function register_company(){
        $res = $this->doApi2('/Help/agreement');
        $this->assign('rs',$res['data']);

        $stru = $this->doApi2('/Erp/stru_type');
        $this->assign('stru',$stru['data']);
        //var_dump($stru);

        $this->display();
    }

    public function register_company_save(){
/* 		if(!preg_match("/^[{\x{4e00}-\x{9fa5}]+$/u",I('post.company'))){
			$this->ajaxReturn(['code'=>0,'msg' => '公司名称必须是中文并且20个字符以内']);
		} */
		$_POST['password'] = $this->password(I('post.password'));
        $res = $this->doApi2('/Erp/register_company',I('post.'));
        if($res['code'] == 1){
            session('user',$res['data']);
            S(md5(session_id()),$res['data']['erp_uid'],3600); //用于ERP快捷登录商城

            //cookie记录用户登录
            $this->cookie_user_encrypt($res['data']);
        }

        $this->ajaxReturn($res);
    }


    /**
     * 发送短信验证码
     */
    public function smscode(){
		if(I('post.username')){
			$data['username']   = I('post.username');
			$data['mobile'] = I('post.mobile');
			$result = $this->doApi2('/Erp/CheckUserMobile',$data);
			if($result['code'] == 1){
				$res = $this->doApi2('/Erp/smscode',['mobile' => I('post.mobile')]);
				$this->ajaxReturn($res);
			}
			$this->ajaxReturn($result);
		}else{
			$res = $this->doApi2('/Erp/smscode',['mobile' => I('post.mobile')]);
			$this->ajaxReturn($res);
		}
    }


    /**
     * 检测手机号码是否被使用
     */
    public function check_mobile(){
        //C('DEBUG_API',true);
        $res = $this->doApi2('/Erp/check_mobile',['mobile' => I('post.mobile')]);
        $this->ajaxReturn($res);
    }

    /**
     * 检测昵称是否被使用
     */
    public function check_username(){
        //C('DEBUG_API',true);
		$data['username'] = I('post.username');
		$data['referrer'] = I('post.referrer');

        $res = $this->doApi2('/Erp/checkUserAndReferrer',$data);
        $this->ajaxReturn($res);
    }

    /**
     * 获取分享人资料
     */
    public function ref_user(){
        $res = $this->doApi2('/Erp/ref_user',['username' => I('post.username')]);
        $this->ajaxReturn($res);
    }


	/**
	* 是否已登录
	*/
	public function is_logined(){
		if(session('user')) $result['code']=1;
		else $result['code']=0;

		$this->ajaxReturn($result);
	}
	/**
	* 是否已认证
	*/
	public function is_authed(){
		if(session('user.is_auth')>0) $result['code']=1;
		else $result['code']=0;

		$this->ajaxReturn($result);
	}	
    /**
    * 登出
    */
    public function logout(){
        /*
		session('user',null);
        cookie('user',null);
        redirect('/');
		*/

        $this->doApi2('/App/logout',['openid' => session('user.openid')]);
		session('user',null);
        cookie('remember',null);
        cookie('wap_status',null);
		$result['code']=1;
		$this->ajaxReturn($result);
    }


    /**
     * 找回密码
     */
    public function forget(){
        $this->display();
    }
	
	/**
     * 找回密码第一步
     */
    public function forget_step1(){
		// C('DEBUG_API',true);
		$data['username'] = I('post.username');
		$data['smscode']  = I('post.smscode');
		$data['mobile']   = I('post.mobile');
		
        $res = $this->doApi2('/Erp/forgot_password_step1',$data);
        $this->ajaxReturn($res);
    }
	
	/**
     * 找回密码第二步
     */
    public function forget_step2(){
		$data['signcode'] = I('get.signcode');
		$data['erp_uid']  = I('get.erp_uid');

		$this->assign('data',$data);
		$this->display();
    }
	/**
     * 保存新密码
     */
    public function save_forget_step2(){
		if(I('post.password2') != I('post.password')){
			$this->ajaxReturn(['code' => 0,'msg'=>'登录密码和确认密码不一致']);
		}
		$data['password'] = $this->password(I('post.password'));
		$data['signcode'] = I('post.signcode');
		$data['erp_uid']  = I('post.erp_uid');

        $res = $this->doApi2('/Erp/forgot_password_step2',$data);
        $this->ajaxReturn($res);
    }
	//验证码
    public function code() {
        //ob_clean();
        $h  =   isset($_GET['h']) ? I('get.h') : 40;
        $w  =   isset($_GET['w']) ? I('get.w') : 100;
        $s  =   isset($_GET['s']) ? I('get.s') : 14;
        $l  =   isset($_GET['l']) ? I('get.l') : 4;
        $Verify = new \Think\Verify;
        $Verify->useImgBg = false;
        $Verify->imageH   = $h;
        $Verify->imageW   = $w;
        $Verify->fontSize = $s;
        $Verify->fontttf  = '5.ttf';
        $Verify->useNoise = false;
        $Verify->length   = $l;
        $Verify->entry();
    }
	/**
     * 检测验证码
     */
    private function checkVerify($code) {
		$verify = new \Think\Verify;
		if (false == $verify->check($code)) {
			$this->ajaxReturn(array('code' => 0, 'msg' => '图形验证码错误'));
		}
		unset($code);
    }
}