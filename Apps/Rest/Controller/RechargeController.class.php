<?php
/**
+----------------------------------------------------------------------
| RestFull API 
+----------------------------------------------------------------------
| 充值管理
+----------------------------------------------------------------------
| Author: lazycat <673090083@qq.com>
+----------------------------------------------------------------------
*/
namespace Rest\Controller;
use Think\Controller\RestController;
class RechargeController extends CommonController {
    protected $action_logs = array('add','update_status');
	public function index(){
    	redirect(C('sub_domain.www'));
    }
    /**
    * 创建充值订单
    */
    /*
    public function add(){
        //频繁请求限制,间隔2秒
        $this->_request_check();

        //必传参数检查
        $this->need_param=array('openid','money','pay_type','sign');
        $this->_need_param();
        $this->_check_sign();

        $data['r_no']       =$this->create_orderno();
        $data['uid']        =$this->uid;
        $data['money']      =I('post.money');
        $data['pay_type']   =I('post.pay_type');
        $data['ordersid']   =I('post.ordersid');
        $data['remark']     =I('post.remark');

        $do=D('Common/Recharge');
        if($do->create($data)){
            if($do->add()){
                $this->apiReturn(1,array('data'=>$data));
            }else{
                $this->apiReturn(0);
            }
        }else{
            $this->apiReturn(4,'',1,'操作失败！'.$do->getError());
        }
    }
    */

    /**
    * 充值成功更新状态
    */
    /*
    public function update_status(){
        //频繁请求限制,间隔2秒
        $this->_request_check();

        //必传参数检查
        $this->need_param=array('openid','money','r_no','sign');
        $this->_need_param();
        $this->_check_sign();

        $do=M('recharge');
        if(!$rs=$do->where(array('uid'=>$this->uid,'r_no'=>I('post.r_no')))->field('id,uid,status,r_no,money,ordersid')->find()){
            //找不到记录
            $this->apiReturn(3);
        }

        //该订单已支付！
        if($rs['status']!=0) $this->apiReturn(131);

        //金额被篡改！
        if($rs['money']!=I('post.money')) $this->apiReturn(132);

        //账户余额是否足够提现
        $u_account=$this->check_account($this->uid);
        $a_account=$this->check_account(1);

        $do->startTrans();
        $data=array();
        $data['pay_time']       =date('Y-m-d H:i:s');
        $data['status']         =1;
        $data['openid']         =I('post.openid');
        $data['trade_no']       =I('post.trade_no');
        $data['trade_status']   =I('post.trade_status');
        
        if(!$sw1=$do->where(array('id'=>$rs['id']))->save($data)) goto error;

        $u_account['ac_cash']+=$rs['money'];
        $u_account['crc']=$this->crc($u_account);

        $a_account['ac_cash']-=$rs['money'];
        $a_account['crc']=$this->crc($a_account);

        //更新账户
        if(!$sw2=M('account')->where(array('uid'=>$this->uid))->save($u_account)) goto error;
        if(!$sw3=M('account')->where(array('uid'=>1))->save($a_account)) goto error;

        //创建现金异动记录　- 系统账户转入个人账户
        $data=array();
        $data['a_no']           =$this->create_orderno();
        $data['money']          =$rs['money'];
        $data['from_uid']       =1;
        $data['from_account']   =$a_account['ac_cash'];
        $data['from_flag']      =1; //现金账户

        $data['to_uid']         =$this->uid;
        $data['to_account']     =$u_account['ac_cash'];
        $data['to_flag']        =1; //提现账户
        $data['status']         =2;

        $data['type_id']        =10; //充值
        $data['ordersno']       =$rs['r_no'];


        if($sw4=D('Common/ChangeCash')->create($data)) $sw4=D('Common/ChangeCash')->add();
        else {
            $msg[]=D('Common/ChangeCash')->getError();
            goto error;
        }  

        success:
            $this->sw=array($sw1,$sw2,$sw3,$sw4);
            $do->commit();
            $this->apiReturn(1,array('data'=>$rs));

        error:
            $this->sw=array($sw1,$sw2,$sw3,$sw4);
            $do->rollback();
            $this->apiReturn(4,'',1,'操作失败！'.@implode('<br>',$msg));

    }
    */

