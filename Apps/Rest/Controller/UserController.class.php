<?php
/*
+----------------------------
+ 用于处理用户的各项操作 by enhong
+-----------------------------
*/
namespace Rest\Controller;
use Think\Controller\RestController;
class UserController extends CommonController {
    public function index(){
    	redirect(C('sub_domain.www'));
    }
    /**
    * 用户登录验证
    * @param string $param['username'] 账号
    * @param string $param['password'] 密码
    * @param integer $param['timestamp']	时间
    */
    public function login(){
    	//频繁请求限制
    	$this->_request_check();

    	//必传参数检查
        $this->need_param=array('username','password','sign');
        $this->_need_param();
        $this->_check_sign();

        $do=D('Common/UserRelation');
		$username=strtolower(trim(I('post.username')));
        $map['_string']='nick="'.$username.'" or mobile="'.$username.'"';
        $map['password']=md5(trim(I('post.password')));
        if(!$rs=D('Common/UserRelation')->relation(true)->where($map)->field('etime,ip',true)->find()){
            //找不到记录，即账号或密码错误
            $this->apiReturn(21);
        }

    	if($rs['status']!=1){
    		//账号被停用
            $this->apiReturn(22);
    	}

    	$do->execute('update '.C('DB_PREFIX').'user set last_login_ip="'.get_client_ip().'",last_login_time=now(),loginum=loginum+1 where id='.$rs['id']);

        $this->apiReturn(1,array('data'=>$rs));

    }

    /**
    * 个人会员注册
    */
    public function register(){
    	//频繁请求限制,间隔2秒
    	$this->_request_check(2);

    	//必传参数检查
        $this->need_param=array('username','password','mobile','smscode','sign');
        $this->_need_param();
        $this->_check_sign();

        if(checkform(I('post.password'),array('text_range',6,20))==false){
            $this->apiReturn(4,'',1,'密码格式错误，必须是6~20位之间的字母或数字组合且区分大小写！');
        }

        $cache_name='sms_vcode_'.trim(I('post.mobile')).'_11';
        $smscode=S($cache_name);
        if($smscode['code']!=I('post.smscode') || empty($smscode) || empty($_POST['smscode'])){
            //验证码错误
            $this->apiReturn(43);
        }

        $do=D('Common/User');
        //检查用户名
        if($do->where(array('nick'=>strtolower(I('post.username'))))->find()){
            //用户名已被占用    
            $this->apiReturn(33);
        }elseif($do->where(array('mobile'=>strtolower(I('post.mobile'))))->find()){
            //手机号码已被占用
            $this->apiReturn(34);
        }

    	//检查上级用户是否存在
    	if(I('post.up_uid')){
    		if(!$urs=$do->where(array('id'=>trim(strtolower(I('post.up_uid'))),'_logic'=>'or','nick'=>trim(strtolower(I('post.up_uid')))))->field('id')->find()){
    			//推荐人不存！
		    	 $this->apiReturn(32);
    		}else{
                $_POST['up_uid']=$urs['id'];
            }
    	}else{
            $_POST['up_uid']=0;
        }

        //取上级
        if(I('post.up_uid')>0){
            $urs=M('user_relation')->where(array('uid'=>I('post.up_uid')))->field('upuid_list')->find();
        }

        $data['nick']=strtolower(I('post.username'));
        $data['password']=md5(trim(I('post.password')));
        $data['mobile']=I('post.mobile');
        $data['up_uid']=I('post.up_uid');
        $data['level']=1;
        $data['openid']=$this->create_id();

        $this->sw=array();
        $do->startTrans();

        //创建用户
        $do=D('Common/User');
        if($sw1=$do->create($data)) {
            $sw1=$do->add();
            $insid=$do->getLastInsID();
        }else{
            $msg[]=$do->getError();
            goto error;
        }

        //var_dump($do->getLastSQL());

        //创建账户
        $do=D('Common/Account');
        $data=array();
        $data['ac_cash']        =0.00;
        $data['ac_commission']  =0.00;
        $data['ac_score']       =0.00;
        $data['ac_finance']     =0.00;
        $data['ac_cash_lock']   =0.00;
        $data['crc']            =$this->crc($data);
        $data['uid']            =$insid;

        //dump($data);

        if($sw2=$do->create($data)) {
            $sw2=$do->add();
        }else{
            $msg[]=$do->getError();
            goto error;
        }

        //记录我的上级UID
        $do=D('Common/UserRelation2');
        $data=array();
        $data['uid']=$insid;
        $data['upuid_list']=$insid;
        if($urs['upuid_list']) $data['upuid_list']=$urs['upuid_list'].','.$insid;
        $data['level']=count(@explode(',',$data['upuid_list']));
        if($sw3=$do->create($data)) {
            $sw3=$do->add();
        }else{
            $msg[]=$do->getError();
            goto error;
        }
        

        //更新上级团队人数
        if(!$sw4=M('user')->where(array('id'=>array('in',$data['upuid_list'])))->setInc('team_num')){
            goto error;
        }

        
        success:
        $this->sw=array($sw1,$sw2,$sw3,$sw4);
        $do->commit();
        $rs=D('Common/UserRelation')->relation(true)->where(array('id'=>$insid))->field('etime,ip',true)->find();
        $this->apiReturn(1,array('data'=>$rs));

        error:
        $this->sw=array($sw1,$sw2,$sw3,$sw4);
        $do->rollback();
        $this->apiReturn(4,'',1,'注册失败！'.@implode('<br>',$msg));  
    }


