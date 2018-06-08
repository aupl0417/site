<?php
/**
+----------------------------------------------------------------------
| RestFull API 
+----------------------------------------------------------------------
| 公共文件
+----------------------------------------------------------------------
| Author: lazycat <673090083@qq.com>
+----------------------------------------------------------------------
*/
namespace Rest\Controller;
use Think\Controller\RestController;
class CommonController extends RestController {
	protected $allowMethod    	= array('get','post','put'); // REST允许的请求类型列表
	protected $allowType      	= array('html','xml','json'); // REST允许请求的资源类型列表
	protected $apps_cfg		  	= array();	//应用接口信息
    protected $need_param   	=array();   //要进行签名的键名
    protected $sw           	=array();   //事务执行结果	
    protected $uid				=0;			//用户UID
    protected $seller_id		=0;			//卖家UID
	protected $user 			=array();	//用户信息
    protected $password_pay		='';		//支付密码
    protected $stime 			=0;			//开始执行时间
    protected $action_logs		=array();	//须要记录日志的方法
    protected $api_cfg; //=$apps_cfg 兼容前端，
    protected $post;   //接口传参数据，即$_POST数据
    protected $ip;
    protected $flag_arr=array(    //来源用户子账户1=现金账户,2=积分账户,3=理财账户
                1=>'ac_cash',
                2=>'ac_score',
                3=>'ac_finance',
                4=>'ac_cash_lock'
            );

    protected $flag_name=array( 
                1=>'现金账户',
                2=>'积分账户',
                3=>'理财账户',
                4=>'提现冻结'
            );    

	public function _initialize() {
		//header('Access-Control-Allow-Origin: *');
        $this->ip = get_client_ip();
		G('begin');
		//各频道子域名
		C('sub_domain',sub_domain());

		//站点配置
		$cfg=D('Common/Config')->config(array('cache_name'=>'cfg'));
		C('cfg',$cfg);

		//加载错误代码库
		//S('error_code',null);
		$error_code=D('Common/ErrorCode')->error_code();
		C('error_code',$error_code);

        //订单状态代码
        $do=D('Common/OrdersCodeRelation');
        $orders_code=$do->relation(true)->cache('orders_code',C('CACHE_LEVEL.OneDay'))->field('id')->select();
        $norders_code=array();
        foreach($orders_code as $val){
            $norders_code[$val['id']]=$val['orders_code'];
        }
        C('orders_code',$norders_code);

		//访问授权检查
        $this->_need_param(array('appid','access_key','secret_key','sign_code'),$this->_apps_param());		
		$res=D('Common/AppsUser')->user_check($this->_apps_param());
		if($res['code']!=10) $this->response($res);

		$this->apps_cfg['appid']		=$res['data']['appid'];
		$this->apps_cfg['access_key']	=$res['data']['access_key'];
		$this->apps_cfg['secret_key']	=$res['data']['secret_key'];
		$this->apps_cfg['sign_code']	=$res['data']['sign_code'];
        $this->api_cfg = $this->apps_cfg;

		if(I('post.openid')) {
			$user=$this->_uid(I('post.openid'));
			$this->user=$user;
			$this->uid=$user['id'];			
			$this->password_pay=$user['password_pay'];
		}

		//file_put_contents('tt.txt',var_export($this->apps_cfg,true));

		//需要验证店铺权限的卖家模块		
		$seller_contorller=['SellerAd','SellerGoods','SellerOrders','SellerRefund','SendAddress','SellerRate','ShopSetting'];

		if(in_array(CONTROLLER_NAME,$seller_contorller)){
			if(empty($user)) $this->apiReturn(650);	//请先登录！
			if($user['shop_type']==0) $this->apiReturn(651);	//您还未开店，无权限操作！
		}
		
        $this->post = I('post.');
	}

	/**
	* 获取$_POST数据中的应用授权参数
	*/
	public function _apps_param($data=null){
		$data=!is_null($data)?$data:I('post.');
		$keys=array('appid','access_key','secret_key','sign_code');
		foreach($data as $key=>$val){
			if(in_array($key,$keys)){
				$result[$key]=$val;
				//unset($_POST[$key]);
			}
		}
		return $result;
	}

