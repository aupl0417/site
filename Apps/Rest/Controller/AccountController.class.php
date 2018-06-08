<?php
/**
+----------------------------------------------------------------------
| RestFull API 
+----------------------------------------------------------------------
| 账户管理
+----------------------------------------------------------------------
| Author: lazycat <673090083@qq.com>
+----------------------------------------------------------------------
*/

namespace Rest\Controller;
use Think\Controller\RestController;
class AccountController extends CommonController {
    public function index(){
    	redirect(C('sub_domain.www'));
    }


    /**
    * 获取用户账户信息
    */
    public function account(){
    	//频繁请求限制,间隔2秒
    	$this->_request_check();

    	//必传参数检查
        $this->need_param=array('openid','sign');
        $this->_need_param();
        $this->_check_sign();


        $do=M('account');
        $rs=$do->where('uid='.$this->uid)->field('ac_cash,ac_score,ac_finance,ac_cash_lock')->find();

        if($rs){
			$rs['all']=array_sum($rs)-$rs['ac_finance'];
			$rs['bank']=M('withdraw_account')->where(array('uid'=>$this->uid,'type'=>array('in','0,1')))->count(); //银行卡
			$rs['alipay']=M('withdraw_account')->where(array('uid'=>$this->uid,'type'=>array('in','2,3')))->count();	//支付宝或财付通
			$rs['finance_in']=M('finance')->where(array('uid'=>$this->uid,'status'=>1))->count();
			$rs['finance_out']=M('finance')->where(array('uid'=>$this->uid,'status'=>1,'pay_time'=>array('lt',date('Y-m-d H:i:s',time()-86400*C('cfg.finance')['min_day']))))->count();
			
			
        	$f=new \Rest\Controller\FinanceController;
        	$res=$f->win_money($this->uid);
        	$rs=array_merge($rs,$res);

        	$this->apiReturn(1,array('data'=>$rs));
        }else{
        	$this->apiReturn(0);
        }

    }


    /**
    * 现金账户异动明细
    */
    public function change_cash(){
    	//频繁请求限制,间隔2秒
    	$this->_request_check();

    	//必传参数检查
        $this->need_param=array('openid','sign');
        $this->_need_param();
        $this->_check_sign();

        $pagesize=I('post.pagesize')?I('post.pagesize'):20;

        $map['uid']=$this->uid;
        if(I('post.type_id')) $map['type_id']=array('in',I('post.type_id'));
		
        $pagelist=pagelist(array(
        		'table'		=>'change_cash',
        		'map'		=>$map,
        		'order'		=>'id desc',
        		'fields'	=>'id,atime,a_no,money,status,from_uid,from_flag,from_account,to_uid,to_flag,to_account,type_id,d_uid,ordersno',
        		'pagesize'	=>$pagesize,
                'action'    =>I('post.action'),
                'query'     =>I('post.query'),
                'p'         =>I('post.p'),
        	));

        $type=M('change_type')->cache(true,C('CACHE_LEVEL.M'))->getField('id,type_name',true);

        if($pagelist['list']){
	        //格式化处理数据
            foreach($pagelist['list'] as $key=>$val){
                $pagelist['list'][$key]['category']=$type[$val['type_id']];
                $pagelist['list'][$key]['type_name']=$val['money']>0?'转入':'转出';
                if($val['from_uid']==$this->uid && $val['from_flag']==1) $pagelist['list'][$key]['account']=$val['from_account'];
                else $pagelist['list'][$key]['account']=$val['to_account'];
				
				if($val['d_uid']>0){
					$pagelist['list'][$key]['d_nick']=CURD(array('table'=>'user','type'=>'F','field'=>'nick','map'=>array('id'=>$val['d_uid'])))['nick'];
				}
            }
			
			$allmoeny=M('change_cash')->where($map)->sum('money');
	        $this->apiReturn(1,array('data'=>$pagelist,'allmoeny'=>$allmoeny));        	

        }else{
        	//找不到记录
        	$this->apiReturn(3);        	
        }
    }

