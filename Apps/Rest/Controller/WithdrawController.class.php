<?php
/*
+------------------------------
+ 提现管理
+-----------------------------
*/
namespace Rest\Controller;
use Think\Controller\RestController;
class WithdrawController extends CommonController {
    public function index(){
    	redirect(C('sub_domain.www'));
    }
    /**
    * 银行列表
    */
    public function bank(){
        //必传参数检查
        $this->need_param=array('sign');
        $this->_need_param();
        $this->_check_sign();

        $do=M('bank_name');
		$map['id']=array('not in','39,40');
        $list=$do->cache(true,C('CACHE_LEVEL.L'))->where($map)->field('id,bank_code,logo,bank_name')->select();
        if($list){
            foreach($list as $key=>$val){
                $list[$key]['logo']=myurl($val['logo'],60);
            }
            $this->apiReturn(1,array('data'=>$list));
        }else{
			//找不到记录
            $this->apiReturn(3);
        }
    }


    /**
    * 添加银行账号
    */
    public function card_add(){
        //频繁请求限制,间隔2秒
        $this->_request_check();

        //必传参数检查
        //if(I('post.type')==2 || I('post.type')==3) $this->need_param=array('openid','type','bank_id','master','account','sign');
        //else 
		$this->need_param=array('openid','type','bank_id','master','account','province','city','address','sign');
        $this->_need_param();
        $this->_check_sign();

        //是否已认证
        $do=M('user');
        $urs=$do->where(array('id'=>$this->uid))->field('id,name,is_auth')->find();
        if($urs['is_auth']==0) $this->apiReturn(122);   //未认证
        if($urs['name']!=I('post.master')) $this->apiReturn(123);   //提现开户名必须与实名认证的名称相同

        //最多限制绑定15个账号
        $do=M('withdraw_account');
        $count=$do->where(array('uid'=>$this->uid))->count();
        if($count==0) $_POST['is_default']=1;

        if($count>14) $this->apiReturn(121);

        //是否存在相当记录
        if(M('withdraw_account')->where(array('uid'=>$this->uid,'account'=>I('post.account')))->field('id')->find()) $this->apiReturn(124);

        $do=M('bank_name');
        $brs=$do->where(array('id'=>I('post.bank_id')))->field('id,bank_code,bank_name')->find();

        $data['uid']        =$this->uid;
        $data['type']       =I('post.type');
        $data['bank_id']    =I('post.bank_id');
        $data['bank_code']  =$brs['bank_code'];
        $data['bank_name']  =$brs['bank_name'];
        $data['master']     =I('post.master');
        $data['account']    =I('post.account');
        $data['province']   =I('post.province');
        $data['city']       =I('post.city');
        $data['is_default'] =I('post.is_default');
        $data['address']    =I('post.address');

        $do=D('Common/WithdrawAccount');
        if($do->create($data)){
            if($id = $do->add()){
                $this->unDefault($data['is_default'], $id);
				//$this->_default();
                //添加成功
                $this->apiReturn(1);
            }else{
                //添加失败
                $this->apiReturn(0);
            }
        }else{
            //数据验证错误
            $this->apiReturn(4,'',1,'操作失败！'.$do->getError());
        }
    }

    /**
    * 修改提现账号
    */
    public function card_edit(){
        //频繁请求限制,间隔2秒
        $this->_request_check();

        //必传参数检查
        if(I('post.type')==2 || I('post.type')==3) $this->need_param=array('openid','id','type','bank_id','master','account','sign');
        else $this->need_param=array('openid','id','type','bank_id','master','account','province','city','address','sign');
        $this->_need_param();
        $this->_check_sign();

        //是否已认证
        $do=M('user');
        $urs=$do->where(array('id'=>$this->uid))->field('id,name,is_auth')->find();
        if($urs['is_auth']==0) $this->apiReturn(122);   //未认证
        if($urs['name']!=I('post.master')) $this->apiReturn(123);   //提现开户名必须与实名认证的名称相同

        //最多限制绑定15个账号
        $do=M('withdraw_account');
        $count=$do->where(array('uid'=>$this->uid))->count();
        if($count==0) $_POST['is_default']=1;

        if($count>14) $this->apiReturn(121);
        
        //是否存在相当记录
        if(M('withdraw_account')->where(array('uid'=>$this->uid,'account'=>I('post.account'),'id'=>array('neq',I('post.id'))))->field('id')->find()) $this->apiReturn(124);

        $do=M('bank_name');
        $brs=$do->where(array('id'=>I('post.bank_id')))->field('id,bank_code,bank_name')->find();

        $data['uid']        =$this->uid;
        $data['type']       =I('post.type');
        $data['bank_id']    =I('post.bank_id');
        $data['bank_code']  =$brs['bank_code'];
        $data['bank_name']  =$brs['bank_name'];
        $data['master']     =I('post.master');
        $data['account']    =I('post.account');
        $data['province']   =I('post.province');
        $data['city']       =I('post.city');
        $data['is_default'] =I('post.is_default');
        $data['address']    =I('post.address');
        $data['id']         =I('post.id');
        $do=D('Common/WithdrawAccount');
        if($do->create($data)){
            if($do->save()){
                $this->unDefault($data['is_default'], $data['id']);
				//$this->_default();
                //添加成功
                $this->apiReturn(1);
            }else{
                //添加失败
                $this->apiReturn(0);
            }
        }else{
            //数据验证错误
            $this->apiReturn(4,'',1,'操作失败！'.$do->getError());
        }        
    }

