<?php
/**
* 提成嘉奖计算
*/
namespace Common\Controller;
use Think\Controller;
class RewardController extends Controller {
	private $uid;	         //当前用户ID
    private $user;          //当前用户详情
    private $upgrade_time;   //当前用户升级时间
	private $ulist			=array();	    //上级用户列表
	private $base_money		=0;		         //提成计算基数
	private $layer8_cfg		=array();	    //8层提成参数,缓存60秒
	private $user8			=array();	    //上8层用户ID
    private $cfg            =array();       //缓存网站配置
    private $level_cfg      =array();       //等级配置参数
    private $up_agentid;                    //上级代理用户
	private $u_no 			='';			//升级订单号
	private $up_orders		=array();		//升级订单详情
    private $flag_arr=array(    //来源用户子账户1=现金账户,2=积分账户,3=理财账户,4=提现冻结
                1=>'ac_cash',
                2=>'ac_score',
                3=>'ac_finance',
                4=>'ac_cash_lock'
            );

    /**
     * 架构函数
     * @access public
     * @param integer 	$uid  用户ID
	 * @param string 	$u_no 升级订单号
     */
    public function __construct($uid,$u_no) {
    	if(empty($uid)) {
            //E('缺少用户ID！');
            $result['code']=103;
            return $result;
        }elseif(empty($u_no)) {
            //E('缺少用户ID！');
            $result['code']=105;
            return $result;
        }

    	$this->uid 			=$uid;
		$this->u_no 		=$u_no;
        $this->user         =$this->user();
        $this->upgrade_time =$this->upgrade_time();
    	$this->ulist 		=$this->up_userlist();
    	$this->layer8_cfg 	=$this->layer8_cfg();
    	$this->base_money 	=$this->base_money();
    	//$this->user8		=$this->user8();

        //站点配置
        $this->cfg=D('Common/Config')->config(array('cache_name'=>'cfg'));
        C('cfg',$cfg);

    }


	/**
	* 设置属性
	*/
	public function __set($name,$v){
		return $this->$name=$v;
	}

	/**
	* 获取属性
	*/
	public function __get($name){
		return isset($this->$name)?$this->$name:null;
	}
	
	/**
	* 销毁属性
	*/
    public function __unset($name) {
        unset($this->$name);
    }
	
	/**
	* 升级订单
	*/
	public function up_orders(){
		$do=M('user_upgrade_logs');
		if($rs=$do->where(array('u_no'=>$this->u_no))->field('id,status,money,level_id,is_reward_layer8,is_reward_agent')->find()){		
			$result['code']=1;
			$result['data']=$rs;
			$this->up_orders=$rs;
			if($rs['status']!=1) $result['code']=106;
		}else $result['code']=107;
		
		return $result;
	}

    /**
    * 当前用户详情
    */
    public function user(){
        $do=M('user');
        $rs=$do->where('id='.$this->uid)->field('atime,etime,ip',true)->find();
        return $rs;
    }

    /**
    * 当前用户升级时间
    */
    public function upgrade_time(){
        $do=M('user_upgrade_logs');
        $rs=$do->where(array('uid'=>$this->uid,'status'=>1))->field('upgrade_time')->find();
        return $rs['upgrade_time'];
    }

    /**
    * 取上级用户
    */
    public function up_userlist(){   	
    	$do=M('user_relation');
    	if($rs=$do->where(array('uid'=>$this->uid))->field('upuid_list')->find()){
    		$userlist=@explode(',',$rs['upuid_list']);
    		$count=count($userlist);
    		if($count>1){
    			rsort($userlist);	//倒序排列
                array_shift($userlist); //移除第一个元素,即移除自己
    			return $userlist;
    		}else return false;
    	}else return false;
    }

    /**
    * 取得第n级用户
    */
    public function nlevel($level=1){
    	if(empty($this->ulist)) $this->up_userlist();
    	return $this->ulist[$level-1];
    }

    /**
    * 获取8层提成参数
    */
    public function layer8_cfg(){
    	$do=M('reward_config');
    	$list=$do->cache(true,60)->getField('level,ratio,reward_type',true);
    	return $list;
    }