    /*
    * 获取用户信息
    */
    public function userinfo(){
        //必传参数检查
        $this->need_param=array('openid','sign');
        $this->_need_param();
        $this->_check_sign();    

        $cache_name=md5($this->_sign().__SELF__);
        $do=M('user');
        $rs=D('Common/UserRelation')->relation(true)->cache(true,C('CACHE_LEVEL.XXS'))->where(array('id'=>$this->uid))->field('etime,ip',true)->find();
        if($rs){
            //获取资料成功
            $this->apiReturn(1,array('data'=>$rs));
        }else{
            //获取资料失败
            $this->apiReturn(3);
        }
    }

    /**
    * 修改密码
    */
    public function change_password(){
        //频繁请求限制,间隔2秒
        $this->_request_check();

        //必传参数检查
        $this->need_param=array('openid','old_password','password','password2','sign');
        $this->_need_param();
        $this->_check_sign();



        if(checkform(I('post.password'),array('text_range',6,20))==false){
            $this->apiReturn(4,'',1,'新密码格式错误，必须是6~20位之间的字母或数字组合且区分大小写！');
        }

        if(I('post.old_password')==I('post.password')){
            //新密码不能与旧密码一样
            $this->apiReturn(37);
        }        

        if(I('post.password')!=I('post.password2')){
            //两次新密码不一致
            $this->apiReturn(36);
        }

        $do=M('user');

        $rs =   $do->where(array('id'=>$this->uid,'password'=>md5(trim(I('post.old_password')))))->getField('password');
        if(!$rs){
            //旧密码错误！
            $this->apiReturn(35);
        }
        if($do->where(array('id'=>$this->uid))->save(array('password'=>md5(trim(I('password')))))){
            //修改成功
            $this->apiReturn(1);
        }else{
            //修改失败
            $this->apiReturn(0);
        }
    }

