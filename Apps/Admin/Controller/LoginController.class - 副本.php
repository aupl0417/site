<?php
namespace Admin\Controller;
use Think\Controller;
class LoginController extends Controller {
	protected $api_url	='';		//API请求地址前缀
	protected $api_cfg 	=array(); 	//API接口配置		
	public function _initialize() {
		//设置
		$do=D('Config');
		$cfg=$do->config(array('cache_name'=>'cfg'));
		$cfg['api']['apiurl']	= DM('rest');
		C('cfg',$cfg);

		//api接口初始信息
		$tmp=C('cfg.api');
		unset($tmp['apiurl']);
		$this->api_cfg=$tmp;
		$this->api_url=C('cfg.api')['apiurl'];
		//dump($cfg);

        C('sub_domain',sub_domain());
	}

    public function index(){
		$this->display();
    }

    //ERP雇员登录
    public function check_login(){
		$Verify = new \Think\Verify();
		$isverify=$Verify->check(trim($_POST['vcode']));
		//if(empty($isverify)) $this->ajaxReturn(array('status'=>'warning','msg'=>'验证码错误！'));

		if(I('post.username')=='admin')	$this->_check_login();
			$this->_check_login();
		exit('1');
		$apiurl='/Erp/admin_login';
		$data['username']	=trim(strtolower(I('post.username')));
		$data['password']	=$this->password(trim(I('post.password')));

		if ($adminIsLogin) {
		    //$this->ajaxReturn(array('status'=>'warning','msg'=>'该雇员账号已在其他地方登陆。'));
		}
		$res=$this->doApi($apiurl,$data);
		//dump($res);exit;

        admin_login_log(array(
            'table'		=>'admin',
            'type'		=>'LOGIN',
            'post_data' =>I('post.'),
            'res'		=>$res->code,
            'sql'		=>'',
            'insid'		=>''
        ));

        if($res->code==1){
			//print_r(objectToArray($res->data));exit;
			session('admin',objectToArray($res->data));
			//dump($rs);

            //记录在线雇员
            /*
            M('admin_online')->where(['admin_id' => $res->data->id])->delete();
            $online_data = [
                'atime'     => time(),
                'etime'     => time(),
                'ip'        => get_client_ip(),
                'admin_id'  => $res->data->id,
                'username'  => $res->data->username,
                'name'      => $res->data->name,
                'session_id'=> session_id(),
            ];
            $insid = M('admin_online')->add($online_data);
            */

            //dump($online_data);
            //dump($insid); exit();

            //memcached记录在线雇员，永久缓存
            $this->online_admin(objectToArray($res->data));

			$jm=new \Think\Crypt\Driver\Crypt();
			cookie('admin',array('uid'=>$jm::encrypt($res->data->id,C('CRYPT_PREFIX'))));

			$this->ajaxReturn(array('status'=>'success','msg'=>'登录成功！'));
		}else{
			$this->ajaxReturn(array('status'=>'warning','msg'=>'账号或密码错误！'));
		}
    }
	
	//生成扫码登录的二维码
    public function qrcode(){
		Vendor("qrcode.phpqrcode.phpqrcode");
		$saoma ='admin_qrcode_'.md5(session_id());
		if(S($saoma) === false){
			$data =['session_id' => session_id(),'time' => time()];
			S($saoma,$data,30);
		}else{
			$data = S($saoma);
		}
		
		$errorCorrectionLevel =intval(3) ;//容错级别 
		$matrixPointSize = intval(5);//生成图片大小 
		//生成二维码图片 
		$object = new \QRcode();
		$object->png($saoma, false, $errorCorrectionLevel, $matrixPointSize, 2);
    }
	
	//检查扫码接口登录是否成功
    public function checked_login(){
		if(S('admin_'.session_id()) !==false){
			admin_login_log(array(
				'table'		=>'admin',
				'type'		=>'LOGIN',
				'res'		=>1,
				'sql'		=>'',
				'insid'		=>''
			)); 
			$res = S('admin_'.session_id());
			//print_r(objectToArray($res->data));exit;
			session('admin',$res);
			S('admin_'.session_id(),null);
			//dump($rs);

            /*
            //记录在线雇员
            M('admin_online')->where(['admin_id' => $res['id']])->delete();
            $online_data = [
                'atime'     => time(),
                'etime'     => time(),
                'ip'        => get_client_ip(),
                'admin_id'  => $res['id'],
                'username'  => $res['username'],
                'name'      => $res['name'],
                'session_id'=> session_id(),
            ];
            $insid = M('admin_online')->add($online_data);
            */

            //dump($online_data);
            //dump($insid); exit();

            //memcached记录在线雇员，永久缓存
            $this->online_admin($res);

			$jm=new \Think\Crypt\Driver\Crypt();
			cookie('admin',array('uid'=>$jm::encrypt($res['id'],C('CRYPT_PREFIX'))));

			$this->ajaxReturn(array('status'=>'success','msg'=>'登录成功！'));
		}else{
			$this->ajaxReturn(array('status'=>'warning','msg'=>'账号或密码错误！'));
		}
    }
    public function test(){
		
        dump(md5('123456'));
    }