    /**
    * 积分账户异动明细
    */
    public function change_score(){
        //频繁请求限制,间隔2秒
        $this->_request_check();

        //必传参数检查
        $this->need_param=array('openid','sign');
        $this->_need_param();
        $this->_check_sign();

        $pagesize=I('post.pagesize')?I('post.pagesize'):20;

        $map['uid']=$this->uid;
        if(I('post.type_id')) $map['type_id']=array('in',I('post.type_id'));

        $pagelist=pagelist(array(
                'table'     =>'change_score',
                'map'       =>$map,
                'order'     =>'id desc',
                'fields'    =>'id,atime,c_no,money,from_uid,from_flag,from_account,to_uid,to_flag,to_account,status,type_id,d_uid,ordersno',
                'pagesize'  =>$pagesize,
                'action'    =>I('post.action'),
                'query'     =>I('post.query'),
                'p'         =>I('post.p'),
            ));

        $type=M('change_type')->cache(true,C('CACHE_LEVEL.M'))->getField('id,type_name',true);

        if($pagelist['list']){
            //格式化处理数据
            foreach($pagelist['list'] as $key=>$val){
                $pagelist['list'][$key]['category']=$type[$val['type_id']];
                $pagelist['list'][$key]['type_name']=$val['money']>0?'转入':'转出';

                if($val['from_uid']==$this->uid && $val['from_flag']==2) $pagelist['list'][$key]['account']=$val['from_account'];
                else $pagelist['list'][$key]['account']=$val['to_account'];
				
				if($val['d_uid']>0){
					$pagelist['list'][$key]['d_nick']=CURD(array('table'=>'user','type'=>'F','field'=>'nick','map'=>array('id'=>$val['d_uid'])))['nick'];
				}				
            }

			$allmoeny=M('change_score')->where($map)->sum('money');
	        $this->apiReturn(1,array('data'=>$pagelist,'allmoeny'=>$allmoeny));             

        }else{
            //找不到记录
            $this->apiReturn(3);            
        }
    }

    /**
    * 理财账户异动明细
    */
    public function change_finance(){
        //频繁请求限制,间隔2秒
        $this->_request_check();

        //必传参数检查
        $this->need_param=array('openid','sign');
        $this->_need_param();
        $this->_check_sign();

        $pagesize=I('post.pagesize')?I('post.pagesize'):20;

        $map['uid']=$this->uid;
        if(I('post.type_id')) $map['type_id']=array('in',I('post.type_id'));

        $pagelist=pagelist(array(
                'table'     =>'change_finance',
                'map'       =>$map,
                'order'     =>'id desc',
                'fields'    =>'id,atime,d_no,money,from_uid,from_flag,from_account,to_uid,to_flag,to_account,status,type_id,d_uid,ordersno',
                'pagesize'  =>$pagesize,
                'action'    =>I('post.action'),
                'query'     =>I('post.query'),
                'p'         =>I('post.p'),
            ));

        $type=M('change_type')->cache(true,C('CACHE_LEVEL.M'))->getField('id,type_name',true);

        if($pagelist['list']){
            //格式化处理数据
            foreach($pagelist['list'] as $key=>$val){
                $pagelist['list'][$key]['category']=$type[$val['type_id']];
                $pagelist['list'][$key]['type_name']=$val['money']>0?'转入':'转出';
                if($val['from_uid']==$this->uid && $val['from_flag']==3) $pagelist['list'][$key]['account']=$val['from_account'];
                else $pagelist['list'][$key]['account']=$val['to_account'];
            }

			$allmoeny=M('change_finance')->where($map)->sum('money');
	        $this->apiReturn(1,array('data'=>$pagelist,'allmoeny'=>$allmoeny));           

        }else{
            //找不到记录
            $this->apiReturn(3);            
        }
    }