    /**
    * 设置安全密码
    */
    public function set_password_pay(){
        //频繁请求限制,间隔2秒
        $this->_request_check();

        //必传参数检查
        $this->need_param=array('openid','login_password','password','password2','sign');
        $this->_need_param();
        $this->_check_sign();



        if(checkform(I('post.password'),array('password_safe'))==false){
            $this->apiReturn(4,'',1,'密码格式错误，必须是6位数字！');
        }

        if(I('post.login_password')==I('post.password')){
            //登录密码不能与安全密码一样
            $this->apiReturn(39);
        }        

        if(I('post.password')!=I('post.password2')){
            //两次密码不一致
            $this->apiReturn(301);
        }

        $do=M('user');
        $rs=$do->where(array('id'=>$this->uid))->field('password,password_pay')->find();
        
        //已设置过安全密码不可再次设置，如果要修改安全密码请到安全中心修改
        if($rs['password_pay']!='') $this->apiReturn(302);

        //登录密码错误！
        if($rs['password']!=md5(trim(I('post.login_password')))) $this->apiReturn(300);
        //$this->ajaxReturn(array('code' => 0, 'msg' => $rs['password'] . '===' . md5(trim(I('post.login_password'))) . '===' . $this->uid));

        if($do->where(array('id'=>$this->uid))->save(array('password_pay'=>md5(trim(I('post.password')))))){
            //修改成功
            $this->apiReturn(1);
        }else{
            //修改失败
            $this->apiReturn(0);
        }
    }    


    /**
    * 修改安全密码
    */
    public function change_password_pay(){
        //频繁请求限制,间隔2秒
        $this->_request_check();

        //必传参数检查
        $this->need_param=array('openid','old_password','password','password2','sign');
        $this->_need_param();
        $this->_check_sign();



        if(checkform(I('post.password'),array('password_safe'))==false){
            $this->apiReturn(4,'',1,'密码格式错误，必须是6位数字！');
        }

        if(I('post.old_password')==I('post.password')){
            //新密码不能与旧密码一样
            $this->apiReturn(37);
        }        

        if(I('post.password')!=I('post.password2')){
            //两次新密码不一致
            $this->apiReturn(36);
        }

        $do=M('user');
        if(!$rs=$do->where(array('id'=>$this->uid,'password_pay'=>md5(trim(I('post.old_password')))))->find()){
            //旧密码错误！
            $this->apiReturn(355);
        }

        if($do->where(array('id'=>$this->uid))->save(array('password_pay'=>md5(trim(I('post.password')))))){
            //修改成功
            $this->apiReturn(1);
        }else{
            //修改失败
            $this->apiReturn(0);
        }
    }

	/**
	* 更改手机号码步骤一：向旧手机号发验证码
	*/
	public function old_mobile_smscode(){
        //频繁请求限制
        $this->_request_check();

        //必传参数检查
        $this->need_param=array('openid','sign');
        $this->_need_param();
        $this->_check_sign();
		
		$tplid=12; //短信模板
		
		$rs=M('user')->where('id='.$this->uid)->field('mobile')->find();
		if($rs){
			$cache_name='sms_vcode_'.$rs['mobile'].'_'.$tplid;
			$code=S($cache_name);
			//dump($code);exit;
			if(empty($code)) {
				$code['code']=rand(100000,999999);
				$code['atime']=time();
			}else{
				if(time()-$code['atime'] < 60){ //60秒内只能发送一次
					//请不要频繁发送！
					$this->apiReturn(42);
				}
						
				$code['atime']=time();
			}
				
			if(false==$content=$this->sms_tpl($tplid,'{code}',$code['code'])){
				//短信模板错误！
				$this->api_result(41);
			}

			$data['content']=$content;
			$data['userid']=C('cfg.sms')['userid'];
			$data['account']=C('cfg.sms')['account'];
			$data['password']=C('cfg.sms')['password'];
			$data['action']='send';
			$data['mobile']=$rs['mobile'];
			//dump($cache_name);
			//dump($data);exit;
			$api=C('cfg.sms')['sms'];
			$res=$this->curl_post($api,$data);
			//dump($res);
			$xml = simplexml_load_string($res);
			if($xml->returnstatus=='Success'){
				S($cache_name,$code,60*10); //10分钟内有效
				$this->apiReturn(1);
			}else{
				$this->apiReturn(0);
			}		
			
		}else{
			//找不到记录
			$this->apiReturn(3);
		}		
	}