    /**
    * 取会员等级参数
    */
    public function level_cfg(){
    	$do=M('user_level');
    	$list=$do->cache(true,60)->getField('id,upgrade_money,team_ratio,upgrade_ratio,upuser_ratio',true);
    	return $list;
    }

    /**
    * 取上级代理用户ID,
    */
    public function agent_uid(){
        if(empty($this->ulist)) return false;

    	$do=M('user');
    	$list=$do->where(array('level_id'=>array('gt',2),'id'=>array('in',$this->ulist)))->order('id desc')->getField('id');
    	return $list;
    }

    /**
    * 检查当前用户是否已计算过上级提成
    */
    public function is_reward_layer8(){
        if($this->user['is_reward_layer8']==1) return false;
    	else return true;
    }


    /**
    * 取用户上8层用户ID
    */
    public function user8(){
        if(empty($this->ulist)) return false;

    	//$user=array();
    	//for($i=0;$i<8;$i++){
    		//if($this->ulist[$i]) $user[]=$this->ulist[$i];
    	//}


        $do=D('Common/User8Relation');
        $list=$do->relation(true)->relationWhere('user_upgrade_logs','status=1')->where(array('id'=>array('in',$this->ulist),'level_id'=>array('gt',1)))->field('id,level_id')->order('id desc')->limit(8)->select();
        //dump($list);
        return $list;
    }

    /**
    * 取提创业会员缴纳金额作为提成计算基数,用于8层提成计算
    */
    public function base_money(){
    	$do=M('user_level');
    	$rs=$do->cache(true,60)->field('upgrade_money')->find(2);
    	return $rs['upgrade_money'];
    }

    /**
    * 计算提成
    */
    public function reward(){
        if($this->user['level_id']!=2){
            //E('不是创业会员！');
            $result['code']=102;
            return $result;
        }

    	//if($this->is_reward_layer8()==false) return false; //已计算过上级提成
		//检查升级订单是否正常
		$ores=$this->up_orders();
		if($ores['code']!=1) return $ores;
		elseif($ores['data']['is_reward_layer8']==1) return false; //已计算过上级提成

        $this->user8=$this->user8();    //上8层用户
        if(empty($this->user8)) {   //没有上级用户
            $do=M('user');
            $do->where('id='.$this->uid)->setField('is_reward_layer8',1);
            return true;            
        }


    	$do=M();
        $n=0;   //统计达到提成要求的上级人数
        $k=0;  //统计完成提成的人数
    	foreach($this->user8 as $key=>$val){
            if($val['level_id']>1 && $val['upgrade']<$this->upgrade_time){  //必须比当前用户先升级才计算提成，此验证防止出错时可重新计算
                $n++;
                //前三级现金奖励
                if($key<3){
                    $res=$this->layer3($val,$key+1);
                    //dump($res);                 
                }else{
                    $res=$this->layer5($val,$key+1);
                }

                if($res['code']==1) $k++;
                $msg[]=$res;
            }
    	}

        if($k>0){   //可能会有部分用户账户存在异常(如被冻结或账户金额被篡改)，将直接跳过
            $do=M('user_upgrade_logs');
            $do->where(array('u_no'=>$this->u_no))->setField('is_reward_layer8',1);
            return true;
        }else{
            return false;
        }

    }

