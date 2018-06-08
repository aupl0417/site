<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2014 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
namespace Think;
use Common\Builder\Auth;

/**
 * ThinkPHP 控制器基类 抽象类
 */
abstract class Controller {

    /**
     * 视图实例对象
     * @var view
     * @access protected
     */    
    protected $view     =  null;

    /**
     * 控制器参数
     * @var config
     * @access protected
     */      
    protected $config   =   array();

   /**
     * 架构函数 取得模板对象实例
     * @access public
     */
    public function __construct() {
        Hook::listen('action_begin',$this->config);
        //实例化视图类
        $this->view     = Think::instance('Think\View');
        //控制器初始化
        if(method_exists($this,'_initialize'))
            $this->_initialize();
    }

    /**
     * 模板显示 调用内置的模板引擎显示方法，
     * @access protected
     * @param string $templateFile 指定要调用的模板文件
     * 默认为空 由系统自动定位模板文件
     * @param string $charset 输出编码
     * @param string $contentType 输出类型
     * @param string $content 输出内容
     * @param string $prefix 模板缓存前缀
     * @return void
     */
    protected function display($templateFile='',$charset='',$contentType='',$content='',$prefix='') {
        $this->view->display($templateFile,$charset,$contentType,$content,$prefix);
    }

    /**
     * 输出内容文本可以包括Html 并支持内容解析
     * @access protected
     * @param string $content 输出内容
     * @param string $charset 模板输出字符集
     * @param string $contentType 输出类型
     * @param string $prefix 模板缓存前缀
     * @return mixed
     */
    protected function show($content,$charset='',$contentType='',$prefix='') {
        $this->view->display('',$charset,$contentType,$content,$prefix);
    }

    /**
     *  获取输出页面内容
     * 调用内置的模板引擎fetch方法，
     * @access protected
     * @param string $templateFile 指定要调用的模板文件
     * 默认为空 由系统自动定位模板文件
     * @param string $content 模板输出内容
     * @param string $prefix 模板缓存前缀* 
     * @return string
     */
    protected function fetch($templateFile='',$content='',$prefix='') {
        return $this->view->fetch($templateFile,$content,$prefix);
    }

    /**
     *  创建静态页面
     * @access protected
     * @htmlfile 生成的静态文件名称
     * @htmlpath 生成的静态文件路径
     * @param string $templateFile 指定要调用的模板文件
     * 默认为空 由系统自动定位模板文件
     * @return string
     */
    protected function buildHtml($htmlfile='',$htmlpath='',$templateFile='') {
        $content    =   $this->fetch($templateFile);
        $htmlpath   =   !empty($htmlpath)?$htmlpath:HTML_PATH;
        $htmlfile   =   $htmlpath.$htmlfile.C('HTML_FILE_SUFFIX');
        Storage::put($htmlfile,$content,'html');
        return $content;
    }

    /**
     * 模板主题设置
     * @access protected
     * @param string $theme 模版主题
     * @return Action
     */
    protected function theme($theme){
        $this->view->theme($theme);
        return $this;
    }

    /**
     * 模板变量赋值
     * @access protected
     * @param mixed $name 要显示的模板变量
     * @param mixed $value 变量的值
     * @return Action
     */
    protected function assign($name,$value='') {
        $this->view->assign($name,$value);
        return $this;
    }

    public function __set($name,$value) {
        $this->assign($name,$value);
    }

    /**
     * 取得模板显示变量的值
     * @access protected
     * @param string $name 模板显示变量
     * @return mixed
     */
    public function get($name='') {
        return $this->view->get($name);      
    }

    public function __get($name) {
        return $this->get($name);
    }

    /**
     * 检测模板变量的值
     * @access public
     * @param string $name 名称
     * @return boolean
     */
    public function __isset($name) {
        return $this->get($name);
    }

