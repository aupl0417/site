<?php
/**
 * ----------------------------------------------------------
 * RestFull API V2.0
 * ----------------------------------------------------------
 * ERP(与ERP对接的接口)
 * ----------------------------------------------------------
 * Author:Lazycat <673090083@qq.com>
 * ----------------------------------------------------------
 * 2017-01-13
 * ----------------------------------------------------------
 */
namespace Rest2\Controller;
use Think\Controller\RestController;
use Think\Exception;

class ErpController extends OrdersController {
    protected $action_logs = array('check_login','account','orders_multi_pay','orders_pay','orders_confirm','register_person','register_company','sub_user','changePass','changeAvatar','delAccount','mobile_recharge_refund,checkErpOrderStatus');

    /**
     * subject: 用户登录
     * api: /Erp/check_login
     * author: Lazycat
     * day: 2017-01-13
     *
     * [字段名,类型,是否必传,说明]
     * param: username,string,1,用户昵称或手机号码
     * param: password,string,1,加密后的密码，不支持明文
     * param: device_id,string,0,设备ID，没有请传session_id
     * param: xg_token,string,1,信鸽token,IOS及android客户端必传
     * param: appid,int,1,appid,IOS及android客户端必传
     */
    public function check_login(){
        $this->check($this->_field('device_id','username,password'));

        $res = $this->_check_login($this->post);
        $this->apiReturn($res);
    }

    public function _check_login($param=null){
        $need_sign	='username,password,parterId';
        $url = strpos($this->post['username'], '-') !== false ? '/customerLogin.json' : '/login.json';
        $res = $this->erpApi($url,$param,$need_sign);        //dump($res);
        $trjAppId = [5 => 1,6 => 2];//5：IOS，6：android
        if($res['id'] == 1001){
            //判断用户是否已入库
            $data    =   [
                'erp_uid'         =>  $res['info']['u_id'],
                'type'            =>  $res['info']['u_type'],
                'nick'            =>  $res['info']['u_nick'],
                'face'            =>  $res['info']['u_logo']?$res['info']['u_logo']:'https://img.trj.cc/FplovbCyAOdbztCfRqP9H02ec9hE',
                'password'        =>  I('post.password'),
                'name'            =>  $res['info']['u_name'],
                'email'           =>  $res['info']['u_email'],
                'mobile'          =>  $res['info']['u_tel'],
                'group_id'        =>  $res['info']['u_groupId'],
                'level_id'        =>  $res['info']['u_level'],
                'status'          =>  $res['info']['u_state'],
                'code'            =>  $res['info']['u_code'],
                //'up_uid'          =>  $res['info']['u_fCode'],
                'is_auth'         =>  $res['info']['u_auth'] ? : '0000',
                //'openid'          =>  $this->create_id(), //防止多出登陆
                'is_un'           =>  $res['info']['u_isUn'] ? : 0,
                'is_bm'           =>  $res['info']['u_isBm'] ? : 0,
                'is_soc'          =>  $res['info']['u_isSoc'] ? : 0,
                'is_bc'           =>  $res['info']['u_isBc'] ? : 0,
                'is_ledt'         =>  $res['info']['u_isLedt'] ? : 0,
                'is_rest_username'=>  $res['info']['u_resetUsername'] ? : 0,
                'is_quit'         =>  $res['info']['u_isQuit'] ? : 0,
                'is_virtual'      =>  $res['info']['u_isVirtual'] ? : 0,
                'un_time'         =>  $res['info']['u_unTime'] ? : '1970-01-01',
                'bm_time'         =>  $res['info']['u_bmTime'] ? : '1970-01-01',
                'soc_time'        =>  $res['info']['u_socTime'] ? : '1970-01-01',
                'ledt_time'       =>  $res['info']['u_ledtTime'] ? : '1970-01-01',
                'rest_username_time' => $res['info']['u_resetUsernameTime'] ? : '1970-01-01',
                'quit_time'       =>  $res['info']['u_quitTime'] ? : '1970-01-01',
            ];
            if($res['info']['u_fax']) $data['fax'] = $res['info']['u_fax'];
            //
            $user = M('user')->where(['erp_uid' => $res['info']['u_id'] ])->field('status,id,openid,loginum,shop_type,shop_id')->find();
            if($user){
                if($user['status'] != 1){
                    $reason = M('prohibit_user')->where(['uid' => $user['id']])->order("atime desc")->getField('reason');
                    $reason = $reason ? $reason : '账号已被暂停使用！';
                    return ['code' => 0,'msg' => $reason];
                }
                $data['last_login_time']    = date('Y-m-d H:i:s');
                $data['ip']				    = get_client_ip();
                $data['loginum']		    = $user['loginum']+1;


                M('user')->where(['id' => $user['id']])->save($data);
                $data['level_name'] =   $res['info']['u_level_text'];
                $data = array_merge($data,$user);

                //用于APP保存登录状态（默认为7天）
                if($this->token['data']['device_id']) S('app_logined_'.$user['openid'], ['device_id' => $this->token['data']['device_id']], 86400 * 7);
                if (array_key_exists($this->post['appid'], $trjAppId) && !empty($this->post['xg_token'])) {    //如果客户端为IOS或者android时则记录用户的device——token
                    if (M('user_device')->where(['uid' => $user['id']])->find()) {
                        if (M('user_device')->where(['uid' => $user['id']])->save(['token' => $this->post['xg_token'], 'status' => 1, 'appid' => $this->post['appid'], 'type' => $trjAppId[$this->post['appid']]]) === false) return ['code' => 0, 'msg' => '更新token失败！'];
                    } else {
                        if (M('user_device')->add(['uid' => $user['id'], 'token' => $this->post['xg_token'], 'status' => 1, 'appid' => $this->post['appid'], 'type' => $trjAppId[$this->post['appid']]]) == false) return ['code' => 0, 'msg' => '记录token失败！'];
                    }
                }
                return ['code' => 1,'data' => $data];
            }else{
                $data['openid']		= $this->create_id();
                $data['ip']			= get_client_ip();
                if($data['id'] = M('user')->add($data)){
                    $data['level_name'] = $res['info']['u_level_text'];

                    //用于APP保存登录状态（默认为7天）
                    if($this->token['data']['device_id']) S('app_logined_'.$data['openid'], ['device_id' => $this->token['data']['device_id']], 86400 * 7);
                    if (array_key_exists($this->post['appid'], $trjAppId) && !empty($this->post['xg_token'])) {    //如果客户端为IOS或者android时则记录用户的device——token
                        if (M('user_device')->add(['token' => $this->post['xg_token'], 'status' => 1, 'uid' => $data['id'], 'appid' => $this->post['appid'], 'type' => $trjAppId[$this->post['appid']]]) == false) return ['code' => 0,'msg' => '记录token失败！'];
                    }

                    return ['code' => 1,'data' => $data];
                }else{
                    return ['code' => 0,'msg' => '登录失败！'];
                }
            }
        }else{
            //登录失败
            return ['code' => $res['code'],'msg' => $res['info']];
        }
    }

    /**
     * subject: 获取账户信息
     * api: /Erp/account
     * author: Lazycat
     * day: 2017-01-14
     * content: erp_uid 和 openid必须填一个
     *
     * [字段名,类型,是否必传,说明]
     * param: erp_uid,string,0,在ERP中的用户UID
     * param: openid,string,0,用户openid
     */

    public function account(){
        $field = 'openid,sign';
        if(in_array('erp_uid',array_keys(I('post.')))) $field = 'erp_uid,sign';
        $this->check($field,false);

        $erp_uid = $this->post['erp_uid'] ? $this->post['erp_uid'] : $this->user['erp_uid'];

        $res = $this->_account(['userID' => $erp_uid]);
        $this->apiReturn($res);
    }

    public function _account($param=null){
        $need_sign='userID,parterId';
        $res=$this->erpApi('/account.json',$param,$need_sign);
        if($res['code'] == 1){
            return ['code' => 1,'data' => $res['info']];
        }else{
            return ['code' => 0,'msg' => $res['msg']];
        }
    }


    /**
     * subject: 获取用户信息
     * api: /Erp/user_info
     * author: Lazycat
     * day: 2017-01-16
     * content: erp_uid 和 openid必须填一个
     *
     * [字段名,类型,是否必传,说明]
     * param: erp_uid,string,0,在ERP中的用户UID
     * param: openid,string,0,用户openid
     */
    public function user_info(){
        $field = 'openid,sign';
        if(in_array('erp_uid',array_keys(I('post.')))) $field = 'erp_uid,sign';
        $this->check($field);

        $erp_uid = $this->post['erp_uid'] ? $this->post['erp_uid'] : $this->user['erp_uid'];

        $res = $this->_user_info(['userID' => $erp_uid]);
        $this->apiReturn($res);
    }

    public function _user_info($param=null){
        $need_sign='userID,parterId';
        //$res=$this->erpApi('/getUserInfo.json',$param,$need_sign);
        $res=$this->erpApi('/getUserInfoByUserId.json',$param,$need_sign);
        if($res['code'] == 1){
            return ['code' => 1,'data' => $res['info']];
        }else{
            return ['code' => 0,'data'=>$res,'msg' => $res['info']];
        }
    }
	