	/**
	* 生成签名
	* @param array $data 要进行签名的数据
	* @param string $field 要进行签字的键名,为空是表是所有key
	* @param integer $type　1表示$field为不进行签名的Key
	*/
	public function _sign($data,$field='',$type=1){
		if(isset($data['sign'])) unset($data['sign']);
		//清除不进行签名的字段
		if(!empty($field) && $type==1){
			$field=is_array($field)?$field:array($field);
			foreach($data as $key=>$val){
				if(in_array($key, $field)) unset($data[$key]);
			}
		}elseif(!empty($field) && $type!=1){
			$field=is_array($field)?$field:array($field);
			foreach($data as $key=>$val){
				if(!in_array($key, $field)) unset($data[$key]);
			}
		}

		//dump($data);

		ksort($data);
		$query=http_build_query($data).'&'.$this->apps_cfg['sign_code'];
		$query=urldecode($query);
		return md5($query);
	}

	/**
	* 检查签名是否正确
	* @param string $_POST['appid']			应用ID
	* @param string $_POST['access_key']	接口access_key
	* @param string $_POST['secret_key']	接口secret_key
	* @param string $_POST['sign_code']		用于签名的加密字符串
	*/
	public function _check_sign(){
		$need_param=array_merge(array('appid','access_key','secret_key','sign_code'),$this->need_param);
		$sign=$this->_sign($_POST,$need_param,2);
		//dump($_POST);
		//dump($need_param);
		//var_dump($sign);
        //签名检查
        if(I('post.sign')!=$sign){
            //签名错误
            $this->apiReturn(2);         
        }

	}

	/**
	* 防止重复请求
	* @param integer $appid	应用ID
	* @param inteter $time 重复请求间隔,单位秒,1秒=1000毫秒,默认为0.3秒=300毫秒
	*/
	public function _request_check($time=0.3){
		$need_param=array_merge(array('appid','access_key','secret_key','sign_code'),$this->need_param);
		$sign=$this->_sign($_POST,$need_param,2);

		$microtime=microtime(true);
		//$cache_name=md5($sign.'_'.get_client_ip().'_'.__SELF__);
		$cache_name=md5($sign.'_'.__SELF__.implode(',',$_POST));
		$cache_time=S($cache_name);
		
		//file_put_contents('t.txt',$microtime.'-'.$cache_time.'<br>',FILE_APPEND);
		//$fp=fopen('b.txt','a+');
		//fwrite($fp,$this->uid.'['.$cache_name.']-['.$sign.'_'.get_client_ip().'_'.__SELF__.']-'.$microtime.'-'.$cache_time.'='.($microtime-$cache_time).chr(13).chr(10));
		//fclose($fp);

		if($cache_time>0 && ($microtime-$cache_time < $time)){
			$this->apiReturn(15);			
		}
		
		S($cache_name,$microtime,10);

	}

	/**
	* 检查必传参数
	* @param array $nkey 必传参数的key
	* @param array $param 参数
	*/
	public function _need_param($nkey='',$param=''){
		$nkey=$nkey?$nkey:$this->need_param;
		//$nkey=is_array($nkey)?$nkey:explode(',', $nkey);

		$param=$param?$param:I('post.');
		$res=need_param($nkey,$param);
		if($res['code']!=1){
			$this->apiReturn(13,'',1,C('error_code')[13].$res['nokey']);
		}
	}

	/**
	* 接口返回，方便记录日志
	* @param integer 	$code 	错误代码
	* @param string 	$msg 	错误信息
	* @param integer 	$is_break 是否中间并返回json
	* @param array 		$data 	要一并返回的数据
	* @param string 	$msg 	自定义错误信息
	*/
	public function apiReturn($code,$data=array(),$is_break=1,$msg=''){
		$result['code']=(string) $code;
		$result['msg']=$msg?$msg:C('error_code')[$code];
		if(!empty($data)) $result=array_merge($result,$data);

		G('end');
		//在此记录日志，方便接口错误调试
		if(C('API_LOG') && in_array(ACTION_NAME,$this->action_logs)){
			$logs['atime']	=date('Y-m-d H:i:s');
			$logs['ip']		=$this->ip;
			$logs['nick']	=$this->user['nick'];		
			$logs['code']	=$result['code'];
			$logs['msg']	=$result['msg'];
			$logs['dotime']	=G('begin','end');
			$logs['url']	=($_SERVER['HTTPS']=='on'?'https://':'http://').$_SERVER['HTTP_HOST'].__SELF__;
			$logs['sw']		=@implode(',',$this->sw);
			$logs['post']	=@var_export(I('post.'),true);
			$logs['res']	=@var_export($data,true);

			log_add('api_'.date('Ym'),$logs);
		}


		if($is_break==1 || is_null($is_break)) $this->response($result);
		return $result;
	}


