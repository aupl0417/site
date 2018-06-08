<?php
/*
+----------------------------
+ 会员升级
+-----------------------------
*/
namespace Rest\Controller;
use Think\Controller\RestController;
class UserUpgradeController extends CommonController {
    public function index(){
    	redirect(C('sub_domain.www'));
    }


    //缴纳升级费用步骤一：创建订单
    public function upgrade_create(){
        //频繁请求限制,间隔2秒
        $this->_request_check();

        //必传参数检查
        if(I('post.level')<3) $this->need_param=array('openid','level','sign');
        else $this->need_param=array('openid','level','city_id','sign');
        $this->_need_param();
        $this->_check_sign();

        
        $do=M('user');
        $urs=$do->where(array('id'=>$this->uid))->field('level_id')->find();
        if(!$urs){
            //用户不存在
            $this->apiReturn(97);            
        }
		//elseif($urs['level_id']>2){
            //如果已是代理，不可再升级
            //$this->apiReturn(95);
        //}
		elseif($urs['level_id']<2 && I('post.level')>2){
			//必须是创业会员才可以升级成为代理会员
			$this->apiReturn(100);
		}

        //如果申请的地区已有代理商，不可提交
        if(I('post.level')>2){
            //检查代理级别对应的地区层次
            $city_layer=array(3=>4,4=>3,5=>2,6=>1);
            $city_ids=upsid(array('table'=>'area','id'=>I('post.city_id')));
            if(count($city_ids)!=$city_layer[I('post.level')]) $this->apiReturn(98);


            $do=M('user_upgrade_logs');
            if($agent=$do->where(array('city_id'=>I('post.city_id'),'status'=>1))->field('id')->find()){
                //升级失败！您申请的地区已有代理商！
                $this->apiReturn(96);
            }
            $data['city_id']=I('post.city_id');
        }

        $lrs=M('user_level')->cache(true,60)->where(array('id'=>I('post.level')))->field('upgrade_money')->find();

       
        $data['level_id']=I('post.level');
        $data['uid']=$this->uid;   
        

        $do=D('Common/UpgradeLogsLevelRelation');
        $map=$data;
        if($rs=$do->relation(true)->where($map)->field('etime,ip',true)->find()){
            if($rs['status']==0) $this->apiReturn(1,array('data'=>$rs));
            else $this->apiReturn(93);  //升级出错，该级别已交纳过费用，请联系客服
        }else{
            $do=D('Common/UserUpgradeLogs');
            $data['u_no']=$this->create_orderno();
            $data['money']=$lrs['upgrade_money'];
            if($do->create($data)){
                if($do->add()){
                    //创建订单成功
                    $insid=$do->getLastInsID();
                    $rs=D('Common/UpgradeLogsLevelRelation')->relation(true)->where(array('id'=>$insid))->field('etime,ip',true)->find();
                    $this->apiReturn(1,array('data'=>$rs));
                }else{
                    //创建订单失败
                    $this->apiReturn(0);
                }
            }else{
                //数据验证失败
                $this->apiReturn(4,'',1,$do->getError());
            }
        }
    }

    /**
    * 升级订单详情
    */
    public function view(){
        //必传参数检查
        $this->need_param=array('openid','u_no','sign');
        $this->_need_param();
        $this->_check_sign(); 

        $do=D('Common/UpgradeLogsLevelRelation');
        $rs=$do->relation(true)->where(array('u_no'=>I('post.u_no'),'uid'=>$this->uid))->field('etime,ip',true)->find();
        if($rs){
			if($rs['city_id']>0) $rs['city']=nav_sort(array('table'=>'area','field'=>'id,sid,a_name','id'=>$rs['city_id'],'key'=>'a_name','icon'=>' - ','cache_name'=>'city_id_'.$rs['city_id']));
            $this->apiReturn(1,array('data'=>$rs));
        }else{
            $this->apiReturn(0);
        }
        

    }
	