    /**
    * 删除提现账号
    */
    public function card_del(){
        //频繁请求限制,间隔2秒
        $this->_request_check();

        //必传参数检查
        $this->need_param=array('openid','id','sign');
        $this->_need_param();
        $this->_check_sign();

        $do=M('withdraw_account');
        if($do->where(array('uid'=>$this->uid,'id'=>array('in',I('post.id'))))->delete()){
            $this->_default();
			$this->apiReturn(1);
        }else{
            $this->apiReturn(0);
        }
    }

    /**
    * 银行卡详情
    */
    public function card_view(){
        //频繁请求限制,间隔2秒
        $this->_request_check();

        //必传参数检查
        $this->need_param=array('openid','id','sign');
        $this->_need_param();
        $this->_check_sign();

        $type=array('银行储蓄卡','银行信用卡','支付宝','财付通');

        $do=M('withdraw_account');
        $rs=$do->cache(true,C('CACHE_LEVEL.XXS'))->where(array('uid'=>$this->uid,'id'=>I('post.id')))->field('etime,ip',true)->find();

        if($rs){
			$area 	=	$this->cache_table('area');
            $rs['province_name']    =$area[$rs['province']];
            $rs['city_name']        =$area[$rs['city']];
			$rs['type_name']        =$type[$rs['type']];
            $this->apiReturn(1,array('data'=>$rs));
        }else{
            //找不到记录
            $this->apiReturn(0);
        }

    }

    /**
    * 提现账号列表
    */
    public function card_list(){
        //必传参数检查 
        $this->need_param=array('openid','sign');
        $this->_need_param();
        $this->_check_sign();
		
		//$map['bank_id']	=array('not in','39,40');
		$map['uid']	=$this->uid;
		if(I('post.type')==1) $map['type']=array('in', '0,1');
		elseif(I('post.type')==2) $map['type']=array('in', '2,3');
		
        $do=D('WithdrawAccountRelation');
        if($list=$do->relation(true)->cache(true,C('CACHE_LEVEL.XXS'))->where($map)->field('id,atime,uid,type,bank_id,master,account,province,city,is_default,address')->order('atime desc')->select()){           
            $area	=	$this->cache_table('area');
			foreach($list as $key=>$val){
                $list[$key]['bank']['logo']		=myurl($val['bank']['logo'],60);
                $list[$key]['master']			=msubstr($val['master'],0,1,'utf-8',false).'**';
                $list[$key]['account']			=msubstr($val['account'],0,4,'utf-8',false).'*******'.msubstr($val['account'],-4,4,'utf-8',false);
				
                $list[$key]['province']    		=$area[$val['province']];
                $list[$key]['city']        		=$area[$val['city']];
				
            }
            $this->apiReturn(1,array('data'=>$list));
        }else{
			//没有记录
            $this->apiReturn(3);
        }               
    }

    /**
    * 默认银行卡
    */
    public function _default(){
        $do=M('withdraw_account');
        if($rs=$do->where(array('uid'=>$this->uid))->order('is_default desc,id desc')->field('id,is_default')->find()){
            if($rs['is_default']==0) $do->where(array('id'=>$rs['id']))->setField('is_default',1);
        }

    }	
    
