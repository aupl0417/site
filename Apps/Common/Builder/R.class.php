<?php
/**
 * Created by PhpStorm.
 * User: dttx
 * Date: 2017/3/23
 * Time: 16:57
 */

namespace Common\Builder;
use Think\Hook;


/**
 * 处理接口请求
 *
 * Class Run
 * @package Common\Builder\
 */

class R
{
    protected $params                   = []; //参数
    protected static $tokenCacheName    = 'pcTokenCacheName';  //token缓存key
    protected static $md5Key            = ['password', 'repassword', 'login_password', 'opassword', 'password2', 'password_pay', 'old_password'];  //需要加密的key
    protected static $sessionArr        = [
        '/Erp/check_login',    //登录
        '/Erp/register',       //注册
        '/Erp/register_company',   //企业注册
    ];  //需要设置session的api

    protected static $restSessions      = [
        '/User/change_password',
        '/User/set_password_pay',
        '/User/change_password_pay',
        '/User/change_mobile',
        '/UserUpgrade/upgrade_pay',
        '/user/change_information',
        '/Auth/auth_mobile',
    ];  //重置session的接口

    protected static $checkVerify       = [
        '/Erp/Erp/change_pay_password',    //修改登录密码
        '/Erp/sms_code',       //获取短信
    ];  //需要验证图形验证码的API

    const REST = 'rest';
    const REST2= 'rest2';

    protected static $isAjax = true;
    protected static $rest   = 'rest2';
    //isAjax
    //isRest2

    //protected static $instance;
    /**
     * 构造函数
     *
     * R constructor.
     * @param null $params
     * ['url', 'data' => [], 'rest', 'isAjax']
     * @param $rest     可选 rest，rest2
     * @param $isAjax   是否为ajax返回
     */
    public function __construct($params = null)
    {
        if (isset($params['isAjax'])) self::$isAjax = $params['isAjax'];
        if ($params) $this->params = $params;
        if (isset($params['rest']) && $params['rest'] == 'rest') {  //如果是rest的话则需要传appid等配置信息
            $this->params['data'] = !empty($this->params['data']) ? array_merge($this->params['data'], getApiCfg()) : getApiCfg();
            self::$rest = $params['rest'];
        } else {    //如果使用rest2的话则需要传token
            if (S(self::$tokenCacheName) == false) self::getToken();    //重新获取token
            $this->params['data']['token'] = S(self::$tokenCacheName);
            self::$rest = 'rest2';
        }
        if (isset($this->params['data']['file'])) unset($this->params['data']['file']);
    }

    /**
     * subject: 单例
     * api: getInstance
     * author: Mercury
     * day: 2017-03-23 17:38
     * [字段名,类型,是否必传,说明]
     * @param null $params
     * @return R
     */
    public static function getInstance($params = null)
    {
        //if (self::$instance instanceof self) return self::$instance;
        return new self($params);
    }

    /**
     * subject: run
     * api: run
     * author: Mercury
     * day: 2017-03-23 17:41
     * [字段名,类型,是否必传,说明]
     */
    public function run()
    {
        //图形验证
        self::checkVerify($this->params['url'], $this->params['data']['code']);
        //检测hash
        $this->hash();
        //加密
        $this->key2md5();
        //重置
        $this->resetFields();
        $this->params['data']['p']        = I('get.p', 1, 'int');
        $this->params['data']['action']   = $this->params['data']['action'] ? : __ACTION__;
        $this->params['data']['pagesize'] = $this->params['data']['pagesize'] ? : 10;
        ksort($this->params);
        $this->params['data']['sign']     = _sign($this->params['data'], self::$rest == 'rest2' ? '' : 'p,action,pagesize,file');
        $res = self::curl($this->params['url'], $this->params['data']);
        //if ($res['code'] != 1) self::ajaxReturn($res);
        //设置session
        self::setSessions($this->params['url'], $res);
        //重置session
        self::restSession($this->params['url']);
        if (C('DEBUG_API') == true) dump($res);
        if (self::$isAjax == false) return $res;
        self::ajaxReturn($res);
    }