    /**
    * 更改手机号码步骤二
    */
    public function change_mobile(){
        //频繁请求限制,间隔2秒
        $this->_request_check();

        //必传参数检查
        $this->need_param=array('openid','mobile','old_smscode','smscode','sign');
        $this->_need_param();
        $this->_check_sign();
        
        
        if(M('user')->where(array('mobile' => I('post.mobile')))->find()) {
            $this->apiReturn(34);
        }
        
        
        $cache_name='sms_vcode_'.trim(I('post.mobile')).'_12';
        $smscode=S($cache_name);
        if($smscode['code']!=I('post.smscode')){
            //验证码错误
            $this->apiReturn(43);
        }

        $rs=M('user')->where('id='.$this->uid)->field('mobile')->find();
        $cache_name2='sms_vcode_'.$rs['mobile'].'_12';
        $old_code=S($cache_name2);
		//dump($rs['mobile']);
        if(empty($old_code['code']) || $old_code['code']!=I('post.old_smscode')){
            //旧手机号验证码错误
            $this->apiReturn(44);
        }

        if(M('user')->where('id='.$this->uid)->setField('mobile',I('post.mobile'))){
            //修改成功
            S($cache_name,null);
            S($cache_name2,null);
            $this->apiReturn(1);
        }else{
            //修改失败
            $this->apiReturn(0);
        }


    }

    /**
     * wap端,向旧手机号发送验证码
     * @param string $openid 用户openid
     * @param sign $sign 签名
     */
    public function wap_mobile_smscode_old(){
        $this->need_param = array('openid','sign');
        $this->_need_param();
        $this->_check_sign();
        // 是否存在用户
        $openid = I('openid', '');
        $model  = M('user');
        $user   = $model->where(['openid' => $openid])->field('id,openid,mobile')->find();
        if(! isset($user['id'])){
            $this->apiReturn(8);// 并没找到用户
        }
        // 是否有缓存
        $cache_key  = 'wap_change_mobile_old' . $user['openid'];
        $cache      = S($cache_key);
        if(isset($cache['code'])){
            $stime = $cache['stime'];
            if(time() - $stime < 60){
                $this->apiReturn(42);// 不能频繁发送短信
            }
        }

        $code = mt_rand(100000,999999);
        if( $this->sms_code($user['mobile'], $code) ){
            S($cache_key, ['code'=>$code,'stime'=>time()], 60 * 10);
            $this->apiReturn(1);
        }else{
            $this->apiReturn(0);
        }
    }

    /**
     * wap端,验证旧手机验证码
     * @param string $openid 用户openid
     * @param string $code 6位验证码
     * @param sign $sign 签名
     */
    public function wap_change_mobile_check(){
        $this->need_param = array('openid','code','sign');
        $this->_need_param();
        $this->_check_sign();

        $openid = I('openid', '');
        $model  = M('user');
        $user   = $model->where(['openid' => $openid])->field('id,openid,mobile')->find();
        if(! isset($user['id'])){
            $this->apiReturn(8);// 并没找到用户
        }

        $cache_key = 'wap_change_mobile_old' . $user['openid'];
        $cache = S($cache_key);
        if(! isset($cache['code'])){
            $this->apiReturn(5);// 过期
        }

        $cache_code = $cache['code'];
        S($cache_key, null);
        if($cache_code == I('post.code', 0, 'int')){
            $this->apiReturn(1);
        }else{
            // $cache['postcode'] = I('post.code', 0, 'int');
            $this->apiReturn(0,['msg'=>'验证码错误']);
        }
    }

    /**
     * wap端,向新手机号发送验证码
     * @param string $openid 用户openid
     * @param string $mobile 手机号码
     * @param sign $sign 签名
     */
    public function wap_mobile_smscode_new(){
        $this->need_param = array('openid','mobile','sign');
        $this->_need_param();
        $this->_check_sign();
        // 是否存在用户
        $openid = I('openid', '');
        $model  = M('user');
        $user   = $model->where(['openid' => $openid])->field('id,openid,mobile')->find();
        if(! isset($user['id'])){
            $this->apiReturn(8);// 并没找到用户
        }
        // 是否有缓存
        $cache_key  = 'wap_change_mobile_new' . $user['openid'];
        $cache      = S($cache_key);
        if(isset($cache['code'])){
            $stime = $cache['stime'];
            if(time() - $stime < 60){
                $this->apiReturn(42);// 不能频繁发送短信
            }
        }

        $code = mt_rand(100000,999999);
        $mobile = I('post.mobile');
        if( $this->sms_code($mobile, $code) ){
            S($cache_key, ['code'=>$code,'stime'=>time(),'mobile'=>$mobile], 60 * 10);
            $this->apiReturn(1);
        }else{
            $this->apiReturn(0);
        }
    }