    /**
    * 充值 - 创建充值订单
    * @param string $_POST['openid']    用户openid
    * @param float  $_POST['money']     充值金额
    * @param int    $_POST['paytype']   1=支付宝,2=微信
    */
    public function add(){
        //频繁请求限制
        $this->_request_check();

        //必传参数检查
        $this->need_param=array('openid','money','paytype','sign');
        $this->_need_param();
        $this->_check_sign();

        $data   =   [
            'r_no'      =>$this->create_orderno(),
            'uid'       =>$this->uid,
            's_no'      =>I('post.s_no'),
            'o_no'      =>I('post.o_no'),
            'money'     =>I('post.money'),
            'pay_type'  =>I('post.paytype')
        ];

        $do=D('Common/Recharge');
        if(!$do->create($data)){
            $this->apiReturn(4,'',1,$do->getError());
        }

        if($do->add()){
            $data['id'] = $do->getLastInsID();

            //ERP充值类型，2 支付宝 ，1 微信，5 工行POS, 6 微赢微信 ，7 微赢支付宝 ，8 银联
            $paytype=I('post.type')==2?1:2;

            $res=A('Erp')->_recharge_add(['money' => I('post.money'),'r_no' =>$data['r_no'],'paytype'=>$paytype]);
            if($res->code==1 && $do->where(['id' => $data['id']])->save(['erp_no' => $res->info])){
                $data['erp_no'] = $res->info;
                $this->apiReturn(1,['data' => $data]);
            }else $this->apiReturn(0);
            
        }else $this->apiReturn(0);        
    }

    /**
    * 充值成功更新状态
    * @param string $_POST['openid']    用户openid
    * @param string $_POST['r_no']      充值流水号
    * @param string $_POST['trade_no']  第三方交易流水号
    */

    public function update_status(){
        //频繁请求限制,间隔2秒
        $this->_request_check();

        //必传参数检查
        $this->need_param=array('openid','r_no','trade_no','sign');
        $this->_need_param();
        $this->_check_sign();

        $do=M('recharge');
        if(!$rs=$do->where(array('uid'=>$this->uid,'r_no'=>I('post.r_no')))->field('id,uid,status,r_no,money,erp_no')->find()){
            //找不到记录
            $this->apiReturn(3);
        }

        //该订单已支付！
        if($rs['status']!=0) $this->apiReturn(131);

        $data   =[
            'status'        => 1,
            'step'          => 1,
            'pay_time'      => date('Y-m-d H:i:s'),
            'trade_no'      =>I('post.trade_no'),
            'trade_status'  =>I('post.trade_status'),
        ];

        //更新充值订单状态，考虑到ERP充值状态可能会更新失败，所以不列入事务
        $do->where(['id' => $rs['id'],'status' => 0])->save($data);

        //更新ERP充值状态
        $res=A('Erp')->_recharge_status($rs['erp_no'],I('post.trade_no'));
        if($res->code==1){
            $do->where(['id' => $rs['id']])->save(['step'=>2]);
        }
        $this->apiReturn($res->code);

    }


    /**
    * 充值记录
    * @param string $_POST['openid']    用户openid
    */
    public function recharge_list(){
        //必传参数检查
        $this->need_param=array('openid','sign');
        $this->_need_param();
        $this->_check_sign();

        $payname=array('1'=>'支付宝充值','2'=>'微信充值');

        $map['uid']=$this->uid;
        $map['status']=1;
        $pagesize=I('post.pagesize')?I('post.pagesize'):20;
        $pagelist=pagelist(array(
                'table'     =>'recharge',
                'map'       =>$map,
                'order'     =>'atime desc',
                'fields'    =>'id,atime,r_no,status,money,ordersid,account,pay_type,pay_time,openid,trade_status,trade_no,remark',
                'pagesize'  =>$pagesize,
                'action'    =>I('post.action'),
                'p'         =>I('post.p')?I('post.p'):1
            ));

        if($pagelist['list']){
            foreach($pagelist['list'] as $key=>$val){
                $pagelist['list'][$key]['pay_name']=$payname[$val['pay_type']];
            }

            $this->apiReturn(1,array('data'=>$pagelist));
        }else{
            $this->apiReturn(3);
        }
    }
	
	/**
	* 充值订单详情
	* @param string $_POST['openid']   用户OpenID
	* @param string $_POST['r_no']     充值订单流水号
	*/
	public function view(){
        //必传参数检查
        $this->need_param=array('openid','r_no','sign');
        $this->_need_param();
        $this->_check_sign();				
	}
}