    /**
     * subject: 发送验证码
     * api: /Erp/smscode
     * author: Lazycat
     * day: 2017-01-19
     *
     * [字段名,类型,是否必传,说明]
     * param: mobile,string,1,手机号码
     */
    public function smscode(){
        $this->check('mobile');
        if(!checkform($this->post['mobile'],'is_mobile')) $this->apiReturn(['code' => 0,'msg' => '手机号码格式错误！']);

        $res = $this->_smscode($this->post);
        $this->apiReturn($res);
    }

    public function _smscode($param){
        $need_sign='mobile';
		
        $res=$this->erpApi('/smsCode.json',$param,$need_sign);
        if($res['code'] == 1){
            return ['code' => 1,'data' => $res['info']];
        }else{
            return ['code' => 0,'msg' => $res['info']];
        }
    }

    /**
     * subject: 组织机构类型
     * api: /Erp/stru_type
     * author: Lazycat
     * day: 2017-01-19
     *
     * [字段名,类型,是否必传,说明]
     */
    public function stru_type(){
        $this->check('',false);

        $res = $this->_stru_type($this->post);
        $this->apiReturn($res);
    }
    public function _stru_type($param){
        $need_sign='parterId';
        $res=$this->erpApi('/getOrganize.json',[],$need_sign);
        if($res['code'] == 1){
            return ['code' => 1,'data' => $res['info']];
        }else{
            return ['code' => 0,'msg' => $res['info']];
        }
    }

    /**
     * subject: 检查手机号码是否已被使用
     * api: /Erp/check_mobile
     * author: Lazycat
     * day: 2017-01-19
     *
     * [字段名,类型,是否必传,说明]
     * param: mobile,string,1,手机号码
     */
    public function check_mobile(){
        $this->check('mobile',false);

        $res = $this->_check_mobile($this->post);
        $this->apiReturn($res);
    }

    public function _check_mobile($param){
        if(!checkform($param['mobile'],'is_mobile')) return ['code' => 0,'msg' => '手机号码格式错误！'];

        $need_sign='mobile,parterId';
        $res=$this->erpApi('/checkMobile.json',$param,$need_sign);
        if($res['code'] == 1){
            return ['code' => 1,'msg' => '手机号码可用！'];
        }else{
            return ['code' => 0,'msg' => '手机号码已被使用！'];
        }
    }
    /**
     * subject: 检查用户昵称和手机号码是否匹配
     * api: /Erp/CheckUserMobile
     * author: lizuheng
     * day: 2017-03-16
     *
     * [字段名,类型,是否必传,说明]
     * param: username,string,1,用户昵称
     * param: mobile,int,1,手机号码
     */
    public function CheckUserMobile(){
        $this->check('username,mobile',false);

        $res = $this->_CheckUserMobile($this->post);
        $this->apiReturn($res);
    }

    public function _CheckUserMobile($param){
        $need_sign='mobile,username,parterId';
        $res=$this->erpApi('/CheckUserMobile.json',$param,$need_sign);

        if($res['code'] == 1){
            return ['code' => 1,'data' => $res['info']];
        }else{
            return ['code' => 0,'msg' => $res['info']];
        }
    }
    /**
     * subject: 检查用户昵称和推荐人
     * api: /Erp/checkUserAndReferrer
     * author: lizuheng
     * day: 2017-01-19
     *
     * [字段名,类型,是否必传,说明]
     * param: username,string,1,用户昵称
     * param: referrer,string,1,推荐人
     */
    public function checkUserAndReferrer(){
        $this->check('username,referrer',false);

        $res = $this->_checkUserAndReferrer($this->post);
        $this->apiReturn($res);
    }

    public function _checkUserAndReferrer($param){
        $need_sign='referrer,username,parterId';
        $res=$this->erpApi('/checkUserAndReferrer.json',$param,$need_sign);

        if($res['code'] == 1){
            return ['code' => 1,'data' => $res['info']];
        }else{
            return ['code' => 0,'msg' => $res['info']];
        }
    }
    /**
     * subject: 检查用户昵称
     * api: /Erp/check_username
     * author: Lazycat
     * day: 2017-01-19
     *
     * [字段名,类型,是否必传,说明]
     * param: username,string,1,用户昵称或推荐码
     */
    public function check_username(){
        $this->check('username',false);

        $res = $this->_check_username($this->post);
        $this->apiReturn($res);
    }
	
    public function _check_username($param){
        // 用户名不能以 数字 _  开头
        if(!preg_match("/^([{\x{4e00}-\x{9fa5}]|[a-zA-Z])+([{\x{4e00}-\x{9fa5}]|[0-9a-zA-Z\_])*([{\x{4e00}-\x{9fa5}]|[0-9a-zA-Z])+$/u",$param['username'])){
            return ['code' => 0,'msg' => '用户名必须是6~20位之间的中文/字母/数字/下划线组合,不能以_或数字开头,不能以_结束'];
        }

        // 用户名不合法
        if(preg_match('/乐兑|dttx|客服|管理员|系统管理员|全返|赠送|大唐|dt|大堂|云联惠|云联|yunlianhui|yunlian|乐兑|云连惠|云连会|云支付|云加速|云数据|芸联惠|芸连惠|芸连会|芸联会|云联汇|云连汇|芸联汇|芸连汇|匀连惠|匀联惠|匀联汇|老战士|云转回|匀加速|零购|云回转|成谋商城|脉单|众智云|麦点|秀吧|一点公益|商城联盟/',$param['username'])){
            return ['code' => 0,'msg' => '用户名不合法,请更换'];
        }

        $need_sign='code,parterId';
        $res=$this->erpApi('/codeUserInfo.json',['code' => $param['username']],$need_sign);
        //dump($res);
        if($res['code'] == 1){
            return ['code' => 0,'msg' => '用户名已存在！'];
        }else{
            return ['code' => 1,'msg' => '用户名可用！'];
        }
    }

    /**
     * subject: 获取推荐人资料
     * api: /Erp/ref_user
     * author: Lazycat
     * day: 2017-01-19
     * content: username、code两项必填一项
     *
     * [字段名,类型,是否必传,说明]
     * param: username,string,1,用户昵称
     * param: code,string,0,用户推荐码
     */
    public function ref_user(){
        $field = 'username';
        if(in_array('code',array_keys($this->post))) $field = 'code';
        $this->check($field,false);

        $data['code'] = $this->post['username'] ? $this->post['username'] : $this->post['code'];

        $res = $this->_ref_user($data);
        $this->apiReturn($res);
    }

    public function _ref_user($param){
        $need_sign='code,parterId';
        $res=$this->erpApi('/codeUserInfo.json',$param,$need_sign);
        if($res['code'] == 1){
            return ['code' => 1,'data' => $res['info']];
        }else{
            return ['code' => 3];
        }
    }

    /**
     * subject: 个人注册
     * api: /Erp/register_person
     * author: Lazycat
     * day: 2017-01-20
     *
     * [字段名,类型,是否必传,说明]
     * param: username,string,1,用户昵称
     * param: password,string,1,密码
     * param: mobile,string,1,手机号码
     * param: smscode,string,1,短信验证码
     * param: ref,string,0,推荐人
     */
    public function register_person(){
        $this->check('username,password,mobile,smscode');

        $res = $this->_register_person($this->post);
        $this->apiReturn($res);
    }

    public function _register_person($param){
        //验证用户是否可用和验证推荐人
        $param['ref']   = C('cfg.site')['ref_code'];
        if($param['ref']){
			$check = $this->_checkUserAndReferrer(['username' => $param['username'],'referrer'=>$param['ref']]);
            if($check['code'] != 1) return $check;
        }

        $data=[
            'username'		=> $param['username'],
            'mobile'		=> $param['mobile'],
            'smsCode'		=> $param['smscode'],
            'referrer'		=> $param['ref'],
            'password'		=> $param['password'],
            'country'		=> $param['country'] ? $param['country'] : 37,  //默认37为中国
        ];

        $need_sign  = 'username,mobile,smsCode,referrer,password,country,parterId';
        $res        = $this->erpApi('/regMember.json',$data,$need_sign);

        if($res['code'] == 1){
            $data=[
                'openid'			=> $this->create_id(),
                'erp_uid'			=> $res['info']['u_id'],
                'nick'				=> $res['info']['u_nick'],
                'face'				=> $res['info']['u_logo'],
                'password'			=> $res['info']['u_loginPwd'],
                'password_pay'		=> $res['info']['u_payPwd'],
                'name'				=> $res['info']['u_name'],
                'mobile'			=> $res['info']['u_tel'],
                'level_id'			=> $res['info']['u_level'],
                'birthday'			=> $res['info']['u_birth'],
                'sex'				=> $res['info']['u_sex'],
                'ip'				=> get_client_ip(),
                'atime'				=> date('Y-m-d H:i:s')
            ];
            if($data['id'] = M('user')->add($data)){
				
				//发送消息
				$msg_data = ['tpl_tag'=>'register_success','uid'=>$data['id']];
				tag('send_msg',$msg_data);
				
                return ['code' => 1,'data' => $data];
            }else{
                return ['code' => 0,'msg' => '注册失败！'];
            }
        }else{
            return ['code' => 0,'msg' => $res['info']];
        }
    }


    /**
     * subject: 企业注册
     * api: /Erp/register_company
     * author: Lazycat
     * day: 2017-01-20
     *
     * [字段名,类型,是否必传,说明]
     * param: username,string,1,用户昵称
     * param: password,string,1,密码
     * param: mobile,string,1,手机号码
     * param: smscode,string,1,短信验证码
     * param: ref,string,0,推荐人
     * param: organize,int,1,组织机构类型
     * param: company,string,1,企业名称
     * param: company_license,string,1,营业执照号码
     */
    public function register_company(){
        $this->check('username,password,mobile,smscode,organize,company,company_license');
        $res = $this->_register_company($this->post);
        $this->apiReturn($res);
    }