    /**
     * 魔术方法 有不存在的操作的时候执行
     * @access public
     * @param string $method 方法名
     * @param array $args 参数
     * @return mixed
     */
    public function __call($method,$args) {
        if( 0 === strcasecmp($method,ACTION_NAME.C('ACTION_SUFFIX'))) {
            if(method_exists($this,'_empty')) {
                // 如果定义了_empty操作 则调用
                $this->_empty($method,$args);
            }elseif(file_exists_case($this->view->parseTemplate())){
                // 检查是否存在默认模版 如果有直接输出模版
                $this->display();
            }else{
                E(L('_ERROR_ACTION_').':'.ACTION_NAME);
            }
        }else{
            E(__CLASS__.':'.$method.L('_METHOD_NOT_EXIST_'));
            return;
        }
    }

    /**
     * 操作错误跳转的快捷方法
     * @access protected
     * @param string $message 错误信息
     * @param string $jumpUrl 页面跳转地址
     * @param mixed $ajax 是否为Ajax方式 当数字时指定跳转时间
     * @return void
     */
    protected function error($message='',$jumpUrl='',$ajax=false) {
        $this->dispatchJump($message,0,$jumpUrl,$ajax);
    }

    /**
     * 操作成功跳转的快捷方法
     * @access protected
     * @param string $message 提示信息
     * @param string $jumpUrl 页面跳转地址
     * @param mixed $ajax 是否为Ajax方式 当数字时指定跳转时间
     * @return void
     */
    protected function success($message='',$jumpUrl='',$ajax=false) {
        $this->dispatchJump($message,1,$jumpUrl,$ajax);
    }