    /**
    * 升级步骤二：支付升级费用
    */
    public function upgrade_pay(){
        //频繁请求限制,间隔2秒
        $this->_request_check();

        //必传参数检查
        $this->need_param=array('openid','u_no','password_pay','sign');
        $this->_need_param();
        $this->_check_sign();

        //验证支付密码
        $do=M('user');
        $urs=$do->where(array('id'=>$this->uid))->field('password_pay')->find();
        if(md5(I('post.password_pay'))!=$urs['password_pay']){
            //支付密码错误
            $this->apiReturn(6);
        }

        $do=M('user_upgrade_logs');
        if($rs=$do->where(array('uid'=>$this->uid,'u_no'=>I('post.u_no')))->field('id,status,u_no,level_id,money,city_id')->find()){
            if($rs['status']==1) $this->apiReturn(94); //订单已支付，不可再次操作！
			
            if($rs['level_id']>2){
				if($do->where(array('city_id'=>$rs['city_id'],'status'=>1))->field('id')->find()){
					//升级失败！您申请的地区已有代理商！
					$this->apiReturn(96);
				}
			}

            //检查用户账户是否正常、余额是否足够
            $u_account=$this->check_account($this->uid,1,$rs['money']);
            $a_account=$this->check_account(1); //系统账号

            //扣费
            $do=M();
            $do->startTrans();

            $a_account['ac_cash']+=$rs['money'];
            $a_account['crc']=$this->crc($a_account);

            $u_account['ac_cash']-=$rs['money'];
            $u_account['crc']=$this->crc($u_account);

            //dump($u_account);
            //dump($a_account);

            //更新账户
            $sw1=M('account')->where(array('uid'=>$this->uid))->save($u_account);
            $sw2=M('account')->where(array('uid'=>1))->save($a_account);

            //dump(M('account')->getLastSQL());

            //用户异动记录 从用户转到系统账户
            $data=array();
            $data['a_no']         =$this->create_orderno();
            $data['uid']          =$this->uid;
            $data['money']        =$rs['money']*-1;
            $data['from_uid']     =$this->uid;
            $data['from_account'] =$u_account['ac_cash'];
            $data['from_flag']    =1; //现金账户

            $data['to_uid']       =1;
            $data['to_account']   =$a_account['ac_cash'];
            $data['to_flag']      =1;  //现金账户

            $data['status']       =2;
            $data['type_id']      =5; //会员升级缴费
            $data['ordersno']     =$rs['u_no'];

            if($sw3=D('Common/ChangeCash')->create($data)) $sw3=D('Common/ChangeCash')->add();
            else {
                goto error;
            }

            //admin异动记录 从用户转到系统账户
            $a_data=$data;
            $a_data['a_no']         =$this->create_orderno();
            $a_data['uid']          =1;
            $a_data['money']        =$rs['money'];

            if($sw4=D('Common/ChangeCash')->create($a_data)) $sw4=D('Common/ChangeCash')->add();
            else {
                goto error;
            }

            //dump(D('Common/ChangeCash')->getError());
			//记录最高会员级别
			if($this->user['level_id'] < $rs['level_id']){
				if(!$sw5=M('user')->where(array('id'=>$this->uid))->setField('level_id',$rs['level_id'])){
					goto error;
				}
			}
            if(!$sw6=M('user_upgrade_logs')->where(array('id'=>$rs['id']))->setField(array('status'=>1,'pay_time'=>date('Y-m-d H:i:s'),'upgrade_time'=>date('Y-m-d H:i:s')))){
                goto error;
            }
			
            success:
                $do->commit();
                //计算提嘉将，后续如果数据太庞大可将8层提成嘉奖丢入队列或临时表让程序自动处理
                $tc=new \Common\Controller\RewardController($this->uid,$rs['u_no']);
                if($rs['level_id']==2) $tc->reward();  //创业会员8层提成嘉奖
                else $tc->reward_agent();    //代理提成嘉奖

                $this->sw=array($sw1,$sw2,$sw3,$sw4,$sw5,$sw6); 
                $this->apiReturn(1);

            error:
                $do->rollback();
                $this->sw=array($sw1,$sw2,$sw3,$sw4,$sw5,$sw6);
                $this->apiReturn(0);            

        }else{
            //找不到订单记录
            $this->apiReturn(0);
        }


    }

