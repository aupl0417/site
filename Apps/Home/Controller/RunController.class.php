<?php
/**
// +----------------------------------------------------------------------
// | YiDaoNet [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) www.yunlianhui.com  All rights reserved.
// +----------------------------------------------------------------------
// | Author: Mercury <mercury@jozhi.com.cn>
// +----------------------------------------------------------------------
// | Create Time: 2016 下午6:45:40    用于获取数据的控制器              字段替换请使用双下划线！
// +----------------------------------------------------------------------
*/
namespace Home\Controller;
use Common\Builder\Auth;
use Common\Common\Apiurl;
use Qiniu\Qiniu;
import('Vendor.Qiniu.Qiniu');

class RunController extends CommonController {
    protected $_token;
	public function _initialize() {
	    parent::_initialize();
		if(isset($_SERVER['HTTP_ACCEPT_MOUDLE']) && !empty($_SERVER['HTTP_ACCEPT_MOUDLE'])) {
			$this->_apicfg       =   Apiurl::url(ucwords($_SERVER['HTTP_ACCEPT_MOUDLE']), deCryptRestUri($_SERVER['HTTP_ACCEPT_ACTION']));
		} else {
		    $tmpModule = ucwords(strstr($_SERVER['HTTP_HOST'], '.', true));
		    //if ($tmpModule == 'Mall') $tmpModule = 'User';
			$this->_apicfg       =   Apiurl::url($tmpModule, deCryptRestUri($_SERVER['HTTP_ACCEPT_ACTION']));
		}
		if (empty($this->_apicfg)) {
		    log_add('pcapicfg', ['action' => deCryptRestUri($_SERVER['HTTP_ACCEPT_ACTION']), 'time' => date('Y-m-d H:i:s', NOW_TIME), 'url' => $_SERVER['HTTP_HOST']]);
		}
	}
	
	/**
	 * 普通提交
	 */
    public function index() {
        if (IS_POST) {
            if (!$this->_apicfg['action']) {
                return;
            }
            
            $this->key2md5();           //是否包含需要加密的字段
            $this->resetFields();       //是否包含重置的字段
            $this->checkVerify();       //是否需要图形验证
            $this->hash();              //token
            $this->tj();                //统计相关设置
            $this->nosign();            //判断是否有nosign字段提交
            if (isset($this->_apicfg['callback'])) {
                $this->_apicfg['nosign']    .=  ','.call_user_func($this->_apicfg['callback'], $this->_data);
            }
            
            ksort($this->_data);
            $this->_data['sign']    =   _sign($this->_data, ltrim($this->_apicfg['nosign'], ','));
            $this->_data            =   $this->curl($this->_apicfg['action'], $this->_data);
            $this->_data = (json_decode($this->_data, true));
            
            //判断是否有token
            if ($this->_token) {
                if ($this->_data['code'] == 1) {
                    $this->checkToken($this->_token, 1); //清除token
                } else {
                    $this->checkToken($this->_token, 2); //清除token
                }
            }
            if ($this->_data['code'] == 1) {
                if (isset($this->_apicfg['cache'])) {   //设置缓存
                    call_user_func_array($this->_apicfg['cache'], [$this->_data['data'], I('post.mobile')]);
                    $this->_data['data']['mobile']  =   enCryptRestUri(I('post.mobile'));
                }
            }
            
            $this->sessions();          //是否需要设置session
            $this->ajaxReturn($this->_data);
           
        }
    }    
    
    /**
     * 用户提交
     */
    public function authRun() {
        if (IS_POST) {
            if (!isset($_SESSION['user'])) $this->ajaxReturn(['code' => 401, 'msg' => '请登录']);

            if (session('user.parent_uid')) {   //权限判断
                if (Auth::getInstance('mapping_rest', $this->_apicfg['action'])->check() == false) {
                    $this->ajaxReturn(['code' => 0, 'msg' => '您没有权限进行访问或操作！！！']);
                }
            }
            $this->key2md5();           //是否包含需要加密的字段
            $this->resetFields();       //是否包含重置的字段
            $this->checkVerify();       //是否需要图形验证
            $this->hash();              //token
            $this->nosign();            //判断是否有nosign字段提交
            if (isset($this->_apicfg['callback'])) {    //签名个性化
                $this->_apicfg['nosign']    .=  ','.call_user_func_array($this->_apicfg['callback'], [$this->_data]);
            }
            
            if (isset($this->_apicfg['isArr'])) {   //数据多维数组 http_builder
                $this->_data    =   call_user_func_array($this->_apicfg['isArr'][0], [$this->_data, $this->_apicfg['isArr'][1]]);
            }
            $this->_data['openid']  =   session('user.openid');
            ksort($this->_data);
            $this->_data['sign']    =   _sign($this->_data, ltrim($this->_apicfg['nosign'], ','));
            $this->_data            =   $this->curl($this->_apicfg['action'], $this->_data);
            $this->_data = json_decode($this->_data, true);
            
            //判断是否有token
            if ($this->_token) {
                if ($this->_data['code'] == 1) {
                    $this->checkToken($this->_token, 1); //清除token
                } else {
                    $this->checkToken($this->_token, 2); //清除token
                }
            }
            $this->ajaxReturn($this->_data);
        }
    }
    