    public function _register_company($param){
        //验证用户是否可用和验证推荐人
        $param['ref']   = C('cfg.site')['ref_code'];
        if($param['ref']){
            $check = $this->_checkUserAndReferrer(['username' => $param['username'],'referrer'=>$param['ref']]);
            if($check['code'] != 1) return $check;
        }

        $data=[
            'organize'			=> $param['organize'],
            'companyname'		=> $param['company'],
            'companylicense'	=> $param['company_license'],
            'mobile'			=> $param['mobile'],
            'smsCode'			=> $param['smscode'],
            'referrer'			=> $param['ref'],
            'username'			=> $param['username'],
            'password'			=> $param['password'],
        ];
        $need_sign  = 'organize,companyname,companylicense,mobile,smsCode,referrer,username,password,parterId';
        $res        = $this->erpApi('/regCompany.json',$data,$need_sign);
        if($res['code'] == 1){
            $data=[
                'openid'			=> $this->create_id(),
                'erp_uid'			=> $res['info']['u_id'],
                'nick'				=> $res['info']['u_nick'],
                'face'				=> $res['info']['u_logo'],
                'password'			=> $res['info']['u_loginPwd'],
                'password_pay'		=> $res['info']['u_payPwd'],
                'name'				=> $res['info']['u_name'],
                'mobile'			=> $res['info']['u_tel'],
                'level_id'			=> $res['info']['u_level'],
                'birthday'			=> $res['info']['u_birth'],
                'sex'				=> $res['info']['u_sex'],
                'ip'				=> get_client_ip(),
                'atime'				=> date('Y-m-d H:i:s')
            ];
            if($data['id'] = M('user')->add($data)){
				
				//发送消息
				$msg_data = ['tpl_tag'=>'register_success','uid'=>$data['id']];
				tag('send_msg',$msg_data);
				
                return ['code' => 1,'data' => $data];
            }else{
                return ['code' => 0,'msg' => '注册失败！'];
            }
        }else{
            return ['code' => 0,'msg' => $res['info']];
        }
    }


    /**
     * subject: 新企业注册
     * api: /Erp/register_company_step
     * author: Lizuheng
     * day: 2017-03-18
     *
     * [字段名,类型,是否必传,说明]
     * param: username,string,1,用户昵称
     * param: password,string,1,密码
     * param: mobile,string,1,手机号码
     * param: ref,string,0,推荐人(默认乐兑)
     * param: organize,int,1,组织机构类型
     * param: companyname,string,1,企业名称
     * param: company_license,string,1,营业执照号码
     */
    public function register_company_step(){
        $this->check('username,password,mobile,organize,companyname,company_license');
        $res = $this->_register_company_step($this->post);
        $this->apiReturn($res);
    }

    public function _register_company_step($param){
        //验证用户是否可用和验证推荐人
        $param['ref']   = C('cfg.site')['ref_code'];
        if($param['ref']){
            $check = $this->_checkUserAndReferrer(['username' => $param['username'],'referrer'=>$param['ref']]);
            if($check['code'] != 1) return $check;
        }

        $data=[
            'organize'			=> $param['organize'],
            'companyname'		=> $param['companyname'],
            'companylicense'	=> $param['company_license'],
            'mobile'			=> $param['mobile'],
            'referrer'			=> $param['ref'],
            'username'			=> $param['username'],
            'password'			=> $param['password'],
        ];
        $need_sign  = 'organize,companyname,companylicense,mobile,referrer,username,password,parterId';
        $res        = $this->erpApi('/regCompanyTrjApp.json',$data,$need_sign);

        if($res['code'] == 1){
            $data=[
                'openid'			=> $this->create_id(),
                'erp_uid'			=> $res['info']['u_id'],
                'nick'				=> $res['info']['u_nick'],
                'face'				=> $res['info']['u_logo'],
                'password'			=> $res['info']['u_loginPwd'],
                'password_pay'		=> $res['info']['u_payPwd'],
                'name'				=> $res['info']['u_name'],
                'mobile'			=> $res['info']['u_tel'],
                'level_id'			=> $res['info']['u_level'],
                'birthday'			=> $res['info']['u_birth'],
                'sex'				=> $res['info']['u_sex'],
                'ip'				=> get_client_ip(),
                'atime'				=> date('Y-m-d H:i:s')
            ];
            if($data['id'] = M('user')->add($data)){
				
				//发送消息
				$msg_data = ['tpl_tag'=>'register_success','uid'=>$data['id']];
				tag('send_msg',$msg_data);
				
                return ['code' => 1,'data' => $data];
            }else{
                return ['code' => 0,'msg' => '注册失败！'];
            }
        }else{
            return ['code' => 0,'msg' => $res['info']];
        }
    }

    /**
     * ERP统一请求方法
     * @param string $apiurl 要请求的接口
     * @param array	$data 	要请求的数据
     * @param string $need_sign 签名字段
     * @param string $no_sign 不参加签名字段
     * @param string $sign_apiurl  特殊接口，需要保留sign参数
     */
    public function erpApi($apiurl,$data,$need_sign='',$no_sign='',$sign_apiurl=''){
        G('start');
        $data=$this->erp_sign($data,$need_sign,$no_sign,$sign_apiurl);
        //dump($data);
        if (strpos($apiurl, 'http') === false) $apiurl=C('cfg.erp')['apiurl'].$apiurl;
        $res=$this->curl_post($apiurl,$data);
        //dump($apiurl);
        //dump($res);
        $res=json_decode($res,true);

        G('over');

        //在此记录日志，方便接口错误调试
        if(C('API_LOG')){
            $logs['atime']	=date('Y-m-d H:i:s');
            $logs['ip']		=get_client_ip();
            $logs['dotime']	=G('start','over');
            $logs['nick']	=$this->user['nick'];
            $logs['apiurl']	=$apiurl;
            $logs['url']	=($_SERVER['HTTPS']=='on'?'https://':'http://').$_SERVER['HTTP_HOST'].__SELF__;
            $logs['data']	=@var_export($data,true);
            $logs['res']	=@var_export($res,true);
            log_add('erp_v2_'.date('Ym'),$logs);
        }
        if($res['id'] == 1001 || $res['code'] == 1001) $res['code'] = 1;
        else $res['code'] = 0;
        return $res;
    }

    /**
     * ERP数据签名
     * @param array	$data 		要请求的数据
     * @param string $need_sign 	签名字段
     * @param string $no_sign 	不参加签名字段
     * @param string $sign_apiurl  特殊接口，需要保留sign参数
     */
    public function erp_sign($data,$need_sign='',$no_sign='',$sign_apiurl=''){
        $data['parterId'] = C('cfg.erp')['pid'];

        //针对addOrder.json接口
        if(isset($data['appKey'])) unset($data['parterId']);

        //清除不相关的数据
        foreach($this->apps_cfg as $key => $val){
            if(isset($data[$key])) unset($data[$key]);
        }
        if(isset($data['sign']) && $sign_apiurl == '') unset($data['sign']);

        $arr=$data;
        if($need_sign){ //必签字段
            if(!is_array($need_sign)) $need_sign = explode(',',$need_sign);
            foreach($arr as $key => $val){
                if(!in_array($key, $need_sign)) unset($arr[$key]);
            }
        }elseif($no_sign){	 //过滤不参加签名的字段
            if(!is_array($no_sign)) $no_sign = explode(',', $no_sign);
            foreach($no_sign as $val){
                if(isset($arr[$val])) unset($arr[$val]);
            }
        }

        ksort($arr);
        $data['signValue'] = md5(http_build_query($arr).'&'.C('cfg.erp')['sign']);

        return $data;
    }
	
	/**
     * subject: 修改登录密码
     * api: /Erp/change_password
     * author: Lizuheng
     * day: 2017-02-11
     *
     * [字段名,类型,是否必传,说明]
     * param: openid,string,0,用户openid
	 * param: opassword,string,1,旧密码
     * param: password,string,1,新密码
	 * param: mobile,string,1,手机号码
     * param: smscode,string,1,短信验证码
     */
	public function change_password(){
		$field = 'opassword,password,mobile,smscode,openid,sign';
        $this->check($field);

		$this->post['erp_uid'] = $this->user['erp_uid'];
        $res = $this->_change_password($this->post);
        $this->apiReturn($res);		   	   
	} 
	
	public function _change_password($param){
		$data=[
			'opassword'	=> $param['opassword'],
			'password'	=> $param['password'],
			'mobile'	=> $param['mobile'],
			'smsCode'	=> $param['smscode'],
			'userID'	=> $param['erp_uid'],
		];

		$need_sign='opassword,password,userID,parterId,mobile,smsCode';
		$res=$this->erpApi('/modifyPwd.json',$data,$need_sign);
		//dump($res);
		if($res['code'] == 1){
			return ['code' => 1,'data' => $res['info']];
		}else{
			return ['code' => 0,'msg' => $res['info']];
		}		   
	}