    /**
    * 冻结账户异动明细
    */
    public function change_cash_lock(){
    	//频繁请求限制,间隔2秒
    	$this->_request_check();

    	//必传参数检查
        $this->need_param=array('openid','sign');
        $this->_need_param();
        $this->_check_sign();

        $pagesize=I('post.pagesize')?I('post.pagesize'):20;

        $map['uid']=$this->uid;
        if(I('post.type_id')) $map['type_id']=array('in',I('post.type_id'));

        $pagelist=pagelist(array(
        		'table'		=>'change_cash_lock',
        		'map'		=>$map,
        		'order'		=>'id desc',
        		'fields'	=>'id,atime,w_no,money,status,from_uid,from_flag,from_account,to_uid,to_flag,to_account,type_id,d_uid,ordersno',
        		'pagesize'	=>$pagesize,
                'action'    =>I('post.action'),
                'query'     =>I('post.query'),
                'p'         =>I('post.p'),
        	));

        $type=M('change_type')->cache(true,C('CACHE_LEVEL.M'))->getField('id,type_name',true);

        if($pagelist['list']){
	        //格式化处理数据
            foreach($pagelist['list'] as $key=>$val){
                $pagelist['list'][$key]['category']=$type[$val['type_id']];
                $pagelist['list'][$key]['type_name']=$val['money']>0?'转入':'转出';
                if($val['from_uid']==$this->uid && $val['from_flag']==1) $pagelist['list'][$key]['account']=$val['from_account'];
                else $pagelist['list'][$key]['account']=$val['to_account'];
            }

			$allmoeny=M('change_cash_lock')->where($map)->sum('money');
	        $this->apiReturn(1,array('data'=>$pagelist,'allmoeny'=>$allmoeny));         	

        }else{
        	//找不到记录
        	$this->apiReturn(3);        	
        }
    }
	
	/**
	* 现金异动详情
	* @param string $_POST['openid'] 用户OpenID
	* @param string $_POST['a_no']	异动流水号
	*/
	public function cash_view(){
		$this->need_param = ['openid', 'a_no'];
        $this->_need_param();
        $this->_check_sign();

        $a_no = I('post.a_no');
        $model = D('Common/ChangeCashRelation');

        $info = $model->relation(true)->where(['uid' => $this->uid, 'a_no' => $a_no])->field('etime,ip',true)->find();
        if($info){
			$type=M('change_type')->cache(true,C('CACHE_LEVEL.M'))->getField('id,type_name',true);
			$info['from_flag_name']	=$this->flag_name[$info['from_flag']];
			$info['to_flag_name']	=$this->flag_name[$info['to_flag']];
			$info['category']		=$type[$info['type_id']];
			$info['type_name']		=$info['money']>0?'转入':'转出';
			
            $this->apiReturn(1, ['data' => $info]);
        }else{
            $this->apiReturn(3);
        }
	}

	/**
	* 积分异动详情
	* @param string $_POST['openid'] 用户OpenID
	* @param string $_POST['c_no']	异动流水号
	*/
	public function score_view(){
		$this->need_param = ['openid', 'c_no'];
        $this->_need_param();
        $this->_check_sign();

        $c_no = I('post.c_no');
        $model = D('Common/ChangeScoreRelation');

        $info = $model->relation(true)->where(['uid' => $this->uid, 'c_no' => $c_no])->field('etime,ip',true)->find();
        if($info){
			$type=M('change_type')->cache(true,C('CACHE_LEVEL.M'))->getField('id,type_name',true);
			$info['from_flag_name']	=$this->flag_name[$info['from_flag']];
			$info['to_flag_name']	=$this->flag_name[$info['to_flag']];
			$info['category']		=$type[$info['type_id']];
			$info['type_name']		=$info['money']>0?'转入':'转出';
			
            $this->apiReturn(1, ['data' => $info]);
        }else{
            $this->apiReturn(3);
        }
	}
	
	/**
	* 理财异动详情
	* @param string $_POST['openid'] 用户OpenID
	* @param string $_POST['d_no']	异动流水号
	*/
	public function finance_view(){
		$this->need_param = ['openid', 'd_no'];
        $this->_need_param();
        $this->_check_sign();

        $d_no = I('post.d_no');
        $model = D('Common/ChangeFinanceRelation');

        $info = $model->relation(true)->where(['uid' => $this->uid, 'd_no' => $d_no])->field('etime,ip',true)->find();
        if($info){
			$type=M('change_type')->cache(true,C('CACHE_LEVEL.M'))->getField('id,type_name',true);
			$info['from_flag_name']	=$this->flag_name[$info['from_flag']];
			$info['to_flag_name']	=$this->flag_name[$info['to_flag']];
			$info['category']		=$type[$info['type_id']];
			$info['type_name']		=$info['money']>0?'转入':'转出';
			
            $this->apiReturn(1, ['data' => $info]);
        }else{
            $this->apiReturn(3);
        }
	}
	