    /**
    * 前三层现金提成计算
    * @param array $val     待计算提成的用户信息
    * @param integer $layer 深度/层级
    */
    public function layer3($val,$layer){
        //如果已计算过嘉奖提成则直接返回

        $do=M('change_cash');
        if($do->where(array('uid'=>$val['id'],'type_id'=>1,'d_uid'=>$this->uid,'ordersno'=>$this->u_no))->field('id')->find()) {
            $result['code']=1;
            return $result;
        }

        $money=$this->layer8_cfg[$layer]['ratio'] * $this->base_money;

        //接受方账户是否正常
        $to_account=$this->check_account($val['id']);

        //转出方账户需要检查余额是否足够
        $from_account=$this->check_account(1);

        if($to_account['code']!=1) {
            $result['code']=$to_account['code'];
            return $result;
        }

        if($from_account['code']!=1) {
            $result['code']=$from_account['code'];
            return $result;
        }


        $from_account['data']['ac_cash']        -=$money;
        $to_account['data']['ac_cash']          +=$money;

        $from_account['data']['crc']=$this->crc($from_account['data']);
        $to_account['data']['crc']=$this->crc($to_account['data']);

        //转出方异动
        $data=array();
        $data['uid']            =1;
        $data['money']          =$money * -1;
        $data['a_no']           =$this->create_orderno();
        $data['status']         =2;     //状态
        $data['from_uid']       =1;     //1为系统账户
        $data['from_flag']      =1;     //现金账户
        $data['from_account']   =$from_account['data']['ac_cash'];

        $data['to_uid']         =$val['id']; 
        $data['to_flag']        =1;
        $data['to_account']     =$to_account['data']['ac_cash'];

        $data['type_id']        =1;     //下线升级创业会员现金奖励
        $data['d_uid']          =$this->uid;   
		$data['ordersno']		=$this->u_no;

        $do->startTrans();
        if($sw1=D('Common/ChangeCash')->token(false)->create($data)){
            $sw1=D('Common/ChangeCash')->add();
        }else{
            $msg[]=D('Common/ChangeCash')->getError();
            goto error;
        }        

        //接收方异动 
        $to_data=$data;
        $to_data['uid']            =$val['id'];
        $to_data['money']          =$money;
        $to_data['a_no']           =$this->create_orderno();

        $do->startTrans();
        if($sw2=D('Common/ChangeCash')->token(false)->create($to_data)){
            $sw2=D('Common/ChangeCash')->add();
        }else{
            $msg[]=D('Common/ChangeCash')->getError();
            goto error;
        }


        
        if(!$sw3=M('account')->where('uid='.$data['from_uid'])->save($from_account['data'])) goto error;        
        if(!$sw4=M('account')->where('uid='.$data['to_uid'])->save($to_account['data'])) goto error;

        success:
            $do->commit();
            $result['code']=1;
            return $result;     

        error:
            $do->rollback();
            $result['code']=0;
            $result['msg']='操作失败！'.@implode('<br>',$msg); 
            return $result;

    }

    /**
    * 后5层积分提成
    * @param array $val     待计算提成的用户信息
    * @param integer $layer 深度/层级
    */
    public function layer5($val,$layer){
        //如果已计算过嘉奖提成则直接返回

        $do=M('change_score');
        if($do->where(array('uid'=>$val['id'],'type_id'=>6,'d_uid'=>$this->uid,'ordersno'=>$this->u_no))->field('id')->find()) {
            $result['code']=1;
            return $result;
        }

        $money      =$this->layer8_cfg[$layer]['ratio'] * $this->base_money;

        //接受方账户是否正常
        $to_account=$this->check_account($val['id']);
        //转出方账户需要检查余额是否足够
        $from_account=$this->check_account(1);

        if($to_account['code']!=1) {
            $result['code']=$to_account['code'];
            return $result;
        }

        if($from_account['code']!=1) {
            $result['code']=$from_account['code'];
            return $result;
        }


        $from_account['data']['ac_score']        -=$money;
        $to_account['data']['ac_score']          +=$money;

        $from_account['data']['crc']=$this->crc($from_account['data']);
        $to_account['data']['crc']=$this->crc($to_account['data']);

        //转出方异动
        $data=array();
        $data['uid']            =1;
        $data['money']          =$money * -1;
        $data['c_no']           =$this->create_orderno();
        $data['status']         =2;     //状态
        $data['from_uid']       =1;     //1为系统账户
        $data['from_flag']      =2;     //积分账户
        $data['from_account']   =$from_account['data']['ac_score'];

        $data['to_uid']         =$val['id']; 
        $data['to_flag']        =2;
        $data['to_account']     =$to_account['data']['ac_score'];

        $data['type_id']        =6;     //下线升级创业会员积分奖励
        $data['d_uid']          =$this->uid;   
		$data['ordersno']		=$this->u_no;

        $do->startTrans();
        if($sw1=D('Common/ChangeScore')->token(false)->create($data)){
            $sw1=D('Common/ChangeScore')->add();
        }else{
            $msg[]=D('Common/ChangeScore')->getError();
            goto error;
        }        

        //接收方异动 
        $to_data=$data;
        $to_data['uid']            =$val['id'];
        $to_data['money']          =$money;
        $to_data['c_no']           =$this->create_orderno();

        $do->startTrans();
        if($sw2=D('Common/ChangeScore')->token(false)->create($to_data)){
            $sw2=D('Common/ChangeScore')->add();
        }else{
            $msg[]=D('Common/ChangeScore')->getError();
            goto error;
        }

        
        if(!$sw3=M('account')->where('uid='.$data['from_uid'])->save($from_account['data'])) goto error;        
        if(!$sw4=M('account')->where('uid='.$data['to_uid'])->save($to_account['data'])) goto error;

        success:
            $do->commit();
            $result['code']=1;
            return $result;     

        error:
            $do->rollback();
            $result['code']=0;
            $result['msg']='操作失败！'.@implode('<br>',$msg); 
            return $result;
    }