    /**
     * 取消默认银行卡
     */
    private function unDefault($default,$id) {
        if ($default == 1) {
            $map['uid']         =   $this->uid;
            $map['is_default']  =   1;
            $map['id']          =   array('neq', $id);
            M('withdraw_account')->where($map)->save(array('is_default' => 0));
        }
    }
    
	/**
	* 获取默认银行卡
	*/
	public function default_card(){
        //频繁请求限制,间隔300毫秒
        $this->_request_check();      

        //必传参数检查
        $this->need_param=array('openid','sign');
        $this->_need_param();
        $this->_check_sign();

		$do=D('WithdrawAccountRelation');
		$rs=$do->relation(true)->cache(true,C('CACHE_LEVEL.S'))->where(array('uid'=>$this->uid))->field('bank_id,bank_code,bank_name,master,account,is_default,province,city,address,id')->order('is_default desc')->find();
        if($rs){
           $this->apiReturn(1,array('data'=>$rs));
        }else{
            //找不到记录
            $this->apiReturn(0);
        }
	}	
	
    /**
    * 申请提现
    */
    public function out(){
        //频繁请求限制,间隔2秒
        $this->_request_check(5);

        //必传参数检查
        $this->need_param=array('openid','money','card_id','password_pay','sign');
        $this->_need_param();
        $this->_check_sign();
		
        //是否已认证
        $do=M('user');
        $urs=$do->where(array('id'=>$this->uid))->field('is_auth')->find();
        if($urs['is_auth']==0) $this->apiReturn(122);   //未认证		
		
		//异动金额
		$money=I('post.money');

        //单笔提现金额太少
        if($money < C('cfg.withdraw')['min_money']) $this->apiReturn(126);

        //单笔提现金额太大
        if($money > C('cfg.withdraw')['max_money']) $this->apiReturn(127);

        //验证支付密码
        $do=M('user');
        $urs=$do->where(array('id'=>$this->uid))->field('password_pay')->find();
        if(md5(I('post.password_pay'))!=$urs['password_pay']){
            //支付密码错误
            $this->apiReturn(6);
        }

        //一天最多只能提现3次
        $do=M('withdraw');
        if($do->where(array('uid'=>$this->uid,'_string'=>'date_format(atime,"%Y-%m-%d")="'.date('Y-m-d').'"'))->count() >= C('cfg.withdraw')['max_withdraw']) $this->apiReturn(125);
        //var_dump($do->getLastSQL());
		
		
        //账户余额是否足够提现
        $u_account=$this->check_account($this->uid,1,$money);
        $u_account['ac_cash']-=$money;
        $u_account['ac_cash_lock']+=$money;
        $u_account['crc']=$this->crc($u_account);
		
        //取银行卡资料
        $card=M('withdraw_account')->where(array('id'=>I('post.card_id')))->field('bank_id,type,master,account')->find();

        $do->startTrans();
        //添加提现记录
        $w_data=array();
        $w_data['w_no']     =$this->create_orderno();
        $w_data['uid']      =$this->uid;
        $w_data['money']    =$money;
        $w_data['card_id']  =I('post.card_id');
        $w_data['bank_id']  =$card['bank_id'];
        $w_data['master']   =$card['master'];
        $w_data['account']  =$card['account'];
        $w_data['type']     =$card['type'];
		$w_data['ac_cash']	=$u_account['ac_cash'];

        if($sw1=D('Common/Withdraw')->create($w_data)) {
            $sw1=D('Common/Withdraw')->add();
        }else{
            $msg[]=D('Common/Withdraw')->getError();
            goto error;
        }


        //更新账户
        if(!$sw2=M('account')->where(array('uid'=>$this->uid))->save($u_account)) goto error;

        //创建现金异动记录　- 从现金账户转到冻结账户
        $data=array();
        $data['uid']            =$this->uid;
        $data['a_no']           =$this->create_orderno();
        $data['money']          =$money * -1;
        $data['from_uid']       =$this->uid;
        $data['from_account']   =$u_account['ac_cash'];
        $data['from_flag']      =1; //现金账户

        $data['to_uid']         =$this->uid;
        $data['to_account']     =$u_account['ac_cash_lock'];
        $data['to_flag']        =4; //提现账户
        $data['status']         =2;

        $data['type_id']        =9; //提现申请
        $data['ordersno']       =$w_data['w_no'];


        if($sw3=D('Common/ChangeCash')->create($data)) $sw3=D('Common/ChangeCash')->add();
        else {
            $msg[]=D('Common/ChangeCash')->getError();
            goto error;
        }
		
		//冻结账户异动
		$l_data=$data;
		$l_data['w_no']	=$this->create_orderno();
		$l_data['money']=$money;
        if($sw4=D('Common/ChangeCashLock')->create($l_data)) $sw4=D('Common/ChangeCashLock')->add();
        else {
            $msg[]=D('Common/ChangeCashLock')->getError();
            goto error;
        }		

        success:
            $this->sw=array($sw1,$sw2,$sw3,$sw4);
            $do->commit();
            $this->apiReturn(1);

        error:
            $this->sw=array($sw1,$sw2,$sw3);
            $do->rollback();
            $this->apiReturn(4,'',1,'操作失败！'.@implode('<br>',$msg));

    }