	/**
     * subject: 设置安全密码
     * api: /Erp/set_pay_password
     * author: Lizuheng
     * day: 2017-02-11
     *
     * [字段名,类型,是否必传,说明]
     * param: openid,string,0,用户openid
     * param: password,string,1,安全密码
	 * param: mobile,string,1,手机号码
     * param: smscode,string,1,短信验证码
     */
	public function set_pay_password(){
		$field = 'password,mobile,smscode,openid,sign';
		
        $this->check($field);

		$this->post['erp_uid'] = $this->user['erp_uid'];
        $res = $this->_set_pay_password($this->post);
        $this->apiReturn($res);		   	   
	} 
	
	public function _set_pay_password($param){
		$data=[
			'payPwd'	=> $param['password'],
			'mobile'	=> $param['mobile'],
			'smsCode'	=> $param['smscode'],
			'userID'	=> $param['erp_uid'],
		];

		$need_sign='userID,payPwd,mobile,smsCode,parterId';
		$res=$this->erpApi('/safePwd.json',$data,$need_sign);
		//dump($res);
		if($res['code'] == 1){
			return ['code' => 1,'data' => $res['info']];
		}else{
			return ['code' => 0,'msg' => $res['info']];
		}		   
	}
	
	/**
     * subject: 找回安全密码
     * api: /Erp/forgot_pay_password
     * author: Lizuheng
     * day: 2017-03-16
     *
     * [字段名,类型,是否必传,说明]
     * param: openid,string,1,用户openid
     * param: password,string,1,新安全密码
	 * param: mobile,string,1,手机号码
     * param: smscode,string,1,短信验证码
     */
	public function forgot_pay_password(){
		$field = 'password,mobile,smscode,openid,sign';
		
        $this->check($field);

		$this->post['erp_uid'] = $this->user['erp_uid'];
        $res = $this->_forgot_pay_password($this->post);
        $this->apiReturn($res);		   
	}
	
	public function _forgot_pay_password($param){
		$data=[
			'newPayPwd'			=> $param['password'],
			'mobile'			=> $param['mobile'],
			'smsCode'			=> $param['smscode'],
			'userID'			=> $param['erp_uid'],
		];
	
		$need_sign='userID,newPayPwd,mobile,smsCode,parterId';
		$res=$this->erpApi('/findPayPwd.json',$data,$need_sign);
		//dump($res);
		if($res['code'] == 1){
			return ['code' => 1,'data' => $res['info']];
		}else{
			return ['code' => 0,'msg' => $res['info']];
		}		   
	}

	/**
     * subject: 修改安全密码
     * api: /Erp/change_pay_password
     * author: Lizuheng
     * day: 2017-02-11
     *
     * [字段名,类型,是否必传,说明]
     * param: openid,string,0,用户openid
	 * param: opassword,string,1,旧安全密码
     * param: password,string,1,新安全密码
	 * param: mobile,string,1,手机号码
     * param: smscode,string,1,短信验证码
     */
	public function change_pay_password(){
		$field = 'opassword,password,mobile,smscode,openid,sign';
		
        $this->check($field);

		$this->post['erp_uid'] = $this->user['erp_uid'];
        $res = $this->_change_pay_password($this->post);
        $this->apiReturn($res);		   
	}
	
	public function _change_pay_password($param){
		$data=[
			'oldPayPwd'			=> $param['opassword'],
			'payPwd'			=> $param['password'],
			'mobile'			=> $param['mobile'],
			'smsCode'			=> $param['smscode'],
			'userID'			=> $param['erp_uid'],
		];
	
		$need_sign='userID,oldPayPwd,payPwd,mobile,smsCode,parterId';
		$res=$this->erpApi('/modifySafePwd.json',$data,$need_sign);
		//dump($res);
		if($res['code'] == 1){
			return ['code' => 1,'data' => $res['info']];
		}else{
			return ['code' => 0,'msg' => $res['info']];
		}		   
	}
	
    /**
     * subject: 找回密码步骤一：发送短信验证码
     * api: /Erp/forgot_password_step1
     * author: lizuheng
     * day: 2017-02-11
     *
     * [字段名,类型,是否必传,说明]
     * param: username,string,1,用户昵称
     * param: mobile,string,1,手机号码
     * param: smscode,string,1,短信验证码
     */
	public function forgot_password_step1(){
		$field = 'username,mobile,smscode,sign';		
        $this->check($field);

        $res = $this->_forgot_password_step1($this->post);
        $this->apiReturn($res);			   
	}
	public function _forgot_password_step1($param){
		$data=[
			'nick'			=> $param['username'],
			'mobile'		=> $param['mobile'],
			'smsCode'		=> $param['smscode'],
		];
		
		$need_sign='nick,mobile,smsCode,parterId';
		$res=$this->erpApi('/findPwd.json',$data,$need_sign);
		//dump($res);
		if($res['code'] == 1){
			return ['code' => 1,'data' => $res['info']];
		}else{
			return ['code' => 0,'msg' => $res['info']];
		}		   
	}


    /**
     * 检查安全密码是否正常
     * @param string $param['pay_password'] 加密码后的安全密码
     */
    public function _check_safe_password($param){
        $data['userID']		= $this->user['erp_uid'];
        $data['safePwd']	= $param['pay_password'];

        $need_sign  = 'userID,safePwd,parterId';
        $res        = $this->erpApi('/checkSafePwd.json',$data,$need_sign);

        return $res;
    }
	
    /**
     * subject: 找回密码步骤二：重置密码
     * api: /Erp/forgot_password_step2
     * author: lizuheng
     * day: 2017-02-11
     *
     * [字段名,类型,是否必传,说明]
     * param: erp_uid,string,1,第一次成功之后返回结果userID值
     * param: signcode,string,1,第一次成功之后返回结果的sign值
     * param: password,string,1,新密码
     */
	public function forgot_password_step2(){
		$field = 'erp_uid,password,signcode,sign';		
        $this->check($field);

        $res = $this->_forgot_password_step2($this->post);
        $this->apiReturn($res);			   
	}
	public function _forgot_password_step2($param){
		$data=[
			'userID'	=> $param['erp_uid'],
			'newPwd'	=> $param['password'],
			'sign'		=> $param['signcode'],
		];

		$need_sign='userID,newPwd,sign,parterId';
		$res=$this->erpApi('/findPwd2.json',$data,$need_sign,'',1);
		//dump($res);
		if($res['code'] == 1){
			return ['code' => 1,'data' => $res['info']];
		}else{
			return ['code' => 0,'msg' => $res['info']];
		}		   
	}


    /**
     * subject: 合并订单付款
     * api: /Erp/orders_multi_pay
     * author: Lazycat
     * day: 2017-02-18
     *
     * [字段名,类型,是否必传,说明]
     * param: openid,string,1,用户openid
     * param: o_no,string,1,合并订单号
     * param: pay_password,string,1,安全密码，加密过的
     * param: paytype,int,1,支付方式
     */
    public function orders_multi_pay(){
        $this->check('openid,o_no,pay_password,paytype');

        if(C('cfg.site')['is_pay'] !=1) $this->apiReturn(['code' => 0,'msg' => C('cfg.site')['is_pay_tips']]);

        $tmp = $this->_check_pay_password($this->post['pay_password']);
        if($tmp['code'] != 1) $this->apiReturn($tmp);

        //获取子订单
        $list = M('orders_shop')->where(['o_no' => $this->post['o_no'],'uid' => $this->user['id']])->field('id,status,s_no')->select();
        if(empty($list)) $this->apiReturn(['code' => 0,'msg' => '订单不存在！']);

        //检查子订单是否全部为未支付状态
        $num = 0;
        foreach($list as $val){
            if($val['status'] == 1) $num++;
        }

        if($num != count($list)) $this->apiReturn(['code' => 0,'msg' => '支付失败！子订单存在异常（可能部分子订单已支付或已关闭）!']);

        $res['count']   = count($list);
        $res['ok']      = 0;
        $res['o_no']    = $this->post['o_no'];
        foreach($list as $val){
            $tmp                = $this->_orders_pay(['paytype' => $this->post['paytype'],'s_no' => $val['s_no']]);
            if($tmp['code'] == 1) $res['ok']++;
            $res['orders'][]    = $tmp;
        }

        if($res['ok'] > 0){ //只要有子订单支付成功就更新状态
            M('orders')->where(['o_no' => $this->post['o_no'],'uid' => $this->user['id']])->save(['status' => 2,'pay_time' => date('Y-m-d H:i:s'),'pay_type' => $this->post['paytype']]);
        }

        if($res['ok'] == $res['count']) {
            $this->apiReturn(['code' => 1,'msg' => '支付成功','data' => $res]);
        }
        $this->apiReturn(['code' => 2,'msg' => '存在部分子订单支付失败！','data' => $res]);

    }

    /**
     * subject: 单一订单付款
     * api: /Erp/orders_pay
     * author: Lazycat
     * day: 2017-02-21
     *
     * [字段名,类型,是否必传,说明]
     * param: openid,string,1,用户openid
     * param: s_no,string,1,订单号
     * param: pay_password,string,1,安全密码，加密过的
     * param: paytype,int,1,支付方式
     */
    public function orders_pay(){
        $this->check('openid,s_no,pay_password,paytype');

        if(C('cfg.site')['is_pay'] !=1) $this->apiReturn(['code' => 0,'msg' => C('cfg.site')['is_pay_tips']]);

        $tmp = $this->_check_pay_password($this->post['pay_password']);
        if($tmp['code'] != 1) $this->apiReturn($tmp);

        $res = $this->_orders_pay(['paytype' => $this->post['paytype'],'s_no' => $this->post['s_no']]);

        $this->apiReturn($res);
    }