    /**
     * Ajax方式返回数据到客户端
     * @access protected
     * @param mixed $data 要返回的数据
     * @param String $type AJAX返回数据格式
     * @param int $json_option 传递给json_encode的option参数
     * @return void
     */
    protected function ajaxReturn($data,$type='',$json_option=0) {
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
     * Action跳转(URL重定向） 支持指定模块和延时跳转
     * @access protected
     * @param string $url 跳转的URL表达式
     * @param array $params 其它URL参数
     * @param integer $delay 延时跳转的时间 单位为秒
     * @param string $msg 跳转提示信息
     * @return void
     */
    protected function redirect($url,$params=array(),$delay=0,$msg='') {
        $url    =   U($url,$params);
        redirect($url,$delay,$msg);
    }

    /**
     * 默认跳转操作 支持错误导向和正确跳转
     * 调用模板显示 默认为public目录下面的success页面
     * 提示页面为可配置 支持模板标签
     * @param string $message 提示信息
     * @param Boolean $status 状态
     * @param string $jumpUrl 页面跳转地址
     * @param mixed $ajax 是否为Ajax方式 当数字时指定跳转时间
     * @access private
     * @return void
     */
    private function dispatchJump($message,$status=1,$jumpUrl='',$ajax=false) {
        if(true === $ajax || IS_AJAX) {// AJAX提交
            $data           =   is_array($ajax)?$ajax:array();
            $data['info']   =   $message;
            $data['status'] =   $status;
            $data['url']    =   $jumpUrl;
            $this->ajaxReturn($data);
        }
        if(is_int($ajax)) $this->assign('waitSecond',$ajax);
        if(!empty($jumpUrl)) $this->assign('jumpUrl',$jumpUrl);
        // 提示标题
        $this->assign('msgTitle',$status? L('_OPERATION_SUCCESS_') : L('_OPERATION_FAIL_'));
        //如果设置了关闭窗口，则提示完毕后自动关闭窗口
        if($this->get('closeWin'))    $this->assign('jumpUrl','javascript:window.close();');
        $this->assign('status',$status);   // 状态
        //保证输出不受静态缓存影响
        C('HTML_CACHE_ON',false);
        if($status) { //发送成功信息
            $this->assign('message',$message);// 提示信息
            // 成功操作后默认停留1秒
            if(!isset($this->waitSecond))    $this->assign('waitSecond','1');
            // 默认操作成功自动返回操作前页面
            if(!isset($this->jumpUrl)) $this->assign("jumpUrl",$_SERVER["HTTP_REFERER"]);
            $this->display(C('TMPL_ACTION_SUCCESS'));
        }else{
            $this->assign('error',$message);// 提示信息
            //发生错误时候默认停留3秒
            if(!isset($this->waitSecond))    $this->assign('waitSecond','3');
            // 默认发生错误的话自动返回上页
            if(!isset($this->jumpUrl)) $this->assign('jumpUrl',"javascript:history.back(-1);");
            $this->display(C('TMPL_ACTION_ERROR'));
            // 中止执行  避免出错后继续执行
            exit ;
        }
    }


    /*
    +-------------------------------
    + 接入ERP CURL提交或获取ERP资料
    + 2015-12-22 by enhong
    + curl post数据
    +--------------------------------
    */
    protected function curl_post($url,$data,$param=null){
        $curl = curl_init($url);// 要访问的地址 
        //curl_setopt($curl, CURLOPT_REFERER, $param['referer']); 

        curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.11 (KHTML, like Gecko) Chrome/23.0.1271.97 MSIE 8.0'); // 模拟用户使用的浏览器 
        //curl_setopt($curl, CURLOPT_USERAGENT, 'spider'); // 模拟用户使用的浏览器 
        //curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1); // 使用自动跳转 
        //curl_setopt($curl, CURLOPT_AUTOREFERER, 1); // 自动设置Referer 
        //curl_setopt($curl, CURLOPT_ENCODING, ''); // handle all encodings 
        //curl_setopt($curl, CURLOPT_HTTPHEADER, $refer);        

        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);//SSL证书认证
        //curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);//严格认证
        //curl_setopt($curl, CURLOPT_CAINFO,$cacert_url);//证书地址

        curl_setopt($curl, CURLOPT_TIMEOUT, 30); // 设置超时限制防止死循环
        curl_setopt($curl, CURLOPT_HEADER, 0 ); // 过滤HTTP头
        curl_setopt($curl,CURLOPT_RETURNTRANSFER, 1);// 显示输出结果
        curl_setopt($curl,CURLOPT_POST,true); // post传输数据
        curl_setopt($curl,CURLOPT_POSTFIELDS,$data);// post传输数据

        //是否为上传文件
        if(!is_null($param)) curl_setopt($curl, CURLOPT_BINARYTRANSFER, 1);

        $res = curl_exec($curl);
        //var_dump( curl_error($curl) );//如果执行curl过程中出现异常，可打开此开关，以便查看异常内容
        curl_close($curl);
        return $res;        
    }

    protected function curl_get($url,$param=null){
        $curl = curl_init($url);// 要访问的地址 
        //curl_setopt($curl, CURLOPT_REFERER, $param['referer']); 

        curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.11 (KHTML, like Gecko) Chrome/23.0.1271.97 MSIE 8.0'); // 模拟用户使用的浏览器 
        //curl_setopt($curl, CURLOPT_USERAGENT, 'spider'); // 模拟用户使用的浏览器 
        //curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1); // 使用自动跳转 
        //curl_setopt($curl, CURLOPT_AUTOREFERER, 1); // 自动设置Referer 
        //curl_setopt($curl, CURLOPT_ENCODING, ''); // handle all encodings 
        //curl_setopt($curl, CURLOPT_HTTPHEADER, $refer);        

        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);//SSL证书认证
        //curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);//严格认证
        //curl_setopt($curl, CURLOPT_CAINFO,$cacert_url);//证书地址

        curl_setopt($curl, CURLOPT_TIMEOUT, 30); // 设置超时限制防止死循环
        curl_setopt($curl, CURLOPT_HEADER, 0 ); // 过滤HTTP头
        curl_setopt($curl,CURLOPT_RETURNTRANSFER, 1);// 显示输出结果
        $res = curl_exec($curl);
        //var_dump( curl_error($curl) );//如果执行curl过程中出现异常，可打开此开关，以便查看异常内容
        curl_close($curl);

        return $res;        
    }