    /**
     * wap端，更换手机保存
     * 
     */
    public function wap_change_mobile_save(){
        $this->need_param = array('openid','code','sign');

        $this->_need_param();
        $this->_check_sign();

        $openid = I('openid', '');
        $model  = M('user');
        $user   = $model->where(['openid' => $openid])->field('id,openid,mobile')->find();
        if(! isset($user['id'])){
            $this->apiReturn(8);// 并没找到用户
        }
        $cache_key = 'wap_change_mobile_new' . $user['openid'];
        $cache = S($cache_key);
        if(! isset($cache['code'])){
            $this->apiReturn(5);// 过期
        }

        $cache_code = $cache['code'];
        S($cache_key, null);
        if($cache_code == I('post.code', 0, 'int')){
            $r = M('user')->where(['openid'=>$user['openid']])->data(['mobile'=>$cache['mobile']])->save();
            if($r > 0 || $r === 0){
                $this->apiReturn(1);
            }else{
                $this->apiReturn(0);
            }
        }else{
            $this->apiReturn(0);
        }


    }

    /**
     * 通用发送验证码
     */
    private function sms_code($mobile,$code,$tplid = 12){
        $tplid      = $tplid;// 短信模板
        $code       = $code;
        $mobile     = $mobile;
        $content    = $this->sms_tpl($tplid,'{code}',$code);
        if(! $content){
            return false;//短信模板错误！
            // $this->apiReturn(41);
        }

        $data['content']    = $content;
        $data['userid']     = C('cfg.sms')['userid'];
        $data['account']    = C('cfg.sms')['account'];
        $data['password']   = C('cfg.sms')['password'];
        $data['action']     = 'send';
        $data['mobile']     = $mobile;
        
        $api = C('cfg.sms')['sms'];
        $res = $this->curl_post($api,$data);
        $xml = simplexml_load_string($res);
        if($xml->returnstatus == 'Success'){
            return true;
        }else{
            return false;
        }
    } 


    /**
     * 忘记支付密码 1 发送验证码
     * @param string $openid 微信用户openid
     */
    public function forget_password_pay_sms(){
        $this->need_param = ['openid'];
        $this->_need_param();
        $this->_check_sign();
        // 是否存在用户
        $openid = I('openid', '');
        $model  = M('user');
        $user   = $model->where(['openid' => $openid])->field('id,openid,mobile')->find();
        if(! isset($user['id'])){
            $this->apiReturn(8);// 并没找到用户
        }
        // 是否有缓存
        $cache_key  = 'forget_password_pay_user_code' . $user['openid'];
        $cache      = S($cache_key);
        if(isset($cache['code'])){
            $stime = $cache['stime'];
            if(time() - $stime < 60){
                $this->apiReturn(42);// 不能频繁发送短信
            }
        }

        $tplid      = 12;// 短信模板
        $code       = mt_rand(100000, 999999);
        $mobile     = $user['mobile'];
        $content    = $this->sms_tpl($tplid,'{code}',$code);
        if(! $content){
            //短信模板错误！
            $this->apiReturn(41);
        }

        $data['content']    = $content;
        $data['userid']     = C('cfg.sms')['userid'];
        $data['account']    = C('cfg.sms')['account'];
        $data['password']   = C('cfg.sms')['password'];
        $data['action']     = 'send';
        $data['mobile']     = $mobile;
        
        $api = C('cfg.sms')['sms'];
        $res = $this->curl_post($api,$data);
        $xml = simplexml_load_string($res);
        if($xml->returnstatus == 'Success'){
            // 保存一个key用于第2步,10分钟内有效
            S($cache_key, ['stime' => time(), 'code' => $code], 60*10);
            $this->apiReturn(1);
        }else{
            $this->apiReturn(0);
        }
    }