    /**
     * 单个订单支付
     * Create by Lazycat
     * 2017-02-18
     * @param $param['paytype'] 支付方式
     * @param $param['s_no']    订单号
     * @return array
     */
    public function _orders_pay($param){
        //进入支付流程，1分钟内不可以修改价格或使用其它浏览器或APP进行支付
        $res = A('Rest2/Orders')->_paying(['s_no' => $param['s_no']]);
        if($res['code'] != 1) return $res;

        //检查订单
        $res = $this->_orders_pay_check(['s_no' => $param['s_no'],'is_sms' => 1]);
        if($res['code'] != 1) return $res;

        $ors    = $res['data']['orders'];
        $shop   = $res['data']['shop'];

        $coupon = $this->_coupon_data_format($ors['coupon_id']);
        if($coupon) {
            if($param['paytype'] == 2) return ['code' => 0,'msg' => '该订单使用了乐兑官方优惠券不支持唐宝支付！'];

            //提交至ERP
            $tmp = $this->_put_coupon(['coupon' => $coupon]);
            if($tmp['code'] != 1) return $tmp;
        }

        $data=[
            'ip'				=> get_client_ip(),
            'appKey'			=> C('cfg.erp')['pid'],
            'outTradeNo'		=> $ors['s_no'],
            'timeoutExpress'	=> date('Y-m-d H:i:s',time()+86400*3),
            'outCreateTime'		=> $ors['atime'],
            'sellerID'			=> $shop['erp_uid'],
            'sellerNick'		=> $shop['nick'],
            'buyID'				=> $this->user['erp_uid'],
            'buyNick'			=> $this->user['nick'],
            'totalMoney'		=> $ors['pay_price'],
            'subject'			=> '订购商品，订单号：'.$ors['s_no'],
            'body'				=> $this->user['nick'].'，订购'.$ors['goods_num'].'件商品，合计'.$ors['pay_price'].'元',
            'payType'			=> $param['paytype'],
            'showUrl'			=> 'http://',    //显示
            'returnUrl'			=> 'http://',  //同步
            'notifyUrl'			=> DM('rest', '/notice/run'),  //异步
            'dealType'			=> $ors['inventory_type']==1 ? 1 : 2,   //分账模式
            'totalScore'		=> $ors['score'],
            'isPurchase'        => 0, //是否代购
            'purchaseMoney'     => 0, //代购手续费
            //'payAccountCode'    => 'TRJ_SHOPPING',  //专用通道标识
        ];

        //var_dump($data);

        $need_sign='ip,appKey,outTradeNo,timeoutExpress,outCreateTime,sellerID,sellerNick,buyID,buyNick,totalMoney,subject,body,payType,showUrl,returnUrl,notifyUrl,totalScore,dealType,isPurchase,purchaseMoney,coupon';
        $ret = $this->erpApi('/addOrder.json',$data,$need_sign);

        //print_r($ret);
        //$ret['code'] = 1;

        if($ret['code'] == 1){  //更新订单状态
            $tmp = $this->_update_pay_ok($param['paytype'],$ors,$shop);
            if($coupon) $this->_use_coupon(['coupon' => $coupon]);
            if($tmp['code'] == 1) {
                return ['code' => 1,'msg' => '支付成功！','data' => $res['data']];
            }else{
                return ['code' => 0,'msg' => $tmp['msg'],'data' => $res['data']];
            }
        }

        return ['code' => 0,'msg' => '支付失败！'.$ret['info'],'data' => $res['data']];
    }


    /**
     * 此接口用于话费、流量充值支付
     * Create by lazycat
     * 2017-05-10
     * @param $param['m_no'] string 订单号
     */
    public function _mobile_orders_pay($param){
        $rs = M('mobile_orders')->where(['uid' => $this->user['id'],'m_no' => $param['m_no']])->field('id,m_no,status,pay_price')->find();
        if($rs['status'] != 1) return ['code' => 0,'msg' => '错误的订单状态！'];


    }


    /**
     * 格式化优惠券,用于支付接口
     * Create by lazycat
     * 2017-04-25
     */
    public function _coupon_data_format($ids){
        if(empty($ids)) return '';
        $list = M('coupon')->where(['id' => ['in',$ids],'status' => 1,'type' => 2])->field('price,short_code,orders_no')->select();

        if($list){
            $coupon = [];
            foreach($list as $val){
                $coupon[] = [
                    'shopOrderID'   => $val['orders_no'],
                    'couponID'      => $val['short_code'],
                    'money'         => $val['price'],
                ];
            }
            return json_encode($coupon);
        }

        return '';
    }


    /**
     * subject: 订单确认收货
     * api: /Erp/orders_confirm
     * author: Lazycat
     * day: 2017-02-23
     *
     * [字段名,类型,是否必传,说明]
     * param: openid,string,1,用户openid
     * param: pay_password,string,1,加密后的密码
     * param: s_no,string,1,订单号
     */
    public function orders_confirm(){
        $this->check('openid,pay_password,s_no');

        $tmp = $this->_check_pay_password($this->post['pay_password']);
        if($tmp['code'] != 1) $this->apiReturn($tmp);

        $res = $this->_erp_orders_confirm($this->post);
        $this->apiReturn($res);
    }

    public function _erp_orders_confirm($param){
        $uid = $this->user['id'] ? $this->user['id'] : $param['uid'];
        $ors = M('orders_shop')->where(['uid' => $uid,'s_no' => $param['s_no']])->field('id,status,o_id,o_no,s_no,inventory_type,score,refund_score,pay_price,refund_price,express_price_edit')->find();
        if($ors['status'] != 3) return ['code' => 0,'msg' => '只有已发货状态下的订单方可确认收货！'];

        $data['orderID']      	= $param['s_no'];
        $data['dealType']     	= $ors['inventory_type'] == 1 ? 1 : 2; //分账模式
        $data['returnType']		= ($ors['score'] - $ors['refund_score']) > 0 ? 1: 2;    //是否需要赠送积分
        $data['specialGoods']   = 0;

        $need_sign  = 'orderID,dealType,returnType,parterId,specialGoods';
        $res        = $this->erpApi('/confirmOrder.json',$data,$need_sign);

        if($res['code'] == 1){
            $ret = $this->_orders_confirm($ors);
            return $ret;
        }else{ //ERP经常出现没有返回结果的情况，此处用于补救该问题
            $tmp = $this->_orders_in_erp_status($ors['s_no']);
            //print_r($tmp);
            if($tmp['code'] == 1 && $tmp['data']['o_orderState'] > 2){
                $ret = $this->_orders_confirm($ors);
                return $ret;
            }
        }

        return ['code' => 0,'msg' => $res['msg']];
    }



    /**
     * subject: 获取订单在ERP中的状态
     * api: /Erp/orders_in_erp_status
     * author: Lazycat
     * day: 2017-02-23
     *
     * [字段名,类型,是否必传,说明]
     * param: s_no,string,1,订单号
     */

    public function orders_in_erp_status(){
        $this->check('s_no',false);

        $res = $this->_orders_in_erp_status($this->post['s_no']);
        $this->apiReturn($res);
    }

    /**
     * @param string $s_no 订单号
     */
    public function _orders_in_erp_status($s_no){
        $data['orderNum'] = $s_no;

        $need_sign  = 'orderNum,parterId';
        $res        = $this->erpApi('/getOrderStateByOrderNum.json',$data,$need_sign);

        if($res['code'] = 1){
            return ['code' => 1,'data' => $res['info']];
        }else{
            return ['code' => 0,'msg' => $res['msg']];
        }
    }


    /**
     * subject: 话费订单确认收货
     * api: /Erp/mobile_orders_confirm
     * author: Lazycat
     * day: 2017-05-12
     *
     * [字段名,类型,是否必传,说明]
     * param: openid,string,1,用户openid
     * param: pay_password,string,1,加密后的密码
     * param: s_no,string,1,订单号
     */
    public function mobile_orders_confirm(){
        $this->check('openid,pay_password,s_no');

        $tmp = $this->_check_pay_password($this->post['pay_password']);
        if($tmp['code'] != 1) $this->apiReturn($tmp);

        $res = $this->_erp_mobile_orders_confirm($this->post);
        $this->apiReturn($res);
    }

    public function _erp_mobile_orders_confirm($param){
        $uid = $this->user['id'] ? $this->user['id'] : $param['uid'];
        $ors = M('mobile_orders')->where(['s_no' => $param['s_no'],'uid' => $uid])->field('id,s_no,status,fare,mobile,pay_price,score,recharge_type,type,return_status,next_time')->find();
        if(empty($ors)) return ['code' => 0,'msg' => '订单不存在！'];
        if($ors['status'] != 3) return ['code' => 0,'msg' => '只有已发货订单才能确认收货！'];

        $data['orderID']      	= $param['s_no'];
        $data['dealType']     	= $ors['type'] == 1 ? 2 : 1; //分账模式
        $data['returnType']		= $ors['score'] > 0 ? 1: 2;    //是否需要赠送积分
        $data['specialGoods']   = 0;

        $need_sign  = 'orderID,dealType,returnType,parterId,specialGoods';
        $res        = $this->erpApi('/confirmOrder.json',$data,$need_sign);

        if($res['code'] == 1){
            $ret = A('MobileRecharge')->_orders_confirm($ors);
            return $ret;
        }else{ //ERP经常出现没有返回结果的情况，此处用于补救该问题
            $tmp = $this->_orders_in_erp_status($ors['s_no']);
            //print_r($tmp);
            if($tmp['code'] == 1 && $tmp['data']['o_orderState'] > 2){
                $ret = A('Rest2/MobileRecharge')->_orders_confirm($ors);
                return $ret;
            }
        }

        return ['code' => 0,'msg' => $res['msg']];
    }