	/**
	* 冻结异动详情
	* @param string $_POST['openid'] 用户OpenID
	* @param string $_POST['w_no']	异动流水号
	*/
	public function cash_lock_view(){
		$this->need_param = ['openid', 'w_no'];
        $this->_need_param();
        $this->_check_sign();

        $w_no = I('post.w_no');
        $model = D('Common/ChangeCashLockRelation');

        $info = $model->relation(true)->where(['uid' => $this->uid, 'w_no' => $w_no])->field('etime,ip',true)->find();
        if($info){
			$type=M('change_type')->cache(true,C('CACHE_LEVEL.M'))->getField('id,type_name',true);
			$info['from_flag_name']	=$this->flag_name[$info['from_flag']];
			$info['to_flag_name']	=$this->flag_name[$info['to_flag']];
			$info['category']		=$type[$info['type_id']];
			$info['type_name']		=$info['money']>0?'转入':'转出';
			
            $this->apiReturn(1, ['data' => $info]);
        }else{
            $this->apiReturn(3);
        }
	}
	
	
	/**
	* 本月现金收益
	* @param string $_POST['openid']	用户OpenID
	* @param string $_POST['monty']	月份,格式如 2015-05
	*/
	public function cash_win(){
    	//频繁请求限制,间隔300毫秒
    	$this->_request_check();

    	//必传参数检查
        $this->need_param=array('openid','sign');
        $this->_need_param();
        $this->_check_sign();
		
		$month=date('Y-m');		
		if(I('post.month')) $month=I('post.month');
		
		$map['_string']	='DATE_FORMAT(atime,"%Y-%m")="'.$month.'"';
		$map['uid']		=$this->uid;
		$map['money']	=array('gt',0);
		
		$do=M('change_cash');
		$list=$do->where($map)->field('id,atime,a_no,money,status,from_uid,from_flag,from_account,to_uid,to_flag,to_account,type_id')->order('id desc')->select();
		
        $type=M('change_type')->cache(true,C('CACHE_LEVEL.M'))->getField('id,type_name',true);

        if($list){
			$allmoney=$do->where($map)->sum('money');
	        //格式化处理数据
            foreach($list as $key=>$val){
                $list[$key]['category']	=$type[$val['type_id']];
                $list[$key]['type_name']='转入';
                if($val['from_uid']==$this->uid && $val['from_flag']==1) $list[$key]['account']=$val['from_account'];
                else $list[$key]['account']=$val['to_account'];
            }

	        $this->apiReturn(1,array('data'=>$list,'allmoney'=>$allmoney,'month'=>$month));        	
        }else{
        	//找不到记录
        	$this->apiReturn(3);        	
        }
	
	}
	
	/**
	* 本月积分收益
	* @param string $_POST['openid']	用户OpenID
	* @param string $_POST['monty']	月份,格式如 2015-05
	*/
	public function score_win(){
    	//频繁请求限制,间隔300毫秒
    	$this->_request_check();

    	//必传参数检查
        $this->need_param=array('openid','sign');
        $this->_need_param();
        $this->_check_sign();
		
		$month=date('Y-m');		
		if(I('post.month')) $month=I('post.month');
		
		$map['_string']	='DATE_FORMAT(atime,"%Y-%m")="'.$month.'"';
		$map['uid']		=$this->uid;
		$map['money']	=array('gt',0);
		
		$do=M('change_score');
		$list=$do->where($map)->field('id,atime,c_no,money,status,from_uid,from_flag,from_account,to_uid,to_flag,to_account,type_id')->order('id desc')->select();
		
        $type=M('change_type')->cache(true,C('CACHE_LEVEL.M'))->getField('id,type_name',true);

        if($list){
			$allmoney=$do->where($map)->sum('money');
	        //格式化处理数据
            foreach($list as $key=>$val){
                $list[$key]['category']	=$type[$val['type_id']];
                $list[$key]['type_name']='转入';
                if($val['from_uid']==$this->uid && $val['from_flag']==1) $list[$key]['account']=$val['from_account'];
                else $list[$key]['account']=$val['to_account'];
            }

	        $this->apiReturn(1,array('data'=>$list,'allmoney'=>$allmoney,'month'=>$month));        	
        }else{
        	//找不到记录
        	$this->apiReturn(3);        	
        }
	
	}
	
	
}