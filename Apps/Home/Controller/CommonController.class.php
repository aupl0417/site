<?php
namespace Home\Controller;
use Think\Controller;
use Common\Builder\BuilderForm;
class CommonController extends Controller {
    
    protected $_data    =   [];
    protected $_apicfg  =   [];
    protected $_res;
    protected $_run;
    protected $api_cfg; //RestFull接口
	public function _initialize() {
        if (MODULE_NAME == 'Home')  redirect(DM('seller'));
		if(IS_POST) {
		    //获取商品详情
		    if (isset($_POST['key']) && isset($_POST['val'])) {
		        $_POST[$_POST['key']]   =   $_POST['val'];
		        unset($_POST['val'],$_POST['key']);
		    }
		    $this->_data  =   array_merge(I('post.'), getApiCfg());
		} else {
		    //手机访问301定向到手wap版
// 		    if(isMobile() && !in_array($_SERVER['HTTP_HOST'],array('wap.'.C('DOMAIN'),'m'.C('DOMAIN')))){
// 		        send_http_status(301);
// 		        $url = $this->parseUrl();//获取转换的url
// 		        header('location:'.DM('wap'));
// 		    }
		    cookie('referer', $_SERVER['HTTP_REFERER'] ? $_SERVER['HTTP_REFERER'] : DM('seller'));
		    $this->assign('ref', cookie('referer'));
		    $this->assign('cfg', getSiteConfig());
		    //$this->seo();
		}

        C('cfg',getSiteConfig());
        $this->api_cfg = C('cfg.api');
        unset($this->api_cfg['apiurl']);

		if (isset($_SESSION['user']['id'])) {
		    if (cookie('remember')) {
		        cookie('remember', cookie('remember'), deCryptRestUri(cookie('rememberSaveTime')));
		    }
		    $this->assign('user', session('user'));
		} elseif (cookie('remember')) {
		    $this->autoLogin();
		}
	}
	
	public function _empty() {
	    header('HTTP/1.1 404 Not Found');
	    header('Status:404 Not Found');
	    $this->seo(array('title' => 'sorry,您访问的页面不存在'));
	    $this->display('Home@Empty:404');
	}
	
	/**
	 * 不返回404的接口
	 * @param unknown $url
	 */
	public function no404($url) {
	    $arr = array(
	        '/Sms/check_smscode',           //检测短信
	        '/Auth/auth_status',            //获取认证状态
	        '/user/forget_password_pay_check',
	        '/Erp/check_mobile',            //检测手机号
	    );
	    if (in_array($url, $arr)) {
	        return true;
	    }
	    return false;
	}
    
	/**
	 * 需要认证图形验证码的接口,API URL
	 * @param unknown $uri
	 */
	protected function isVerify($url) {
	    $arr   =   [
	        '/Erp/Erp/change_pay_password',    //修改登录密码
	        '/Erp/sms_code',       //获取短信
	    ];
	    if (S(md5('check_login_vcode' . get_client_ip()))) $arr[] =   '/Erp/check_login';    //用户登录
	    if (in_array($url, $arr)) {
	        return true;
	    }
	    return false;
	}
	
	/**
	 * 是否需要操作session
	 * @param string $url
	 */
	protected function setSession($url) {
	    $arr = array(
	        '/Erp/check_login',    //登录
	        '/Erp/register',       //注册
	        '/Erp/register_company',   //企业注册
	    );
	    if (in_array($url, $arr)) {
	        return true;
	    }
	    return false;
	}
	
	/**
	 * 调用API
	 * @param string $url   api的URL地址
	 * @param array $data   用户提交的数据
	 * @param string $field 过滤字段
	 */
	protected function api($url, $data = array(), $field = null) {
	    $data               =   !empty($data) ? array_merge($data, getApiCfg()) : getApiCfg();
	    $field			    =	!empty($field) ? $field . ',status,p,pagesize,action' : 'status,p,pagesize,action';
	    $data['pagesize']	=	isset($data['pagesize']) ? $data['pagesize'] : 10;
	    $data['action']		=	isset($data['action']) ? $data['action'] : __ACTION__;
	    $data['p']			=	I('get.p') > 0 ? I('get.p') : 1;
	    ksort($data);
	    $data['sign']       =   _sign($data, $field);
	    $this->_data        =   $this->curl($url, $data, 1);
	    return $this;
	}
	
