<?php
/**
+----------------------------------------------------------------------
| RestFull API V2.0
+----------------------------------------------------------------------
| 公共文件
+----------------------------------------------------------------------
| Author: lazycat <673090083@qq.com>
+----------------------------------------------------------------------
+ 2016-12-07
+----------------------------------------------------------------------
*/
namespace Rest2\Controller;
use Think\Controller\RestController;
use Think\Exception;
class CommonController extends RestController {
	protected $allowMethod    	= array('post'); // REST允许的请求类型列表
	protected $allowType      	= array('html','xml','json'); // REST允许请求的资源类型列表
    protected $sw           	= array();   //事务执行结果
    protected $post;            //接口传参数据，即$_POST数据
    protected $get;             //$_GET
    protected $user;            //用户信息
    protected $action_logs;     //要记录日志的方法
    protected $ip;
    protected $need_field;      //必传字段
    protected $token;           //token;
    protected $api_cfg          = array();  //兼容前端接口调用方法 doApi
    protected $terminal         = 1;        //接口所属终端

	public function _initialize() {
        //header('Access-Control-Allow-Origin: *');
        G('begin');


        if(!IS_POST){
	        //E('非法操作！');
            //$this->apiReturn(['code' => 0,'msg' => '非法操作！']);
            //exit();
        }

        $this->ip           = get_client_ip();
        $this->post         = I('post.');
        $this->get          = I('get.');

        //验证请求合法性
        if(CONTROLLER_NAME == 'Auth' && (ACTION_NAME == 'token' || ACTION_NAME == 'token1')){    //授权接口

        }else{  //当请求的不是授权接口时需要进行验证，token有效期为10分钟
            $cache_token = 'api_token_' . $this->post['token'];
            $this->token = S($cache_token);
            if(empty($this->token)){
                $this->apiReturn(['code' => 0,'msg' => 'Token已失效！']);
            }
        }
        $this->terminal = $this->token['data']['terminal'];

		//各频道子域名
		C('sub_domain',sub_domain());

		//站点配置
		$cfg=D('Common/Config')->config(array('cache_name'=>'cfg'));
		C('cfg',$cfg);

        //获取用户信息
        if($this->post['openid']){
            $this->user = $this->_user(['openid' => $this->post['openid']]);
        }


        //加载错误代码库
        //S('error_code',null);
        $error_code = D('Common/ErrorCode')->error_code();
        C('error_code',$error_code);

	}


	/**
     * 返回结果
     * @param array $result  格式如：['code' => 1,'msg' => 'msg','data' => '']
     */
	public function apiReturn($result){
	    $msg[0] = '操作失败！';
	    $msg[1] = '操作成功！';
        $msg[3] = '找不到记录！';

        if(empty($result['msg'])) $result['msg'] = $msg[$result['code']];

        G('end');
        //在此记录日志，方便接口错误调试
        if(C('API_DEBUG_LOG') || (C('API_LOG') && in_array(ACTION_NAME,$this->action_logs))){
            $cache_token = 'api_token_' . $this->token['token'];

            $logs['atime']	= date('Y-m-d H:i:s');
            $logs['ip']		= $this->ip;
            $logs['nick']	= $this->user['nick'];
            $logs['code']	= $result['code'];
            $logs['msg']	= $result['msg'];
            $logs['dotime']	= G('begin','end');
            $logs['url']	= ($_SERVER['HTTPS']=='on'?'https://':'http://').$_SERVER['HTTP_HOST'].__SELF__;
            $logs['sw']		= @implode(',',$this->sw);
            $logs['post']	= @var_export($this->post,true);
            $logs['res']	= @var_export($result['data'],true);
            $logs['token'] = @var_export(S($cache_token),true);

            log_add('api_v2_'.date('Ym'),$logs);
        }
        if($result['code'] == 1 && ($_SERVER['HTTP_HOST'] == 'rest2.dtshop.com' || $_SERVER['HTTP_HOST'] == 'rest2.dtmall.com')){
            M('api_doc2')->where(['api_url' => __SELF__])->data(['return' => json_encode($result)])->save();
        }
        $this->response($result);
        exit();
    }


    /**
     * 获取用户资料
     * @param string $openid 用户openid
     */
    public function _user($param){
        if($param['openid']) $map['openid'] = $param['openid'];
        else $map['id'] = $param['id'];

        $do = M('user');
        if($rs = $do->where($map)->field('id,level_id,nick,password_pay,is_auth,shop_type,erp_uid,shop_id,openid')->find()){
            return $rs;
        }else{
            return false;
        }
    }

    /**
     * 验证必传字段
     */
    public function check_need_field(){
        $this->need_field[] = 'sign';
        $this->need_field   = array_unique($this->need_field);

        foreach($this->need_field as $val){
            if(is_null($this->post[$val]) || (empty($this->post[$val]) && $this->post[$val] !=0)){
                $this->apiReturn(['code' => 0,'msg' => '参数'. $val .'不能为空！']);
            }
        }
    }