    /*
    +----------------------------
    + 高并发下创建不重复订单号
    +----------------------------
    */
    public function create_orderno($prefix='',$uid=''){
        if(empty($uid)) $uid = date('His');
        $str    = $prefix.session_id().microtime(true).uniqid(md5(microtime(true)),true);
        $str    = md5($str);
        $prefix = $prefix.date('YmdH').$uid;
        $code   = $prefix.substr(uniqid($str,true),-8,8);
        return $code;
    }

    /**
    * CRC签名
    * @param array $data 要进行签名的数据
    */
    public function crc($data){
        foreach($data as $key=>$val){
            $data[$key]=number_format($val,2);
        }
        ksort($data);
        $query=http_build_query($data).'&'.C('cfg.crc')['crc_code'];
        return md5($query);
    }

    /**
    * 接口请求
    * @param string $api  调用接口
    * @param array $data 提交的数据
    * @param string $nfield 不参与签名的字段
    * @param int $type 1返回数据格式
    */
    public function doApi($api,$data='',$nfield='',$type=''){
        if(empty($data)) $data=$this->api_cfg;
        else $data=array_merge($this->api_cfg,$data);

        $data['sign']       =   $this->_sign($data,$nfield);
        $data['random']     =   $data['random']?$data['random']:session_id();
        //var_dump($data);

        if(!strstr(strtolower($api),'http://') && !strstr(strtolower($api),'https://')) $api = DM('rest').$api;
        //dump($api);
//log_add('api_debug', ['apiurl' => $api, 'data' => $data]);
        $res=$this->curl_post($api,$data);

        if(C('DEBUG_API')==true) print_r($res);
        
        if($type==1) $res=json_decode($res,true); //返回数组格式
        else $res=json_decode($res);
        if(C('DEBUG_API')==true) dump($res);
        /*
        if($res->code!=1){
            $this->err($res->msg);
        }else{
            return $res->data;
        }
        */
        return $res;
 
    }

    /**
     * 新版接口调方法
     * @param string $api      接口地址
     * @param string $data     提交数据
     * @param string $nfield    不签名字段
     * @param string $format    返回数据格式
     * @return json|array
     */
    public function doApi2($api,$data,$nfield='',$return_array=true){
        $s = microtime(true);
        if(!strstr($api,'/Auth/token') && isset($this->token) && (is_null($data['token']) || empty($data['token']))) $data['token'] =       $this->token;
        $data['sign']       =   $this->_sign($data,$nfield);
        $data['random']     =   $data['random']?$data['random']:session_id();
        //var_dump($data);

        if(!strstr(strtolower($api),'http://') && !strstr(strtolower($api),'https://')) $api = DM('rest2').$api;
        //dump($api);
        //log_add('api_debug', ['data' => $data, 'api' => $api, 'time' => date('Y-m-d H:i:s')]);
        $res=$this->curl_post($api,$data);
        //C('DEBUG_API', true);
        //if(C('DEBUG_API')==true) print_r($res);
        //if(C('DEBUG_API')==true) log_add('api_debug', ['res' => $res, 'api' => $api, 'data' => $data, 'step' => 1, 'time' => date('Y-m-d H:i:s')]);
        $res=json_decode($res,$return_array);

        //if(C('DEBUG_API')==true) dump($res);
        //if(C('DEBUG_API')==true) log_add('api_debug', ['res' => $res, 'api' => $api, 'data' => $data, 'step' => 2, 'time' => date('Y-m-d H:i:s')]);
        $e = microtime(true);
        //log_add('api_debug_time', ['data' => $data,  'dotime' => ($e-$s), 'api' => $api, 'res' => $res]);
        return $res;

    }