    /**
     * 文件上传
     */
    public function upload() {
        if (IS_POST) {
            if (empty($_FILES)) {
                $this->ajaxReturn(array('code' => 0, 'msg' => '文件不能为空'));
            }
            //$this->hash();              //token
            $this->nosign();            //判断是否有nosign字段提交
            if (isset($this->_data['action'])) {
                $url    =   deCryptRestUri($this->_data['action']);
                unset($this->_data['action']);
            } else {
                $url = '/Upload/curlUpload';
            }
            ksort($this->_data);
            $this->_data['sign']       =   _sign(getApiCfg(), ltrim($this->_apicfg['nosign'], ','));
            $this->_data['imageData']  =   serialize($_FILES['file']);
            $this->_data               =   $this->curl($url, $this->_data);
            $this->_data               =   json_decode($this->_data, true);
            if ($this->_data) {
                $this->ajaxReturn($this->_data);
            }
            $this->ajaxReturn(array('code' => 0, 'msg' => '服务器内部出现错误！'));
        }
    }

    public function ueditorUpload(){
        header("Content-Type: text/html; charset=utf-8");
        $editconfig = json_decode(preg_replace("/\/\*[\s\S]+?\*\//", "", file_get_contents(COMMON_PATH."Conf/config.json")), true);
        $action = I('get.action');
        switch ($action) {
            case 'config':
                $result =  $editconfig;
                break;

            /* 上传图片 */
            case 'uploadimage':
                /* 上传涂鸦 */
            case 'uploadscrawl':
                /* 上传视频 */
            case 'uploadvideo':
                /* 上传文件 */
            case 'uploadfile':
                if(!in_array('.' . end(explode('/', $_FILES['file']['type'])), $editconfig['imageAllowFiles'])){
                    $this->ajaxReturn(array('state'=> '文件非法'));
                }

                if($_FILES['file']['size'] > $editconfig['imageMaxSize']){
                    $this->ajaxReturn(array('state'=> '文件大小已超出最大上传限制'));
                }

                $result = $this->getUploadFile();
                break;
            default:
                $result = array('state'=> '请求地址出错');
                break;
        }

        $this->ajaxReturn($result);

    }

    public function getUploadFile(){
        $SAVETYPE = 'date';

        //开启水印
        $USEWATER = false;
        $WATERIMAGEURL = "http://gitwiduu.u.qiniudn.com/ueditor-bg.png"; //七牛上的图片地址
        //水印透明度
        $DISSOLVE = 50;
        //水印位置
        $GRAVITY = "SouthEast";
        //边距横向位置
        $DX  = 10;
        //边距纵向位置
        $DY  = 10;

        $CONFIG = json_decode(preg_replace("/\/\*[\s\S]+?\*\//", "", file_get_contents(COMMON_PATH."Conf/config.json")), true);
        switch (htmlspecialchars($_GET['action'])) {
            case 'uploadimage':
                $fieldName = $CONFIG['imageFieldName'];
                break;
            case 'uploadscrawl':
                $fieldName = $CONFIG['scrawlFieldName'];
                break;
            case 'uploadvideo':
                $fieldName = $CONFIG['videoFieldName'];
                break;
            case 'uploadfile':
            default:
                $fieldName = $CONFIG['imageFieldName'];
                break;
        }

        $res = $this->getQiniuConfigData();

        /* 生成上传实例对象并完成上传 */
        $config = array(
            'secrectKey'     => $res['sk'],
            'accessKey'      => $res['ak'],
            'domain'         => $res['domain'],
            'bucket'         => $res['bucket'],
            'timeout'        => 3600,
        );
        $qiniu = new Qiniu($config);

        $upfile = array(
            'name'     => 'file',
            'fileName' => $SAVETYPE == 'date' ? time().'.'.pathinfo($_FILES[$fieldName]["name"], PATHINFO_EXTENSION) : $_FILES[$fieldName]['name'],
            'fileBody' => file_get_contents($_FILES[$fieldName]['tmp_name'])
        );
        $config = array();
        $result = $qiniu->upload($config, $upfile);
//        return $result;
        if(!empty($result['hash'])){
            $url = '';
            if(htmlspecialchars($_GET['action']) == 'uploadimage'){
                if($USEWATER){
                    $waterBase = $this->urlsafe_base64_encode($WATERIMAGEURL);
                    $url  =  $qiniu->downlink($result['key'])."?watermark/1/image/{$waterBase}/dissolve/{$DISSOLVE}/gravity/{$GRAVITY}/dx/{$DX}/dy/{$DY}";
                }else{
                    $url  =  $qiniu->downlink($result['key']);
                }
            }else{
                $url  =  $qiniu->downlink($result['key']);
            }
            /*构建返回数据格式*/
            $FileInfo = array(
                "state"    => "SUCCESS",
                "url"      => $url,
                "title"    => $result['key'],
                "original" => $_FILES[$fieldName]['name'],
                "type"     => $_FILES[$fieldName]['type'],
                "size"     => $_FILES[$fieldName]['size'],
            );

            /* 返回数据 */
            return $FileInfo;
        }
    }

