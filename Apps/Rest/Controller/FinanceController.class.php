<?php
/*
+----------------------------
+ 理财
+-----------------------------
*/
namespace Rest\Controller;
use Think\Controller\RestController;
class FinanceController extends CommonController {
    public function index(){
    	redirect(C('sub_domain.www'));
    }


    /**
    * 理财收益
    * @param ingeter $uid 用户ID
    */
    public function win_money($uid){
        //累计收益
        $do=M('finance');
        $result['finance_win']=$do->where(array('uid'=>$uid,'status'=>4))->sum('win_money');
        $result['finance_win']=is_null($result['finance_win'])?'0.00':$result['finance_win'];

        //上月收益
        $result['finance_win_this_month']=$do->where(array('uid'=>$uid,'status'=>4,'_string'=>'date_format(out_time,"%Y-%m")="'.date('Y-m',time()).'"'))->sum('win_money');
        $result['finance_win_prev_month']=$do->where(array('uid'=>$uid,'status'=>4,'_string'=>'date_format(out_time,"%Y-%m")="'.date('Y-m',time()-86400*30).'"'))->sum('win_money');
        $result['finance_win_this_month']=is_null($result['finance_win_this_month'])?'0.00':$result['finance_win_this_month'];
        $result['finance_win_prev_month']=is_null($result['finance_win_prev_month'])?'0.00':$result['finance_win_prev_month'];

        return $result;
    }

    /**
    * 创建理财订单
    */
    public function add(){
        //频繁请求限制,间隔2秒
        $this->_request_check();

        //必传参数检查
        $this->need_param=array('openid','score','sign');
        $this->_need_param();
        $this->_check_sign();

        if(I('post.score')<C('cfg.finance')['min_money']){
            //转入积分过低
            $this->apiReturn(110);
        }
        if(I('post.score')>C('cfg.finance')['max_money']){
            //转入积分过高
            $this->apiReturn(111);
        }
		
		//检查是否有足够的积分
		$this->check_account($this->uid,2,I('post.score'));


        $data['f_no']   =$this->create_orderno();
        $data['uid']    =$this->uid;
        $data['score']  =I('post.score');
        $data['money']  =round(C('cfg.finance')['add_ratio'] * $data['score'],2);
        $data['ratio']  =C('cfg.finance')['add_ratio'];
        $data['year_ratio']=C('cfg.finance')['year_ratio'];
        $data['total_money']=$data['score']+$data['money'];

        $do=D('Common/Finance');

        if($do->create($data)){
            if($do->add()){
                $this->apiReturn(1,array('data'=>$data));
            }else{
                $this->apiReturn(0);
            }
        }else{
            $this->apiReturn(4,'',1,$do->getError());
        }

        
    }