    /**
     * 忘记支付密码 2 手机验证码验证和保存新密码
     * @param string $openid 微信用户openid
     * @param int $code 6位数手机验证码
     * @param string pay_new
     * @param string pay_new_confirm
     */
    public function forget_password_pay_save(){
        $this->need_param = ['openid', 'code', 'pay_new', 'pay_new_confirm'];
        $this->_need_param();
        $this->_check_sign();
        
        
        
        $pay_new         = I('post.pay_new');
        $pay_new_confirm = I('post.pay_new_confirm');
        $openid = I('post.openid');
        if(checkform(I('post.pay_new'),array('password_safe'))==false){
            $this->apiReturn(4,'',1,'密码格式错误，必须是6位数字！');
        }
        // 是否有缓存
        $cache_key  = 'forget_password_pay_user_code' . $openid;
        $cache      = S($cache_key);
        S($cache_key,null);
        if(! isset($cache['code']) || (time() - $cache['stime'] > 60 * 10) ){
            // 验证码不存在或者过期
            $this->apiReturn(5);
        }
        if($cache['code'] !== I('code', 0, 'int')){
            $this->apiReturn(43);// 验证码错误
        }

        
        if($pay_new !== $pay_new_confirm ){
            // 新支付密码和确认的不一致
            $this->apiReturn(36);
        }else{
            $model = M('user');
            if($model->where(['id' => $this->uid])->data(['password_pay' => md5($pay_new)])->save()){
                $this->apiReturn(1);
            }else{
                $this->apiReturn(0);
            }
        }
    }


    /**
     * 修改用户资料
     * @param string $openid
     * @param string $name
     * @param int $sex
     * @param string $face
     * @param date birthday
     * @param string email
     * @param int qq
     */
    public function change_information(){
        $this->need_param = ['openid'];
        $this->_need_param();
        $this->_check_sign();

        $model = M('user');
        $user = $model->where(['id' => $this->uid])->field('id,name,sex,face,birthday,email,qq,is_auth')->find();
        if(! isset($user['id'])){
            // 没有找到用户
            $this->apiReturn(8);
        }


        isset($_POST['name'])       ? $data['name']     = I('post.name')            : '';
        isset($_POST['sex'])        ? $data['sex']      = I('post.sex', 0, 'int')   : '';
        isset($_POST['face'])       ? $data['face']     = I('post.face')            : '';
        isset($_POST['birthday'])   ? $data['birthday'] = I('post.birthday')        : '';
        isset($_POST['email'])      ? $data['email']    = I('post.email')           : '';
        isset($_POST['qq'])         ? $data['qq']       = I('post.qq', 0, 'int')    : '';
        isset($_POST['signature'])  ? $data['signature']= I('post.signature')       : '';
        if($user['is_auth'] == 1){
            unset($data['name']);
        }

        $r = $model->where(['id' => $user['id']])->data($data)->save();
        if($r >0 || $r === 0){
            $this->apiReturn(1);
        }else{
            $this->apiReturn(0);
        }
    }

    /**
    * 找回密码
    */
    public function password_forget(){
        //频繁请求限制,间隔2秒
        $this->_request_check();

        //必传参数检查
        $this->need_param=array('mobile','smscode','password','password2','sign');
        $this->_need_param();
        $this->_check_sign();

        $cache_name='sms_vcode_'.trim(I('post.mobile')).'_12';
        $smscode=S($cache_name);
        if($smscode['code']!=I('post.smscode')){
            //验证码错误
            $this->apiReturn(43);
        }

        // $check = checkform(I('post.password'),array('string_range',6,20));
        // if( $check == false ){
        //     $this->apiReturn(4,'',1,'密码格式错误，必须是6~20位之间且区分大小写！');
        // } 
        if(I('post.password')!=I('post.password2')) $this->apiReturn(4,'',1,'两次新密码不一致！');
        
        $do=M('user');
        if($rs=$do->where(array('mobile'=>I('post.mobile')))->field('id')->find()){
            if($do->where(array('id'=>$rs['id']))->setField('password',md5(trim(I('post.password')))) !== false ){
                //修改成功
                $this->apiReturn(1);
            }else{
                //修改失败！
                $this->apiReturn(0);
            }
        }else{
            //找不到记录
            $this->apiReturn(45);
        }
    }