    /**
     * subject: auth
     * api: auth
     * author: Mercury
     * day: 2017-03-23 17:21
     * [字段名,类型,是否必传,说明]
     * @param $url
     * @param $data
     * @param bool $jsonde
     * @param bool $header
     * @return mixed
     */
    public function auth()
    {
        if (session('user.openid') == false) self::ajaxReturn(['code' => 401, 'msg' => '请登录']);
        $this->params['data']['openid'] = session('user.openid');
        if (self::$isAjax == true) {
            $this->run();
        } else {
            return $this->run();
        }
    }


    /**
     * subject: 同时执行多个curl
     * api: multiCurl
     * author: Mercury
     * day: 2017-05-24 10:01
     * [字段名,类型,是否必传,说明]
     * @return array
     * param ['url' => ['payment' => '/erp/get_seller_wait_payment', 'shop' => '/shopSetting/shop_info', 'total' => '/seller/total', 'account' => '/erp/account'], 'rest' => ['rest2', 'rest', 'rest', 'rest'], 'isAjax' => false, 'data' => [['openid' => session('user.openid')],['openid' => session('user.openid')],['openid' => session('user.openid')],['openid' => session('user.openid')]]]
     * return
     * array (
            'payment' => '{"code":1,"data":7007.83,"msg":"操作成功！"}',
            'shop' => '{"code":"1","msg":"操作成功！","data":{"id":"376","atime":"2016-11-23 11:35:52","status":"1","uid":"705332","shop_name":"个人二三零","max_sub_user":"35","max_sub_group":"20","shop_level":"5","shop_point":"123","shop_logo":"https:\\/\\/pic.tangmall.net\\/FgQykYypCldJ6fY4O5XgWfzeOoGu","about":"个人品牌店铺 开店测试专用111111111111111111111111111111111111111111111权威的权威","scope":"","huoyan":"","type_id":"3","category_id":"100845562,100845542","category_second":"100843201,100843204,100843207,100843210,100844011,100844106,100844109,100844112,100845232,100845247,100845250","linkname":"个人开店联系人","province":"2741","city":"2803","district":"2803","town":"2810","street":"hhcyyccycf","domain":"ceshi","qq":"4567465","mobile":"13288866656","tel":"","email":"4657456465@qq.com","fav_num":"12","pr":"38.64","wang":"","goods_num":"35","sale_num":"2901","remark":"","fraction_speed":"4.62","fraction_service":"4.69","fraction_desc":"4.57","fraction":"4.63","inventory_type":"1","max_goods":"500","max_best":"30","illegl_point":"16.00","is_test":"0","total_money_pay":"93335.48","banner":"","appid":"0","complaints_num":"4","nick":"atestnum230","erp_uid":"7aee886a3ec5db761c32d67c5f400ea7","province_name":"贵州省","city_name":"毕节地区","district_name":"毕节地区","town_name":"威宁彝族回族苗族自治县","status_name":"营业中","shop_url":"http:\\/\\/ceshi.dtshop.com","type_name":"专卖店","logo":"https:\\/\\/pic.tangmall.net\\/FgQykYypCldJ6fY4O5XgWfzeOoGu?imageMogr2\\/thumbnail\\/!100x100r\\/gravity\\/Center\\/crop\\/100x100","category_name":["手机，数码，家电","其它"]}}',
            'total' => '{"code":"1","msg":"操作成功！","data":{"orders":{"1":"75","2":"116","3":"80","4":"79","5":"709","6":"1","10":"929","20":"38","21":"34","30":"0","all":"1989"},"goods":{"1":"36","2":"17","3":"1","4":"0","5":"0","6":"0","online_zero":"1","best":"1"},"illegl":{"1":"6","2":"3"},"sale_total":{"num":"0","money":"0.00","goods_num":0,"buyer":0,"price":"0.00","refund":"3"},"shop_fav":0,"goods_fav":"9","wait":0}}',
            'account' => '{"code":"1","msg":"操作成功！","data":{"a_id":1.5862354563972e+15,"a_uid":"7aee886a3ec5db761c32d67c5f400ea7","a_nick":"atestnum230","a_payPwd":"a87f3df83b5e29959d6780489c558182","a_payPwdNew":"f93181914197a514daecf34f26625d0d","a_freeMoney":"88802927.88","a_frozenMoney":"0.00","a_score":"645403.000","a_tangBao":"2937966.000","a_storeScore":"132765624.750","a_scoreTotal":"0.000","a_tangTotal":"0.000","a_crc":"d104754c19daca34d3d3ddbce8606327","a_state":1,"a_createTime":"2016-11-22 19:36:32","a_isDefault":1,"a_payAccountCode":"ERP_RECHARGE","a_isTest":1,"a_memo":"","bank":5,"zhifubao":0}}',
            )
     */
    public function multiCurl()
    {
        $i                                = 0;
        $ch                               = [];
        $res                              = [];
        $curlInit                         = curl_multi_init();
        $options[CURLOPT_USERAGENT]       = 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.11 (KHTML, like Gecko) Chrome/23.0.1271.97 MSIE 8.0';
        $options[CURLOPT_RETURNTRANSFER]  = true;
        $options[CURLOPT_POST]            = true;
        $options[CURLOPT_SSL_VERIFYPEER]  = false;
        $options[CURLOPT_TIMEOUT]         = 30;
        $options[CURLOPT_HEADER]          = false;
        foreach ($this->params['url'] as $k => $v) {
            if (strpos($v, 'http') === false) $v  = DM($this->params['rest'][$i]) . $v;
            $options[CURLOPT_URL]                 =   $v;
            $this->params['data'][$i]['p']        = I('get.p', 1, 'int');
            $this->params['data'][$i]['action']   = $this->params['data'][$i]['action'] ? : __ACTION__;
            $this->params['data'][$i]['pagesize'] = $this->params['data'][$i]['pagesize'] ? : 10;
            if ($this->params['rest'][$i] == 'rest') {
                $this->params['data'][$i]         = array_merge($this->params['data'][$i], getApiCfg());
            } else {
                $this->params['data'][$i]['token']= S(self::$tokenCacheName) ? : self::getToken();
            }
            ksort($this->params['data'][$i]);
            $this->params['data'][$i]['sign']     = _sign($this->params['data'][$i], $this->params['rest'][$i] == 'rest2' ? 'filebody' : 'p,action,pagesize,file');
            $options[CURLOPT_POSTFIELDS]          = $this->params['data'][$i];
            $ch[$i] =   curl_init();                        //初始化curl
            curl_setopt_array($ch[$i], $options);           //设置参数
            curl_multi_add_handle($curlInit, $ch[$i]);      //添加到handle
            ++$i;
        }


        $running = null;
        do {
            $tmp = curl_multi_exec($curlInit, $running);       //轮询执行句柄,当轮询完后$running则返回false

        } while ($running > 0);
        //} while ($tmp == CURLM_CALL_MULTI_PERFORM);

        //$running，所以这里用到了curl_multi_exec的返回值判断是否还有数据，当有数据的时候就不停调用curl_multi_exec，暂时没有数据就进入select阶段，新数据一来就可以被唤醒继续执行。这里的好处就是CPU的无谓消耗没有了。
        while ($running && $tmp == CURLM_OK) {
            if (curl_multi_select($curlInit) != -1) {
                do {
                    $tmp = curl_multi_exec($curlInit, $running);       //轮询执行句柄,当轮询完后$running则返回false
                } while ($tmp == CURLM_CALL_MULTI_PERFORM);
            }
        }

        $i = 0;
        foreach ($this->params['url'] as $k => $v) {
            if (C('DEBUG_API') == true) dump(curl_multi_getcontent($ch[$i]));
            $res[$k] = json_decode(curl_multi_getcontent($ch[$i]), true);         //获取返回数据
            curl_multi_remove_handle($curlInit, $ch[$i]);    //移除handle
            ++$i;
        }
        curl_multi_close($curlInit);        //关闭资源
        return $res;
    }

