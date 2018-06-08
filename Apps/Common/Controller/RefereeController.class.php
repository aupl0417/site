<?php
namespace Common\Controller;
use Common\Builder\Activity;
use Rest\Controller\ErpController;
class RefereeController extends SellerOrdersController {
    private $action_logs    =   array('run');   //须要记录日志的方法
    private $_param         =   [];
    private $_res           =   [];
    function __construct($param) {
        //parent::__construct($param);
        $this->s_no     =   $param['s_no'];
        $this->seller_id=   $param['seller_id'];
        $this->_param   =   array_merge($param, I('post.'));
        $this->_res     =   $this->check_s_orders(1);
    }
    
    public function run() {
        if ($this->_res['code'] != 1) {
            return $this->_res;
        }
        $rs=M('refund')
        ->where([
            'id'            => $this->_param['id'],
            'r_no'          => $this->_param['r_no'],
            's_id'          => $this->_res['data']['id'],
            'orders_status' => $this->_res['data']['status']
        ])
        ->field('id,r_no,type,money,score,orders_goods_id,num,activity_money,s_no,refund_express,status')->find();
        switch ($this->_res['data']['status']) {
            case 3:
                if (!in_array($this->_param['status'], [2,5])) return ['code' => 0, 'msg' => '已收货订单才可申请售后！'];
                break;
            default:
                //if (!in_array($rs['status'], [])) return ['code' => 0, 'msg' => '状态不正确'];
        }
        
        
        
        
        if(!$rs) return ['code' => 3, 'msg' => '找不到记录！'];    //找不到记录
        
        //退款订单已失效！
        if(in_array($rs['status'],[20,100,4,5,11]))    return ['code' => 0, 'msg' => '退款订单已失效'];
        $buyer  =M('user')->where(['id' => $this->_res['data']['uid']])->field('nick,erp_uid')->find();
        $seller =M('user')->where(['id' => $this->_res['data']['seller_id']])->field('nick,erp_uid')->find();
        //1=不退积分退款，2=需要退积分的退款
        $refund_type = $rs['score'] > 0? 2:1;
        $inventory_type =   $this->_res['data']['inventory_type'] == 0 ? 2 : $this->_res['data']['inventory_type'];
        $post_data  =   [
            'money'             =>$rs['money'],
            'r_no'              =>$rs['r_no'],
            'score'             =>$rs['score'],
            'buyer_uid'         =>$buyer['erp_uid'],
            'buyer_nick'        =>$buyer['nick'],
            'seller_uid'        =>$seller['erp_uid'],
            'seller_nick'       =>$seller['nick'],
            's_no'              =>$this->_res['data']['s_no'],
            'pay_type'          =>$this->_res['data']['pay_type'],
            'inventory_type'    =>$inventory_type,
            'refundType'        =>$refund_type   
        ];
        if ($rs['refund_express'] > 0) {
            $post_data['money']   +=    $rs['refund_express'];   //有退运费，则相加
        }
        $mallDeal   =   true;
        if ($post_data['money'] > 0) {
            $erp_res = $this->curl('/Erp/refundAdmin', $post_data, 1);
            if ($erp_res['code'] != 1) $mallDeal = false;
        }
        
        if($mallDeal){
            $do=M();
            $do->startTrans();
            //订单参与活动
            $reason      = C('error_code.1007');
            
            if ($rs['money'] > 0) Activity::refundLessMoney($this->s_no, $this->_res['data']['uid'], $rs['money']);  //退款后减金额，主要针对累积升级
            if(!$this->sw[]=M('refund')->where(['r_no' => $rs['r_no']])->save(['status' => 100,'dotime' => date('Y-m-d H:i:s'),'accept_time' => date('Y-m-d H:i:s')])) goto error;

            if ($rs['refund_express'] > 0) {
                if(!$this->sw[]=$do->execute('update '.C('DB_PREFIX').'orders_shop set refund_num=refund_num+'.$rs['num'].',refund_price=refund_price+'.$rs['money'].',refund_score=refund_score+'.$post_data['score'].',money=money-'.$post_data['money'].',refund_express=refund_express+'.$rs['refund_express'].' where s_no="'.$this->s_no .'"')) goto error;
            } else {
                if(!$this->sw[]=$do->execute('update '.C('DB_PREFIX').'orders_shop set refund_num=refund_num+'.$rs['num'].',refund_price=refund_price+'.$rs['money'].',refund_score=refund_score+'.$rs['score'].',money=money-'.$rs['money'].' where s_no="'.$this->s_no.'"')) goto error;
            }
            
            if ($rs['money'] > 0 || $rs['num'] > 0) {
                if(!$this->sw[]=$do->execute('update '.C('DB_PREFIX').'orders_goods set refund_num=refund_num+'.$rs['num'].',refund_price=refund_price+'.$rs['money'].',refund_score=refund_score+'.$rs['score'].' where id='.$rs['orders_goods_id'])) goto error;
            }
            //如果订单中全部款退过完即关闭订单
            if(round($this->_res['data']['money']-$this->_res['data']['daigou_cost'], 2)-round($post_data['money'], 2) ==0){
                Activity::setStatus($this->s_no, $this->_res['data']['uid'], 2);           //关闭其他活动
                if(!$this->sw[]=M('orders_shop')->where(['s_no' => $this->s_no])->save(['status' => 11,'close_time' => date('Y-m-d H:i:s')])) goto error;
            }
           

            $do->commit();
            return ['code' => 1, 'msg' => '操作成功', 'data' => ['r_no' => $rs['r_no']]];

            error:
                $do->rollback();
                return ['code' => 0,C('error_code.0')];     
        }else{
            return ['code' => $erp_res['code'],'msg' => $erp_res['msg']];
        }
    }
}