    public function getToken(){

        //开启水印
        $USEWATER = false;
        $WATERIMAGEURL = "http://gitwiduu.u.qiniudn.com/ueditor-bg.png"; //七牛上的图片地址
        //水印透明度
        $DISSOLVE = 50;
        //水印位置
        $GRAVITY = "SouthEast";
        //边距横向位置
        $DX  = 10;
        //边距纵向位置
        $DY  = 10;

        $res = $this->getQiniuConfigData();

        $config = array(
            'secrectKey'     => $res['sk'],
            'accessKey'      => $res['ak'],
            'domain'         => $res['domain'],
            'bucket'         => $res['bucket'],
            'timeout'        => 3600,
        );

        $qiniu = new Qiniu($config);

        if($USEWATER && empty($_GET['type'])){
            $waterBase = urlsafe_base64_encode($WATERIMAGEURL);
            $returnBody = "{\"url\":\"{$config['domain']}/$(key)?watermark/1/image/{$waterBase}/dissolve/{$DISSOLVE}/gravity/{$GRAVITY}/dx/{$DX}/dy/{$DY}\", \"state\": \"SUCCESS\", \"name\": $(fname),\"size\": \"$(fsize)\",\"w\": \"$(imageInfo.width)\",\"h\": \"$(imageInfo.height)\"}";
        }else{
            $returnBody = "{\"url\":\"{$config['domain']}/$(key)\", \"state\": \"SUCCESS\", \"name\": $(fname),\"size\": \"$(fsize)\",\"w\": \"$(imageInfo.width)\",\"h\": \"$(imageInfo.height)\"}";
        }

        $data =  array(
            "scope"      => $config['bucket'] . ":" . $_GET['key'],
            "deadline"   => time() + $config['timeout'],
            "ReturnBody" => $returnBody
        );

        return $qiniu->UploadToken($res['sk'], $res['ak'], $data);
    }

    public function urlsafe_base64_encode($data){
        return str_replace(array('+', '/'), array('-', '_'), base64_encode($data));
    }

    public function curl_post($url,$data,$param=null){
        $curl = curl_init($url);// 要访问的地址

        curl_setopt($curl, CURLOPT_TIMEOUT, 30); // 设置超时限制防止死循环
        curl_setopt($curl, CURLOPT_HEADER, 0 ); // 过滤HTTP头
        curl_setopt($curl,CURLOPT_RETURNTRANSFER, 1);// 显示输出结果
        curl_setopt($curl,CURLOPT_POST,true); // post传输数据
        curl_setopt($curl,CURLOPT_POSTFIELDS,$data);// post传输数据
        $res = curl_exec($curl);
        //var_dump( curl_error($curl) );//如果执行curl过程中出现异常，可打开此开关，以便查看异常内容
        curl_close($curl);

        return $res;
    }

    public function getQiniuConfigData(){
        $res = [
            'ak'     => 'Ix2vsZCIzffxF11Rp5WTJ9gn4Ps-4vuRzfgCocZK',
            'sk'     => '5NpvtxzggC8GSNcu5QbuONUFIfYo_gd6mUTlBwIa',
            'domain' => 'http://orxwhobqu.bkt.clouddn.com',
            'bucket' => 'images',
        ];
        return $res;
    }
    
    /**
     * 判断是否有nosign字段提交
     */
    private function nosign() {
        if (isset($this->_data['nosign'])) {
            $this->_apicfg['nosign']    .=  ',' . deCryptRestUri($this->_data['nosign']);
            unset($this->_data['nosign']);
        }
    }
    
