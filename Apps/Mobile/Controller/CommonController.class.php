<?php
namespace Mobile\Controller;
use Think\Controller;
class CommonController extends Controller {
	protected $api_cfg 	=array(); 	//API接口配置
	protected $data 	=array();	//要请求的数据
    protected $token;               //接口授权token
    protected $sw;                  //记录事务处理结果

	public function _initialize() {
		$cfg=D('Common/Config')->config(array('cache_name'=>'cfg'));
        $cfg['erp']['domain']=eval(html_entity_decode($cfg['erp']['domain']));
		C('cfg',$cfg);

		C('sub_domain',sub_domain());

        //保持用户登录状态
        if(CONTROLLER_NAME != 'Login' && ACTION_NAME != 'index') $this->cookie_user_decrypt();


		$this->api_cfg = $this->get_api_cfg();

        //每隔10分钟更新一次token
		$cache_name 	= 'apitoken_'.session_id();
		$this->token 	= S($cache_name);
		if(empty($this->token)) {
			$res = $this->doApi2('/Auth/token', $this->api_cfg);
			if ($res['code'] == 1) {
				$this->token = $res['data']['token'];
				S($cache_name,$res['data']['token'],600);
			} else {
				echo $res['msg'];
				exit();
			}
		}

        if($_SESSION['user']['openid'] && empty($_POST['openid'])) $_POST['openid'] = session('user.openid');

        //登录状态下
        if($_SESSION['user']['id']){
            $total = $this->doApi2('/Cart/total',['openid' => session('user.openid')]);
            $this->assign('cart_total',$total['data']);
            //dump($total);
        }
        $this->assign('nav_action', __ACTION__);
		//$this->assign('sex',array('保密','男','女'));
	}

	/**
	* 检查是否登录
	*/
	public function check_logined(){
		if(empty($_SESSION['user'])) redirect('/Login');

        //判断服务器端是否保存登录状态
        /*
        $res = $this->doApi2('/App/logined',['openid' => session('user.openid')]);
        if($res['code'] != 1) {
            session('user',null);
            cookie('remember',null);
            cookie('wap_status',null);
            redirect('/Login');
            exit();
        }
        */


	}

	public function ajax_check_logined(){
        if(empty($_SESSION['user'])) $this->ajaxReturn(['code' => 10,'msg' => '请先登录！']);

        //判断服务器端是否保存登录状态
        /*
        $res = $this->doApi2('/App/logined',['openid' => session('user.openid')]);
        if($res['code'] != 1) {
            session('user',null);
            cookie('remember',null);
            cookie('wap_status',null);
            $this->ajaxReturn(['code' => 10,'msg' => $res['msg']]);
        }
        */
    }

	/**
	* 接口参数
	*/
	public function get_api_cfg(){
		//$param=C('cfg.api');
		//unset($param['apiurl']);
        $param = [
            'appid'             => 11,
            'access_key'        => 'a322182508a9200fae7cdceeb29bc049',
            'secret_key'        => 'e1061393a4ddd0b805d92da83477be48',
            'sign_code'         => '53d45f1a00761ef0bae02fdf73d3b284',
            'device_id'         => session_id(),
        ];
		return $param;
	}


	/**
	* 错误提示页面
	*/
	public function err($msg){
		$this->error($msg);
	}


	/**
	* 文件上传
	*/
    public function _upload($field,$width=0,$height=0){
        $this->ajax_check_logined();

        if (empty($_FILES)) {
	        $result['code']=53;
	        $result['status']=0;
	        $result['msg']=C('error_code')[$result['code']];
	        return $result;
        }

	    //充许上传格式
	    $ext_arr    =array('gif','jpg','jpeg','png');
	    $file_ext   =strtolower(pathinfo($_FILES[$field]['name'], PATHINFO_EXTENSION));
	    if(!in_array($file_ext,$ext_arr)){
	       	$result['code']=52;
	       	$result['status']=0;
	       	$result['msg']=C('error_code')[$result['code']];
	        return $result;
	    }
	    //充许上传文件大小，限制3M
	    $maxsize=1024*1024*3;
	    $filesize=filesize($_FILES[$field]['tmp_name']);
	    if($filesize>$maxsize){
	        $result['code']=51;
	        $result['status']=0;
	        $result['msg']=C('error_code')[$result['code']];
	        return $result;
	    }

		//尺寸要求
		$imginfo=getimagesize($_FILES[$field]['tmp_name']);
		if($width>0 && $height>0){
			if($imginfo[0]!=$width || $imginfo[1]!=$height) {
				$result['code'] 	= 550;	//图片尺寸不符合要求
				$result['status']	= 0;
				$result['msg']		= C('error_code')[$result['code']];
				return $result;
			}


		}

	    $res=$this->doApi('/Upload/upload2',array('openid'=>session('user.openid'),'content'=>file_get_contents($_FILES[$field]['tmp_name'])),'content,openid');

	    $result['code']		=$res->code;
	    $result['status']	=1;
	    $result['msg']		=$res->msg;
	    $result['url']		=$res->data->url;
		$result['imginfo']	=$imginfo;

	    return $result;
   
    }


    /**
     * 创建用户登录cookie
     * Create by Lazycat
     * 2017-02-20
     */
    public function cookie_user_encrypt($data){
        $jm=new \Think\Crypt\Driver\Crypt();
        cookie('wap_status',array('ststus' => $jm::encrypt(implode(',',[$data['openid'],$data['nick'],time()]),C('CRYPT_PREFIX'))),86400);
    }

    /**
     * 从cookie读取登录状态
     * Create by Lazycat
     * 2017-02-20
     */
    public function cookie_user_decrypt(){
        $cookie_user = cookie('wap_status');

        if($cookie_user && empty($_SESSION['user']['id'])){
            $jm     = new \Think\Crypt\Driver\Crypt();
            $decode = $jm::decrypt($cookie_user['ststus'],C('CRYPT_PREFIX'));
            $decode = explode(',',$decode);

            if(($decode[2]+86400) > time()) {   //验证cookie是否在有效期内
                $user = M('user')->where(['openid' => $decode[0], 'nick' => $decode[1]])->field('id,status,up_uid,nick,name,email,mobile,level_id,face,openid,loginum,shop_type,shop_id,erp_uid,type,is_auth')->find();
                if ($user) {
                    $level = $this->cache_table('user_level');
                    $user['level_name'] = $level[$user['level_id']];
                    session('user', $user);
                    cookie('wap_status', $cookie_user, 86400);
                }
            }
        }
    }

}