    /**
    * 升级为代理后，上级提成嘉奖计算
    */
    public function reward_agent(){
        //不是代理会员
        if($this->user['level_id']<3){
            $result['code']=104;
            return $result;
        }

        //是否已计算过代理提成
        //if($this->user['is_reward_agent']==1) return false;
		//检查升级订单是否正常
		$ores=$this->up_orders();
		if($ores['code']!=1) return $ores;
		elseif($ores['data']['is_reward_agent']==1) return false; //已计算过上级提成		
		
        $this->level_cfg  =$this->level_cfg();    //等级配置
        $this->up_agentid =$this->agent_uid();    //取最近上级代理用户ID

        //当前用户获得升级代理积分奖励
        $res1=$this->my_upgrade_score();
		
		$n=0;
		if($res1['code']==1) $n++;
		
		

        if(empty($this->up_agentid)){
            $n++;
        }else{
            $res2=$this->reward_agentid();
			if($res2['code']==1) $n++;
        }
		
	
		if($n==2){
            $do=M('user_upgrade_logs');
            $do->where(array('u_no'=>$this->u_no))->setField('is_reward_agent',1);
            return true;
		}

        return false;
    }

    /**
    * 当前用户获得升级代理积分奖励
    */
    public function my_upgrade_score(){
        //是否已计算过奖励
        $do=M('change_score');
        if($do->where(array('uid'=>$this->uid,'type_id'=>7,'ordersno'=>$this->u_no))->field('id')->find()){
            $result['code']=1;
            return $result;
        }

        $money=$this->level_cfg[$this->up_orders['level_id']]['upgrade_ratio'] * $this->level_cfg[$this->up_orders['level_id']]['upgrade_money'];

        //接受方账户是否正常
        $to_account=$this->check_account($this->uid);
        //转出方账户需要检查余额是否足够
        $from_account=$this->check_account(1);

        if($to_account['code']!=1) {
            $result['code']=$to_account['code'];
            return $result;
        }

        if($from_account['code']!=1) {
            $result['code']=$from_account['code'];
            return $result;
        }


        $from_account['data']['ac_score']        -=$money;
        $to_account['data']['ac_score']          +=$money;     
        $from_account['data']['crc']            =$this->crc($from_account['data']);   
        $to_account['data']['crc']              =$this->crc($to_account['data']);

        //转出方异动
        $data=array();
        $data['uid']            =1;
        $data['money']          =$money * -1;
        $data['c_no']           =$this->create_orderno();
        $data['status']         =2;     //状态
        $data['from_uid']       =1;     //1为系统账户
        $data['from_flag']      =2;     //积分账户
        $data['from_account']   =$from_account['data']['ac_score'];

        $data['to_uid']         =$this->uid; 
        $data['to_flag']        =2;
        $data['to_account']     =$to_account['data']['ac_score'];

        $data['type_id']        =7;     //升级代理本人获得积分奖励
        $data['d_uid']          =$this->uid;   
		$data['ordersno']		=$this->u_no;

        $do->startTrans();
        if($sw1=D('Common/ChangeScore')->token(false)->create($data)){
            $sw1=D('Common/ChangeScore')->add();
        }else{
            $msg[]=D('Common/ChangeScore')->getError();
            goto error;
        }        

        //接收方异动 
        $to_data=$data;
        $to_data['uid']            =$this->uid;
        $to_data['money']          =$money;
        $to_data['c_no']           =$this->create_orderno();

        $do->startTrans();
        if($sw2=D('Common/ChangeScore')->token(false)->create($to_data)){
            $sw2=D('Common/ChangeScore')->add();
        }else{
            $msg[]=D('Common/ChangeScore')->getError();
            goto error;
        }
        
        if(!$sw3=M('account')->where('uid='.$data['from_uid'])->save($from_account['data'])) goto error;        
        if(!$sw4=M('account')->where('uid='.$data['to_uid'])->save($to_account['data'])) goto error;

        success:
            $do->commit();
            $result['code']=1;
            return $result;     

        error:
            $do->rollback();
            $result['code']=0;
            $result['msg']='操作失败！'.@implode('<br>',$msg); 
            return $result;      
    }

