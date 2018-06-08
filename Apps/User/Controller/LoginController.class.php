<?php
namespace User\Controller;
use Common\Builder\R;
class LoginController extends AuthController {
    public function _initialize() {
        parent::_initialize();
    }
    
    public function index() {
        $this->builderForm()
        ->keyText('username', '用户名', 1)
        ->keyPass('password', '密码', 1)
        ->keyVcode('vcode', '图形验证码', 1)
        ->view();
        C('seo', ['title' => '用户登录']);
        $this->display();
    }
    /**
     * 用户登录
     * Create by liangfeng
     * 2017-09-06
     */
    public function login(){
        //dump(I('post.'));

        if(isset($_POST['vcode']) && !empty($_POST['vcode'])){
            $verify = new \Think\Verify;
            if (false == $verify->check(I('post.vcode'))) {
                $this->ajaxReturn(array('code' => 0, 'msg' => '图形验证码错误'));
            }
        }

        $data['username'] = I('post.username');
        $data['password']= $this->password(I('post.password'));
        //dump($data);
        $user = M('user')->where(['nick'=>$data['username'],'password'=>$data['password']])->find();
        //dump(M()->getlastsql());exit();

        if($user){
            if($user['status'] != 1){
                $reason = M('prohibit_user')->where(['uid' => $user['id']])->order("atime desc")->getField('reason');
                $reason = $reason ? $reason : '账号已被暂停使用！';
                $this->ajaxReturn(['code' => 0,'msg' => $reason]);
            }
            $data['last_login_time']    = date('Y-m-d H:i:s');
            $data['ip']				    = get_client_ip();
            $data['loginum']		    = $user['loginum']+1;


            M('user')->where(['id' => $user['id']])->save($data);

            session('user',$user);

            //用于APP保存登录状态（默认为7天）
            if($this->token['data']['device_id']) S('app_logined_'.$user['openid'], ['device_id' => $this->token['data']['device_id']], 86400 * 7);

            $res = ['code'=>1,'msg'=>'登录成功！'];

        }else{

            $res = R::getInstance(['url' => ['login' => '/Erp/check_login'], 'rest' => ['rest2'], 'data' => [$data]])->multiCurl();
            $res = $res['login'];
            if($res['code'] == 1){
                session('user',$res['data']);
            }
            $res['msg'] = '账户不存在或密码错误！';
        }

        $this->ajaxReturn($res);


    }
}