    /**
    * 检查某地区是否已存在代理会员
    */
    public function check_city_agent(){
        //频繁请求限制,间隔2秒
        $this->_request_check();

        //必传参数检查
        $this->need_param=array('city_id','sign');
        $this->_need_param();
        $this->_check_sign();

        $do=D('UpgradeUserRelation');
        $rs=$do->relation(true)->where(array('city_id'=>I('post.city_id'),'status'=>1))->field('uid,level_id')->find();

        if($rs){
            //该地区已有代理用户！
            unset($rs['uid']);
            unset($rs['level_id']);
            $rs['mobile']=hiddenStr($rs['mobile']);
            $this->apiReturn(99,array('data'=>$rs));
        }else{
            $this->apiReturn(1);
        }

    }
	
	
	/**
	* 升级订单列表
	* @param string $_POST['openid'] 	用户openid
	* @param int 	$_POST['status']	订单状态
	* @param int 	$_POST['level']		等级ID
	*/
	public function orders_list(){
        //频繁请求限制,间隔2秒
        $this->_request_check();

        //必传参数检查
        $this->need_param=array('openid','sign');
        $this->_need_param();
        $this->_check_sign();
		
		$map['uid']=$this->uid;
		if(I('post.status')!='') $map['status']=I('post.status');
		if(I('post.level')) $map['level_id']=array('in',I('post.level'));
		
		$do=D('Common/UpgradeUserRelation');
		$list=$do->relation('level')->where($map)->field('ip',true)->order('id desc')->select();
		if($list){
			foreach($list as $key=>$val){
				if($val['city_id']>0){
					$list[$key]['city']=nav_sort(array('table'=>'area','field'=>'id,sid,a_name','id'=>$val['city_id'],'key'=>'a_name','icon'=>' - ','cache_name'=>'city_id_'.$val['city_id']));
				}
				
				$list[$key]['status_name']=$val['status']==1?'已付款':'待付款';
			}
			$this->apiReturn(1,['data'=>$list]);
		}else{
			$this->apiReturn(3);
		}
	}
	
	
	/**
	* 查看等级权益
	*/
	public function level_about(){
        //频繁请求限制,间隔2秒
        $this->_request_check();

        //必传参数检查
        $this->need_param=array('level','sign');
        $this->_need_param();
        $this->_check_sign();
		
		$do=M('user_level');
		$rs=$do->where(array('id'=>I('post.level')))->field('atime,etime,ip',true)->find();
		
		if($rs){
			$rs['about']=html_entity_decode($rs['about']);
			$this->apiReturn(1,array('data'=>$rs));
		}else{
			$this->apiReturn(0);
		}
	}
	
	/**
	* 获取代理等级
	*/
	public function agent_level(){
        //频繁请求限制,间隔2秒
        $this->_request_check();

        //必传参数检查
        $this->need_param=array('sign');
        $this->_need_param();
        $this->_check_sign();
			
		$do=M('user_level');
		$list=$do->where(array('id'=>array('gt',2)))->field('id,level_name,icon')->order('sort asc')->select();
		if($list){
			$this->apiReturn(1,array('data'=>$list));
		}else{
			$this->apiReturn(3);
		}
	}
	