    /**
    * 支付理财订单
    */
    public function pay(){
        //频繁请求限制,间隔2秒
        $this->_request_check();

        //必传参数检查
        $this->need_param=array('openid','f_no','password_pay','sign');
        $this->_need_param();
        $this->_check_sign();

        //验证支付密码
        $do=M('user');
        $urs=$do->where(array('id'=>$this->uid))->field('password_pay')->find();
        if(md5(I('post.password_pay'))!=$urs['password_pay']){
            //支付密码错误
            $this->apiReturn(6);
        }

        //取订单
        $do=M('finance');
        if(!$rs=$do->where(array('uid'=>$this->uid,'f_no'=>I('post.f_no')))->field('atime,etime,ip',true)->find()){
            //找不到订单
            $this->apiReturn(3);
        }elseif($rs['status']!=0){
            //该订单已支付，请不要重复操作！
            $this->apiReturn(112);
        }

        //检查用户账户是否正常、余额是否足够
        $u_account=$this->check_account($this->uid);

        if($u_account['ac_cash']<$rs['money']) $this->apiReturn(115);     //现金账户余额不足
        if($u_account['ac_score']<$rs['score']) $this->apiReturn(117);    //积分账户余额不足

        $do->startTrans();

        //现金转出
        $u_account['ac_cash']-=$rs['money'];
        $u_account['ac_score']-=$rs['score'];
        $u_account['ac_finance']+=$rs['money'];        


        //现金异动
        $data=array();
        $data['uid']            =$this->uid;
        $data['a_no']           =$this->create_orderno();
        $data['money']          =$rs['money']*-1;
        $data['from_uid']       =$this->uid;
        $data['from_account']   =$u_account['ac_cash'];
        $data['from_flag']      =1; //现金账户

        $data['to_uid']         =$this->uid;
        $data['to_account']     =$u_account['ac_finance'];
        $data['to_flag']        =3; //理财账户

        $data['status']         =2;
        $data['type_id']        =3; //现金转至理财账户
        $data['ordersno']       =$rs['f_no'];

        if($sw2=D('Common/ChangeCash')->create($data)) $sw2=D('Common/ChangeCash')->add();
        else {
            $msg[]=D('Common/ChangeCash')->getError();
            goto error;
        }        

        //理财异动
        $d_data=$data;
        $d_data['d_no']           =$this->create_orderno();
        $d_data['money']          =$rs['money'];

        if($sw3=D('Common/ChangeFinance')->create($d_data)) $sw3=D('Common/ChangeFinance')->add();
        else {
            $msg[]=D('Common/ChangeFinance')->getError();
            goto error;
        }          

		$u_account['ac_finance']+=$rs['score']; 
        //积分异动
        $data=array();
        $data['uid']            =$this->uid;
        $data['c_no']           =$this->create_orderno();
        $data['money']          =$rs['score']*-1;
        $data['from_uid']       =$this->uid;
        $data['from_account']   =$u_account['ac_score'];
        $data['from_flag']      =2; //积分账户

        $data['to_uid']         =$this->uid;
        $data['to_account']     =$u_account['ac_finance'];
        $data['to_flag']        =3; //理财账户

        $data['status']         =2;
        $data['type_id']        =11; //积分转至理财账户
        $data['ordersno']       =$rs['f_no'];

        if($sw4=D('Common/ChangeScore')->create($data)) $sw4=D('Common/ChangeScore')->add();
        else {
            $msg[]=D('Common/ChangeScore')->getError();
            goto error;
        }        

        //理财异动
        $d_data=$data;
        $d_data['d_no']           =$this->create_orderno();
        $d_data['money']          =$rs['score'];

        if($sw5=D('Common/ChangeFinance')->create($d_data)) $sw5=D('Common/ChangeFinance')->add();
        else {
            $msg[]=D('Common/ChangeFinance')->getError();
            goto error;
        }        


        //更新订单
        if(!$sw6=M('finance')->where(array('id'=>$rs['id']))->save(array('status'=>1,'pay_time'=>date('Y-m-d H:i:s')))) goto error;

        $u_account['crc']=$this->crc($u_account);
        if(!$sw1=M('account')->where(array('uid'=>$this->uid))->save($u_account)) goto error;

        success:
            $this->sw=array($sw1,$sw2,$sw3,$sw4,$sw5,$sw6);
            $do->commit();
            $this->apiReturn(1);

        error:
            $this->sw=array($sw1,$sw2,$sw3,$sw4,$sw5,$sw6);
            $do->rollback();
            $this->apiReturn(4,'',1,'操作失败！'.@implode('<br>',$msg));

    }



    /**
    * 获取理财订单记录
    */