    /**
    * 提现记录
    */
    public function withdraw_list(){
        //必传参数检查
        $this->need_param=array('openid','sign');
        $this->_need_param();
        $this->_check_sign();

        $status=array('审核中','已到账','被驳回');
		if(I('post.status')!='') $map['status']=I('post.status');
        $map['uid']=$this->uid;
        $pagesize=I('post.pagesize')?I('post.pagesize'):20;
        $pagelist=pagelist(array(
                'table'     =>'WithdrawRelation',
                'do'        =>'D',
                'map'       =>$map,
                'order'     =>'atime desc',
                'fields'    =>'id,atime,w_no,status,uid,type,money,card_id,bank_id,master,account,trade_no,trade_status,pay_time,reason',
                'pagesize'  =>$pagesize,
                'relation'  =>true,
                'action'    =>I('post.action'),
                'query'     =>I('post.query'),
                'p'         =>I('post.p'),
            ));

        foreach($pagelist['list'] as $key=>$val){
            $pagelist['list'][$key]['status_name']=$status[$val['status']];
            $pagelist['list'][$key]['master']=msubstr($val['master'],0,1,'utf-8',false).'**';
            $pagelist['list'][$key]['account']=msubstr($val['account'],0,4,'utf-8',false).'*******'.msubstr($val['account'],-4,4,'utf-8',false);
            $pagelist['list'][$key]['bank']['logo']=myurl($val['bank']['logo'],25);
        }

        if($pagelist['list']){
            $this->apiReturn(1,array('data'=>$pagelist));
        }else{
            $this->apiReturn(3);
        }
    }


    /**
    * 取提现详情
    */
    public function view(){
        //必传参数检查
        $this->need_param=array('openid','id','sign');
        $this->_need_param();
        $this->_check_sign();

        $status=array('审核中','已到账','被驳回');

        $do=D('WithdrawRelation');
        $rs=$do->relation(true)->cache(true,C('CACHE_LEVEL.XXS'))->where(array('uid'=>$this->uid,'id'=>I('post.id')))->field('etime,ip',true)->find();

        if($rs){
            $rs['status_name']=$status[$rs['status']];
            $rs['master']=msubstr($rs['master'],0,1,'utf-8',false).'**';
            $rs['account']=msubstr($rs['account'],0,4,'utf-8',false).'*******'.msubstr($rs['account'],-4,4,'utf-8',false);
            if($rs['logs']){
                foreach($rs['logs'] as $key=>$val){
                    $rs['logs'][$key]['status_name']=$status[$val['status']];
                }
            }

            $this->apiReturn(1,array('data'=>$rs));
        }else{
            //找不到记录
            $this->apiReturn(3);
        }
    }
	
	
	/**
	* 添加支付宝或财富通
	* @param int 	$_POST['type']  	3=支付宝,4=财富通
	* @param string $_POST['openid']	用户openid
	* @param string $_POST['master']	户名
	* @param string $_POST['account']	账号
	* @param int 	$_POST['is_default']是否默认，不是必填
	*/
	public function card_alipay_add(){
		$bank_id = [2 => 40, 3 => 39]; //当为支付宝是bank_id=40;为财富通时bank_id=39
        
		$this->need_param = ['type', 'openid', 'master', 'account'];
        $this->_need_param();
        $this->_check_sign();
        //是否已认证
        $do=M('user');
        $urs=$do->where(array('id'=>$this->uid))->field('id,name,is_auth')->find();
        if($urs['is_auth']==0) $this->apiReturn(122);   //未认证
        if($urs['name']!=I('post.master')) $this->apiReturn(123);   //提现开户名必须与实名认证的名称相同
        
        
        
        $data['type']       = I('post.type', 0, 'int');
        $data['uid']        = $this->uid;
        $data['master']     = I('post.master');
        $data['account']    = I('post.account');
        $data['is_default'] = I('post.is_default', 0, 'int');

        if(! isset($bank_id[$data['type']])){
            // 不是指定的type类型
            $this->apiReturn(12, [], 1, '参数type错误');
        }else{
            // 是否已经存在记录
            $model = M('withdraw_account');
            $one = $model->where(['uid' => $this->uid, 'type' => $data['type']])->field('id')->find();
            if(isset($one['id'])){
                // 已存在记录，不能重复添加
                $this->apiReturn(9);
            }
            // 银行信息
            $data['bank_id'] = $bank_id[$data['type']];
            $bank = M('bank_name')->field('id,bank_code,bank_name')->find($data['bank_id']);
            if(! $bank){
                // 找不到银行记录
                $this->apiReturn(3, [], 1, '找不到银行记录');
            }else{
                $data['bank_code'] = $bank['bank_code'];
                $data['bank_name'] = $bank['bank_name'];
            }
        }

        $data['ip'] = get_client_ip();
        
        $id = $model->data($data)->add();
        if($id){
            // 修改默认
            if($data['is_default']){
                $where = ['uid' => $this->uid , 'is_default' => 1, 'id' => ['NEQ', $id]];
                $save = $model->where($where)->data(['is_default' => 0])->save();
            }
            $this->apiReturn(1);
        }else{
            $this->apiReturn(0);
        }
	}