    /**
     * 密码加密
     */
    private function key2md5() {
        $md5Key =   ['password', 'repassword', 'login_password', 'opassword', 'password2', 'password_pay', 'old_password'];
        foreach ($md5Key as $val) {
            if (array_key_exists($val, $this->_data)) {
                $this->_data[$val]  =   $this->password(($this->_data[$val]));
            }
        }
    }
    
    /**
     * 字段重置
     */
    private function resetFields() {
        if (isset($this->_data['restField']) && !empty($this->_data['restField'])) {
            $tmpData    =   explode(',', $this->_data['restField']);
            if (!empty($tmpData) && is_array($tmpData)) {
                foreach ($tmpData as $v) {
                    $this->_data[strstr($v, '__', true)]   =   $this->_data[$v];
                    unset($this->_data[$v]);
                }
                unset($this->_data['restField'], $tmpData, $v);
            }
        }
    }
    
    /**
     * 检测验证码
     */
    private function checkVerify() {
        if ($this->isVerify($this->_apicfg['action'])) {
            $verify = new \Think\Verify;
            if (false == $verify->check($this->_data['vcode'])) {
                $this->ajaxReturn(array('code' => 0, 'msg' => '图形验证码错误'));
            }
            unset($this->_data['vcode']);
        }
    }
    
    /**
     * 加入session
     */
    private function sessions() {
        if ($this->setSession($this->_apicfg['action'])) {
            if (isset($this->_data['data']) && $this->_data['code'] == 1) {
                session('user', $this->_data['data']);
                //保存登陆信息
                $expire =   $this->_data['data']['remember'] == 1 ? 43200 : 3600;
                cookie('rememberSaveTime', enCryptRestUri($expire), $expire);
                $this->_data['data']['ip'] 		= 	get_client_ip();

                S(md5(session_id()),$this->_data['data']['erp_uid'],3600);
                cookie('remember', enCryptRestUri(serialize($this->_data['data'])), $expire);
                unset($expire);
                S(md5('check_login_vcode' . get_client_ip()), null);   //删除验证码标记
            }
            if (isset($_SESSION['user']['id'])) {
                $this->ajaxReturn(array('code' => 1, 'msg' => '登录成功'));
            }
            S(md5('check_login_vcode' . get_client_ip()), 1);
            $mag    =   $this->_data['msg'] ? $this->_data['msg'] : '服务器内部错误';
            $this->ajaxReturn(array('code' => 0, 'msg' => $mag));
        }
    }
    
    /**
     * 判断hash
     */
    private function hash() {
        if (isset($this->_data['__hash__'])) {
            $this->_token  =   $this->checkToken($this->_data['__hash__']);
            if (false == $this->_token) $this->ajaxReturn(array('code' => 0, 'msg' => '请刷新页面重新操作，谢谢！'));
            unset($this->_data['__hash__']);
        }
    }
    
    
    /**
     * 检测token是否正确
     * @param unknown $token
     * @return boolean
     */
    private function checkToken($token, $unset = null) {
        $tokenArr   =   explode('_', $token);
        if ($unset) {
            S('token_' . $token, null);
            if ($unset == 1) {
                session('__hash__.' . $tokenArr[0], null);
            }
        } else {
            if (session('__hash__.' . $tokenArr[0]) == $tokenArr[1]) {
                if (S('token_' . $token)) {
                    return false;
                }
                S('token_' . $token, 1);
                return $token;
            }
            return false;
        }
    }
    
    /**
     * 是否需要重置session
     * @param string $url
     */
    private function restSession($url) {
        $arr = array(
            '/User/change_password',
            '/User/set_password_pay',
            '/User/change_password_pay',
            '/User/change_mobile',
            '/UserUpgrade/upgrade_pay',
            '/user/change_information',
            '/Auth/auth_mobile',
        );
        if (in_array($url, $arr)) {
            $this->authApi('/User/userinfo');
            $res    =   $this->_data;
            if ($res['code'] == 1) {
                //重置session
                session('user', $res['data']);
                //重置cookie
                if (cookie('remember')) {
                    $res['data']['ip'] 		    = 	get_client_ip();
                    $res['data']['session_id']	=	session_id();
                    cookie('remember', enCryptRestUri(serialize($res['data'])), deCryptRestUri(cookie('rememberSaveTime')));
                    unset($res);
                }
            }
        }
    }
    
    private function tj(){
        if($this->_apicfg['action'] == '/Tj/ad_show'){
            $this->_data['ip'] = get_client_ip();
            $this->_data['key'] = session_id();
            $this->_data['user'] = json_encode(session('user'));
            $this->_data['device'] = 'pc';
        }
    }


}