	/**
	* 会员升级为创业会员并缴费
	* @param string $_POST['openid'] 用户openid
	* @param number $_POST['password_pay'] 安全密码
	*/
	public function upgrade_create_pay(){
        //频繁请求限制,间隔2秒
        $this->_request_check();

        //必传参数检查
        $this->need_param=array('openid','password_pay','sign');
        $this->_need_param();
        $this->_check_sign();
		
	
        if(!$this->user){
            //用户不存在
            $this->apiReturn(97);            
        }elseif($this->user['level_id']!=1){
			//已是创业会员，不可再次升级
			$this->apiReturn(101);
		}
		
        //验证支付密码
        if(md5(I('post.password_pay'))!=$this->user['password_pay']){
            //支付密码错误
            $this->apiReturn(6);
        }		

        $lrs=M('user_level')->cache(true,60)->where(array('id'=>2))->field('upgrade_money')->find();

       
        $map['level_id']=2;
        $map['uid']=$this->uid;        

        $do=M('user_upgrade_logs');
        if($rs=$do->where($map)->field('etime,ip',true)->find()){
            if($rs['status']==1) $this->apiReturn(93);  //升级出错，该级别已交纳过费用，请联系客服
        }else{
			$do=D('Common/UserUpgradeLogs');
			$rs=$map;
            $rs['u_no']=$this->create_orderno();
            $rs['money']=$lrs['upgrade_money'];
            if($do->create($rs)){
                if($do->add()){
                    //创建订单成功
                    $rs['id']=$do->getLastInsID();
                }else{
                    //创建订单失败
                    $this->apiReturn(0);
                }
            }else{
                //数据验证失败
                $this->apiReturn(4,'',1,$do->getError());
            }			
		}
		
		
		$do->startTrans();		
        
        //检查用户账户是否正常、余额是否足够
        $u_account=$this->check_account($this->uid,1,$rs['money']);
        $a_account=$this->check_account(1); //系统账号

        $a_account['ac_cash']+=$rs['money'];
        $a_account['crc']=$this->crc($a_account);

        $u_account['ac_cash']-=$rs['money'];
        $u_account['crc']=$this->crc($u_account);


        //更新账户
        $this->sw[]=M('account')->where(array('uid'=>$this->uid))->save($u_account);
		$this->sw[]=M('account')->where(array('uid'=>1))->save($a_account);

		//用户异动记录 从用户转到系统账户
        $data=array();
		$data['a_no']         =$this->create_orderno();
		$data['uid']          =$this->uid;
		$data['money']        =$rs['money']*-1;
		$data['from_uid']     =$this->uid;
		$data['from_account'] =$u_account['ac_cash'];
		$data['from_flag']    =1; //现金账户

		$data['to_uid']       =1;
		$data['to_account']   =$a_account['ac_cash'];
		$data['to_flag']      =1;  //现金账户

		$data['status']       =2;
		$data['type_id']      =5; //会员升级缴费
		$data['ordersno']     =$rs['u_no'];

		if($this->sw[]=D('Common/ChangeCash')->create($data)) $this->sw[]=D('Common/ChangeCash')->add();
		else {
			goto error;
		}

		//admin异动记录 从用户转到系统账户
		$a_data=$data;
		$a_data['a_no']         =$this->create_orderno();
		$a_data['uid']          =1;
		$a_data['money']        =$rs['money'];

		if($this->sw[]=D('Common/ChangeCash')->create($a_data)) $this->sw[]=D('Common/ChangeCash')->add();
		else {
			goto error;
		}

		//dump(D('Common/ChangeCash')->getError());

		if(!$this->sw[]=M('user')->where(array('id'=>$this->uid))->setField('level_id',$rs['level_id'])){
			goto error;
		}
		
		if(!$this->sw[]=M('user_upgrade_logs')->where(array('id'=>$rs['id']))->setField(array('status'=>1,'pay_time'=>date('Y-m-d H:i:s'),'upgrade_time'=>date('Y-m-d H:i:s')))){
			goto error;
		}		

        success:
            $do->commit();
            //计算提嘉将，后续如果数据太庞大可将8层提成嘉奖丢入队列或临时表让程序自动处理
            $tc=new \Common\Controller\RewardController($this->uid,$rs['u_no']);
            $tc->reward();  //创业会员8层提成嘉奖
            $this->apiReturn(1);

        error:
        $do->rollback();
        $this->apiReturn(0);
	}
	