    //使用本地雇员表数据登录
    public function _check_login(){
        //exit();
		$username=trim(strtolower(I('post.username')));
		$password=md5(I('post.password'));
		
		$map['password']=$password;
		$map['username']=$username;		
		$do=M('admin');
		if($rs=$do->where($map)->field('id,sid,username')->find()){
			$do->execute('update '.C('DB_PREFIX').'admin set logintime=now(),loginum=loginum+1,ip="'.get_client_ip().'" where id='.$rs['id']);
			//S('admin',$rs);
			session('admin',$rs);
			//dump($rs);

			$jm=new \Think\Crypt\Driver\Crypt();
			cookie('admin',array('uid'=>$jm::encrypt($rs['id'],C('CRYPT_PREFIX'))));

			$this->ajaxReturn(array('status'=>'success','msg'=>'登录成功！'));
		}else{
			$this->ajaxReturn(array('status'=>'warning','msg'=>'账号或密码错误！'));
			
		}    	
    }
	
	//登录验证	
	public function check_login_(){
		
		if(!empty($_POST['vcode'])){
			$Verify = new \Think\Verify();
			$isverify=$Verify->check(trim($_POST['vcode']));
			if(empty($isverify)) $this->ajaxReturn(array('status'=>'warning','msg'=>'验证码错误！'));
		}
		
		
		$do=D('AdminView');
		
		$username=trim(strtolower(I('post.username')));
		$password=md5(I('post.password'));
		
		$map['password']=$password;
		$map['username']=$username;
		
		if($rs=$do->where($map)->field('id,sid,username')->find()){
			$do->execute('update '.C('DB_PREFIX').'admin set logintime=now(),loginum=loginum+1,ip="'.get_client_ip().'" where id='.$rs['id']);
			//S('admin',$rs);
			session('admin',$rs);
			//dump($rs);

			$jm=new \Think\Crypt\Driver\Crypt();
			cookie('admin',array('uid'=>$jm::encrypt($rs['id'],C('CRYPT_PREFIX'))));

			$this->ajaxReturn(array('status'=>'success','msg'=>'登录成功！'));
		}else{
			$this->ajaxReturn(array('status'=>'warning','msg'=>'账号或密码错误！'));
			
		}
	}
	
	
	//登出
	public function logout(){
	    //在线雇员表中踢除
        //M('admin_online')->where(['admin_id' => session('admin.id')])->delete();

		session('admin',null);
		cookie('admin',null);
		//S(null);

		
		$js='<script>top.location.href="/Login";</script>';
		echo $js;
	}

	/*
	+-----------------------------------------------
	+ 验证码  2015-12-04  by enhong
	+------------------------------------------------
	*/

	Public function verify(){
		ob_clean();
		//import('ORG.Util.Image');
		//Image::buildImageVerify(4,1,'png',90,50);
		//中文验证码
		$Verify = new \Think\Verify();
		$Verify->useImgBg = false;
		$Verify->imageH   = 40;
		$Verify->imageW   = 168;		
		$Verify->fontSize = 18;
		$Verify->fontttf  = '5.ttf';
		$Verify->useNoise	=false;
		$Verify->length   = rand(4,5);
		$Verify->entry();
	}	

	//退出SEO设置
	public function seo_exit(){
		session('is_seo',null);
		$this->success('已退出SEO设置');
	}

	/**
     * memcached记录在线雇员，永久缓存
     * Create by Lazycat
     * 2017-02-05
     */
	public function online_admin($user){
	    if(empty($user)) reutrn;

        $user['ip']     = get_client_ip();
        $user['time']   = time();
        $user['atime']  = date('Y-m-d H:i:s');

        $online_admin = S('online_admin');
        if($online_admin){  //清除登录时间超过5分钟未更新的雇员
            foreach($online_admin as $key => $val){
                if(time() - $val['time'] > (5 * 60)){
                    unset($online_admin[$key]);
                }
            }
        }

        $online_admin[$user['id']] = $user;
        S('online_admin',$online_admin,0);
	}



}