    public function finance_list(){
        //频繁请求限制,间隔2秒
        $this->_request_check();

        //必传参数检查
        $this->need_param=array('openid','sign');
        $this->_need_param();
        $this->_check_sign();

        $status=array('待付款','理财中','转出申请','转出驳回','已转出');

        $map['uid']=$this->uid;
        if(I('post.status')!='') $map['status']=I('post.status');
		
		if(I('post.month')) $map['_string']='date_format(out_time,"%Y-%m")="'.I('post.month').'"';

        $pagesize=I('post.pagesize')?I('post.pagesize'):20;
        $pagelist=pagelist(array(
                'table'     =>'finance',
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
			$pagelist['list'][$key]['win_money_30day']=round($val['year_ratio']*$val['total_money']/365*30,2);
        }
		
		$pagelist['win']=$this->win_money($this->uid);

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
        $this->need_param=array('openid','f_no','sign');
        $this->_need_param();
        $this->_check_sign();

        //取订单
        $do=M('finance');
        if(!$rs=$do->where(array('uid'=>$this->uid,'f_no'=>I('post.f_no')))->field('id,etime,ip',true)->find()){
            //找不到订单
            $this->apiReturn(0);
        }

        $status=array('待付款','理财中','转出申请','被驳回','已转出');
        $rs['status_name']=$status[$rs['status']];
        $rs['win_money_30day']=round($rs['year_ratio']*$rs['total_money']/365*30,2);

        $rs['is_out']=0;
        if($rs['status']==1){
            $sday=date_create($rs['pay_time']);
            $eday=date_create();
            $rs['date_diff']=date_diff($sday,$eday);
            if(C('cfg.finance')['min_day'] <= $rs['date_diff']->days) $rs['is_out']=1;
        }



        $this->apiReturn(1,array('data'=>$rs));
    }

    /**
    * 理财转出
    */
    public function out(){
        //频繁请求限制,间隔2秒
        $this->_request_check();

        //必传参数检查
        $this->need_param=array('openid','f_no','password_pay','sign');
        $this->_need_param();
        $this->_check_sign();


        //验证支付密码
        $do=M('user');
        $urs=$do->where(array('id'=>$this->uid))->field('password_pay')->find();
        if(md5(I('post.password_pay'))!=$urs['password_pay']){
            //支付密码错误
            $this->apiReturn(6);
        }

        $do=M('finance');
        if(!$rs=$do->where(array('uid'=>$this->uid,'f_no'=>I('post.f_no')))->field('atime,etime,ip',true)->find()){
            //记录不存在
            $this->apiReturn(3);
        }

        //不充许操作此状态下的记录！
        if($rs['status']!=1) $this->apiReturn(113);

        //是否满30天以上
        //if($rs['pay_time'] > date('Y-m-d H:i:s',time()-86400*C('cfg.finance')['min_day'])) $this->apiReturn(114);
        $sday=date_create($rs['pay_time']);
        $eday=date_create();
        $rs['date_diff']=date_diff($sday,$eday);
        if(C('cfg.finance')['min_day'] > $rs['date_diff']->days) $this->apiReturn(114);

        //利息，由admin给出
        $win_money=round($rs['total_money']*$rs['year_ratio']/365*$rs['date_diff']->days,2);

        //税费，直接扣除转入admin
        $tax_money=round(($rs['total_money']+$win_money)*C('cfg.finance')['withdrawal_ratio'],2);

        //实际转出金额
        $out_money=$rs['total_money']-$tax_money;

        $do->startTrans();

        //更新状态
        if(!$sw1=M('finance')->where(array('id'=>$rs['id']))->save(array('win_money'=>$win_money,'tax_money'=>$tax_money,'out_money'=>$out_money,'status'=>4,'out_time'=>date('Y-m-d H:i:s')))) goto error;

        //税收转入系统账户
        //检查用户账户是否正常、余额是否足够
        $u_account=$this->check_account($this->uid);
        $a_account=$this->check_account(1); //系统账户

        $u_account['ac_finance']-=$tax_money;
        $a_account['ac_cash']+=$tax_money;

        //dump(M('account')->getLastSQL());

        //admin现金异动 - 税费转入
        $data=array();
        $data['uid']            =1;
        $data['a_no']           =$this->create_orderno();
        $data['money']          =$tax_money;
        $data['from_uid']       =$this->uid;
        $data['from_account']   =$u_account['ac_finance'];
        $data['from_flag']      =3; //理财账户

        $data['to_uid']         =1; //系统用户
        $data['to_account']     =$a_account['ac_cash'];
        $data['to_flag']        =1; //现金账户
        $data['status']         =2;

        $data['type_id']        =8; //理财转出扣税
        $data['ordersno']       =$rs['f_no'];

        if($sw4=D('Common/ChangeCash')->create($data)) $sw4=D('Common/ChangeCash')->add();
        else {
            $msg[]=D('Common/ChangeCash')->getError();
            goto error;
        }

        //用户理财账户异动 - 税费转出
        $d_data=$data;
        $d_data['uid']            =$this->uid;
        $d_data['d_no']           =$this->create_orderno();
        $d_data['money']          =$tax_money*-1;

        if($sw5=D('Common/ChangeFinance')->create($d_data)) $sw5=D('Common/ChangeFinance')->add();
        else {
            $msg[]=D('Common/ChangeFinance')->getError();
            goto error;
        }
		
		$a_account['ac_cash']-=$win_money;
		$u_account['ac_cash']+=$win_money;
		
        //admin现金异动 - 利息转出
        $data=array();
        $data['uid']            =1;
        $data['a_no']           =$this->create_orderno();
        $data['money']          =$win_money * -1;
        $data['from_uid']       =1;	//系统用户
        $data['from_account']   =$a_account['ac_cash'];
        $data['from_flag']      =1; //理财账户

        $data['to_uid']         =$this->uid; 
        $data['to_account']     =$u_account['ac_cash'];
        $data['to_flag']        =1; //现金账户
        $data['status']         =2;

        $data['type_id']        =16; //理财利息
        $data['ordersno']       =$rs['f_no'];

        if($sw6=D('Common/ChangeCash')->create($data)) $sw6=D('Common/ChangeCash')->add();
        else {
            $msg[]=D('Common/ChangeCash')->getError();
            goto error;
        }

        //现金异动 - 利息转入
        $d_data=$data;
        $d_data['uid']            =$this->uid;
        $d_data['a_no']           =$this->create_orderno();
        $d_data['money']          =$win_money;

        if($sw7=D('Common/ChangeCash')->create($d_data)) $sw7=D('Common/ChangeCash')->add();
        else {
            $msg[]=D('Common/ChangeCash')->getError();
            goto error;
        }		
		

        $u_account['ac_finance']	-=$out_money;
		$u_account['ac_cash']		+=$out_money;
        
        //扣税后的金额转入用户现金账户
        $data=array();
        $data['uid']            =$this->uid;
        $data['a_no']           =$this->create_orderno();
        $data['money']          =$out_money;
        $data['from_uid']       =$this->uid;
        $data['from_account']   =$u_account['ac_finance'];
        $data['from_flag']      =3; //理财账户

        $data['to_uid']         =$this->uid;
        $data['to_account']     =$u_account['ac_cash'];
        $data['to_flag']        =1; //现金账户
        $data['status']         =2;

        $data['type_id']        =4; //理财转出
        $data['ordersno']       =$rs['f_no'];


        if($sw8=D('Common/ChangeCash')->create($data)) $sw8=D('Common/ChangeCash')->add();
        else {
            $msg[]=D('Common/ChangeCash')->getError();
            goto error;
        }  

        //用户理财账户异动
        $d_data=$data;
        $d_data['uid']            =$this->uid;
        $d_data['d_no']           =$this->create_orderno();
        $d_data['money']          =$out_money*-1;

        if($sw9=D('Common/ChangeFinance')->create($d_data)) $sw9=D('Common/ChangeFinance')->add();
        else {
            $msg[]=D('Common/ChangeFinance')->getError();
            goto error;
        } 

        //更新账户
        $u_account['crc']=$this->crc($u_account);
        $a_account['crc']=$this->crc($a_account);    
        if(!$sw2=M('account')->where(array('uid'=>$this->uid))->save($u_account)) goto error;
        if(!$sw3=M('account')->where(array('uid'=>1))->save($a_account)) goto error;

        success:
            $this->sw=array($sw1,$sw2,$sw3,$sw4,$sw5,$sw6,$sw7,$sw8,$sw9);
            $do->commit();
            $this->apiReturn(1);

        error:
            $this->sw=array($sw1,$sw2,$sw3,$sw4,$sw5,$sw6,$sw7,$sw8,$sw9);
            $do->rollback();
            $this->apiReturn(4,'',1,'操作失败！'.@implode('<br>',$msg));

    }


}