	/**
	* 购买代理并付款
	* @param string $_POST['openid'] 用户openid
	* @param number $_POST['password_pay'] 安全密码
	*/
	public function agent_create_pay(){
        //频繁请求限制,间隔2秒
        $this->_request_check();

        //必传参数检查
        $this->need_param=array('openid','level','city_id','password_pay','sign');
        $this->_need_param();
        $this->_check_sign();
		
	
        if(!$this->user){
            //用户不存在
            $this->apiReturn(97);            
        }elseif($this->user['level_id']<2){
			//必须是创业会员才可以升级成为代理会员
			$this->apiReturn(100);
		}
		
        //验证支付密码
        if(md5(I('post.password_pay'))!=$this->user['password_pay']){
            //支付密码错误
            $this->apiReturn(6);
        }

        //如果申请的地区已有代理商，不可提交

        //检查代理级别对应的地区层次
		$city_layer=array(3=>4,4=>3,5=>2,6=>1);
		$city_ids=upsid(array('table'=>'area','id'=>I('post.city_id')));
		if(count($city_ids)!=$city_layer[I('post.level')]) $this->apiReturn(98);


		$do=M('user_upgrade_logs');
		if($agent=$do->where(array('city_id'=>I('post.city_id'),'status'=>1))->field('id')->find()){
			//升级失败！您申请的地区已有代理商！
			$this->apiReturn(96);
		}
		$map['city_id']=I('post.city_id');
	
		
        $lrs=M('user_level')->cache(true,60)->where(array('id'=>I('post.level')))->field('upgrade_money')->find();

       
        $map['level_id']=I('post.level');
        $map['uid']=$this->uid;      

        $do=M('user_upgrade_logs');
        if($rs=$do->where($map)->field('etime,ip',true)->find()){
            if($rs['status']==1) $this->apiReturn(93);  //升级出错，该级别已交纳过费用，请联系客服
        }else{
			$do=D('Common/UserUpgradeLogs');
			$rs=$map;
            $rs['u_no']=$this->create_orderno();
            $rs['money']=$lrs['upgrade_money'];
            if($do->create($rs)){
                if($do->add()){
                    //创建订单成功
                    $rs['id']=$do->getLastInsID();
                }else{
                    //创建订单失败
                    $this->apiReturn(0);
                }
            }else{
                //数据验证失败
                $this->apiReturn(4,'',1,$do->getError());
            }			
		}
		
		
		$do->startTrans();		
        
        //检查用户账户是否正常、余额是否足够
        $u_account=$this->check_account($this->uid,1,$rs['money']);
        $a_account=$this->check_account(1); //系统账号

        $a_account['ac_cash']+=$rs['money'];
        $a_account['crc']=$this->crc($a_account);

        $u_account['ac_cash']-=$rs['money'];
        $u_account['crc']=$this->crc($u_account);


        //更新账户
        $this->sw[]=M('account')->where(array('uid'=>$this->uid))->save($u_account);
		$this->sw[]=M('account')->where(array('uid'=>1))->save($a_account);

		//用户异动记录 从用户转到系统账户
        $data=array();
		$data['a_no']         =$this->create_orderno();
		$data['uid']          =$this->uid;
		$data['money']        =$rs['money']*-1;
		$data['from_uid']     =$this->uid;
		$data['from_account'] =$u_account['ac_cash'];
		$data['from_flag']    =1; //现金账户

		$data['to_uid']       =1;
		$data['to_account']   =$a_account['ac_cash'];
		$data['to_flag']      =1;  //现金账户

		$data['status']       =2;
		$data['type_id']      =5; //会员升级缴费
		$data['ordersno']     =$rs['u_no'];

		if($this->sw[]=D('Common/ChangeCash')->create($data)) $this->sw[]=D('Common/ChangeCash')->add();
		else {
			goto error;
		}

		//admin异动记录 从用户转到系统账户
		$a_data=$data;
		$a_data['a_no']         =$this->create_orderno();
		$a_data['uid']          =1;
		$a_data['money']        =$rs['money'];

		if($this->sw[]=D('Common/ChangeCash')->create($a_data)) $this->sw[]=D('Common/ChangeCash')->add();
		else {
			goto error;
		}

		//dump(D('Common/ChangeCash')->getError());
        $r1 = $this->sw[]=M('user')->where(array('id'=>$this->uid))->setField('level_id',$rs['level_id']);
		if($r1 === false ){
			goto error;
		}
		
		if(!$this->sw[]=M('user_upgrade_logs')->where(array('id'=>$rs['id']))->setField(array('status'=>1,'pay_time'=>date('Y-m-d H:i:s'),'upgrade_time'=>date('Y-m-d H:i:s')))){
			goto error;
		}		

        success:
            $do->commit();
            //计算提嘉将，后续如果数据太庞大可将8层提成嘉奖丢入队列或临时表让程序自动处理
            $tc=new \Common\Controller\RewardController($this->uid,$rs['u_no']);
            $tc->reward_agent();    //代理提成嘉奖
            $this->apiReturn(1);

        error:
        $do->rollback();
        $this->apiReturn(0);
	}
	
    
}