	/**
	* 修改支付宝或财富通
	* @param int 	$_POST['type']  	3=支付宝,4=财富通
	* @param string $_POST['openid']	用户openid
	* @param string $_POST['master']	户名
	* @param string $_POST['account']	账号
	* @param int 	$_POST['is_default']是否默认，不是必填
	* @param int 	$_POST['id']		要修改的记录ID
	*/
	public function card_alipay_edit(){
		$bank_id = [2 => 40,3 => 39]; //当为支付宝是bank_id=40;为财富通时bank_id=39

		$this->need_param = ['type', 'openid', 'master', 'account', 'id'];
        $this->_need_param();
        $this->_check_sign();

        $id                 = I('post.id', 0, 'int');
        $data['type']       = I('post.type', 0, 'int');
        $data['uid']        = $this->uid;
        $data['master']     = I('post.master');
        $data['account']    = I('post.account');
        $data['is_default'] = I('post.is_default', 0, 'int');

        if(! isset($bank_id[$data['type']])){
            // 不是指定的type类型
            $this->apiReturn(12, [], 1, '参数type错误');
        }else{
            $model = M('withdraw_account');
            $one = $model->field('id')->find($id);
            if(! isset($one['id'])){
                $this->apiReturn(3, [], 1, '没要找到要修改的记录');
            }
            // 银行信息
            $data['bank_id'] = $bank_id[$data['type']];
            $bank = M('bank_name')->field('id,bank_code,bank_name')->find($data['bank_id']);
            if(! $bank){
                // 找不到银行记录
                $this->apiReturn(3, [], 1, '找不到银行记录');
            }else{
                $data['bank_code'] = $bank['bank_code'];
                $data['bank_name'] = $bank['bank_name'];
            }
        }
        
        $r = $model->where(['id' => $id])->data($data)->save();
        if($r > 0 || $r === 0){
            // 修改默认,有影响记录数的时候才执行
            if($data['is_default'] && $r !== 0){
                $where = ['uid' => $this->uid , 'is_default' => 1, 'id' => ['NEQ', $id]];
                $save = $model->where($where)->data(['is_default' => 0])->save();
            }
            $this->apiReturn(1);
        }else{
            $this->apiReturn(0);
        }

	}
	
	//删除支付宝或财富通账号请直接用 card_del;
	
	/**
	* 获取支付宝或财富通列表
	* @param string $_POST['openid']	用户openid
	*/
	public function cart_alipay_list(){
        $this->need_param = ['openid'];
        $this->_need_param();
        $this->_check_sign();

		$map['bank_id'] = ['in','39,40'];
        $map['uid']     = $this->uid;

        $model = D('Common/WithdrawAccountView');
        $list = $model->where($map)->order('id desc')->select();
        if($list === false){
            $this->apiReturn(0);
        }elseif(isset($list[0])){
            $this->apiReturn(1, ['data' => $list]);
        }else{
            $this->apiReturn(3);
        }
	}
	
	
}