	/**
	 * 需要登录调用API
	 * @param string $url   api的URL地址
	 * @param array $data   用户提交的数据
	 * @param string $field 过滤字段
	 */
	protected function authApi($url, $data = array(), $field = null) {
	    if (!$_SESSION['user']['openid']) $this->ajaxReturn(array('code' => 401, 'msg' => '请登录'));
	    $data['openid']     =  isset($data['openid'])?$data['openid']:session('user.openid');
	    $this->api($url, $data, $field);
	    return $this;
	}
	
	
	/**
	 * 设置前端显示变量
	 * @param string $name 变量名
	 * @param array  $data 变量值
	 */
	protected function with($name = 'data', $data = null) {
	    if (!$data) $data = ($this->_data['data'] ? $this->_data['data'] : $this->_data);
	    $this->assign($name, $data);
	}
	
	/**
	 * 创建表单对象
	 * @return \Common\Builder\BuilderForm
	 */
	protected function builderForm() {
	    return new BuilderForm();
	}
	
	/**
	 * 设置页面标题
	 * @param unknown $data
	 */
	protected function seo($data = array()) {
	    if (empty($data)) {
	        $data['title']      =   '请设置标题';
	        $data['keyword']    =   '请设置关键词';
	        $data['desc']       =   '请设置页面介绍';
	    }
	    $this->assign('seo', $data);
	}
	
	/**
	 * 自动登陆
	 */
	protected function autoLogin() {
	    if (cookie('remember')) {
	        $remember	=	cookie('remember');
	        $remember	=	unserialize(deCryptRestUri($remember));
	        // && $remember['session_id'] == session_id()
	        if ($remember['ip'] == get_client_ip()) {
	            session('user', $remember);
	            $this->assign('user', $remember);
	            //用于ERP同步登录
	            S(md5(session_id()),$remember['erp_uid'],3600);
                unset($remember);
	        } else {
	            cookie('remember', null);
	        }
	    }
	}
	
	/**
	 * 设置session
	 */
	protected function setSessions() {
	    $this->api('/Erp/check_login', ['username' => session('user.nick'), 'password' => session('user.password')]);
	    if ($this->_data['code'] == 1) {
	        session('user', $this->_data['data']);
	        $expire =   $this->_data['data']['remember'] == 1 ? 43200 : 3600;
	        cookie('rememberSaveTime', enCryptRestUri($expire), $expire);
	        $this->_data['data']['ip'] 		= 	get_client_ip();
	        S(md5(session_id()),$this->_data['data']['erp_uid'],3600);
	        cookie('remember', enCryptRestUri(serialize($this->_data['data'])), $expire);
	        unset($expire);
	    }
	}
	
	/**
	 * 判断是否为商家域名
	 * @param unknown $param
	 */
	private function parseUrl() {
	    $domain = substr($_SERVER['HTTP_HOST'], 0, strpos($_SERVER['HTTP_HOST'], '.'));
	    $url    = DM('wap', '/index/index');
	    if ($domain == 'item') {   //如果是商品链接
	        $goodsId = pathinfo(__SELF__, PATHINFO_FILENAME);
	        $url .= '?url=/Goods/view/id/'.$goodsId;
	    } else {   //其他域名
	        if (is_numeric($domain)) { 
	            $url .= '?url=/Shop/index/shop_id/' . $domain;
	        } else {
	            $shopId = M('shop')->where(['domain' => $domain])->getField('id');
	            if ($shopId) {
	                $url .= '?url=/Shop/index/shop_id/' . $shopId;
	            }
	        }
	    }
	    return $url;
	}
}