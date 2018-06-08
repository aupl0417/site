<?php
/**
* 提成嘉奖计算
*/
namespace Admin\Controller;
use Think\Controller;
class RewardsController extends Controller {
	private $uid			;	//当前用户ID
	private $ulist			=array();	//上级用户列表
	private $base_money		=0;		//提成计算基数
	private $level8_cfg		=array();	//8层提成参数
	private $user8			=array();	//上8层用户ID

    /**
     * 架构函数
     * @access public
     * @param string $this->str  数据
     */
    public function __construct($uid) {
    	if(empty($uid)) E('缺少用户ID！');

    	$this->uid 			=$uid;
    	$this->ulist 		=$this->up_userlist();
    	$this->level_cfg 	=$this->level8_cfg();
    	$this->base_money 	=$this->base_money();
    	$this->user8		=$this->user8();
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
    * 取上级用户
    */
    public function up_userlist(){   	

    	$do=M('user_relation');
    	if($rs=$do->where(array('uid'=>$this->uid))->field('upuid_list')->find()){
    		$userlist=@explode(',',$rs['upuid_list']);
    		$count=count($userlist);
    		if($count>1){
    			rsort($userlist);	//倒序排列
    			return $userlist;
    		}else return false;
    	}else return false;
    }

    /**
    * 取得第n级用户
    */
    public function nlevel($level=1){
    	if(empty($this->ulist)) $this->up_userlist();
    	return $this->ulist[$level];
    }

    /**
    * 获取8层提成参数
    */
    public function level8_cfg(){
    	$do=M('reward_config');
    	$list=$do->getField('level,ratio,reward_type',true);
    	return $list;
    }

    /**
    * 取会员等级参数
    */
    public function level_cfg(){
    	$do=M('user_level');
    	$list=$do->getField('id,upgrade_money,team_ratio,upgrade_ratio,upuser_ratio',true);
    	return $list;
    }

    /**
    * 取上级代理用户ID
    */
    public function agent_uid(){
    	$do=M('user');
    	$list=$do->where(array('level_id'=>array('gt',2),'id'=>array('in',$this->ulist)))->getField('id');
    	return $list;
    }

    /**
    * 检查当前用户是否已计算过上级提成
    */
    public function is_reward(){
    	$do=M('user');
    	if($do->where(array('id'=>$this->uid,'is_reward'=>1,'level_id'=>2))->find()) return false;
    	else return true;
    }

    /**
    * 取用户上8层用户ID
    */
    public function user8(){
    	if(empty($this->ulist)) $this->up_userlist();

    	$user=array();
    	for($i=1;$i<=8;$i++){
    		if($this->ulist[$i]) $user[]=$this->ulist[$i];
    	}
    	return $user;
    }

    /**
    * 取提创业会员缴纳金额作为提成计算基数
    */
    public function base_money(){
    	$do=M('user_level');
    	$rs=$do->field('upgrade_money')->find(2);
    	return $rs['upgrade_money'];
    }

    /**
    * 计算提成
    */
    public function reward(){
    	//if($this->is_reward()==false) return false; //已计算过上级提成

    	$do=M();
    	$do->startTrans();
    	$sw=1;
    	foreach($this->user8 as $key=>$val){
    		$data=array();
    		$data['money']		=$this->level8_cfg[$key+1]['ratio'] * $this->base_money;
    		$data['from_uid']	=1;		//1为系统账户    		
    		$data['to_uid']    	=$val;
    		$data['status']		=2;
    		//前三级现金奖励
    		if($key<3){
    			$data['a_no']		=$this->create_orderno();
    			$data['from_flag']	=1;		//现金账户
    			$data['to_flag']	=1;

    			
    		}else{	//积分奖励
    			$data['b_no']		=$this->create_orderno();
    			$data['from_flag']	=2;		//佣金账户
    			$data['to_flag']	=2;
    		}
    	}

    	return true;

    }

	



}