    /**
     * subject: 话费退款
     * api: /Erp/mobile_recharge_refund
     * author: Lazycat
     * day: 2017-05-11
     *
     * [字段名,类型,是否必传,说明]
     * param: openid,string,1,用户openid
     * param: pay_password,string,1,加密后的密码
     * param: r_no,string,1,订单号
     */
    public function mobile_recharge_refund(){
        $this->check('openid,pay_password,r_no');

        $tmp = $this->_check_pay_password($this->post['pay_password']);
        if($tmp['code'] != 1) $this->apiReturn($tmp);

        $res = $this->_mobile_recharge_refund($this->post);
        $this->apiReturn($res);
    }
    public function _mobile_recharge_refund($param){
        $uid = $param['uid'] ? $param['uid'] : $this->user['id'];
        $rs = M('mobile_orders_refund')->where(['uid' => $uid, 'r_no' => $param['r_no']])->field('id,r_no,uid,seller_id,shop_id,s_no,money,score,status,orders_status,reason,cancel_time,accept_time,next_time')->find();

        if(!in_array($rs['status'],[1,2])) return ['code' => 0,'msg' => '当前状态下不允许操作退款！'];

        $ors = M('mobile_orders')->where(['s_no' => $rs['s_no'], 'uid' => $uid])->field('id,uid,seller_id,shop_id,s_no,status,fare,mobile,pay_price,score,recharge_type,pay_type,type,return_status,transtat,next_time')->find();
        if(empty($ors))             return ['code' => 0,'msg' => '订单不存在！'];
        if($ors['status'] != 2)     return ['code' => 0,'msg' => '订单状态错误！'];

        //考虑到队列处理可能存在延时问题，所以时间延后10分钟才可申请退款
        //$next_time = date('Y-m-d H:i:s',strtotime($ors['next_time']) + 600);
        if($ors['next_time'] > date('Y-m-d H:i:s')) return ['code' => 0,'msg' => '必须在'.$ors['next_time'].'后方可操作退款！'];

        //transtat及return_status为第三方接口返回的状态，请对照第三方文档
        if(in_array($ors['transtat'],[1,3,4,10,18]) && !in_array($ors['return_status'],[9,14,23,24,51])) return ['code' => 0,'msg' => '充值订单已被受理，不支持退款，有疑问请联系客服处理！'];
        if(in_array($ors['return_status'],[1,10,28,29])) return ['code' => 0,'msg' => '充值订单已被受理，不支持退款，有疑问请联系客服处理！'];

        $buyer  = M('user')->where(['id' => $ors['uid']])->field('nick,erp_uid')->find();
        $seller = M('user')->where(['id' => $ors['seller_id']])->field('nick,erp_uid')->find();

        $data 	=[
            'refundID'			=> $rs['r_no'],
            'appKey'			=> C('cfg.erp')['pid'],
            'refundMoney'		=> $rs['money'],
            'refundScore'		=> $rs['score'],
            'buyerID'			=> $buyer['erp_uid'],
            'buyerNick'			=> $buyer['nick'],
            'sellerID'			=> $seller['erp_uid'],
            'sellerNick'		=> $seller['nick'],
            'orderID'			=> $rs['s_no'],
            'payType'			=> $ors['pay_type'] == 2 ? 2 : 1,   //除唐宝外，其它付款方式均认为余额方式
            'dealType'			=> $ors['type']==1 ? 2: 1,          //分账模式，1=库存积分，2=扣货款
            'refundType'		=> $rs['score'] > 0 ? 2 : 1,        //2=退积分，1=不退积分
        ];
        $tmp = $this->_orders_in_erp_status($ors['s_no']);

        $need_sign='refundID,appKey,refundMoney,refundScore,buyerID,buyerNick,sellerID,sellerNick,orderID,payType,dealType,refundType,parterId';
        $ret=$this->erpApi('/arefund.json',$data,$need_sign);

        if($ret['code'] == 1){
            $ret = A('Rest2/MobileRecharge')->_refund_accept($rs);
            return $ret;
        }else{
            $tmp = $this->_orders_in_erp_status($ors['s_no']);
            if($tmp['code'] == 1 && $tmp['data']['o_surplusMoney'] == 0){
                $ret = A('Rest2/MobileRecharge')->_refund_accept($rs);
                return $ret;
            }
        }

        return ['code' => 0,'msg' => $res['msg']];

    }

    /**
     * 创建订单之前先提交官方优惠券到ERP
     * Create by lazycat
     * 2017-05-15
     */
    public function put_coupon(){
        $this->check('coupon',false);

        $res = $this->_put_coupon($this->post);
        $this->apiReturn($res);
    }
    public function _put_coupon($param){
        $data['coupon'] = $param['coupon'];
        $need_sign      = 'parterId,coupon';
        $res            =$this->erpApi('/adCoupon.json',$data,$need_sign);
        if($res['code'] == 1){
            return ['code' => 1,'msg' => $res['info']];
        }else{
            return ['code' => 0,'msg' => $res['info']];
        }
    }

    /**
     * 使用优惠券
     * Create by lazycat
     * 2017-05-15
     */
    public function use_coupon(){
        $this->check('coupon',false);

        $res = $this->_use_coupon($this->post);
        $this->apiReturn($res);
    }
    public function _use_coupon($param){
        $data['coupon'] = $param['coupon'];
        $need_sign      = 'parterId,coupon';
        $res            =$this->erpApi('/useCoupon.json',$data,$need_sign);
        if($res['code'] == 1){
            return ['code' => 1,'msg' => $res['info']];
        }else{
            return ['code' => 0,'msg' => $res['info']];
        }
    }

    /**
     * 检测优惠券是否合法可用
     * @param $coupon   string json格式
     * @return array
     */
    public function _coupon_check($param){
        //验证优惠券是否可用
        $uid = $param['uid'] ? $param['uid'] : $this->user['id'];
        $tmp = json_decode(html_entity_decode($param['coupon']),true);
        $ids = arr_id(['plist' => $tmp]);
        $list = M('coupon')->where(['id' => ['in',$ids]])->field('uid,short_code,status,sday,eday,type')->select();
        if($list){
            foreach($list as $val){
                if($val['uid'] != $uid) return ['code' => 0,'msg' => '[#'.$val['short_code'].']非该用户所有！'];
                if($val['type'] != 2)   return ['code' => 0,'msg' => '[#'.$val['short_code'].']不是乐兑官方优惠券！'];
                if($val['status'] != 1) return ['code' => 0,'msg' => '[#'.$val['short_code'].']优惠券已被停用！'];
                if($val['sday'] > date('Y-m-d') || $val['eday'] < date('Y-m-d')) return ['code' => 0,'msg' => '[#'.$val['short_code'].']不在当前使用时间范围！'];
            }
        }else{
            return ['code' => 0,'msg' => '非法的优惠券！'];
        }
        return ['code' => 1];
    }


	/**
     * subject: 校验验证码
     * api: /Erp/check_smscode
     * author: lizuheng
     * day: 2017-03-10
     * content: 
     *
     * [字段名,类型,是否必传,说明]
     * param: mobile,string,1,手机号码
     * param: smsCode,string,1,验证码
     */
    public function check_smscode(){
        $field = 'mobile,smsCode';
        $this->check($field);

        $res = $this->_check_smscode($this->post);
        $this->apiReturn($res);
    }

    public function _check_smscode($param=null){
        $need_sign='mobile,smsCode,parterId';
        $res=$this->erpApi('/smsCheck.json',$param,$need_sign);
        if($res['code'] == 1){
            return ['code' => 1,'msg' => $res['info']];
        }else{
            return ['code' => 0,'msg' => $res['info']];
        }
    }
	
	/**
     * subject: 获取ERP公告列表
     * api: /Erp/get_news
     * author: lizuheng
     * day: 2017-03-20
     * content: 
     *
     * [字段名,类型,是否必传,说明]
     * param: openid,string,1,用户的openid
     * param: page_num,int,0,,页面数量(默认10条)
     * param: page,int,0,当前页面(默认第一页)
     */
    public function get_news(){
        $field = 'openid,sign';
        $this->check($field);

        $res = $this->_get_news($this->post);
        $this->apiReturn($res);
    }

    public function _get_news($param=null){
		if(isset($param['page_num'])){
			$map['page_num'] = $param['page_num'];
		}else{
			$map['page_num'] = 10;
		}
		if(isset($param['page'])){
			$map['page'] = $param['page'];
		}else{
			$map['page'] = 1;
		}
		$need_sign='page,page_num,parterId';
		$res=$this->erpApi('/getPublicNews.json',$map,$need_sign);
		
        if($res['code'] == 1){
			//数据格式化
            foreach ($res['info'] as $key => $val) {
				$res['info'][$key]['n_title'] = strip_tags($res['info'][$key]['n_title']); 
				$res['info'][$key]['n_url'] = C('sub_domain.m')."/CNews/view/id/".$res['info'][$key]['n_id'].'/is_header/1'; 
				$res['info'][$key]['n_content1'] = mb_substr(strip_tags(html_entity_decode($val['n_content'])),0,60,'utf-8');
			}
			
            return ['code' => 1,'data' => $res['info']];
        }else{
            return ['code' => 0,'msg' => $res['info']];
        }
    }