    /**
    * 生成签名
    * @param array $data 要进行签名的数据
    * @param string $field 要进行签字的键名,为空是表是所有key
    * @param integer $type　1表示$field为不进行签名的Key
    */
    public function _sign($data,$field='',$type=1){
        //$data=array_merge($data,$this->api_cfg);
        //清除不进行签名的字段
        if(isset($data['random'])) unset($data['random']);
        if(!empty($field) && $type==1){
            $field=is_array($field)?$field:@explode(',',$field);
            foreach($data as $key=>$val){
                if(in_array($key, $field)) unset($data[$key]);
            }
        }elseif(!empty($field) && $type!=1){
            $field=is_array($field)?$field:@explode(',',$field);
            foreach($data as $key=>$val){
                if(!in_array($key, $field)) unset($data[$key]);
            }
        }

        ksort($data);
        $query=http_build_query($data).'&'.$this->api_cfg['sign_code'];
        $query=urldecode($query);
        return md5($query);
    }    

    /**
    * 创建唯一ID
    */
    public function create_id(){
        $str=session_id().uniqid(md5(microtime(true)),true);
        $str=md5($str);
        return $str;
    }
    
    /**
    * 检查账户是否正常
    * @param integer $uid 用户ID
    * @param integer $flag 子账户标记
    * @param float $money 金额
    */
    public function check_account($uid,$flag=0,$money=0){
        $do=M('account');
        $rs=$do->lock(true)->where(array('uid'=>$uid))->field('atime,etime,ip',true)->find();
        //dump($do->getLastSQL());
        if($rs){
            $data['ac_cash']        =$rs['ac_cash'];
            $data['ac_score']       =$rs['ac_score'];
            $data['ac_finance']     =$rs['ac_finance'];
            $data['ac_cash_lock']   =$rs['ac_cash_lock'];

            $sign=$this->crc($data);
            //dump($sign);
            //dump($data);

			//admin不进行签名验证
            if($rs['crc']!=$sign && $uid!=1) $rs['status']=5;

            //检查余额是否足够
            if($flag>0){
                if($rs[$this->flag_arr[$flag]]<$money){
                    $rs['status']=6;
                }
            }

            //状态（0-冻结，1-正常，2注销）
            switch($rs['status']){
                case 5:
                    //CRC签名错误
					$result['code']=85;
                break;            	
                case 0:
                    //账户被冻结
					$result['code']=83;
                break;
                case 2:
                    //账户已注销
					$result['code']=84;
                break;
                case 6:
                    //余额不足
					$result['code']=86;
                break;
                default:
					$result['code']=1;
					$result['data']=$data;
                break;
            }

        }else{
            //账户不存在
			$result['code']=82;
        }
		
		return $result;
    }

    /**
    * 短信模板
    * @param integer $id 短信模板ID
    * @param string|array $fstr 查找内容
    * @param string|array $rstr 替换内容
    */
    public function sms_tpl($id,$fstr='',$rstr=''){
        $do=M('sms_tpl');
        if($rs=$do->where(array('id'=>$id))->field('tpl_content')->find()){
            $result=str_replace($fstr,$rstr,$rs['tpl_content']);
            return $result;
        }else{
            return false;
        }
    }    

    /**
    * 缓存数据表数据
    */
    public function cache_table($table){
        $cache_table=[
            'admin_sort'        =>'id,group_name',
            'api_category'      =>'id,category_name',
            'area'              =>'id,a_name',
            'config_sort'       =>'id,name',
            'express_category'  =>'id,category_name',
            'express_company'   =>'id,sub_name',
            'goods_cfg'         =>'id,cfg_name',
            'help_category'     =>'id,category_name',
            'msg_category'      =>'id,category_name',
            'modules'           =>'id,module_name',
            'news_category'     =>'id,category_name',
            'search_keyword'    =>'id,keyword',
            'shop_type'         =>'id,type_name',
            'shop_notdomain'    =>'id,domain',
            'shop_notname'      =>'id,name',
            'user_level'        =>'id,level_name',
            'goods_category'    =>'id,category_name',
        ];

        $list=S('table_'.$table);
        if(empty($list)){
            $do=M($table);
            $list=$do->cache('table_'.$table,0)->getField($cache_table[$table],true);
        }

        return $list;
    }
    
    
    /**
     * curl
     * @param url $url
     * @param array $data
     * @param string $jsonde
     * @param string $header
     * @return Ambigous <mixed, unknown>
     */
    protected function curl($url = null, $data = null, $jsonde = null, $header = null) {
        $s = microtime(true);
        if (strpos($url, 'http') === false) $url = DM('rest') . $url;
        if (empty($data['sign'])) {
            if (!empty($data)) {
                $data   =   array_merge($data, getApiCfg());
            } else {
                $data   =   getApiCfg();
            }
            ksort($data);
            $nosign     =   $data['nosign'];
            unset($data['nosign']);
            $data['sign']   =   _sign($data, $nosign);
        }
        $data['random']	= $data['random']?$data['random']:session_id();
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
        //echo $res;
        curl_close($ch);
        $res    =  $jsonde == null ? $res : json_decode($res, true);
        $e = microtime(true);
        //writeLog($url . '执行:' . ($e - $s) . '秒');
        return $res;
    }
    