    /**
     * subject: curl
     * api: curl
     * author: Mercury
     * day: 2017-03-23 17:36
     * [字段名,类型,是否必传,说明]
     * @param $url
     * @param $data
     * @param bool $jsonde
     * @param bool $header
     * @return mixed
     */
    public static function curl($url, $data, $jsonde = true, $header = false)
    {
        if (strpos($url, 'http') === false) $url = DM(self::$rest) . $url;
        $s = microtime(true);
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.11 (KHTML, like Gecko) Chrome/23.0.1271.97 MSIE 8.0'); // 模拟用户使用的浏览器
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        if ($header) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        }
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);//SSL证书认证
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $res = curl_exec($ch);
        //如果页面错误则写进日志
        if (is_null(json_decode($res, true))) log_add('pccurl', ['time' => date('Y-m-d H:i:s', NOW_TIME), 'api' => $url, 'data' => $data, 'res' => $res]);
        curl_close($ch);
        $e = microtime(true);
        //writeLog($url . '__执行__' . ($e-$s) . '__秒');
        //echo($res);
        if ($jsonde) return json_decode($res, true);
        return $res;
    }

    /**
     * subject: 获取token
     * api: getToken
     * author: Mercury
     * day: 2017-03-23 17:34
     * [字段名,类型,是否必传,说明]
     */
    public static function getToken() {
        $url = DM('rest2', '/auth/token');
        $cfg                = getApiCfg();
        $cfg['device_id']   = session_id();
        $cfg['sign']        = _sign($cfg);
        $res = self::curl($url, $cfg);
        if ($res['code'] == 1) S(self::$tokenCacheName, $res['data']['token'], ['expire' => 1160]);
    }

    /**
     * subject: 需要转换为MD5的值
     * api: key2md5
     * author: Mercury
     * day: 2017-03-24 10:02
     * [字段名,类型,是否必传,说明]
     */
    private function key2md5() {
        foreach (self::$md5Key as $val) {
            if (array_key_exists($val, $this->params['data'])) {
                $this->params['data'][$val]  =   self::password(($this->params['data'][$val]));
            }
        }
    }

    /**
     * subject: 重置字段名
     * api: resetFields
     * author: Mercury
     * day: 2017-03-24 10:04
     * [字段名,类型,是否必传,说明]
     */
    private function resetFields() {
        if (isset($this->params['data']['restField']) && !empty($this->params['data']['restField'])) {
            $tmpData    =   explode(',', $this->params['data']['restField']);
            if (!empty($tmpData) && is_array($tmpData)) {
                foreach ($tmpData as $v) {
                    $this->params['data'][strstr($v, '__', true)]   =   $this->params['data'][$v];
                    unset($this->params['data'][$v]);
                }
                unset($this->params['data']['restField'], $tmpData, $v);
            }
        }
    }

    /**
     * subject: 图形验证码
     * api: checkVerify
     * author: Mercury
     * day: 2017-03-24 10:14
     * [字段名,类型,是否必传,说明]
     */
    protected static function checkVerify($url, $code) {
        if (S('check_login_vcode')) array_push(self::$checkVerify, '/Erp/check_login');
        if (in_array($url, self::$checkVerify)) {
            $verify = new \Think\Verify;
            if (false == $verify->check($code)) {
                self::ajaxReturn(['code' => 0, 'msg' => '图形验证码错误']);
            }
        }
    }

    /**
     * subject: ajaxReturn
     * api: ajaxReturn
     * author: Mercury
     * day: 2017-03-24 10:14
     * [字段名,类型,是否必传,说明]
     * @param $data
     * @param string $type
     * @param int $json_option
     */
    public static function ajaxReturn($data,$type='',$json_option=0)
    {
        if (self::$isAjax == false) return $data;   //如果非异步则返回数组类型数据
        if(empty($type)) $type  =   C('DEFAULT_AJAX_RETURN');
        switch (strtoupper($type)){
            case 'JSON' :
                // 返回JSON数据格式到客户端 包含状态信息
                header('Content-Type:application/json; charset=utf-8');
                exit(json_encode($data,$json_option));
            case 'XML'  :
                // 返回xml格式数据
                header('Content-Type:text/xml; charset=utf-8');
                exit(xml_encode($data));
            case 'JSONP':
                // 返回JSON数据格式到客户端 包含状态信息
                header('Content-Type:application/json; charset=utf-8');
                $handler  =   isset($_GET[C('VAR_JSONP_HANDLER')]) ? $_GET[C('VAR_JSONP_HANDLER')] : C('DEFAULT_JSONP_HANDLER');
                exit($handler.'('.json_encode($data,$json_option).');');
            case 'EVAL' :
                // 返回可执行的js脚本
                header('Content-Type:text/html; charset=utf-8');
                exit($data);
            default     :
                // 用于扩展其他返回格式数据
                Hook::listen('ajax_return',$data);
        }
    }

    /**
     * subject: 加密
     * api: password
     * author: Mercury
     * day: 2017-03-24 10:12
     * [字段名,类型,是否必传,说明]
     * @param $str
     * @return string
     */
    public static function password($str)
    {
        return MD5(SHA1($str) . '@$^^&!##$$%%$%$$^&&asdtans2g234234HJU');
    }

    /**
     * subject: 判断hash值是否正确
     * api: hash
     * author: Mercury
     * day: 2017-03-24 10:17
     * [字段名,类型,是否必传,说明]
     */
    private function hash() {
        if (isset($this->params['data']['__hash__'])) {
            $this->_token  =   self::checkToken($this->params['data']['__hash__']);
            //if (false == $this->_token) self::ajaxReturn(['code' => 0, 'msg' => '请刷新页面重新操作，谢谢！']);
            unset($this->params['data']['__hash__']);
        }
    }


    /**
     * subject: 检测token是否正确
     * api: checkToken
     * author: Mercury
     * day: 2017-03-24 10:17
     * [字段名,类型,是否必传,说明]
     * @param $token
     * @param null $unset
     * @return bool
     */
    private static function checkToken($token, $unset = null) {
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
     * subject: 设置session
     * api: setSessions
     * author: Mercury
     * day: 2017-03-24 10:30
     * [字段名,类型,是否必传,说明]
     * @param $api
     * @param $data
     */
    private static function setSessions($api, $data)
    {
        if (in_array($api, self::$sessionArr)) {
            if (isset($data['data']) && $data['code'] == 1) {
                session('user', $data['data']);
                //保存登陆信息
                $expire =   $data['data']['remember'] == 1 ? 2592000 : 3600;
                cookie('rememberSaveTime', enCryptRestUri($expire), array('expire' => $expire));
                $data['data']['ip'] 		= 	get_client_ip();

                S(md5(session_id()),$data['erp_uid'],3600);
                cookie('remember', enCryptRestUri(serialize($data['data'])), array('expire' => $expire));
                unset($expire);
                S(md5('check_login_vcode' . session_id()), null);   //删除验证码标记
            }
            if (isset($_SESSION['user']['id'])) {
                self::ajaxReturn(['code' => 1, 'msg' => '登录成功']);
            }
            S(md5('check_login_vcode' . session_id()), 1);
            $mag    =   $data['msg'] ? $data['msg'] : '服务器内部错误';
            self::ajaxReturn(['code' => 0, 'msg' => $mag]);
        }
    }

    /**
     * subject: 重置session
     * api: restSession
     * author: Mercury
     * day: 2017-03-24 10:43
     * [字段名,类型,是否必传,说明]
     * @param $api
     */
    private static function restSession($api)
    {
        if (in_array($api, self::$restSessions)) {
            $res = self::getInstance(['url' => '/User/userinfo']);
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

    function __destruct()
    {
        if ($this->params) $this->params = null;
        // TODO: Implement __destruct() method.
    }
}