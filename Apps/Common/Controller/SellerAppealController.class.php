<?php
namespace Common\Controller;
class SellerAppealController extends SellerOrdersController{
    const   TYPE_ONE    =   1;      //已发货商品只退款
    const   TYPE_TWO    =   2;      //已发货商品退货退款
    const   TYPE_THREE  =   3;      //售后换货维修
    const   TYPE_FOUR   =   4;      //售后商品退货退款
    
    protected $_orders;
    protected $_param   =   [];
    protected $_res     =   [];
    
    function __construct($param = null) {
        $this->s_no     =   $param['s_no'];
        $this->seller_id=   $param['uid'];
        if(!is_null($param)) {
            $this->_param   =   $param;
        } else {
            $this->_param   =   I('post.');
        }
        $this->_res         =   $this->check_s_orders($this->_param['check_type']);
    }
    
    
    public function run() {
        if ($this->_res['code'] != 1) return $this->apiReturn($this->_res['code'], '', $this->_res['msg']);
        //if ($this->_res['data']['status'] <= self::TYPE) return ['code' => 0, 'msg' => '已收货订单才可申请售后！'];
        $rs     =   $this->getData($this->_param['status']);
        if (false == $rs) return $this->apiReturn(3);    //找不到记录
        $do     =   M();
        $do->startTrans();
        if(!$this->sw[]=M('refund')->where(['id' => $rs['id']])->save(['status' => 10,'dotime' => date('Y-m-d H:i:s'),'accept_time' => date('Y-m-d H:i:s')])) goto error;
        //日志数据
        $remark =   $this->statusName($rs);
        if ($remark == false) return $this->apiReturn(1005);
        $remark.=   $this->_param['remark'];
        $logs=[
            'r_id'          =>$rs['id'],
            'r_no'          =>$rs['r_no'],
            'uid'           =>$this->_param['uid'],
            //'status'        =>$rs['status'],
            'status'        =>10,
            'type'          =>$rs['type'],
            'remark'        =>$remark,
            'images'        =>$this->_param['images'] ? $this->_param['images'] : '',
        ];
        
        if(!$this->sw[]=D('Common/RefundLogs')->create($logs)){
            $msg=D('Common/RefundLogs')->getError();
            goto error;
        }
        
        if(!$this->sw[]=D('Common/RefundLogs')->add()) goto error;
        
        $do->commit();
        return $this->apiReturn(1, ['data' => ['r_no' => $rs['r_no']]]);
        
        error:
        $do->rollback();
        return $this->apiReturn(0,'',C('error_code.0').$msg);
    }
    
    private function getData($status = null) {
        $rs=M('refund')
        ->where([
            'r_no'          => $this->_param['r_no'],
            's_id'          => $this->_res['data']['id'],
            'orders_status' => $this->_res['data']['status'],
            'seller_id'     => $this->seller_id,
        ])
        ->field('id,r_no,status,type,money,score,orders_goods_id,num,s_no,activity_money,refund_express,orders_status')->find();
        if(!$rs) return false;
        //售后订单已失效！
        //if($rs['status']!=$status) return $this->apiReturn(1005);
        
        return $rs;
    }
    
    /**
     * 状态名称
     * @param unknown $status
     */
    private function statusName($param) {
        $statusArr      =   [];
        if ($param['orders_status'] == 3) {
            $statusArr  =   [5 => '卖家未收到退货提出申诉'];
        } elseif ($param['orders_status'] > 3) {
            $statusArr  =   [4 =>'卖家未收到售后商品提出申诉'];
        }
        
        if (!array_key_exists($param['status'], $statusArr) || empty($statusArr)) return false;
        
        return '<p class="strong text_red">卖家提出申诉</p>' . $statusArr[$param['status']];
    }
}