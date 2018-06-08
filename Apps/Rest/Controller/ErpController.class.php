<?php
/**
+----------------------------------------------------------------------
| RestFull API 
+----------------------------------------------------------------------
| 与C+商务系统对接的API
+----------------------------------------------------------------------
| Author: lazycat <673090083@qq.com>
+----------------------------------------------------------------------
*/
namespace Rest\Controller;
use Common\Common\Tangpay;
use Think\Controller\RestController;
use Common\Builder\Activity;
class ErpController extends CommonController {
	protected $action_logs = array('orders_group_pay','orders_group_pay_other','orders_pay','orders_pay_other','orders_confirm','ad_pay','token','luckdraw_award_score', 'orders_group_pay2');
	public function index(){
		redirect(C('sub_domain.www'));
	}
	/**
	* 抽奖付运费
	* @param string $param['orderID']	订单号
	* @param string $param['money']		支付金额
	* @param string $param['payType']	支付类型 1.余额 2.唐宝
	*/
	public function luckdraw_express_price($param){
		//return false;
		$data['userID']=$this->user['erp_uid'];
		$data['orderID']=$param['orderID'];
		$data['money']=$param['money'];
		$data['payType']=$param['payType'];
		
		$need_sign	='userID,orderID,money,parterId,payType';
		$res=$this->erpApi('/payFreighForLuckdraw.json',$data,$need_sign);
		
		return $res;
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
			}else{
				return 0;
			}	
		}else{
			return 0;
		}
		
		//return $res;
	}
	/**
	* 会员登录
	* @param string $_POST['username']	账号
	* @param string $_POST['password']	$this->password加密过的密码
	*/
	public function check_login(){
		//频繁请求限制
		$this->_request_check();
		//必传参数检查
		$this->need_param=array('username','password','sign');
		$this->_need_param();
		$this->_check_sign();
		
		$data		=I('post.');
		//$data['password']=$this->password(I('post.password'));


		//$res=$this->erpApi('/login.json',$data,$need_sign);
        $subUser = false;   //子账号
        if (strpos($data['username'], '-') !== false) {
            $data['nick']   = $data['username'];
            $need_sign	    = 'nick,password,parterId';
            $url            = '/customerLogin.json';
            $subUser = true;
        } else {
            $need_sign	='username,password,parterId';
            $url = '/login.json';
        }
        $res = $this->erpApi($url,$data,$need_sign);
		//dump($res);
		if($res->code==1){
		    if ($subUser === true) {    //子账号
                //取父账号信息
                $parentUser = M('user')->where(['erp_uid' => $res->info->su_parent])->cache(true)->field('shop_type,openid,id')->find();
                if ($parentUser == false) $this->apiReturn(0);
                $data    =   [
                    'erp_uid'   =>  $res->info->su_id,
                    'nick'      =>  $res->info->su_nick,
                    'face'      =>  $res->info->su_photo,
                    'password'  =>  $res->info->su_password,
                ];
                $user = M('user')->where(['erp_uid' => $res->info->su_id, 'status' => 1])->field('id,status,openid as sub_openid,loginum,shop_id,shop_auth_group_id,parent_uid')->find();
                if ($user) {    //账户存在
					if($user['status'] == 3){
						$reason = M('prohibit_user')->where(['uid' => $user['id']])->order("atime desc")->getField('reason');
						$this->apiReturn(0, '', 1, $reason);
					}
                    $data['last_login_time']=date('Y-m-d H:i:s');
                    $data['ip']				=get_client_ip();
                    $data['loginum']		=$user['loginum']+1;
                    //权限方法列表
                    $data['funIds']         =M('shop_auth_group')->where(['id' => $user['shop_auth_group_id']])->getField('fun_ids');
                    M('user')->where(['id' => $user['id']])->save($data);
                    $data['sub_id']         =$user['id'];
                    $data['id']             =$parentUser['id'];
                    //$data['level_name']=$res->info->u_level_text;
                    $data=array_merge($data,$user, $parentUser);
                    $this->apiReturn(1,['data' => $data ]);
                }
                $this->apiReturn(0, '', 1, '账户不存在或已冻结');
            } else {    //非子账号
                //判断用户是否已入库
                $data    =   [
                    'erp_uid'         =>  $res->info->u_id,
                    'type'            =>  $res->info->u_type,
                    'nick'            =>  $res->info->u_nick,
                    'face'            =>  $res->info->u_logo?$res->info->u_logo:'https://img.trj.cc/FplovbCyAOdbztCfRqP9H02ec9hE',
                    'password'        =>  I('post.password'),
                    'name'            =>  $res->info->u_name,
                    'email'           =>  $res->info->u_email,
                    'mobile'          =>  $res->info->u_tel,
                    'group_id'        =>  $res->info->u_groupId,
                    'level_id'        =>  $res->info->u_level,
                    'status'          =>  $res->info->u_state,
                    'code'            =>  $res->info->u_code,
                    //'up_uid'          =>  $res->info->u_fCode,
                    'is_auth'         =>  $res->info->u_auth ? : '0000',
                    'upgrade'         =>  $res->info->u_upgrade ? : 0,
                    'upgrade_time'    =>  $res->info->u_upgradeTime ? : '1970-01-01',
                    'lowergrade'      =>  $res->info->u_lowergrade ? : 0,
                    'lowergrade_time' =>  $res->info->u_lowergradeTime ? : '',
                    //'fax'             =>  $res->info->u_fax ? : '02000000000',
                    'is_un'           =>  $res->info->u_isUn ? : 0,
                    'is_bm'           =>  $res->info->u_isBm ? : 0,
                    'is_soc'          =>  $res->info->u_isSoc ? : 0,
                    'is_bc'           =>  $res->info->u_isBc ? : 0,
                    'is_ledt'         =>  $res->info->u_isLedt ? : 0,
                    'is_rest_username'=>  $res->info->u_resetUsername ? : 0,
                    'is_quit'         =>  $res->info->u_isQuit ? : 0,
                    'is_virtual'      =>  $res->info->u_isVirtual ? : 0,
                    'un_time'         =>  $res->info->u_unTime ? : '1970-01-01',
                    'bm_time'         =>  $res->info->u_bmTime ? : '1970-01-01',
                    'soc_time'        =>  $res->info->u_socTime ? : '1970-01-01',
                    'ledt_time'       =>  $res->info->u_ledtTime ? : '1970-01-01',
                    'rest_username_time' => $res->info->u_resetUsernameTime ? : '1970-01-01',
                    'quit_time'       =>  $res->info->u_quitTime ? : '1970-01-01',
                    //'openid'          =>  $this->create_id(), //防止多出登陆
                ];
                if($res->info->u_fax) $data['fax'] = $res->info->u_fax;
                //
                $user=M('user')->where(['erp_uid' => $res->info->u_id ])->field('id,status,openid,loginum,shop_type,shop_id')->find();
                if($user){
					if($user['status'] == 3){
						$reason = M('prohibit_user')->where(['uid' => $user['id']])->order("atime desc")->getField('reason');
						$this->apiReturn(0, '', 1, $reason);
					}
                    $data['last_login_time']=date('Y-m-d H:i:s');
                    $data['ip']				=get_client_ip();
                    $data['loginum']		=$user['loginum']+1;

                    M('user')->where(['id' => $user['id']])->save($data);
                    $data['level_name']=$res->info->levelName;
                    $data=array_merge($data,$user);
                    $this->apiReturn(1,['data' => $data ]);

                }else{
                    $data['openid']		=$this->create_id();
                    $data['ip']			=get_client_ip();
                    if($data['id']=M('user')->add($data)){
                        $data['level_name']=$res->info->levelName;
                        $this->apiReturn(1,['data' => $data ]);
                    }else{
                        $this->apiReturn(0);
                    }
                }
            }
		}else{
			//登录失败
			$this->apiReturn($res->code,'',1,$res->info);
		}

	}


	/**
	* 获取用户信息
	* @param string $_POST['openid']	用户openid
	*/
	public function user_info(){
		//频繁请求限制
		$this->_request_check();

		//必传参数检查
		$this->need_param=array('openid','sign');
		$this->_need_param();
		$this->_check_sign();
		
		$data['userID']=$this->user['erp_uid'];
		$need_sign	='userID,parterId';
		$res=$this->erpApi('/getUserInfo.json',$data,$need_sign);

		//dump($res);
		if($res->code==1){
			$this->apiReturn(1,['data'=>$res->info]);
		}else{
			$this->apiReturn($res->code,'',1,$res->info);
		}
	}

	/**
	* 通过erpuid获取用户资料
	* @param string $_POST['openid']	用户openid
	*/
	public function user_info2(){
		//频繁请求限制
		//$this->_request_check();

		//必传参数检查
		$this->need_param=array('erp_uid','sign');
		$this->_need_param();
		$this->_check_sign();
		
		$data['userID']=I('post.erp_uid');
		$need_sign	='userID,parterId';
		$res=$this->erpApi('/getUserInfo.json',$data,$need_sign);

		//dump($res);
		if($res->code==1){
			$this->apiReturn(1,['data'=>$res->info]);
		}else{
			$this->apiReturn($res->code,'',1,$res->info);
		}
	}

	public function _user_info($erp_uid){
        $data['userID']=$erp_uid;
        $need_sign	='userID,parterId';
        $res=$this->erpApi('/getUserInfo.json',$data,$need_sign);

        //dump($res);
        if($res->code==1){
            return ['code' => 1,'data' => $res->info];
        }else{
            return ['code' => 0,'msg' => $res->info];
        }
    }

	/**
	* 通过code获取ERP用户user uid,主要用于同步登录
	* @param string $_POST['code']	将session_id做为code
	*/
	public function get_erp_uid(){
		//频繁请求限制
		$this->_request_check();

		//必传参数检查
		$this->need_param=array('code','sign');
		$this->_need_param();
		$this->_check_sign();
		
		$data['code']=I('post.code');
		$res=$this->erpApi('/getUserId.json',$data,$need_sign);
		if($res->code==1){
			$this->apiReturn(1,['data'=>$res->info]);
		}else{
			$this->apiReturn($res->code,'',1,$res->info);
		}
	}

	/** 
	* 发送短信验证码
	* @param string $_POST['mobile']	手机号码
	*/
	public function sms_code(){
		//频繁请求限制
		$this->_request_check();

		//必传参数检查
		$this->need_param=array('mobile','sign');
		$this->_need_param();
		$this->_check_sign();
		
		$data['mobile']=I('post.mobile'); 
		if(!preg_match("/^1[34578]{1}\d{9}$/",$data['mobile'])){  
			$this->apiReturn(0,'',1,'请填写正确的手机号码！'); 
		}
		$res=$this->erpApi('/smsCode.json',$data,$need_sign);

		//dump($res);
		if($res->code==1){
			$this->apiReturn(1,['data'=>$res->info]);
		}else{
			$this->apiReturn($res->code,'',1,$res->info);
		}		 
	}

	/** 
	* 检测手机号码
	* @param string $_POST['mobile']	手机号码
	*/
	public function check_mobile(){
		//频繁请求限制
		$this->_request_check();

		//必传参数检查
		$this->need_param=array('mobile','sign');
		$this->_need_param();
		$this->_check_sign();
		
		$data['mobile']=I('post.mobile');
		$need_sign='mobile,parterId';
		$res=$this->erpApi('/checkMobile.json',$data,$need_sign);

		//dump($res);
		if($res->code==1){
			$this->apiReturn(1,['data'=>I('post.mobile')],1,$res->info);
		}else{
			$this->apiReturn($res->code,['data'=>I('post.mobile')],1,$res->msg);
		}		 
	}

	/**
	* 用户昵称检测
	* @param string $_POST['username']	昵称
	*/
	public function register_username_check(){
		//频繁请求限制
		$this->_request_check();

		//必传参数检查
		$this->need_param=array('username','sign');
		$this->_need_param();
		$this->_check_sign();
		// 用户名不能以 数字 _  开头
		if(!preg_match("/^([{\x{4e00}-\x{9fa5}]|[a-zA-Z])+([{\x{4e00}-\x{9fa5}]|[0-9a-zA-Z\_])*([{\x{4e00}-\x{9fa5}]|[0-9a-zA-Z])+$/u",I('post.username'))){
			$this->apiReturn(0, ['msg' => '用户名必须是6~20位之间的中文/字母/数字/下划线组合,不能以_或数字开头,不能以_结束']);
		}
		// 用户名不合法
		if(preg_match('/乐兑|dttx|客服|管理员|系统管理员|全返|赠送|大唐|dt|大堂|云联惠|云联|yunlianhui|yunlian|乐兑|云连惠|云连会|云支付|云加速|云数据|芸联惠|芸连惠|芸连会|芸联会|云联汇|云连汇|芸联汇|芸连汇|匀连惠|匀联惠|匀联汇|老战士|云转回|匀加速|零购|云回转|成谋商城|脉单|众智云|麦点|秀吧|一点公益|商城联盟/',I('post.username'))){
			$this->apiReturn(0, ['msg' => '用户名不合法,请更换']);
		}
		
		$data['code']=I('post.username');
		//dump($data);
		$need_sign='code,parterId';
		$res=$this->erpApi('/codeUserInfo.json',$data,$need_sign);
		//dump($res);
		if($res->code==1){
			$this->apiReturn(0,['msg'=>'此用户名已存在']);
		}else{
			
			$this->apiReturn(1,['msg'=>'可以使用']);
		}		   
	}
	
	/** 
	* 个人会员注册
	* @param string $_POST['mobile']	手机号码
	*/
	public function register(){
		//频繁请求限制
		$this->_request_check();

		//必传参数检查
		$this->need_param=array('username','mobile','smscode','password','sign');
		$this->_need_param();
		$this->_check_sign();

		/* $_POST['ref']=$_POST['ref']!=''?$_POST['ref']:'乐兑'; */
		$_POST['ref']   = C('cfg.site')['ref_code'];

		if (C('cfg.site')['is_register'] == 0) {    //如果不能提供注册服务，
            $this->apiReturn(0, '', 1, C('cfg.site')['is_register_tips']);
        }


		# 用户名不能以 数字 _  开头
		if(!preg_match("/^([{\x{4e00}-\x{9fa5}]|[a-zA-Z])+([{\x{4e00}-\x{9fa5}]|[0-9a-zA-Z\_])*([{\x{4e00}-\x{9fa5}]|[0-9a-zA-Z])+$/u",$_POST['username'])){
			$this->apiReturn(0, ['msg' => '用户名必须是6~20位之间的中文/字母/数字/下划线组合,不能以_或数字开头,不能以_结束']);
		}
		# 用户名不合法
		if(preg_match('/乐兑|ledui|客服|管理员|系统管理员/',$_POST['username'])){
			$this->apiReturn(0, ['msg' => '用户名不合法,请更换']);
		}

		$data=[
			'username'		=>I('post.username'),
			'mobile'		=>I('post.mobile'),
			'smsCode'		=>I('post.smscode'),
			'referrer'		=>I('post.ref'),
			'password'		=>I('post.password'),
		];
		//dump($data);
		$need_sign='username,mobile,smsCode,referrer,password,country,parterId';
		$res=$this->erpApi('/regMember.json',$data,$need_sign);
		//dump($res);
		//dump($res);
		if($res->code==1){
			$data=[
					'openid'			=>$this->create_id(),
					'erp_uid'			=>$res->info->u_id,
					'nick'				=>$res->info->u_nick,
					'face'				=>$res->info->u_logo,
					'password'			=>$res->info->u_loginPwd,
					'password_pay'		=>$res->info->u_payPwd,
					'name'				=>$res->info->u_name,
					'mobile'			=>$res->info->u_tel,
					'level_id'			=>$res->info->u_level,
					'birthday'			=>$res->info->u_birth,
					'sex'				=>$res->info->u_sex,
					'ip'				=>get_client_ip(),
					'atime'				=>date('Y-m-d H:i:s')
				]; 
			if($data['id']=M('user')->add($data)){
				$this->apiReturn(1,['data'=>$data]);
			}else{
				$this->apiReturn(0);
			}
		}else{
			$this->apiReturn($res->code,'',1,$res->info);
		}		   
	}

	/** 
	* 企业注册
	* @param string $_POST['username']	  用户名
	* @param string $_POST['password']	  $this->password加密过的密码
	* @param string $_POST['organize']	  企业类型
	* @param string $_POST['company']	 公司名称
	* @param string $_POST['company_license']	 营业执行照
	* @param string $_POST['mobile']	手机号码
	* @param string $_POST['smscode']	 验证码
	* @param string $_POST['ref']	   推荐人,选填
	*/
	public function register_company(){
		//频繁请求限制
		$this->_request_check();

		//必传参数检查
		$this->need_param=array('username','password','organize','company','company_license','mobile','smscode','sign');
		$this->_need_param();
		$this->_check_sign();

		/* $_POST['ref']=$_POST['ref']!=''?$_POST['ref']:'乐兑'; */
        $_POST['ref']   = C('cfg.site')['ref_code'];

        if (C('cfg.site')['is_register'] == 0) {    //如果不能提供注册服务，
            $this->apiReturn(0, '', 1, C('cfg.site')['is_register_tips']);
        }

		# 用户名不能以 数字 _ 开头
		if(!preg_match("/^([{\x{4e00}-\x{9fa5}]|[a-zA-Z])+([{\x{4e00}-\x{9fa5}]|[0-9a-zA-Z\_])*([{\x{4e00}-\x{9fa5}]|[0-9a-zA-Z])+$/u",$_POST['username'])){
			$this->apiReturn(0, ['msg' => '用户名必须是6~20位之间的中文/字母/数字/下划线组合,不能以_或数字开头,不能以_结束']);
		}
		# 用户名不合法
		if(preg_match('/乐兑|dttx|客服|管理员|系统管理员|admin/',$_POST['username'])){
			$this->apiReturn(0, ['msg' => '用户名不合法,请更换']);
		}
		# 公司名称必须中文
/* 		if($_POST['company']){
			if(!preg_match("/^[{\x{4e00}-\x{9fa5}]+$/u",$_POST['company'])){
				$this->apiReturn(0, ['msg' => '公司名称必须是中文并且20个字符以内']);
			}
		} */

		$_POST['country']=$_POST['country']?$_POST['country']:37;

		$data=[
			'organize'			=>I('post.organize'),
			'companyname'		=>I('post.company'),
			'companylicense'	 =>I('post.company_license'),
			'mobile'			=>I('post.mobile'),
			'smsCode'			=>I('post.smscode'),
			'referrer'			=>I('post.ref'),
			'username'			=>I('post.username'),
			'password'			=>I('post.password')
		];
		$need_sign='organize,companyname,companylicense,mobile,smsCode,referrer,username,password,parterId';
		$res=$this->erpApi('/regCompany.json',$data,$need_sign);
		//dump($res);
		//dump($res);
		if($res->code==1){
			$data=[
					'openid'			=>$this->create_id(),
					'erp_uid'			=>$res->info->u_id,
					'nick'				=>$res->info->u_nick,
					'face'				=>$res->info->u_logo,
					'password'			=>$res->info->u_loginPwd,
					'password_pay'		=>$res->info->u_payPwd,
					'name'				=>$res->info->u_name,
					'mobile'			=>$res->info->u_tel,
					'level_id'			=>$res->info->u_level,
					'birthday'			=>$res->info->u_birth,
					'sex'				=>$res->info->u_sex
				]; 
			if($data['id']=M('user')->add($data)){
				$this->apiReturn(1,['data'=>$data]);
			}else{
				$this->apiReturn(0);
			}
		}else {
            $this->apiReturn($res->code, '', 1, $res->info);
        }
	}


	/** 
	* 获取账户信息
	* @param string $_POST['openid']	用户openid
	*/
	public function account(){
		//频繁请求限制
		$this->_request_check();

		//必传参数检查
		$this->need_param=array('openid','sign');
		$this->_need_param();
		$this->_check_sign();

		$data['userID']=$this->user['erp_uid'];
		//dump($data);
		$need_sign='userID,parterId';
		$res=$this->erpApi('/account.json',$data,$need_sign);
		//dump($res);
		if($res->code==1){
			$this->apiReturn(1,['data'=>$res->info]);

		}else{
			$this->apiReturn($res->code,'',1,$res->msg);
		}		   
	}

	/** 
	* 检查登录密码是否正确
	* @param string $_POST['openid']	用户openid
	* @param string $_POST['password']	$this->password加密码后的密码
	*/
	public function check_password(){
		//频繁请求限制
		$this->_request_check();

		//必传参数检查
		$this->need_param=array('openid','password','sign');
		$this->_need_param();
		$this->_check_sign();

		$data['userID']=$this->user['erp_uid'];
		$data['password']=I('post.password');
		//dump($data);
		$need_sign='userID,password,parterId';
		$res=$this->erpApi('/checkPwd.json',$data,$need_sign);
		//dump($res);
		if($res->code==1){
			$this->apiReturn(1,['data'=>$res->info]);

		}else{
			$this->apiReturn($res->code,'',1,$res->info);
		}		   
	}

	/** 
	* 检查安全密码是否正确
	* @param string $_POST['openid']	用户openid
	* @param string $_POST['password']	$this->password加密码后的密码
	*/
	public function check_pay_password(){
		//频繁请求限制
		$this->_request_check();

		//必传参数检查
		$this->need_param=array('openid','password','sign');
		$this->_need_param();
		$this->_check_sign();

		$res=$this->_check_pay_password(I('post.password'));
		//dump($res);
		if($res->code==1){
			$this->apiReturn(1,['data'=>$res->info]);

		}else{
			$this->apiReturn($res->code,'',1,$res->info);
		}
	}

	public function _check_pay_password($password){
		$data['userID']		=$this->user['erp_uid'];
		$data['safePwd']	=$password;
		//dump($data);
		$need_sign='userID,safePwd,parterId';
		$res=$this->erpApi('/checkSafePwd.json',$data,$need_sign);
		
		return $res;	   
	}

	/**
	* 通过用户名获取用户信息
	* @param string $_POST['username'] 用户名
	*/
	public function get_user_info(){
		//频繁请求限制
		$this->_request_check();

		//必传参数检查
		$this->need_param=array('code','sign');
		$this->_need_param();
		$this->_check_sign();

		$data['code']=I('post.code');
		//dump($data);
		$need_sign='code,parterId';
		$res=$this->erpApi('/codeUserInfo.json',$data,$need_sign);
		//dump($res);
		if($res->code==1){
			$this->apiReturn(1,['data'=>$res->info]);
		}else{
			$this->apiReturn($res->code,'',1,$res->msg);
		}		   
	}	 

	/**
	* 找回密码步骤一：发送短信验证码
	* @param string $_POST['username'] 用户名
	* @param string $_POST['mobile'] 手机号码
	* @param string $_POST['smscode'] 短信验证码
	*/
	public function forgot_password_step1(){
		//频繁请求限制
		$this->_request_check();

		//必传参数检查
		$this->need_param=array('username','mobile','smscode','sign');
		$this->_need_param();
		$this->_check_sign();

		$data=[
			'nick'		=>I('post.username'),
			'mobile'	=>I('post.mobile'),
			'smsCode'	=>I('post.smscode')
		];
		//dump($data);
		$need_sign='nick,mobile,smsCode,parterId';
		$res=$this->erpApi('/findPwd.json',$data,$need_sign);
		//dump($res);
		if($res->code==1){
			$this->apiReturn(1,['data'=>['erp_uid'=>$res->info->userID,'sign_code'=>$res->info->sign]]);

		}else{
			$this->apiReturn($res->code,'',1,$res->info);
		}		   
	}	 

	/**
	* 找回密码步骤二：重置密码
	* @param string $_POST['erp_uid'] 用户绑定的ERP的userID
	* @param int	$_POST['smscode']	第一步时的那个验证码
	* @param string $_POST['password'] 新密码
	* @param string $_POST['password2'] 确认新密码
	*/
	public function forgot_password_step2(){
		//频繁请求限制
		$this->_request_check();

		//必传参数检查
		$this->need_param=array('erp_uid','password','signcode','sign');
		$this->_need_param();
		$this->_check_sign();

		$data=[
			'userID'     =>I('post.erp_uid'),
			'newPwd'     =>I('post.password'),
			'sign'       =>I('post.signcode')
		];
		//dump($data);
		$need_sign='userID,newPwd,sign,parterId';
		$res=$this->erpApi('/findPwd2.json',$data,$need_sign,'',1);
		//dump($res);
		if($res->code==1){
			$this->apiReturn(1,['data'=>$res->info]);

		}else{
			$this->apiReturn($res->code,'',1,$res->info);
		}		   
	}	 


	/**
	* 找回安全密码
	* @param string $_POST['username'] 	用户名
	* @param string $_POST['mobile'] 	手机号码
	* @param string $_POST['smscode'] 	短信验证码
	* @param string $_POST['password'] 	新密码
	*/
	public function forgot_pay_password(){
		//频繁请求限制
		$this->_request_check();

		//必传参数检查
		$this->need_param=array('openid','mobile','smscode','password','sign');
		$this->_need_param();
		$this->_check_sign();

		$data=[
			'userID'	=>$this->user['erp_uid'],
			'mobile'	=>I('post.mobile'),
			'smsCode'	=>I('post.smscode'),
			'newPayPwd' =>I('post.password')
		];
		//dump($data);
		$need_sign='userID,newPayPwd,mobile,smsCode,parterId';
		$res=$this->erpApi('/findPayPwd.json',$data,$need_sign);
		//dump($res);
		if($res->code==1){
			$this->apiReturn(1,['data'=>$res->info]);

		}else{
			$this->apiReturn($res->code,'',1,$res->info);
		}		   
	}	 

	/**
	* 修改登录密码
	* @param string $_POST['openid'] 	用户openid
	* @param string $_POST['opassword'] 旧密码
	* @param string $_POST['password'] 	新密码
	* @param string $_POST['mobile'] 	手机号
	* @param string $_POST['smscode'] 	验证码
	*/
	public function change_password(){
		//频繁请求限制
		$this->_request_check();

		//必传参数检查
		$this->need_param=array('opassword','password','openid','sign', 'smscode', 'mobile');
		$this->_need_param();
		$this->_check_sign();

		//新旧密码不能一样
		if(I('post.password')==I('post.opassword')) $this->apiReturn(37);

		$data=[
			'opassword'			=>I('post.opassword'),
			'password'			=>I('post.password'),
			'smsCode' 			=>I('post.smscode'),
			'mobile' 			=>I('post.mobile'),
			'userID'			=>$this->user['erp_uid']
		];
		//dump($data);
		$need_sign='opassword,password,userID,parterId,mobile,smsCode';
		$res=$this->erpApi('/modifyPwd.json',$data,$need_sign);
		//dump($res);
		if($res->code==1){
			$this->apiReturn(1,['data'=>$res->info]);

		}else{
			$this->apiReturn($res->code,'',1,$res->info .'...');
		}		   
	}  


	/**
	* 设置安全密码
	* @param string $_POST['openid'] 		用户openid
	* @param string $_POST['login_password']登录密码
	* @param string $_POST['password'] 		安全密码
	*/
	public function set_pay_password(){
		//频繁请求限制
		$this->_request_check();

		//必传参数检查
		$this->need_param=array('password','openid','mobile','smscode','sign');
		$this->_need_param();
		$this->_check_sign();

		$data=[
			'payPwd'			=>I('post.password'),
			'mobile'			=>I('post.mobile'),
			'smsCode'			=>I('post.smscode'),
			'userID'			=>$this->user['erp_uid']
		];
		//dump($data);
		$need_sign='userID,payPwd,mobile,smsCode,parterId';
		$res=$this->erpApi('/safePwd.json',$data,$need_sign);
		//dump($res);
		if($res->code==1){
			$this->apiReturn(1,['data'=>$res->info]);

		}else{
			$this->apiReturn($res->code,'',1,$res->info);
		}		   
	}  

	/**
	* 修改安全密码
	* @param string $_POST['openid'] 		用户openid
	* @param string $_POST['old_password'] 	旧安全密码
	* @param string $_POST['password'] 		安全密码
	* @param string $_POST['mobile'] 		手机号码
	* @param string $_POST['smscode'] 		短信验证码
	*/
	public function change_pay_password(){
		//频繁请求限制
		$this->_request_check();

		//必传参数检查
		$this->need_param=array('old_password','password','mobile','smscode','openid','sign');
		$this->_need_param();
		$this->_check_sign();
		//新密码不能与旧密码一样
		if(I('post.password')==I('post.old_password')) $this->apiReturn(37);

		$data=[
			'oldPayPwd'			=>I('post.old_password'),
			'payPwd'			=>I('post.password'),
			'mobile'			=>I('post.mobile'),
			'smsCode'			=>I('post.smscode'),
			'userID'			=>$this->user['erp_uid']
		];
		//dump($data);
		$need_sign='userID,oldPayPwd,payPwd,mobile,smsCode,parterId';
		$res=$this->erpApi('/modifySafePwd.json',$data,$need_sign);
		//dump($res);
		if($res->code==1){
			$this->apiReturn(1,['data'=>$res->info]);

		}else{
			$this->apiReturn($res->code,'',1,$res->info);
		}		   
	}  

	/**
	* 订单合并付款
	* @param string $_POST['o_no'] 			合并付款的订单号
	* @param string $_POST['password_pay'] 	安全密码
	* @param int 	$_POST['paytype']		1=余额付款,2=唐宝付款
	*/
	public function orders_group_pay(){
		//频繁请求限制
		$this->_request_check(5);
		//必传参数检查
		$this->need_param=array('openid','o_no','password_pay','paytype','sign');
		$this->_need_param();
		$this->_check_sign();

        if (C('cfg.site')['is_pay'] == 0) {    //如果不能提供支付服务，
            $this->apiReturn(0, '', 1, C('cfg.site')['is_pay_tips']);
        }

		$this->check_password_pay(I('post.password_pay'));

		$orders=new \Common\Controller\OrdersController(array('o_no'=>I('post.o_no'),'uid'=>$this->uid));
		$ret=$orders->check_orders();
		if($ret['code']!=1) $this->apiReturn($ret['code'],'',1,$ret['msg']);

		$n=0;
		foreach($ret['data']['orders_shop'] as $val){
			$res=$this->_orders_pay($val['s_no'],I('post.paytype'));
			if($res['code']==1) $n++;

			$result[]=$res;
		}
		
		$code=0;

		if($n==count($ret['data']['orders_shop'])) $code=1;
		$this->apiReturn($code,['data' => $result,'payok_num'=>$n, 'msg' => $result[0]['msg']]);
	}

    /**
     * 新的支付方式(多订单)
     */
	public function orders_group_pay2() {
        //频繁请求限制
        $this->_request_check(5);
        //必传参数检查
        $this->need_param=array('openid','o_no', 'pay_type','sign');
        $this->_need_param();
        $this->_check_sign();

        if (C('cfg.site')['is_pay'] == 0) {    //如果不能提供支付服务，
            $this->apiReturn(0, '', 1, C('cfg.site')['is_pay_tips']);
        }

        $orders=new \Common\Controller\OrdersController(array('o_no'=>I('post.o_no'),'uid'=>$this->uid));
        $ret=$orders->check_orders();
        if($ret['code']!=1) $this->apiReturn($ret['code'],'',1,$ret['msg']);
        $result = [];
        $userDo = M('user');
        $ordersGoodsDo = M('orders_goods');
        //$payType = Tangpay::PAY_TYPE;
        foreach($ret['data']['orders_shop'] as $val){
            $payPrice   =   $val['pay_price'];
            $score      =   $val['score'];
            if ($_POST['pay_type'] == 2) {
                $activity = Activity::getTangpayActivityByOrdersShop($val);   //查找活动
                if ($activity) {
                    $payPrice   =   $activity['pay_price'];
                    $score      =   $activity['score'];
                }
            }

//            foreach ($payType as $k => $v) {    //为支付方式重新赋值
//                if ($activity && $k == 'Tangbao') {    //可参与唐宝支付活动
//                    $payType[$k] = (float)round($activity['pay_price'], 2);
//                } else {
//                    $payType[$k] = (float)round($val['pay_price'], 2);
//                }
//            }
            $result[] = array(
                //来源渠道
                'channelID'     => C('cfg.dtpay')['tangpay_channelID'],
                //收款人userID
                'recieverID'    => $userDo->cache(true)->where(['id' => $val['seller_id']])->getField('erp_uid'),
                //买家
                'buyerID'       => $this->user['erp_uid'],

                'buyerNick'     => $this->user['nick'],

                //商家订单号
                'merOrderID'    => $val['s_no'],
                //结算模式：1扣库存积分，2扣货款
                'settleMode'    => $val['inventory_type']==1?1:2,
                //代购手续费，以“分”为单位。没有则传0
                'buyAgentFee'   => $val['daigou_cost'] * 100,
                //订单金额：人民币，以分为单位
                'orderAmount'   => $payPrice * 100,

                /**
                 * 支付方式
                 */
                'payChannel'    => array_search($_POST['pay_type'], Tangpay::PAY_TYPE),

                //折扣数据
                #'discountJson'  => json_encode($payType),
                //'discountJson'  => '',
                //赠送积分
                'giveScore'     => (int)$score,
                //是否自动收货
                'autoRecieve'   => 0,
                //商品url
                'goodsUrl'      => implode(',', array_column($ordersGoodsDo->cache(true)->where(['s_id' => $val['id']])->getField('id,CONCAT("'.DM('item').'/goods/",attr_list_id, ".html") as url', true), 'url')),
                //商品名称
                'goodsName'     => implode(',', $ordersGoodsDo->cache(true)->where(['s_id' => $val['id']])->getField('goods_name', true)),
                //备注，可为空，原样返回
                'remark'        => 'remark',
                //时间戳
                //'timestamp'     => NOW_TIME,
                'disabledPay'   => '',
                //同步通知地址
                'returnUrl'     => DM('cart', '/tangpay/returnUrl'),     //同步通知地址
                //异步通知地址
                'notifyUrl'     => DM('cart', '/tangpay/notifyUrl'),     //同步通知地址
                //业务类型：“余额业务ID,唐宝业务ID”
                'busID'         => C('cfg.dtpay')['tangpay_busID'],
            );
        }
        $this->apiReturn(1,['data' => $result, 'buyUid' => $this->user['erp_uid'], 'nick' => $this->user['nick'], 'msg' => $result[0]['msg']]);
    }


    /**
     * 新的支付方式（单订单）
     */
    public function orders_single_pay2() {
        //频繁请求限制
        $this->_request_check(5);
        //必传参数检查
        $this->need_param=array('openid','s_no', 'pay_type','sign');
        $this->_need_param();
        $this->_check_sign();

        if (C('cfg.site')['is_pay'] == 0) {    //如果不能提供支付服务，
            $this->apiReturn(0, '', 1, C('cfg.site')['is_pay_tips']);
        }
        $orders=new \Common\Controller\OrdersController(array('s_no'=>I('post.s_no'),'uid'=>$this->uid));
        $ret=$orders->check_s_orders(2);
        if($ret['code']!=1) $this->apiReturn($ret['code'],'',1,$ret['msg']);
        $userDo = M('user');
        $ordersGoodsDo = M('orders_goods');
        //$payType = Tangpay::PAY_TYPE;
        $payPrice= $ret['data']['pay_price'];
        $score   = $ret['data']['score'];
        if ($_POST['pay_type'] == 2) {
            $activity = Activity::getTangpayActivityByOrdersShop($ret['data']);   //查找活动
            if ($activity) {
                $payPrice   =   $activity['pay_price'];
                $score      =   $activity['score'];
            }
        }
        $result = array(
            //来源渠道
            'channelID'     => C('cfg.dtpay')['tangpay_channelID'],

            //买家
            'buyerID'       => $this->user['erp_uid'],

            'buyerNick'     => $this->user['nick'],
            //收款人userID
            'recieverID'    => $userDo->cache(true)->where(['id' => $ret['data']['seller_id']])->getField('erp_uid'),
            //商家订单号
            'merOrderID'    => $ret['data']['s_no'],
            //结算模式：1扣库存积分，2扣货款
            'settleMode'    => $ret['data']['inventory_type']==1?1:2,
            //代购手续费，以“分”为单位。没有则传0
            'buyAgentFee'   => $ret['data']['daigou_cost'] * 100,
            /**
             * 支付方式
             */
            'payChannel'    => array_search($_POST['pay_type'], Tangpay::PAY_TYPE),
            //订单金额：人民币，以分为单位
            'orderAmount'   => $payPrice * 100,
            //折扣数据
            //'discountJson'  => json_encode($payType),
            //'discountJson'  => '',
            //赠送积分
            'giveScore'     => (int)$score,
            //是否自动收货
            'autoRecieve'   => 0,
            //商品url
            'goodsUrl'      => implode(',', array_column($ordersGoodsDo->cache(true)->where(['s_id' => $ret['data']['id']])->getField('id,CONCAT("'.DM('item').'/goods/",attr_list_id, ".html") as url', true), 'url')),
            //商品名称
            'goodsName'     => implode(',', $ordersGoodsDo->cache(true)->where(['s_id' => $ret['data']['id']])->getField('goods_name', true)),
            //备注，可为空，原样返回
            'remark'        => 'remark',
            //时间戳
            //'timestamp'     => NOW_TIME,
            'disabledPay'   => '',
            //同步通知地址
            'returnUrl'     => DM('cart', '/tangpay/returnUrl'),     //同步通知地址
            //异步通知地址
            'notifyUrl'     => DM('cart', '/tangpay/notifyUrl'),     //同步通知地址
            //业务类型：“余额业务ID,唐宝业务ID”
            'busID'         => C('cfg.dtpay')['tangpay_busID'],
        );
        $this->apiReturn(1,['data' => $result]);
    }

    /**
     * 获取支付方式
     */
    public function get_erp_paytype() {
        $ret = $this->erpApi(C('cfg.dtpay')['tangpay_url_paytype']);
        log_add('pcapicfg', $ret);
        unset($ret->code);
        $ret = json_decode(json_encode($ret), true);
        $this->apiReturn(1, ['data' => array_values($ret)]);
    }

	/**
	* 用于第三方支付时使用,不需要传入安全密码
	*/
	public function orders_group_pay_other(){
		//频繁请求限制
		$this->_request_check();
		//必传参数检查
		$this->need_param=array('openid','o_no','paytype','sign');
		$this->_need_param();
		$this->_check_sign();

        if (C('cfg.site')['is_pay'] == 0) {    //如果不能提供支付服务，
            $this->apiReturn(0, '', 1, C('cfg.site')['is_pay_tips']);
        }

		$orders=new \Common\Controller\OrdersController(array('o_no'=>I('post.o_no'),'uid'=>$this->uid));
		$ret=$orders->check_orders();
		if($ret['code']!=1) $this->apiReturn($ret['code'],'',1,$ret['msg']);

		$n=0;
		foreach($ret['data']['orders_shop'] as $val){
			$res=$this->_orders_pay($val['s_no'],I('post.paytype'),I('post.other_paytype'));
			if($res['code']==1) $n++;

			$result[]=$res;
		}
		
		$code=0;

		if($n==count($ret['data']['orders_shop'])) $code=1;
		$this->apiReturn($code,['data' => $result,'payok_num'=>$n, 'msg' => $result[0]['msg']]);
	}

	/**
	* 单个订单付款
	* @param string $_POST['openid']	用户openid
	* @param string $_POST['s_no']		商家订单号
	* @param string $_POST['password_pay'] 安全密码	   
	* @param int 	$_POST['paytype']	1=余额付款,2=唐宝付款
	*/
	public function orders_pay(){
		//频繁请求限制
		$this->_request_check();

		//必传参数检查
		$this->need_param=array('openid','s_no','password_pay','paytype','sign');
		$this->_need_param();
		$this->_check_sign();
        if (C('cfg.site')['is_pay'] == 0) {    //如果不能提供支付服务，
            $this->apiReturn(0, '', 1, C('cfg.site')['is_pay_tips']);
        }
		$this->check_password_pay(I('post.password_pay'));

		$res=$this->_orders_pay(I('post.s_no'),I('post.paytype'));

		$this->apiReturn($res['code'],['data'=>$res['data'],'res'=>$res['res']],1,$res['msg']);
	}

	/**
	* 用于第三方支付时使用,不需要传入安全密码
	*/
	public function orders_pay_other(){
		//频繁请求限制
		$this->_request_check();

		//必传参数检查
		$this->need_param=array('openid','s_no','paytype','sign');
		$this->_need_param();
		$this->_check_sign();

		$res=$this->_orders_pay(I('post.s_no'),I('post.paytype'),I('post.other_paytype'));

		$this->apiReturn($res['code'],['data'=>$res['data'],'res'=>$res['res']],1,$res['msg']);
	}

	/**
	* 订单付款
	* @param string $s_no 商家订单号
	* @param int 	$paytype 付款方式 1=余额,2=唐宝
	* @param int 	$other_paytype 第三方支付方式，用于更新订单支付方式,3=支付宝,4=微信
	*/
	public function _orders_pay($s_no,$paytype=1,$other_paytype=''){
		$orders=new \Common\Controller\OrdersController(array('s_no'=>$s_no,'uid'=>$this->uid));
		$ret=$orders->check_s_orders(2);
		if($ret['data']['status']!=1) return ['code' => 198 ,'msg'=>'该订单状态下不允许付款！'];	

		if (S(md5('orders_shop_pay_total_price' . $ret['data']['s_no'])) == true) goto success;
		//dump($ret);
		if($ret['code']!=1) return $ret;

		//检查订单中商品库存是否足够或商品属性是否有变更
		$check=$orders->check_goods_attr();
		if($check['code']!=1) return $check;
		$seller=M('user')->where(['id' => $ret['data']['seller_id']])->field('nick,erp_uid')->find();
		$pay_price    =   $ret['data']['pay_price'];
		$score        =   $ret['data']['score'];
		$activity     =   [];
		if ($paytype == 2) {
		    $activity = Activity::tangPaysActivity($ret['data']);
		    if ($activity == false) {
		        $activity =   Activity::getActivityByShopOrders($ret['data'], 4);
		    }
		    if ($activity) {
		        $pay_price    =   $activity['pay_price'];
		        $score        =   $activity['score'];
		    }
		}
		
		$data=[
				'ip'				=>get_client_ip(),
				'appKey'			=>C('cfg.erp')['pid'],
				'outTradeNo'		=>$ret['data']['s_no'],
				'timeoutExpress'	=>date('Y-m-d H:i:s',time()+86400*3),
				'outCreateTime'		=>$ret['data']['atime'],
				'sellerID'			=>$seller['erp_uid'],
				'sellerNick'		=>$seller['nick'],
				'buyID'				=>$this->user['erp_uid'],
				'buyNick'			=>$this->user['nick'],
				'totalMoney'		=>round($pay_price-$ret['data']['daigou_cost'], 2),
				'subject'			=>'订购商品，订单号：'.$ret['data']['s_no'],
				'body'				=>$this->user['nick'].'，订购'.$ret['data']['goods_num'].'款商品，合计'.$ret['data']['pay_price'].'元',
				'payType'			=>$paytype,
				'showUrl'			=>'http://',    //显示
				'returnUrl'			=>'http://',  //同步
				'notifyUrl'			=>DM('rest', '/notice/run'),  //异步
				'dealType'			=>$ret['data']['inventory_type']==1?1:2,
				'totalScore'		=>round($score),
		        'isPurchase'        =>$ret['data']['daigou_cost']>0?1:0,//是否代购
		        'purchaseMoney'     =>$ret['data']['daigou_cost'],//代购手续费
			];
		//只有未付款订单方可付款！
		if($ret['data']['status']!=1) return ['code' => 198 ,'data'=>$data,'msg'=>'该订单状态下不允许付款！'];			
		//dump($data);
		$need_sign='ip,appKey,outTradeNo,timeoutExpress,outCreateTime,sellerID,sellerNick,buyID,buyNick,totalMoney,subject,body,payType,showUrl,returnUrl,notifyUrl,totalScore,dealType,isPurchase,purchaseMoney';
		$res=$this->erpApi('/addOrder.json',$data,$need_sign); 
		//dump($res);
		if($res->code==1){
		    //判断是否已支付,如果已经支付，则直接返回
		    if (M('orders_shop')->where(['id' => $ret['data']['id'], 'status' => 2, 'is_pay' => 1])->find()) goto success;
			$do=M();
			$do->startTrans();
			//如果有唐宝支付折扣则更新所有商品价格
			if (!empty($activity) && $activity['full_value'] > 0 && $paytype == 2) {
			    $sql    =   'update ' . C('DB_PREFIX') . 'orders_goods SET total_price_edit = total_price_edit * '
			        . ($activity['full_value']) . ',score = score_ratio * score * '. ($activity['full_value']) .' WHERE s_id = ' . $ret['data']['id'] . ' AND s_no = '.$ret['data']['s_no'];
                if ($do->execute($sql) == false) {
			        $msg =   '订单商品更新失败';
			        goto error;
			    }
			    //修改商家订单
			    if(M('orders_shop')->where(['id' => $ret['data']['id']])->save(['pay_price' => $pay_price, 'goods_price_edit' => $activity['goods_price_edit'], 'score' => $score, 'pay_time' => date('Y-m-d H:i:s'),'status'=>2,'pay_type' => ($other_paytype?$other_paytype:$paytype), 'money' => $pay_price, 'notify_type' => 1, 'is_pay' => 1]) == false) {
			        $msg =   '订单更新失败';
			        goto error;
			    }
			} else {
			    //更新订单状态
			    if(!$this->sw[]=M('orders_shop')->where(['s_no' => $s_no])->save(['pay_time' => date('Y-m-d H:i:s'),'status'=>2,'pay_type' => ($other_paytype?$other_paytype:$paytype), 'money' => $pay_price])) goto error;
			}

			//写入日志
			$logs_data=array(
					'o_id'		=>$ret['data']['o_id'],
					'o_no'		=>$ret['data']['o_no'],
					's_id'		=>$ret['data']['id'],
					's_no'		=>$ret['data']['s_no'],
					'status'	=>2,
					'remark'	=>'买家已付款'
				);

			if(!$this->sw[]=D('Common/OrdersLogs')->create($logs_data)){
				$msg=D('Common/OrdersLogs')->getError();
				goto error;
			}
			if(!$this->sw[]=D('Common/OrdersLogs')->add()) goto error;


			//更新库存，有部分库存属可能已列新，所以不列入事务是否一定要执行成功
			$num = 0;
			foreach($check['data'] as $i => $val){
				$goods_id[] = $val['goods_id'];
				$num 	+=	$val['num'];
				$do->execute('update '.C('DB_PREFIX').'goods_attr_list set num=num-'.$val['num'].',sale_num=sale_num+'.$val['num'].' where id='.$val['attr_list_id']);
				//更新销量
				$do->execute('update '.C('DB_PREFIX').'goods set num=num-'.$val['num'].',sale_num=sale_num+'.$val['num'].' where id='.$val['goods_id']);
			}
			$goods_id = array_unique($goods_id);

			//更新店铺销量
			if(!$this->sw[]=M('shop')->where(['id' => $ret['data']['shop_id']])->setInc('sale_num',$num)) goto error;			
			
			//付款加1
			if (Activity::activityInc($s_no, 'payment_num') == false) {
			    //goto error;
			}
			//设置参与者状态
			if (Activity::setStatus($s_no, $this->uid, 1, (!empty($activity) ? null : 4)) === false) {
			    //goto error;
			}
			
			$do->commit();

	        //发短信通知
	        $sms_data['mobile']=M('shop')->where(['id' => $ret['data']['shop_id']])->getField('mobile');
	        if(!empty($sms_data['mobile'])){	            
	            $sms_data['content']=$this->sms_tpl(14,
	                    ['{nick}','{orderno}','{money}','{goods_num}'],
	                    [$this->user['nick'],$ret['data']['s_no'],$ret['data']['pay_price'],$ret['data']['goods_num']]
	                );

                if(!strstr($sms_data['content'],'test') && !strstr($sms_data['content'],'测试')) sms_send($sms_data);
	        }
	        
	        success:
	        S(md5('orders_shop_pay_total_price' . $ret['data']['s_no']), 1);

	        shop_pr($ret['data']['shop_id']);	//更新店铺PR
	        goods_pr($goods_id);				//更新宝贝PR

			return ['code'=>1,'data'=>$data,'res'=>$res,'msg' => $res->info];
            
			error:
			    S(md5('orders_shop_pay_total_price' . $ret['data']['s_no']), null);
				$do->rollback();
				return ['code'=>0,'data'=>$data,'res'=>$res,'msg'=>$msg];

		}else{
			return ['code'=>0,'data'=>$data,'res'=>$res,'msg'=>$res->info];
		}
	
	}

	
    /**
     * 订单修复
     */
    public function orders_fix(){
        //频繁请求限制
        $this->_request_check();

        //必传参数检查
        $this->need_param=array('openid','s_no','sign');
        $this->_need_param();
        $this->_check_sign();

        $orders=new \Common\Controller\OrdersController(array('s_no'=>I('post.s_no'),'uid'=>$this->uid));
        $ret=$orders->check_s_orders(0);
        if($ret['code']!=1) $this->apiReturn($ret['code'],'',1,$ret['msg']);

        if(!in_array($ret['data']['status'],array(0,1,10))) $this->apiReturn(0,'',1,'该订单状态下不允许执行此操作');

        //获取订单在ERP中的状态
        $status = $this->_check_orders_status(I('post.s_no'));
        if($status->code == 1){
            $paytype = $status->info->o_payType;
        }else{
            $this->apiReturn($status['code'],'',1,$status['msg']);    //检测状态失败！
        }
        //dump($status);
        //dump($paytype);



        //检查订单中商品库存是否足够或商品属性是否有变更
        $check=$orders->check_goods_attr();
        if($check['code']!=1) return $this->apiReturn($check['code'],'',1,$check['msg']);

        $seller=M('user')->where(['id' => $ret['data']['seller_id']])->field('nick,erp_uid')->find();
        $pay_price    =   $ret['data']['pay_price'];
        $score        =   $ret['data']['score'];

        $activity     =   [];
        if ($paytype == 2) {
            $activity = Activity::tangPaysActivity($ret['data']);
            if ($activity == false) {
                $activity =   Activity::getActivityByShopOrders($ret['data'], 4);
            }
            if ($activity) {
                $pay_price    =   $activity['pay_price'];
                $score        =   $activity['score'];
            }
        }

        //dump($activity);

        $do=M();
        $do->startTrans();
        //如果有唐宝支付折扣则更新所有商品价格
        if (!empty($activity) && $activity['full_value'] > 0 && $paytype == 2) {
            $sql    =   'update ' . C('DB_PREFIX') . 'orders_goods SET total_price_edit = total_price_edit * '
                . $activity['full_value'] . ',score = score_ratio * score * '. $activity['full_value'] .' WHERE s_id = ' . $ret['data']['id'] . ' AND s_no = '.$ret['data']['s_no'];
            if ($do->execute($sql) == false) {
                $msg =   '订单商品更新失败';
                goto error;
            }
            //修改商家订单
            if(M('orders_shop')->where(['id' => $ret['data']['id']])->save(['pay_price' => $pay_price, 'goods_price_edit' => $activity['goods_price_edit'], 'score' => $score, 'pay_time' => date('Y-m-d H:i:s'),'status'=>2,'pay_type' => ($other_paytype?$other_paytype:$paytype), 'money' => $pay_price]) == false) {
                $msg =   '订单更新失败';
                goto error;
            }
        } else {
            //更新订单状态
            if(!$this->sw[]=M('orders_shop')->where(['s_no' => I('post.s_no')])->save(['pay_time' => date('Y-m-d H:i:s'),'status'=>2,'pay_type' => ($other_paytype?$other_paytype:$paytype), 'money' => $pay_price])) goto error;
        }



        //写入日志
        $logs_data=array(
            'o_id'		=>$ret['data']['o_id'],
            'o_no'		=>$ret['data']['o_no'],
            's_id'		=>$ret['data']['id'],
            's_no'		=>$ret['data']['s_no'],
            'status'	=>2,
            'remark'	=>'买家已付款'
        );

        if(!$this->sw[]=D('Common/OrdersLogs')->create($logs_data)){
            $msg=D('Common/OrdersLogs')->getError();
            goto error;
        }
        if(!$this->sw[]=D('Common/OrdersLogs')->add()) goto error;



        //更新库存，有部分库存属可能已列新，所以不列入事务是否一定要执行成功
        $num = 0;
        foreach($check['data'] as $i => $val){
            $goods_id[] = $val['goods_id'];
            $num 	+=	$val['num'];
            $do->execute('update '.C('DB_PREFIX').'goods_attr_list set num=num-'.$val['num'].',sale_num=sale_num+'.$val['num'].' where id='.$val['attr_list_id']);
            //更新销量
            $do->execute('update '.C('DB_PREFIX').'goods set num=num-'.$val['num'].',sale_num=sale_num+'.$val['num'].' where id='.$val['goods_id']);
        }
        $goods_id = array_unique($goods_id);

        //更新店铺销量
        if(!$this->sw[]=M('shop')->where(['id' => $ret['data']['shop_id']])->setInc('sale_num',$num)) goto error;

        //付款加1
        if (Activity::activityInc(I('post.s_no'), 'payment_num') == false) {
            //goto error;
        }
        //设置参与者状态
        if (Activity::setStatus(I('post.s_no'), $this->uid, 1, (!empty($activity) ? 4 : null)) === false) {
            //goto error;
        }

        $do->commit();

        //发短信通知
        $sms_data['mobile']=M('shop')->where(['id' => $ret['data']['shop_id']])->getField('mobile');
        if(!empty($sms_data['mobile'])){
            $sms_data['content']=$this->sms_tpl(14,
                ['{nick}','{orderno}','{money}','{goods_num}'],
                [$this->user['nick'],$ret['data']['s_no'],$ret['data']['pay_price'],$ret['data']['goods_num']]
            );

            //sms_send($sms_data);
        }

        success:

        shop_pr($ret['data']['shop_id']);	//更新店铺PR
        goods_pr($goods_id);				//更新宝贝PR

        $this->apiReturn(1,['data' => $data],1,$res->info);

        error:
        $do->rollback();
        $this->apiReturn(0,['data' => $data],1,$msg);
    }



	/**
	* 退款
	* @param string $param['r_no']			退款单号
	* @param float  $param['money']			退款金额
	* @param int 	$param['score']			退回积分	
	* @param string $param['buyer_uid']		买家UID
	* @param string $param['buyer_nick']	买家昵称
	* @param string $param['seller_uid']	卖家UID
	* @param string $param['seller_nick']	卖家昵称
	* @param string $param['s_no']			订单号
	* @param int 	$param['pay_type']		支付类型
	* @param int 	$param['inventory_type']库存结算方式,0=非即结算，1=即时结算
	* @param int 	$param['refundType']	1=退运费，2=退商品
	*/
	public function _refund($param){
	    if($param['pay_type'] == 2) {
	        $payType   =   2;
	    } else {
	        $payType   =   1;
	    }
		$data 	=[
			'refundID'			=>$param['r_no'],
			'appKey'			=>C('cfg.erp')['pid'],
			'refundMoney'		=>$param['money'],
			'refundScore'		=>$param['score'],
			'buyerID'			=>$param['buyer_uid'],
			'buyerNick'			=>$param['buyer_nick'],
			'sellerID'			=>$param['seller_uid'],
			'sellerNick'		=>$param['seller_nick'],
			'orderID'			=>$param['s_no'],
			'payType'			=>$payType,
			'dealType'			=>$param['inventory_type'],
			'refundType'		=>$param['refundType'],
		];

		$need_sign='refundID,appKey,refundMoney,refundScore,buyerID,buyerNick,sellerID,sellerNick,orderID,payType,dealType,refundType,parterId';
		$res=$this->erpApi('/arefund.json',$data,$need_sign);
		return $res;	
	}
    
	/**
	 * 仅供后台使用
	 * @param unknown $param
	 */
	public function refundAdmin() {
	    //频繁请求限制
	    $this->_request_check();
	    //必传参数检查
	    $this->need_param=array('money','r_no','score','buyer_uid', 'sign', 'buyer_nick', 's_no', 'pay_type', 'inventory_type', 'refundType', 'seller_nick', 'seller_uid');
	    $this->_need_param();
	    $this->_check_sign();
	    $param = I('post.');
	    if($param['pay_type'] == 2) {
	        $payType   =   2;
	    } else {
	        $payType   =   1;
	    }
		$data 	=[
			'refundID'			=>$param['r_no'],
			'appKey'			=>C('cfg.erp')['pid'],
			'refundMoney'		=>$param['money'],
			'refundScore'		=>$param['score'],
			'buyerID'			=>$param['buyer_uid'],
			'buyerNick'			=>$param['buyer_nick'],
			'sellerID'			=>$param['seller_uid'],
			'sellerNick'		=>$param['seller_nick'],
			'orderID'			=>$param['s_no'],
			'payType'			=>$payType,
			'dealType'			=>$param['inventory_type'],
			'refundType'		=>$param['refundType'],
		];

		$need_sign='refundID,appKey,refundMoney,refundScore,buyerID,buyerNick,sellerID,sellerNick,orderID,payType,dealType,refundType,parterId';
		$res=$this->erpApi('/arefund.json',$data,$need_sign);
		$this->ajaxReturn($res);	
	}
	
	/**
	* 根据第三方订单号获取订单状态
	* @param string $_POST['s_no'] 商家订单号
	*/
	public function check_orders_status(){
		//频繁请求限制
		//$this->_request_check();
		//必传参数检查
		$this->need_param=array('s_no','sign');
		$this->_need_param();
		$this->_check_sign();

		$res=$this->_check_orders_status(I('post.s_no'));

		if($res->code==1){
			$this->apiReturn(1,['data' => $res->info]);

		}else{
			$this->apiReturn($res->code,'',1,$res->info);
		}		   
	}

	public function _check_orders_status($s_no){
		$data['orderNum']=$s_no;
		//dump($data);
		$need_sign='orderNum,parterId';
		$res=$this->erpApi('/getOrderStateByOrderNum.json',$data,$need_sign);
		return $res;		
	}

	/**
	* 确认订单
	* @param string $_POST['s_no'] 			订单号
	* @param string $_POST['openid'] 		用户openid
	* @param string $_POST['password_pay']	安全密码，加密码过的
	* @param int 	$_POST['is_sys']		1为系统操作
	*/
	public function orders_confirm(){
		//频繁请求限制
		$this->_request_check();

		//必传参数检查
		$this->need_param=array('openid','s_no','password_pay','sign');
		$this->_need_param();
		$this->_check_sign();

		$this->check_password_pay(I('post.password_pay'));

		$data 			=I('post.');
		$data['uid']	=$this->uid;
		$res=$this->_orders_confirm($data);

		$this->apiReturn($res['code'],['data'=>$res['data']],1,$res['msg']);
	}  

	/**
	* 用于超时自动确认收货
	* @param string $_POST['s_no']	订单号
	* @param string $_POST['uid']	用户UID
	*/
	public function orders_confirm_auto($s_no,$uid,$is_sys=0){
		//必传参数检查
		//$this->need_param=array('s_no','uid','sign');
		//$this->_need_param();
		//$this->_check_sign();
		$data = [
			's_no'	=>$s_no,
			'uid'	=>$uid,
			'is_sys'=>$is_sys
		];
		$res=$this->_orders_confirm($data,0);

		return $res;
	} 	
	/**
	* 确认订单
	* @param int $check_type 	1检测卖家，2检测买家，0不检测
	*/
	public function _orders_confirm($param,$check_type=2){
		$is_sys 	=$param['is_sys']?1:0;

		$orders=new \Common\Controller\OrdersController(array('s_no'=>$param['s_no'],'uid'=>$param['uid']));
		$ret=$orders->check_s_orders($check_type);
		if($ret['code']!=1) return $ret;

		$rs=$ret['data'];
		//$seller=M('user')->where(['id' => $rs['seller_id']])->field('nick,erp_uid')->find();
        $specialGoods       =   0;
        //获取是否有参与累积升级
        if ($rs['pay_type'] != 2) {    //不为唐宝支付时才去查看是否有参与满消费升级
            $activity = M('activity_participate')->where(['s_no' => $param['s_no'], 'type_id' => 7, 'uid' => $param['uid'], 'status' => 0])->field('id,status')->find();
            if ($activity) {
                $getMoney       =   M('orders_shop')->where(['s_no' => $param['s_no'], 'uid' => $param['uid'], 'status' => 3])->field('pay_price,refund_price,express_price_edit')->find();
                //累积升级金额                  =   订单支付金额    -   (运费 + 退款金额)
                $specialGoods   =   round($getMoney['pay_price'] - ($getMoney['express_price_edit'] + $getMoney['refund_price']), 2);
            }
        }
		$data['orderID']      	=$param['s_no'];
        $data['dealType']     	=$ret['data']['inventory_type'] == 1 ? 1 : 2; //暂时只支持1（即时扣库存积分）
        $data['returnType']		=($rs['score']-$rs['refund_score']) >0 ?1:2;
        $data['specialGoods']   =$specialGoods;
		//dump($data);
		$need_sign='orderID,dealType,returnType,parterId,specialGoods';
		$res=$this->erpApi('/confirmOrder.json',$data,$need_sign);

		if($res->code !=1){
			$status = $this->_check_orders_status($param['s_no']);
		}

		if($res->code==1 || (isset($status) && $status->info->o_orderState ==5)){

			$refund=M('refund')->where(['s_id' => $rs['id'],'status' => ['not in','20,100']])->field()->select();

			$do=M();
			$do->startTrans();
			if ($activity && $activity['status'] == 0) { //如果状态为未支付，则改为已支付
			    M('activity_participate')->where(['id' => $activity['id']])->save(['status' => 1]);    //修改状态
			}
			
			$luckdraw = getSiteConfig('luckdraw');   //抽奖
			$luckdrawFlag = false;
			if (round($rs['goods_price_edit'] - $rs['refund_price'], 2) >= $luckdraw['luckdraw_orders_money']) {
			    $luckdrawMap = [
			         'id' => $rs['shop_id'],
			         'type_id' => ['in', $luckdraw['luckdraw_shop_type']],
			    ];
			    if (M('shop')->where($luckdrawMap)->getField('id')) {
			        $luckdrawDo = M('luckdraw_chance');
			        $luckdrawId = $luckdrawDo->where(['uid' => $rs['uid']])->getField('id');
			        if ($luckdrawId > 0) {
			            if(!$this->sw[]=$luckdrawDo->where(['id' => $luckdrawId])->setInc('free_chance', $luckdraw['luckdraw_orders_num'])) {
			                $msg = '免费抽奖机会加' . $luckdraw['luckdraw_orders_num'] . '失败';
			                goto error;
			            }
			        } else {
			            if(!$this->sw[]=$luckdrawDo->add(['free_chance' => $luckdraw['luckdraw_orders_num'], 'uid' => $rs['uid']])) {
			                $msg = '添加免费抽奖机会加' . $luckdraw['luckdraw_orders_num'] . '失败';
			                goto error;
			            }
			        }
			        
			        //记录免费抽奖机会
			        $freeData = [
			             'no'    =>  $this->create_orderno('LA',$this->uid),    //单号
			             'uid'   =>  $this->uid,                 //用户ID
			             'status'=>  1,                          //状态
			             'type'  =>  2,                          //类型
			        ];
			        if (false == M('luckdraw_chance_free')->add($freeData)) {
			            $msg = '记录免费抽奖机会加' . $luckdraw['luckdraw_orders_num'] . '失败';
			            goto error;
			        }
			        
			        $luckdrawFlag = true;
			    }
			}
			
			//如果存在着退款，即将退款取消
			if($refund){
				if(!$this->sw[]=M('refund')->where(['s_id' => $rs['id'],'status' => ['not in','20,100']])->save(['status' => 20,'cancel_time' => date('Y-m-d H:i:s')])) goto error;
				//日志
				foreach($refund as $val){
			        //日志数据
			        $logs=[
			            'r_id'          =>$val['id'],
			            'r_no'          =>$val['r_no'],
			            'uid'           =>$param['uid'],
			            'status'        =>20,
			            'type'          =>$val['type'],
			            'remark'        =>C('error_code.1006'), //买家取消退款！
			        ];

			        if(!$this->sw[]=D('Common/RefundLogs')->create($logs)){
			            $msg=D('Common/RefundLogs')->getError();
			            goto error;            
			        }        

			        if(!$this->sw[]=D('Common/RefundLogs')->add()) goto error;
				}
			}


			//更新订单
			if(!$this->sw[]=M('orders_shop')->where(array('id'=>$rs['id']))->save(array('status'=>4,'receipt_time'=>date('Y-m-d H:i:s'),'next_time' => date('Y-m-d H:i:s',time() + C('cfg.orders')['rate_add']),'is_problem' => 0))){
				//更新订单状态失败！
				$result['code']=200;
				goto error; 
			}

			//订单日志
			$logs_data=array(
					'o_id'		=>$rs['o_id'],
					'o_no'		=>$rs['o_no'],
					's_id'		=>$rs['id'],
					's_no'		=>$rs['s_no'],
					'status'	=>4,
					'remark'	=>'买家确认收货',
					'is_sys'	=>$is_sys
				);
			
			if(!$this->sw[]=D('Common/OrdersLogs')->create($logs_data)){
				$result['code']=4;
				$msg=D('Common/OrdersLogs')->getError();
				goto error;
			}

			if(!$this->sw[]=D('Common/OrdersLogs')->add()){
				$result['code']=202;
				goto error;
			}

			//付款成功，事务提交
			$do->commit();
			$returnData['s_no'] = $rs['s_no'];
			$returnData['luckdraw'] = $luckdrawFlag == true ? 1 : 0;
			return array('code' =>1,'data' => $returnData);

			error:
				$do->rollback();
				return array('code' => 0,'msg' => $msg);
		}else{
			return array('code' => $res->code,'msg' => $res->info);
		}	
	}

	/**
	* 雇员登录
	* @param string $_POST['username'] 用户名
	* @param string $_POST['password'] 密码
	*/
	public function admin_login(){
		//频繁请求限制
		$this->_request_check();

		//必传参数检查
		$this->need_param=array('username','password','sign');
		$this->_need_param();
		$this->_check_sign();

		$data=I('post.');
		//dump($data);
		$need_sign='username,password,parterId';
		$res=$this->erpApi('/gyLogin.json',$data,$need_sign);
		//dump($res);
		if($res->code==1){
			$data=[
				'erp_uid'	=>$res->info->e_uid,
				'username'	=>$res->info->e_id,
				'password'	=>$data['password'],
				'name'		=>$res->info->e_name,
				'face'		=>$res->info->e_photo,
				'mobile'	=>$res->info->e_tel
			];
			//检查是否已存在
			$user=M('admin')->where(['erp_uid' => $data['erp_uid']])->find();
			if($user){
				M()->execute('update '.C('DB_PREFIX').'admin set loginum=loginum+1,logintime=now() where id='.$user['id']);			   
				$this->apiReturn(1,['data'=>$user ]);	 
			}else{
				$data['sid']	=100810429; //默认权限分组
				if($data['id']=M('admin')->add($data)){
					$this->apiReturn(1,['data'=>$data ]);		
				}else{
					$this->apiReturn(0);
				}	

			}			 

		}else{
			$this->apiReturn($res->code,'',1,$res->msg);
		}		   
	}  


	/**
	* 同步雇员资料
	* @param int 	$_POST['start']	起始记录
	* @param int 	$_POST['limit']	要获取的记录数量
	*/
	public function admin_sync(){
		//频繁请求限制
		$this->_request_check();

		//必传参数检查
		$this->need_param=array('start','limit','sign');
		$this->_need_param();
		$this->_check_sign();

		$data 	=	[
			'page'		=> I('post.start'),
			'page_num'	=>	I('post.limit')
		];

		//dump($data);
		$need_sign='page,page_num,parterId';
		$res=$this->erpApi('/getEmployInfo.json',$data,$need_sign);
		if($res->code==1){
			$n=0;
			foreach($res->info as $val){
				$data=[
					'erp_uid'	=>$val->e_uid,
					'username'	=>$val->e_id,
					'password'	=>$val->e_loginPwd?$val->e_loginPwd:md5(time()),
					'name'		=>$val->e_name,
					'face'		=>$val->e_photo,
					'mobile'	=>$val->e_tel
				];

				if($data['erp_uid'] && $data['username'] && $data['name']) {
                    //检查是否已存在
                    $user = M('admin')->where(['erp_uid' => $data['erp_uid']])->find();
                    if (!$user) {
                        M('admin')->where(['username' => $data['username']])->setField('username', $data['username'] . '_copy');
                        $data['sid'] = 100810429; //默认权限分组
                        if (M('admin')->add($data)) $n++;
                    }
                }
			}

			$this->apiReturn(1,'',1,'更新'.$n.'条记录！');

		}else{
			$this->apiReturn($res->code,'',1,$res->msg);
		}		   
	}  

	/**
	* 同步雇员资料
	*/
	public function top_news(){
		//频繁请求限制
		$this->_request_check();

		//必传参数检查
		$this->need_param=array('sign');
		$this->_need_param();
		$this->_check_sign();


		//dump($data);
		$data=[
			'page_num'		=>I('post.pagesize')?I('post.pagesize'):8,
			'page'			=>I('post.p')?I('post.p'):1
		];
		$need_sign='page,page_num,parterId';
		$res=$this->erpApi('/getPublicNews.json',$data,$need_sign);
		//dump($res);
		if($res->code==1){
			$this->apiReturn(1,['data' => $res->info]);
		}else{
			$this->apiReturn(3,'',1,$res->info);
		}		   
	} 

	/**
	* 企业类型
	*/
	public function company_type(){
		//频繁请求限制
		$this->_request_check();

		//必传参数检查
		$this->need_param=array('sign');
		$this->_need_param();
		$this->_check_sign();


		//dump($data);
		$need_sign='parterId';
		$res=$this->erpApi('/getOrganize.json',$data,$need_sign);
		//dump($res);
		if($res->code==1){

			$this->apiReturn(1,['data' => $res->info]);
		}else{
			$this->apiReturn($res->code,'',1,$res->info);
		}		   
	} 
	/**
	* 检查用户是否有开店的权限
    * @param string     $_POST['openid']    用户openid
	*/
	public function check_open_shop(){
		//频繁请求限制
		//$this->_request_check();

		//必传参数检查
		$this->need_param=array('openid','sign');
		$this->_need_param();
		$this->_check_sign();

		$data['userID'] = $this->user['erp_uid'];
		$need_sign='userID,parterId';
		$res=$this->erpApi('/getUserTypeByID.json',$data,$need_sign);
		//dump($res);
		if($res->code==1){
			$this->apiReturn(1,['data' => $res->info]);
		}else{
			$this->apiReturn($res->code,'',1,$res->info);
		}  

	}

    /**
    * 广告订单付款
    * @param string     $_POST['openid']    用户openid
    * @param string     $_POST['a_no']      订单号
    */
    public function ad_pay(){
        //频繁请求限制
        $this->_request_check();

        //必传参数检查
        $this->need_param=array('openid','a_no','password_pay','sign');
        $this->_need_param();
        $this->_check_sign();

        $this->check_password_pay(I('post.password_pay'));

        $rs=M('ad')->where(['uid' => $this->uid,'a_no' => I('post.a_no')])->field('id,a_no,num,money_pay')->find();

        if(!$rs) $this->apiurl(3);

        //只有未付款的订单才可以删除
        if($rs['status']!=0) $this->apiReturn(555);
        # 金额是0不请求接口
        if($rs['money_pay'] <= 0){
        	$r = M('ad')->where(['uid' => $this->uid,'a_no' => $rs['a_no']])->data(['status' => 1, 'pay_time' => date('Y-m-d H:i:s')])->save();
        	
            $this->apiReturn(1,['data' => '操作成功']);
        }
        $data=[
            'buyerID'       =>$this->user['erp_uid'],
            'buyerNick'     =>$this->user['nick'],
            'money'         =>$rs['money_pay'],
            'payType'       =>1,
            'subject'       =>'购买广告',
            'body'          =>'购买广告，投放时间'.$rs['num'].'天，合计'.$rs['money_pay'].'元',
            'orderID'       =>$rs['a_no']
        ];

        $need_sign='buyerID,buyerNick,money,payType,subject,body,orderID,parterId';
        $res=$this->erpApi('/addAdOrder.json',$data,$need_sign);

        if($res->code==1){
        	// 更改广告订单状态
        	$r = M('ad')->where(['uid' => $this->uid,'a_no' => $rs['a_no']])->data(['status' => 1, 'pay_time' => date('Y-m-d H:i:s')])->save();
        	
            $this->apiReturn(1,['data' => $res->info]);
        }else{
            $this->apiReturn($res->code,'',1,$res->info);
        }               
    }

    /**
    * 充值 - 创建充值订单
    * @param float 	$_POST['money']		充值金额
    * @param string	$_POST['r_no']		乐兑充值单号
    * @param int 	$_POST['paytype']	充值类型，2 支付宝 ，1 微信，5 工行POS, 6 微赢微信 ，7 微赢支付宝 ，8 银联
    */
    public function _recharge_add($param){
		$data 	=	[
			'money'			=>$param['money'],
			'userID'		=>$this->user['erp_uid'],
			'username'		=>$this->user['nick'],
			'memo'			=>'乐兑商城充值流水号:'.$param['r_no'],
			'rechargeType'	=>$param['paytype']
	    ];

		$need_sign='money,userID,username,memo,rechargeType,parterId';
		$res=$this->erpApi('/rechargeOrder.json',$data,$need_sign);
		return $res;
    }

    /**
    * 充值 - 更改状态
    * @param string $erp_no 充值异动号
    */
    public function _recharge_status($erp_no,$trade_no){
    	$data 	=	[
    		'orderID'	=>$erp_no,
    		'userID'	=>$this->user['erp_uid'],
    		'username'	=>$this->user['nick'],
    		'payState'	=>1,
    		'rechargeID'=>$trade_no

    	];
		$need_sign='orderID,userID,username,payState,rechargeID,parterId';
		$res=$this->erpApi('/updateRechargeOrder.json',$data,$need_sign);
		return $res;    	
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
		$res=json_decode($res);

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
			$logs['res']	=$res;
			log_add('erp_'.date('Ym'),$logs);
		}
		if (is_array($res)) $res = (object)$res;
		if($res->id==1001) $res->code=1;
		else $res->code=0;
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
		$data['parterId']=C('cfg.erp')['pid'];

		//针对addOrder.json接口
		if(isset($data['appKey'])) unset($data['parterId']);

		//清除不相关的数据
		foreach($this->apps_cfg as $key=>$val){
			if(isset($data[$key])) unset($data[$key]);
		}
		if(isset($data['sign']) && $sign_apiurl=='') unset($data['sign']);

		$arr=$data;
		if($need_sign){ //必签字段
			if(!is_array($need_sign)) $need_sign=explode(',',$need_sign);
			foreach($arr as $key=>$val){
				if(!in_array($key, $need_sign)) unset($arr[$key]);
			}
		}elseif($no_sign){	 //过滤不参加签名的字段
			if(!is_array($no_sign)) $no_sign=explode(',', $no_sign);
			foreach($no_sign as $val){
				if(isset($arr[$val])) unset($arr[$val]);
			}
		}

		ksort($arr);
		$data['signValue']=md5(http_build_query($arr).'&'.C('cfg.erp')['sign']);
		
		return $data;
	}
    
	
	/**
	 * 开店保证金
	 * @param string $_POST['opendi']	用户openid
	 * @param string $_POST['password_pay']	安全密码
	 */
	public function depositPays() {
	    //频繁请求限制
	    $this->_request_check();
	    //必传参数检查
	    $this->need_param=array('openid','password_pay','sign');
	    $this->_need_param();
	    $this->_check_sign();
	    $rs    =   M('shop_join_orders')->where(['uid' => $this->uid])->find();
	    //只有未付款的订单才可以删除
	    $this->check_password_pay(I('post.password_pay'));
	    $data=[
	        'userID'        =>$this->user['erp_uid'],
	        'username'      =>$this->user['nick'],
	        'money'         =>$rs['price'],
	        'payType'       =>1,
	        'subject'       =>'交付开店保证金',
	        'body'          =>'交付开店保证金',
	        'orderID'       =>$rs['o_no']
	    ];
	    $need_sign='userID,username,money,payType,subject,body,orderID,parterId';
	    $res=$this->erpApi('/outDeposit.json',$data,$need_sign);
	    if($res->code==1){
	        unset($data);
	        $model  =   D('Common/ShopJoinOrders');
	        $cData =   [
	           'pay_status'    =>  1,
	           'pay_money'     =>  $rs['price'],
	           'pay_time'      =>  date('Y-m-d H:i:s', NOW_TIME),
	           'sign'          =>  md5($this->uid . date('Y-m-d H:i:s', NOW_TIME) . C('CRYPT_PREFIX')), 
	        ];
	        $data =   $model->create($cData);
	        $model->where(['uid' => $this->uid, 'id' => $rs['id']])->save();
	        $this->apiReturn(1,['data' => $res->info]);
	    }else{
	        $this->apiReturn($res->code,'',1,$res->info);
	    }
	}
	
	/**
	 * 短信验证码验证
	 * @param string $_POST['opendi']	用户openid
	 * @param string $_POST['mobile']	手机号码
	 * @param string $_POST['smscode']	验证码
	 */
	public function checkSmsCode() {
	    ///smsCheck.json
	    //频繁请求限制
	    $this->_request_check();
	    //必传参数检查
	    $this->need_param=array('openid','mobile', 'smscode','sign');
	    $this->_need_param();
	    $this->_check_sign();
	    $data  =   [
	       'mobile'    =>  I('post.mobile'),
	       'smsCode'   =>  I('post.smscode'),
	    ];
	    $need_sign='mobile,smsCode,parterId';
	    $res=$this->erpApi('/smsCheck.json',$data,$need_sign);
	    
	    if($res->code==1001){
	       $this->apiReturn(1,['data' => $res->info]);
	    }else{
	        $this->apiReturn($res->code,'',1,$res->info);
	    }
	}
	
	/**
	 * 消费满升级检测
	 */
	public function checkUpgrade() {
	    $this->_request_check();
	    //必传参数检查
	    $this->need_param=array('openid','sign');
	    $this->_need_param();
	    $this->_check_sign();
	    $data  =   [
	       'userID'    =>  $this->user['erp_uid'],    
	    ];
	    $need_sign='userID,parterId';
	    $res=$this->erpApi('/upUserLevel.json',$data,$need_sign);
	    if($res->code==1){
	        $this->apiReturn(1,['data' => $res->info]);
	    }else{
	        $this->apiReturn($res->code,'',1,$res->info);
	    }
	}


	/**
	* ERP获取TOKEN，用于跳到商城自动登录
	* @param string $_POST['uid']			ERP用户UID
	* @param string $_POST['redirect_url']	要跳转的URL，选填
	*/
	public function token(){
	    //必传参数检查
	    $this->need_param=array('uid','sign');
	    if(I('post.redirect_url')) $this->need_param[] = 'redirect_url';
	    $this->_need_param();
	    $this->_check_sign();

	    $data['token'] = md5(implode(',',I('post.')));
	    S($data['token'],array('uid' => I('post.uid')),3600*2);	//有效期2小时

	    if(I('post.redirect_url')) {
	    	$data['redirect_url'] = C('sub_domain.oauth2').'/Erp/login?token='.$data['token'].'&redirect_url='.urlencode(I('post.redirect_url'));
	    }
	    
	    $this->apiReturn(1,['data' => $data]);
	}
	
	public function addSpecialMoney() {
	    //频繁请求限制
	    $this->_request_check();
	    //必传参数检查
	    $this->need_param=array('orderID','securityID','money','sign');
	    $this->_need_param();
	    $this->_check_sign();
	    $data  =   [
	        'orderID'      =>  I('post.orderID'),
	        'money'        =>  I('post.money'),
	        'securityID'   =>  I('securityID'),
	    ];
	    $need_sign='orderID,securityID,money,parterId';
	    $res   =   $this->erpApi('/addSpecialMoney.json', $data, $need_sign);
	    if($res->code==1){
	        $this->apiReturn(1,['data' => $res->info]);
	    }else{
	        $this->apiReturn($res->code,'',1,$res->info);
	    }
	}
	
	/**
	* 扫码--work商城自动登录
	* @param string $_POST['username']			ERP用户username
	* @param string $_POST['device_id']	        设备ID
	* @param string $_POST['code']	            memcached code
	*/
	public function admin_saoma(){
	    //必传参数检查
	    $this->need_param=array('code','username','device_id','sign');
		$do = M("admin");
        $data = S(I('post.code'));
        if(empty($data)) $this->apiReturn(4,'','二维码已失效！');

		$res = $do->where(['username'=>I('post.username')])->find();

		if($res){
			if($res['device_id'] && $res['device_id'] == I('post.device_id')){
				S('admin_'.$data['session_id'],$res);
		
				$this->apiReturn(1,['data' => $res]);
			}else{
				$data[device_id] = I('post.device_id');
				$result = $do->where(['username'=>I('post.username')])->save($data);
				if($result !== false){
					S('admin_'.$data['session_id'],$res);
					$this->apiReturn(1,['data' => $res]);
				}else{
					$this->apiReturn(0,['data' => $res]);
				}
			}
		}

	    $this->apiReturn(0,['data' => $res]);
	}
	
	/** 
	* 获取token
	* @param string $_POST['erp_uid']	用户的erp_uid
	*/
	public function user_token(){
		//频繁请求限制
		$this->_request_check();

		//必传参数检查
		$this->need_param=array('erp_uid','sign');
		$this->_need_param();
		$this->_check_sign();
		
		$data['userID']=I('post.erp_uid');
		$need_sign='userID,parterId';
		$res=$this->erpApi('/getUserToken.json',$data,$need_sign);
		//dump($res);
		if($res->code==1){
			$rs['token'] = $res->info;
			$length = strlen($res->info);
			if($length == 40){
				$rs['type'] = '2';	//安卓
			}else{
				$rs['type'] = '1';	//IOS
			}
			$this->apiReturn(1,['data'=>$rs]);
		}else{
			$this->apiReturn($res->code,'',1,$res->info);
		}	 
	}

    /**
     * 创建订单之前先提交官方优惠券到ERP
     * Create by lazycat
     * 2017-05-15
     * @param $coupon string json格式优惠券
     */
    public function put_coupon(){
        //必传参数检查
        $this->need_param=array('coupon','sign');
        $this->_need_param();
        $this->_check_sign();

        $data['coupon'] = html_entity_decode(I('post.coupon'));
        $need_sign      = 'parterId,coupon';
        $res            =$this->erpApi('/adCoupon.json',$data,$need_sign);

        $this->apiReturn($res->code,'',1,$res->info);
    }

    /**
     * 使用优惠券
     * Create by lazycat
     * 2017-05-15
     * @param $coupon string json格式优惠券
     */
    public function use_coupon(){
        //必传参数检查
        $this->need_param=array('coupon','sign');
        $this->_need_param();
        $this->_check_sign();

        $data['coupon'] = html_entity_decode(I('post.coupon'));
        $need_sign      = 'parterId,coupon';
        $res            =$this->erpApi('/useCoupon.json',$data,$need_sign);

        $this->apiReturn($res->code,'',1,$res->info);
    }

    /**
     * 查看优惠券状态
     * Create by lazycat
     * 2017-05-15
     * @param $s_no string 订单号
     */
    public function coupon_info(){
        //必传参数检查
        $this->need_param=array('s_no','sign');
        $this->_need_param();
        $this->_check_sign();

        $data['orderID']    = I('post.s_no');
        $need_sign          = 'parterId,orderID';
        $res                =$this->erpApi('/getCouponInfo.json',$data,$need_sign);

        $this->apiReturn($res->code,'',1,$res->info);
    }
	
	/**
     * 已到账金额订单列表
     * Create by liangfeng
     * 2017-06-02
     * @param $openid string 商家用户openid
     * @param $p string 页数
     * @param $pagesize string 每页显示数量
     * @param $sday string 开始时间 2017-04-07 21:53:31
     * @param $eday string 结束时间 2017-04-07 21:53:31
     */
	 
    public function get_received_orders_list(){
        //必传参数检查
        $this->need_param=array('openid','type','sign');
        $this->_need_param();
        $this->_check_sign();
		
	
		$data['userID'] = $this->user['erp_uid'];
		$data['page'] = isset($_POST['p']) ? I('post.p') : 1;
		$data['pageSize'] = isset($_POST['pagesize']) && $_POSt['pagesize'] < 500 ? I('post.pagesize') : 500;
		
		if(I('post.type') == 1){
			$data['beginTime'] = isset($_POST['sday']) ? I('post.sday') : date('Y-m-d H:i:s',(time()-86400*7));
			$data['endTime'] = isset($_POST['eday']) ? I('post.eday') : date('Y-m-d H:i:s',time());
			
			$need_sign          = 'userID,parterId,page,pageSize,beginTime,endTime';
			$res                =$this->erpApi('/getPaymentForGoodListByUserId.json',$data,$need_sign);
			
		}else if(I('post.type') == 2){
			$data['beginTime'] = isset($_POST['sday']) ? I('post.sday') : date('Y-m-d H:i:s',time()-86400);
			$data['endTime'] = isset($_POST['eday']) ? I('post.eday') : date('Y-m-d H:i:s',time());

			$need_sign          = 'userID,parterId,page,pageSize,beginTime,endTime';
			$res                =$this->erpApi('/getArrivedAccountForGoodsListByUserId.json',$data,$need_sign);
		}
        //$this->apiReturn($res->code,'',1,$res->info);
		if($res->code==1){
	        $this->apiReturn(1,['data' => $res->info]);
	    }else{
	        $this->apiReturn($res->code,'',1,$res->info);
	    }
    }

    /**
     * 乐兑确认订单
     * Create by liangfeng
     * 2017-08-15
     * @param $s_no string 订单号
     * @param $score_type int 全返类型 1.全返 2.不全返
     */
    public function orders_confirm2(){
        //必传参数检查
        $this->need_param=array('s_no','score_type','sign');
        $this->_need_param();
        $this->_check_sign();

        $data['orderID']    = I('post.s_no');
        $data['returnType']    = I('post.score_type') == 2 ? 1 : 2 ;
        $need_sign          = 'parterId,orderID,returnType';
        $res                =$this->erpApi('/confirmOrder.json',$data,$need_sign);

        if($res->id==1001){
            $this->apiReturn(1,['data' => $res->info]);
        }else{
            $this->apiReturn(0,'',1,$res->info);
        }
    }
}