    protected function curlGet($url = null, $data = null, $jsonde = null, $header = null) {
        $s = microtime(true);
        $data['random']	= $data['random']?$data['random']:session_id();
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_URL, $url);
        //curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.11 (KHTML, like Gecko) Chrome/23.0.1271.97 MSIE 8.0'); // 模拟用户使用的浏览器
        if ($header) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        }
        //curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);//SSL证书认证
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $res = curl_exec($ch);
        //如果页面错误则写进日志
        if (is_null(json_decode($res, true))) log_add('pccurl', ['time' => date('Y-m-d H:i:s', NOW_TIME), 'api' => $url, 'data' => $data, 'res' => $res]);
        //echo $res;
        curl_close($ch);
        $res    =  $jsonde == null ? $res : json_decode($res, true);
        $e = microtime(true);
        //writeLog($url . '执行:' . ($e - $s) . '秒');
        return $res;
    }
    
    /**
    * 密码加密码方法
    */
    public function password($str){
        return MD5(SHA1($str) . '@$^^&!##$$%%$as%$$^&&le2g3*4Hd42#JU2|3t');
    }

    /**
     * cookie自动登陆
     * @param int $expire COOKIE有效期
     */
    public function cookie_login($expire=86400) {
        $cookie = cookie('remember');
        if($cookie && session('user') == false){
            $cookie = unserialize(deCryptRestUri($cookie));
            $id     = $cookie['sub_id'] ? : $cookie['id'];  //有子账号ID则使用子账号登陆
            if($user = M('user')->where(['id' =>$id,'status' => 1])->field('id,ip,group_id,parent_uid,shop_auth_group_id,openid,level_id,status,up_uid,nick,name,face,mobile,type,erp_uid,is_auth,shop_type,shop_id,company')->find()){
                $user_level = $this->cache_table('user_level');
                $user['level_name'] = $user_level[$user['level_id']];
                if ($user['parent_uid'] > 0) {  //子账号
                    $parentUser = M('user')->cache(true)->where(['id' => $user['parent_uid']])->field('shop_type,openid,id')->find();
                    $user['sub_openid'] = $user['openid'];
                    $user['openid']     = $parentUser['openid'];
                    $user['sub_id']     = $user['id'];
                    $user['id']         = $parentUser['id'];
                    $user['shop_type']  = $parentUser['shop_type'];
                }
                M()->execute('update '.C('DB_PREFIX').'user set last_login_ip="'.get_client_ip().'",last_login_time=now(),loginum=loginum+1 where id='.$id);
                S(md5(session_id()),$user['erp_uid'],3600); //用于ERP快捷登录商城

                session('user',$user);
                cookie('remember', enCryptRestUri(serialize($user)), $expire);
            }
        }
    }

   /**
     * 析构方法
     * @access public
     */
    public function __destruct() {
        // 执行后续操作
        Hook::listen('action_end');
    }
}
// 设置控制器别名 便于升级
class_alias('Think\Controller','Think\Action');