	/**
     * subject: 公告详情
     * api: /Erp/news_view
     * author: lizuheng
     * day: 2017-03-28
     * content: 
     *
     * [字段名,类型,是否必传,说明]
     * param: openid,string,1,用户的openid
     * param: id,string,1,公告id
     */
    public function news_view(){
        $field = 'id';
        $this->check($field);

        $res = $this->_news_view($this->post);
        $this->apiReturn($res);
    }

    public function _news_view($param=null){
        $need_sign='id,parterId';
        $res=$this->erpApi('/getNewsDateil.json',$param,$need_sign);
        if($res['code'] == 1){
			$res['info']['n_content1'] = html_entity_decode($res['info']['n_content']);
            return ['code' => 1,'data' => $res['info']];
        }else{
            return ['code' => 0,'msg' => $res['info']];
        }
    }
	
	
	/**
     * subject: 获取最新的一条公告
     * api: /Erp/new_news
     * author: lizuheng
     * day: 2017-03-28
     * content: 
     *
     * [字段名,类型,是否必传,说明]
     * param: openid,string,1,用户的openid
     */
    public function new_news(){
        $field = 'openid';
        $this->check($field);

        $res = $this->_new_news($this->post);
        $this->apiReturn($res);
    }

    public function _new_news($param=null){
        $need_sign='parterId';
        $res=$this->erpApi('/getNewPublicNotice.json',$param,$need_sign);
		
        if($res['code'] == 1){
            return ['code' => 1,'data' => $res['info']];
        }else{
            return ['code' => 0,'msg' => $res['info']];
        }
    }
	
	/**
     * subject: 获取商家的未到账金额
     * api: /Erp/get_seller_payment
     * author: liangfeng
     * day: 2017-05-06
     * content: 
     *
     * [字段名,类型,是否必传,说明]
     * param: openid,string,1,用户的openid
     */
    public function get_seller_wait_payment(){
        $this->check('openid',false);
        $res = $this->_get_seller_wait_payment(['userID'=>$this->user['erp_uid']]);
        $this->apiReturn($res);
    }
    public function _get_seller_wait_payment($param=null){
		$need_sign='userID,parterId';
        $res=$this->erpApi('/getPaymentForGoodsByUserId.json',$param,$need_sign);
        if($res['code'] == 1){
            return ['code' => 1,'data' => $res['info']];
        }else{
            return ['code' => 0,'msg' => $res['info']];
        }
    }

    /**
     * subject: 待分账订单列表
     * api: getSellerWaitPaymentList
     * author: Mercury
     * day: 2017-05-16 11:06
     * [字段名,类型,是否必传,说明]
     * userID	string	是		商家ID
     * parterId	string	是		密钥
     * page	int	是		页码
     * pageSize	int	是		数据条数
     * signValue	string	是		签名值
     */
    public function getSellerWaitPaymentOrderList()
    {
        $this->check('openid');
        $need_sign='userID,parterId,page,pageSize,beginTime,endTime';
        $param['userID']    =   $this->user['erp_uid'];
        $param['page']      =   $this->post['p'];
        $param['pageSize']  =   $this->post['pagesize'];
        $param['beginTime'] =   date('Y-m-d H:i:s', NOW_TIME-(3600 * 24 * 60));
        $param['endTime']   =   date('Y-m-d H:i:s', NOW_TIME);
        $res=$this->erpApi('/getPaymentForGoodListByUserId.json',$param,$need_sign);
        $this->apiReturn(['code' => $res['code'], 'data' => $res['info']]);
    }

    /**
     * subject: 注册子账号
     * api: /Erp/sub_user
     * author: Mercury
     * day: 2017-03-23 10:27 
     * [字段名,类型,是否必传,说明]
     * parent int 1 父ID
     * nick str 1 昵称
     * password str 1 密码
     * photo str 1 头像
     */
    public function sub_user()
    {
        $cnt = M('user')->where(['parent_uid' => $this->user['id'], 'status' => ['in', '1,3']])->count();
        $max = M('shop')->where(['id' => $this->user['shop_id']])->getField('max_sub_user');
        if ($cnt >= $max) $this->ajaxReturn(['code' => 0, 'msg' => '您的子账号已达到上限']);
        $field  = 'nick,password,photo,shop_auth_group_id,repassword';
        $this->check($field);
        $this->post['parent']     = $this->user['erp_uid'];
        $this->post['nick']       = $this->user['nick'] .'-'. $this->post['nick'];
        if (M('user')->cache(true)->where(['nick' => $this->post['nick']])->getField('id')) $this->apiReturn(['code' => 0, 'msg' => '账户已存在']);
        $res    = $this->_sub_user($this->post);
        $this->apiReturn($res);
    }

    /**
     * subject:
     * api: _sub_user
     * author: Mercury
     * day: 2017-03-23 10:27
     * [字段名,类型,是否必传,说明]
     * @param null $params
     * @return array
     */
    protected function _sub_user($params = null)
    {
        try {
            $need_sign='parent,nick,password,photo,parterId';
            $res=$this->erpApi('/createSubUser.json',$params,$need_sign);
            if($res['code'] != 1) throw new Exception($res['msg'] . $res['info']);
            $data=[
                'openid'			=> $this->create_id(),
                'erp_uid'			=> $res['info']['su_id'],
                'nick'				=> $res['info']['su_nick'],
                'face'				=> $res['info']['su_photo'],
                'password'			=> $res['info']['su_password'],
                'ip'				=> get_client_ip(),
                'atime'				=> date('Y-m-d H:i:s'),
                'shop_auth_group_id'=> $this->post['shop_auth_group_id'],   //所属权限组
                'parent_uid'        => $this->user['id'],   //上级用户ID
                'shop_id'           => $this->user['shop_id'],  //店铺ID
            ];
            if(M('user')->add($data) == false) throw new Exception('注册失败');
            return ['code' => 1];
        } catch (Exception $e) {
            return ['code' => 0, 'msg' => $e->getMessage()];
        }
    }

    /**
     * subject: 修改密码
     * api: changePass
     * author: Mercury
     * day: 2017-03-25 16:50
     * [字段名,类型,是否必传,说明]
     * userID	string	是		客服ID
       oldPassword	string	是		旧密码（加密后的密文）
       newPassword	string	是		新密码（加密后的密文）
       parterId	string	是		密钥
       signValue	string	是		签名校验值
     */
    public function changePass()
    {
        ///modifyCustomServicePw.json
        try {
            if ($this->post['password'] != $this->post['repassword']) throw new Exception('两次密码不想同');
            $data = [
                'newPassword'   =>  $this->post['password'],
                'fartherID'     =>  $this->user['erp_uid'],
                'userID'        =>  M('user')->where(['id' => $this->post['id']])->cache(true)->getField('erp_uid'),
            ];
            $need_sign='fartherID,userID,newPassword,parterId';
            $res=$this->erpApi('/modifyCustomServicePw.json',$data,$need_sign);
            if($res['code'] != 1) throw new Exception($res['msg'] . $res['info']);
            if (M('user')->where(['parent_uid' => $this->user['id'], 'id' => $this->post['id']])->save(['password' => $this->post['password']]) === false) throw new Exception('密码修改失败');
            $this->apiReturn(['code' => 1, 'msg' => '操作成功']);
        } catch (Exception $e) {
            $this->apiReturn(['code' => 0, 'msg' => $e->getMessage()]);
        }
    }

    /**
     * subject: 修改头像
     * api: changeAvatar
     * author: Mercury
     * day: 2017-03-25 16:50
     * [字段名,类型,是否必传,说明]
     * userID	string	是		客服ID
       photo	string	是		头像路径
       parterId	string	是		密钥
       signValue	string	是		签名校验值
     */
    public function changeAvatar()
    {
        //modifyCustomServicePhoto.json
        try {
            $data = [
                'userID'    =>  M('user')->where(['id' => $this->post['id']])->cache(true)->getField('erp_uid'),
                'photo'     =>  $this->post['photo'],
            ];
            $need_sign='userID,photo,parterId';
            $res=$this->erpApi('/modifyCustomServicePhoto.json',$data,$need_sign);
            if($res['code'] != 1) throw new Exception($res['msg'] . $res['info']);
            if (M('user')->where(['id' => $this->post['id'], 'parent_uid' => $this->user['id']])->save(['face' => $this->post['photo']]) === false) throw new Exception('上传失败');
            $this->apiReturn(['code' => 1, 'msg' => '操作成功']);
        } catch (Exception $e) {
            $this->apiReturn(['code' => 0, 'msg' => $e->getMessage()]);
        }
    }