    /**
    * 生成二维码
    */
    public function qrcode(){
        //频繁请求限制,间隔2秒
        $this->_request_check();

        //必传参数检查
        $this->need_param=array('text','openid','sign');
        $this->_need_param();
        $this->_check_sign();

        $size       =I('post.size')?I('post.size'):4;   //尺寸
        $errorlevel =I('post.errorlevel')?I('post.errorlevel'):'L';    //容错级别

        $dir='./Apps/Runtime/qrcode';
        if(!is_dir($dir)) @mkdir($dir);
        $img=$this->uid.'_'.md5(implode(',',I('post.'))).'.png';
        $url=C('sub_domain.m').'/Apps/Runtime/qrcode/'.$img;
        $file=$dir.'/'.$img;

        $logo='./Public/images/qrcode_logo.png';

        //直接返回已生成过的二维码
        if(file_exists($file))  $this->apiReturn(1,array('data'=>$url));  


        Vendor('PHPQrcode.phpqrcode');
        \QRcode::png(trim(I('post.text')), $file, $errorlevel, $size);

        //生成带logo二维码        
        if(I('post.is_logo')==1){
            $QR = imagecreatefromstring(file_get_contents($file));
            $logo = imagecreatefromstring(file_get_contents($logo));
            $QR_width = imagesx($QR);
            $QR_height = imagesy($QR);
            $logo_width = imagesx($logo);
            $logo_height = imagesy($logo);
            $logo_qr_width = $QR_width / 5;
            $scale = $logo_width / $logo_qr_width;
            $logo_qr_height = $logo_height / $scale;
            $from_width = ($QR_width - $logo_qr_width) / 2;
            imagecopyresampled($QR, $logo, $from_width, $from_width, 0, 0, $logo_qr_width, $logo_qr_height, $logo_width, $logo_height);
            imagepng($QR, $file);          
        }

        if(file_exists($file)){
            //创建二维码失败
            $this->apiReturn(1,array('data'=>$url));            
        }else{
            //创建二维码失败
            $this->apiReturn(0);
        }

    }



    /**
    * 推荐人信息
    */
    public function ref_user(){
         //频繁请求限制,间隔2秒
        $this->_request_check();

        //必传参数检查
        $this->need_param=array('uid','sign');
        $this->_need_param();
        $this->_check_sign();

        $do=M('user');
		$map['id']		=I('post.uid');
		$map['_logic']	='or';
		$map['nick']	=strtolower(I('post.uid'));
        $rs=$do->where($map)->field('id,nick,name,mobile')->find();
		
		if($rs['id']<5){
			$this->apiReturn(3);
		}
		
        if($rs){
			unset($rs['id']);
            $rs['mobile']=msubstr($rs['mobile'],0,3,'utf-8',false).'*****'.msubstr($rs['mobile'],-3,3,'utf-8',false);
            if($rs['name']) $rs['name']=msubstr($rs['name'],0,1,'utf-8',false).'*'.msubstr($rs['name'],-1,1,'utf-8',false);
            $this->apiReturn(1,array('data'=>$rs));
        }else{
            //找不到用户
            $this->apiReturn(3);
        }
    }

    /**
    * 推荐我的人
    */
    public function up_user(){
         //频繁请求限制,间隔2秒
        $this->_request_check();

        //必传参数检查
        $this->need_param=array('openid','sign');
        $this->_need_param();
        $this->_check_sign();

        $do=M('user');
        $mrs=$do->where(array('id'=>$this->uid))->field('up_uid')->find();
        //无上级推荐人
        if(!$mrs) $this->apiReturn(38);

        $rs=$do->where(array('id'=>$mrs['up_uid']))->field('nick,name,mobile')->find();
        $this->apiReturn(1,array('data'=>$rs));
    }


}