    /**
    * 检查账户是否正常
    * @param integer $uid 	用户ID
    * @param integer $flag 	子账户标记
    * @param float 	$money 	金额
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
                    $this->apiReturn(85);
                break;            	
                case 0:
                    //账户被冻结
                    $this->apiReturn(83);
                break;
                case 2:
                    //账户已注销
                    $this->apiReturn(84);
                break;
                case 6:
                    //余额不足
                    $this->apiReturn(86);
                break;
                default:
                    //$this->apiReturn(1,array('data'=>$data));
                	return $data;
                break;
            }

        }else{
            //账户不存在
            $this->apiReturn(82);
        }
    }	

    /**
    * 根据用户openid获取uid
    * @param string $openid 	用户的openid
    */
    public function _uid($openid){
    	$do=M('user');
    	if($rs=$do->where(array('openid'=>$openid))->field('id,level_id,nick,password_pay,is_auth,shop_type,erp_uid,shop_id')->find()){
    		return $rs;
    	}else{
    		//用户不存在
    		$this->apiReturn(8);
    	}
    }

    /**
    * 检查支付密码
    * @param string $password 用$this->password处理过的密码
    */
    public function check_password_pay($password){
    	$max = 5;
    	# 是否已冻结2小时
		$key = 'passowrd_pay_errors' . $this->uid;
		$s = S($key);
		if($s && $s >= $max){
			$this->apiReturn(0,['msg' => '您已10分钟内输错' . $max . '次安全密码，已被冻结2小时！']);
		}

    	$res = A('Erp')->_check_pay_password($password);

        # 安全密码错误
        if($res->code != 1){
        	# 记录错误次数
            if($s){
            	$s += 1;
            	if($s >= $max){
            		S($key, $s, 3600 * 2);
            	}else{
            		S($key, $s, 600);
            	}
            }else{
            	$s = 1;
            	S($key, $s, 600);
            }
            if($s == $max){
            	$this->apiReturn(6, ['msg' => '您已10分钟内输错' . $max . '次安全密码，已被冻结2小时！']);
            }else{
            	$this->apiReturn(6, ['msg' => '安全密码错误，还有' . ($max - $s) . '次机会']);
            }
        }
    }


    /**
    * 统一请求入口
    * 2016-08-02 更新
    * @param string $method 	要执行的方法
    * @param string|array 		要签名的字段
    * @param int 	$reauire_check 防刷
    * @param int 	$require_check_time 防刷间隔（秒）
    * @param int 	$is_sign 	是否验证签名
    */
    public function _api($param){
        $method='_'.$param['method'];
        $field_cfg=$this->_sign_field($method);

        $require_check = 1;
        $require_check_time = 0.3;
        if(is_array($field_cfg)){
        	$sign 			= $field_cfg['field'];        	
        	$require_check 	= is_null($field_cfg['require_check'])?1:$field_cfg['require_check'];
        	$require_check_time 	= is_null($field_cfg['require_check_time'])?0.3:$field_cfg['require_check_time'];
        	$not_sign 		= $field_cfg['not_sign'];
        }else{
        	$sign=$field_cfg;
        }

        //频繁请求限制,间隔300毫秒
        if($require_check==1) $this->_request_check($require_check_time);        

        //必传参数检查
        if($not_sign!=1){
        	//$sign=$param['sign']?(is_array($param['sign'])?$param['sign']:explode(',', $param['sign'])):$this->_sign_field($method);
	        if(empty($sign)) $sign=array('sign');
	        else $sign = is_array($sign)?$sign:explode(',', $sign);
	        $this->need_param=$sign;
	        $this->_need_param();
	        $this->_check_sign();
    	}

        $res=$this->$method();

        $this->apiReturn($res['code'],['data' => $res['data']],$res['is_break'],$res['msg']);
    }



}