    /**
     * 签名校验
     */
    public function check_sign($nosign=''){
        if($this->post['sign'] != $this->_sign($nosign)){
            $this->apiReturn(['code' => 0,'msg' => '签名校验失败！']);
        }
    }

    /**
     * 生成签名
     * @param string|array $nosign 不参与签名的字段，如文件上传等
     */
    public function _sign($nosign=''){
        if($nosign){
            if(!is_array($nosign)) {
                $nosign = explode(',',$nosign);
                $nosign[] = 'random';
                $nosign[] = 'sign';
            }
        }else $nosign = ['random','sign'];

        $data = array();
        foreach($this->post as $key => $val){
            if(!in_array($key,$nosign)) $data[$key] = $val;
        }
        ksort($data);
        $query=http_build_query($data).'&'.($this->token['data']['sign_code'] ? $this->token['data']['sign_code'] : $this->post['sign_code']);
        $query=urldecode($query);
        return md5($query);

    }

    /**
     * 防止重复请求
     * @param float $time 允许重复请求的时间间隔
     */
    public function check_require($time=0.3){
        $cache_name = 'req_'.md5($this->post['sign'].'_'.$this->post['random'].'_'.$this->ip);

        $cache_time = S($cache_name);
        $microtime=microtime(true);
        if($cache_time>0 && ($microtime-$cache_time < $time)){
            $this->apiReturn(['code' => 0,'msg' => '请不要频繁请求！']);
        }

        S($cache_name,$microtime,10);
    }

    /**
     * 请求方法前相关校验
     * @param string $need_field 必填字段
     * @param int $check_require 请求限制
     * @param string $nosign_field 不参与签名字段
     */
    public function check($need_field='sign',$check_require=1,$nosign_field=''){
        if($check_require !== false && $check_require !== 0) $this->check_require($check_require);

        if(empty($need_field)) $need_field[] = 'sign';
        if(!is_array($need_field)) $need_field = explode(',',$need_field);

        //必填校验
        $this->need_field = $need_field; //必传字段
        $this->check_need_field();

        //签名校验
        $this->check_sign($nosign_field);
    }

    /**
     * 数据验证
     * @param string $value 要验证的数据
     * @parma array $regex 验证规则
     * $this->check_data($value,array(array('regex'=>'is_mobile','msg'=>'手机号码格式错误'));
     */
    public function check_data($value,$regex){
        foreach($regex as $val){
            if(!checkform($value,$val['regex'])){
                return ['code' => 0,'msg' => $val['msg']];
                break;
            }
        }
        return ['code' => 1];
    }

    /**
     * 批量验证数据
     * @param array $data 要验证的数据
     */
    public function batch_check_data($data){
        foreach($data as $val){
            $res = $this->check_data($val['value'],$val['regex']);
            if($res['code'] != 1) {
                $this->apiReturn($res);
                break;
            }
        }
    }

    /**
     * 自动取非必传字段
     * @param string|array $field       非必填字段，字符串是多个用逗号隔开
     * @param string|array $need_field  必传字段
     */
    public function _field($field='',$need_field=''){
        $sign_field = array();
        if($need_field){
            if(!is_array($need_field)) $need_field = explode(',',$need_field);
            if(!empty($need_field)) $sign_field = array_merge($need_field,$sign_field);
        }

        if($field){
            if(!is_array($field)) $field = explode(',',$field);

            if(!empty($field)) {
                $keys = array_keys($this->post);
                foreach($field as $val){
                    if(in_array($val,$keys)) $sign_field[] = $val;
                }
            }

        }

        return $sign_field;
    }


    /**
     * 检查支付密码
     * Create by Libo
     * @param string $pay_password 用$this->password处理过的密码
     */
    public function _check_pay_password($pay_password){
        $max        = 5;
        $cache_name = md5('pay_password_errors' . $this->user['id']);
        $is_lock    = S('Lock_'.$cache_name);
        if(!empty($is_lock)) return ['code' => 0,'msg' => '您在10钟内输错安全密码已超过'.$max.'次，被冻结2小时！'];

        $res = A('Erp')->_check_safe_password(['pay_password' => $pay_password]);
        //print_r($res);
        if($res['code'] == 1) {
            S($cache_name,null);
            return ['code' => 1,'msg' => '安全密码正确！'];
        }

        $data       = S($cache_name);
        if(empty($data)) $data['num'] = 0;
        $data['num']++;

        if($data['num'] >= $max){
            S('Lock_'.$cache_name, time(), 3600 * 2);   //冻结2小时
            S($cache_name,null);
        }else{
            S($cache_name,$data,600);
        }

        return ['code' => 0,'msg' => '您的安全密码已输入错误'.$data['num'].'次，如果安全密码连续错误'.$max.'次，您的支付账户将被冻结2小时！'];
    }

	 /**
    * 检查七牛图片尺寸
    * @param string $_POST['img'] 图片url
    * @param int    $_POST['width'] 宽度
    * @param int    $_POST['height'] 高度
    */
    public function qn_imgsize($img,$width,$height){
        $res=$this->curl_get($img.'?imageInfo');
        $res=json_decode($res);
        if($width==$res->width && $height==$res->height) return true;
        else return false;
    }

}