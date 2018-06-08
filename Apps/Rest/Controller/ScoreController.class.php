<?php
/**
+----------------------------------------------------------------------
| RestFull API 
+----------------------------------------------------------------------
| 积分管理
+----------------------------------------------------------------------
| Author: lazycat <673090083@qq.com>
+----------------------------------------------------------------------
*/
namespace Rest\Controller;
use Think\Controller\RestController;
class ScoreController extends CommonController {
    public function index(){
    	redirect(C('sub_domain.www'));
    }


    /**
    * 创建订单并付款
    */
    public function score_pay(){
        //频繁请求限制,间隔2秒
        $this->_request_check();

        //必传参数检查
        $this->need_param=array('openid','score','password_pay','sign');
        $this->_need_param();
        $this->_check_sign();


        if(I('post.score')<C('cfg.score')['min_score']){
            //转入积分过低
            $this->apiReturn(500);
        }
        if(I('post.score')>C('cfg.score')['max_score']){
            //转入积分过高
            $this->apiReturn(501);
        }

        //检查支付密码
        if(md5(trim(I('post.password_pay'))) != $this->user['password_pay']) $this->apiReturn(6);

        //所需要现金
        $money=round(I('post.score') * C('cfg.score')['ratio'],2);
		
		//检查是否有足够的积分
		$u_account=$this->check_account($this->uid,1,$money);
        $a_account=$this->check_account(1);

        $data['s_no']       =$this->create_orderno();
        $data['uid']        =$this->uid;
        $data['status']     =1;
        $data['score']      =I('post.score');
        $data['money']      =$money;
        $data['pay_money']  =$money;
        $data['ratio']      =C('cfg.score')['ratio'];
        $data['pay_time']   =date('Y-m-d H:i:s');

        $do=M();
        $do->startTrans();

        //创建订单
        if($this->sw[]=D('Common/Score')->create($data)){
            $this->sw[]=D('Common/Score')->add();
            $data['id']=D('Common/Score')->getLastInsID();
        }else{
            $msg=D('Common/Score')->getError();
            goto error;
        }

        //现金异动
        $a_account['ac_cash']+=$money;
        $u_account['ac_cash']-=$money;

        $a_data=array();
        $a_data['uid']            =1;
        $a_data['a_no']           =$this->create_orderno();
        $a_data['money']          =$money;
        $a_data['from_uid']       =$this->uid;
        $a_data['from_account']   =$u_account['ac_cash'];
        $a_data['from_flag']      =1; //现金账户

        $a_data['to_uid']         =1;
        $a_data['to_account']     =$a_account['ac_cash'];
        $a_data['to_flag']        =1; //现金账户
        $a_data['status']         =2;

        $a_data['type_id']        =19; //购买积分
        $a_data['ordersno']       =$data['s_no'];

        if($this->sw[]=D('Common/ChangeCash')->create($a_data)) $this->sw[]=D('Common/ChangeCash')->add();
        else {
            $msg=D('Common/ChangeCash')->getError();
            goto error;
        }

        $u_data=$a_data;
        $u_data['uid']  =$this->uid;
        $u_data['a_no'] =$this->create_orderno();
        $u_data['money']=$money * -1;
        if($this->sw[]=D('Common/ChangeCash')->create($u_data)) $this->sw[]=D('Common/ChangeCash')->add();
        else {
            $msg=D('Common/ChangeCash')->getError();
            goto error;
        }

        //积分异动
        //现金异动
        $a_account['ac_score']-=I('post.score');
        $u_account['ac_score']+=I('post.score');

        $a_data=array();
        $a_data['uid']            =1;
        $a_data['c_no']           =$this->create_orderno();
        $a_data['money']          =I('post.score') * -1;
        $a_data['from_uid']       =1;
        $a_data['from_account']   =$a_account['ac_score'];
        $a_data['from_flag']      =2; //积分账户

        $a_data['to_uid']         =$this->uid;
        $a_data['to_account']     =$u_account['ac_score'];
        $a_data['to_flag']        =2; //积分账户
        $a_data['status']         =2;

        $a_data['type_id']        =19; //购买积分
        $a_data['ordersno']       =$data['s_no'];

        if($this->sw[]=D('Common/ChangeScore')->create($a_data)) $this->sw[]=D('Common/ChangeScore')->add();
        else {
            $msg=D('Common/ChangeScore')->getError();
            goto error;
        }

        $u_data=$a_data;
        $u_data['uid']  =$this->uid;
        $u_data['c_no'] =$this->create_orderno();
        $u_data['money']=I('post.score');
        if($this->sw[]=D('Common/ChangeScore')->create($u_data)) $this->sw[]=D('Common/ChangeScore')->add();
        else {
            $msg=D('Common/ChangeScore')->getError();
            goto error;
        }

        //更新账户
        $u_account['crc']=$this->crc($u_account);
        $a_account['crc']=$this->crc($a_account);
        if(!$this->sw[]=M('account')->where(array('uid'=>$this->uid))->save($u_account)) goto error;        
        if(!$this->sw[]=M('account')->where(array('uid'=>1))->save($a_account)) goto error;        

        success:
            $do->commit();
            $this->apiReturn(1,array('data'=>$data));

        error:
            $do->rollback();
            $this->apiReturn(4,'',1,'操作失败！'.$msg);
    }



    /**
    * 积分购买 -订单记录
    */

    public function orders_list(){
        //频繁请求限制,间隔2秒
        $this->_request_check();

        //必传参数检查
        $this->need_param=array('openid','sign');
        $this->_need_param();
        $this->_check_sign();

        $status=array('待付款','已结算','已作废');

        $map['uid']=$this->uid;
        //if(I('post.status')!='') $map['status']=I('post.status');		
		//if(I('post.month')) $map['_string']='date_format(out_time,"%Y-%m")="'.I('post.month').'"';
        $map['status']=1;

        $pagesize=I('post.pagesize')?I('post.pagesize'):20;
        $pagelist=pagelist(array(
                'table'     =>'score',
                'map'       =>$map,
                'order'     =>'id desc',
                'fields'    =>'',
                'pagesize'  =>$pagesize,
                'action'    =>I('post.action'),
                'query'     =>I('post.query'),
                'p'         =>I('post.p')?I('post.p'):1,
            ));

        foreach($pagelist['list'] as $key=>$val){
            $pagelist['list'][$key]['status_name']=$status[$val['status']];
        }
		

        if($pagelist['list']){
            $this->apiReturn(1,array('data'=>$pagelist));
        }else{
            $this->apiReturn(3);
        }
    }


    /**
    * 单笔订单详情
    */
    public function view(){
        //频繁请求限制,间隔2秒
        $this->_request_check();

        //必传参数检查
        $this->need_param=array('openid','s_no','sign');
        $this->_need_param();
        $this->_check_sign();

        //取订单
        $do=M('score');
        if(!$rs=$do->where(array('uid'=>$this->uid,'s_no'=>I('post.s_no')))->field('id,etime,ip',true)->find()){
            //找不到订单
            $this->apiReturn(0);
        }

        $status=array('待付款','已结算','已作废');
        $rs['status_name']=$status[$rs['status']];

        $this->apiReturn(1,array('data'=>$rs));
    }



}