    /**
     * subject: 删除客服
     * api: delAccount
     * author: Mercury
     * day: 2017-03-25 17:04
     * [字段名,类型,是否必传,说明]
     * userID	string	是		客服ID
       fartherID	string	是		父级ID
       parterId	string	是		密钥
       state	string	是		状态（-1冻结，2为删除）
       signValue	string	是		签名校验值
     */
    public function delAccount()
    {
        ///modifyCustomServiceState.json
        try {
            $statusArr = [-1,1,2];
            if (!in_array($this->post['status'], $statusArr)) throw new Exception('非法操作');
            $data = [
                'fartherID' =>  $this->user['erp_uid'],
                'userID'    =>  M('user')->where(['id' => $this->post['id']])->cache(true)->getField('erp_uid'),
                'state'     =>  $this->post['status'],
            ];
            $need_sign='fartherID,userID,state,parterId';
            $res=$this->erpApi('/modifyCustomServiceState.json',$data,$need_sign);
            if($res['code'] != 1) throw new Exception($res['msg'] . $res['info']);
            $status = 1;
            switch ($data['state']) {
                case 1: //解冻
                    $status = 1;
                    break;
                case 2: //删除
                    $status = 4;
                    break;
                case -1://冻结
                    $status = 3;
                    break;
            }
            if (M('user')->where(['id' => $this->post['id'], 'parent_uid' => $this->user['id']])->save(['status' => $status]) === false) throw new Exception('删除失败');
            $this->apiReturn(['code' => 1, 'msg' => '操作成功']);
        } catch (Exception $e) {
            $this->apiReturn(['code' => 0, 'msg' => $e->getMessage()]);
        }
    }

    /**
     * 使用唐宝兑换抽奖机会
     * @param string $param['orderID']	订单号
     * @param string $param['tangbao']	唐宝数量
     */
    public function luckdraw_tangbao_chance($param){
        //return false;
        $data['userID']=$this->user['erp_uid'];
        $data['orderID']=$param['orderID'];
        $data['tangbao']=$param['tangbao'];
        $need_sign	='userID,orderID,tangbao,parterId';
        $res=$this->erpApi('/costTangbaoForLuckdraw.json',$data,$need_sign);
        return $res;
    }

    /**
     * 抽奖领取积分
     * @param string $param['username']	账号
     * @param string $param['password']	$this->password加密过的密码
     */
    public function luckdraw_award_score($param){
        //return false;
        $data['userID']=$this->user['erp_uid'];
        $data['orderID']=$param['orderID'];
        $data['score']=$param['score'];

        $need_sign	='userID,orderID,score,parterId';
        $res=$this->erpApi('/scoreInForLuckDraw.json',$data,$need_sign);

        return $res;
    }

    /**
     * 抽奖当查询不到用户信息时，向erp获取用户信息
     * @param string $param['erp_uid']	用户erp_uid
     */
    public function luckdraw_user_info($param){
        //return false;
        $data['userID']=$param['erp_uid'];
        $need_sign	='userID,parterId';
        $res=$this->erpApi('/getUserInfo.json',$data,$need_sign);
        //将查询到的用户信息添加到用户表
        if($res->code==1){
            $data = [
                'erp_uid'         =>  $res->info->u_id,
                'type'            =>  $res->info->u_type,
                'nick'            =>  $res->info->u_nick,
                'face'            =>  $res->info->u_logo?$res->info->u_logo:'https://img.trj.cc/FplovbCyAOdbztCfRqP9H02ec9hE',
                'password'        =>  $res->info->u_loginPwd,
                'name'            =>  $res->info->u_name,
                'email'           =>  $res->info->u_email,
                'mobile'          =>  $res->info->u_tel,
                'group_id'        =>  $res->info->u_groupId,
                'level_id'        =>  $res->info->u_level,
                'status'          =>  $res->info->u_state,
                'code'            =>  $res->info->u_code,
                'up_uid'          =>  $res->info->u_fCode,
                'is_auth'         =>  $res->info->auth,
                //'openid'          =>  $this->create_id(), //防止多出登陆
            ];
            $data['openid']		=$this->create_id();
            $data['ip']			=get_client_ip();
            if($data['id']=M('user')->add($data)){
                return $data['id'];
            }
        }
        return 0;
    }

    /**
     * subject: 订单支付
     * api: orderPay
     * author: Mercury
     * day: 2017-06-22 9:17
     * [字段名,类型,是否必传,说明]
     */
    public function multiOrderPay($data)
    {

        try {
//            $data       = [
//                'channelID'     =>  'C000000000000005',
//                'settleMode'    =>  1,  //1实物商品，2虚拟商品
//                'merOrderID'    =>  $this->post['order_no'], //订单号
//                'goodsName'     =>  $this->post['goods_name'], //商品名称
//                'ldbScore'      =>  0, //乐兑宝
//                'orderAmount'   =>  $this->post['money'] * 100, //订单金额，分
//                'autoRecieve'   =>  0,  //是否自动收货，0否，1是
//                'payAccountCode'=>  $this->post['account_code'], //会员角色 'NORMAL':消费者 'UN':联盟商账号 'BM':业务经理账号 'SOC':运营中心账号 'BC':商务中心账号
//                'returnUrl'     =>  '', //同步回调地址
//                'notifyUrl'     =>  '', //异步回调地址
//                'busID0'        =>  '', //余额
//                'busID1'        =>  '', //金积分
//                'busID2'        =>  '', //银积分
//            ];
            $signData   = 'parterId,channelID,settleMode,merOrderID,goodsName,ldbScore,orderAmount,autoRecieve,payAccountCode,returnUrl,notifyUrl,busID';
            $cfg = getSiteConfig('erp');
            $res = $this->erpApi($cfg['domain']['pay'].'/cashier/submitCashier.json', $data, $signData);
            if ($res['code'] != 1) throw new Exception($res['data']);
            $returnData = [
                'code'  =>  1,
                'data'  =>  $res['data']
            ];
        } catch (Exception $e) {
            $returnData = [
                'code'  =>  0,
                'msg'   =>  $e->getMessage()
            ];
        }
        return ($returnData);
    }

    /**
     * subject: 检测订单状态
     * api: checkOrderStatus
     * author: Mercury
     * day: 2017-06-22 10:12
     * [字段名,类型,是否必传,说明]
     */
    public function checkOrderStatus()
    {
        //order_no
    }

    /**
     * subject: 轮询erp订单状态接口
     * api: checkErpOrderStatus
     * author: Mercury
     * day: 2017-06-22 10:23
     * [字段名,类型,是否必传,说明]
     */
    public function checkErpOrderStatus()
    {
        //OG DD
        $this->check('s_no', false);
        $model = M('orders_shop');
        $model->startTrans();
        try {
            $data = [
                'outTradeNo'    => $this->post['s_no'],
            ];
            $res = $this->erpApi('/getOrderStateByOrderNum.json', $data, 'parterId,outTradeNo');
            if ($res['code'] != 1) throw new Exception($res['msg']);
            if ($res['info']['state'] != 1) throw new Exception('用户未支付');
            if ($this->post['s_no'][0] == 'O') {
                $map['o_no'] = $this->post['s_no'];
            } else {
                $map['s_no'] = $this->post['s_no'];
            }
            $map['status']      = 1;
            $orders             = $model->where($map)->field('id,s_no,o_no,o_id,money,shop_id')->find();
            $data['status']     = 2;
            $data['dtpay_no']   = $res['info']['orderID'];
            $data['pay_time']   = date('Y-m-d H:i:s', NOW_TIME);
            $data['money']      = $res['info']['money'];
            $data['pay_price']  = $res['info']['money'];
            //$data['pay_type']   = $res['data']['pay_type'];
            if ($model->where($map)->save($data) == false) throw new Exception('更新订单失败');
            $logs  = [
                'status'    => 2,
                'remark'    => '买家已付款',
                'o_id'      => $orders['o_id'],
                'o_no'      => $orders['o_no'],
                's_id'      => $orders['id'],
                's_no'      => $orders['s_no'],
                'ip'        => get_client_ip(),
            ];
            if (M('orders_logs')->add($logs) == false) throw new Exception('添加日志失败');

            $do         = M();
            $num        = 0;
            $goods      = M('orders_goods')->where(['s_id' => $orders['id']])->field('id,goods_id,num,attr_list_id')->select();
            $goods_id   = array();
            foreach($goods as $i => $val){
                $goods_id[] = $val['goods_id'];
                $num 	   +=	$val['num'];
                $do->execute('update '.C('DB_PREFIX').'goods_attr_list set num=num-'.$val['num'].',sale_num=sale_num+'.$val['num'].' where id='.$val['attr_list_id']);
                //更新销量
                $do->execute('update '.C('DB_PREFIX').'goods set num=num-'.$val['num'].',sale_num=sale_num+'.$val['num'].' where id='.$val['goods_id']);
            }
            $goods_id = array_unique($goods_id);

            //更新店铺销量
            if(!$this->sw[]=M('shop')->where(['id' => $orders['shop_id']])->setInc('sale_num',$num)) throw new Exception('更新店铺销量失败');
            goods_pr($goods_id);				//更新宝贝PR


            $returnData = ['code' => 1];
            $model->commit();
        } catch (Exception $e) {
            $returnData = ['code' => 0, 'msg' => $e->getMessage()];
            $model->rollback();
        }
        $this->apiReturn($returnData);
    }


    /**
     * 乐兑确认订单
     * Create by liangfeng
     * 2017-08-15
     * @param $s_no string 订单号
     * @param $score_type int 全返类型 1.全返 2.不全返
     */
    public function orders_confirm2(){
        $this->check('s_no,score_type', false);
        $res=  $this->_orders_confirm2($this->post);
        $this->apiReturn($res);
    }
    public function _orders_confirm2($param){
        $data['orderID']=$param['s_no'];
        $data['returnType']=$param['score_type'] == 2 ? 1 : 2 ;;
        $need_sign	='orderID,returnType,parterId';
        $res=$this->erpApi($cfg['domain']['pay'].'/confirmOrder.json',$data,$need_sign);
        if($res['id'] != 1001){
            return ['code'=>0,'msg'=>$res['ifno']];
        }else{
            return ['code'=>1];
        }

    }
}