    /**
    * 升级成为代理后上级代理奖励计算
    */
    public function reward_agentid(){
        //如果已计算过嘉奖提成则直接返回
        $do=M('change_cash');
        if($rs=$do->where(array('uid'=>$this->up_agentid,'type_id'=>2,'d_uid'=>$this->uid,'ordersno'=>$this->u_no))->field('id')->find()) {
            $result['code']=1;
            return $result;
        }
		

        $money      =$this->level_cfg[$this->up_orders['level_id']]['upuser_ratio'] * $this->level_cfg[$this->up_orders['level_id']]['upgrade_money'];
		
        //接受方账户是否正常
        $to_account=$this->check_account($this->up_agentid);
        //转出方账户需要检查余额是否足够
        $from_account=$this->check_account(1);

        if($to_account['code']!=1) {
            $result['code']=$to_account['code'];
            return $result;
        }

        if($from_account['code']!=1) {
            $result['code']=$from_account['code'];
            return $result;
        }


        $from_account['data']['ac_cash']        -=$money;
        $to_account['data']['ac_cash']          +=$money;     
        $from_account['data']['crc']            =$this->crc($from_account['data']);   
        $to_account['data']['crc']              =$this->crc($to_account['data']);

        //转出方异动
        $data=array();
        $data['uid']            =1;
        $data['money']          =$money * -1;
        $data['a_no']           =$this->create_orderno();
        $data['status']         =2;     //状态
        $data['from_uid']       =1;     //1为系统账户
        $data['from_flag']      =1;     //现金账户
        $data['from_account']   =$from_account['data']['ac_cash'];

        $data['to_uid']         =$this->up_agentid; 
        $data['to_flag']        =1;
        $data['to_account']     =$to_account['data']['ac_cash'];

        $data['type_id']        =2;     //下线升为代理现金奖励
        $data['d_uid']          =$this->uid;   
		$data['ordersno']		=$this->u_no;

        $do->startTrans();
        if($sw1=D('Common/ChangeCash')->token(false)->create($data)){
            $sw1=D('Common/ChangeCash')->add();
        }else{
            $msg[]=D('Common/ChangeCash')->getError();
            goto error;
        }        

        //接收方异动 
        $to_data=$data;
        $to_data['uid']            =$this->up_agentid;
        $to_data['money']          =$money;
        $to_data['a_no']           =$this->create_orderno();

        $do->startTrans();
        if($sw2=D('Common/ChangeCash')->token(false)->create($to_data)){
            $sw2=D('Common/ChangeCash')->add();
        }else{
            $msg[]=D('Common/ChangeCash')->getError();
            goto error;
        }

        
        if(!$sw2=M('account')->where('uid='.$data['from_uid'])->save($from_account['data'])) goto error;
        if(!$sw3=M('account')->where('uid='.$data['to_uid'])->save($to_account['data'])) goto error;

        //if(!$sw4=M('user')->where(array('id'=>$this->uid))->setField('is_reward_agent',1)) goto error;

        success:
            $do->commit();
            $result['code']=1;
            return $result;     

        error:
            $do->rollback();
            $result['code']=0;
            $result['msg']='操作失败！'.@implode('<br>',$msg); 
            return $result;

    }
	

}