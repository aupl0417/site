<?php
/**
 +----------------------------------------------------------------------
 | RestFull API
 +----------------------------------------------------------------------
 | 买家退款 - 已发货 未收货退款
 +----------------------------------------------------------------------
 | Author: lazycat <673090083@qq.com>
 +----------------------------------------------------------------------
 */
namespace Rest\Controller;
class Refund3Controller extends CommonController {
    protected $action_logs = array('add','cancel','send_express');
    
    /**
     * 可退商品
     * @param string $_POST['openid']	用户openid
     * @param string $_POST['s_no']		订单号
     * @param int 	$_POST['imgsize']	图片尺寸
     * @param int    $_POST['orders_goods_id']   订单中商品ID
     */
    public function goods(){
        //频繁请求限制,间隔300毫秒
        $this->_request_check();
    
        //必传参数检查
        $this->need_param=array('openid','s_no','sign');
        $this->_need_param();
        $this->_check_sign();
    
        $orders=new \Common\Controller\Refund3Controller(['uid' => $this->uid,'s_no' => I('post.s_no')]);
        $res=$orders->goods(I('post.'));
        $this->apiReturn($res['code'],array('data'=>$res['data']),1,$res['msg']);
    }
    
    /**
     * 创建退款订单
     * @param string $_POST['openid']	用户openid
     * @param string $_POST['s_no']		订单号
     * @param int    $_POST['orders_goods_id']   订单中商品ID
     * @param float  $_POST['price']             退款金额
     * @param int    $_POST['num']               退掉商品数量
     * @param string $_POST['reason']            退款原因
     * @param int    $_POST['type']              类型，1退货退款，2只退款
     * @param string $_POST['images']            证据图片，多张用逗号隔开
     */
    public function add(){
        //频繁请求限制,间隔300毫秒
        $this->_request_check();
        //必传参数检查
        $this->need_param=array('openid','s_no','reason','orders_goods_id','sign', 'num');
        $this->_need_param();
        $this->_check_sign();
        $orders=new \Common\Controller\Refund3Controller(['uid' => $this->uid,'s_no' => I('post.s_no')]);
        $res=$orders->add(I('post.'));
        $this->apiReturn($res['code'],array('data'=>$res['data']),1,$res['msg']);
    }
    
    public function edit(){
        //频繁请求限制,间隔300毫秒
        $this->_request_check();
        //必传参数检查
        $this->need_param=array('openid','s_no','reason','r_no','sign', 'num', 'orders_goods_id', 'id');
        $this->_need_param();
        $this->_check_sign();
        $orders=new \Common\Controller\Refund3Controller(['uid' => $this->uid,'s_no' => I('post.s_no')]);
        $res=$orders->edit(I('post.'));
        $this->apiReturn($res['code'],array('data'=>$res['data']),1,$res['msg']);
    }
    
    /**
     * 取消退款
     * @param string $_POST['openid']	用户openid
     * @param string $_POST['s_no']		订单号
     * @param string $_POST['r_no']		退款单号
     */
    public function cancel(){
        //频繁请求限制,间隔300毫秒
        $this->_request_check();
    
        //必传参数检查
        $this->need_param=array('openid','s_no','r_no','sign');
        $this->_need_param();
        $this->_check_sign();
    
        $orders=new \Common\Controller\Refund3Controller(['uid' => $this->uid,'s_no' => I('post.s_no')]);
        $res=$orders->cancel(I('post.r_no'));
        $this->apiReturn($res['code'],array('data'=>$res['data']),1,$res['msg']);
    }
    
    /**
     * 寄回商品
     * @param string $_POST['openid']    用户openid
     * @param string $_POST['s_no']      订单号
     * @param string $_POST['r_no']      退款单号
     * @param float  $_POST['express_company_id']    快递公司ID
     * @param string $_POST['express_code']          快递单号
     * @param string $_POST['reason']                备注
     */
    public function send_express(){
        //频繁请求限制,间隔300毫秒
        $this->_request_check();
    
        //必传参数检查
        $this->need_param=array('openid','express_company_id','express_code','r_no','s_no','sign', 'address_id');
        $this->_need_param();
        $this->_check_sign();
    
        $orders=new \Common\Controller\Refund3Controller(['uid' => $this->uid,'s_no' => I('post.s_no')]);
        $res=$orders->send_express(I('post.'));
        $this->apiReturn($res['code'],array('data'=>$res['data']),1,$res['msg']);
    }
    
    public function accept() {
        //频繁请求限制,间隔300毫秒
        $this->_request_check();
        
        //必传参数检查
        $this->need_param=array('openid','r_no','s_no','sign', 'password_pay');
        $this->_need_param();
        $this->_check_sign();
        $this->check_password_pay(I('post.password_pay'));
        $orders=new \Common\Controller\Refund3Controller(['uid' => $this->uid,'s_no' => I('post.s_no')]);
        $res=$orders->accept(I('post.'));
        $this->apiReturn($res['code'],array('data'=>$res['data']),1,$res['msg']);
    }
    
    /**
     * 退款详情
     * @param string $_POST['openid']	用户openid
     * @param string $_POST['r_no']	退款单号
     */
    public function view(){
        //频繁请求限制,间隔300毫秒
        $this->_request_check();
    
        //必传参数检查
        $this->need_param=array('openid','r_no','sign');
        $this->_need_param();
        $this->_check_sign();
    
        $type_name	=['','换货','维修'];
        $status_name=[1=>'售后中',2=>'卖家拒绝售后',3=>'卖家同意售后',4=>'买家寄回商品',5=>'卖家收到商品',6=>'卖家寄出商品', 10 => '等待仲裁',20=>'取消售后',100=>'售后完成'];
    
        $do=D('Common/RefundRelation');
        $rs=$do->relation(['seller','shop','orders_goods'])->where(['r_no' => I('post.r_no'),'uid' => $this->uid])->field('etime,ip',true)->find();
        if(!$rs) $this->apiReturn(3);
        //数据格式化
        $rs['logs']         =D('Common/RefundLogsRelation')->relation(true)->where(['r_id' => $rs['id']])->field('etime,ip',true)->order('id desc')->select();
        foreach($rs['logs'] as $i => $val){
            if($val['remark']) $rs['logs'][$i]['remark'] = html_entity_decode($val['remark']);
            if($val['images']) $rs['logs'][$i]['images'] = explode(',',rtrim($val['images'], ','));
            if(!empty($val['express_company_id']) && !empty($val['express_code'])) {
                $rs['express'][$i]['express_company_id']   =   $val['express_company_id'];
                $rs['express'][$i]['express_code']         =   $val['express_code'];
                $rs['logs'][$i]['express_company']         =   M('express_company')->where(['id' => $val['express_company_id']])->cache(true)->getField('company');
            }
            //是否有退货地址
            if($val['status']==4){
                $rs['address']=html_entity_decode($val['remark']);
            }
        }
        $rs['can_money']    =number_format($rs['money'] - $rs['activity_money'], 2);
        $rs['orders_goods']	=imgsize_list($rs['orders_goods'],'images',160);
        $rs['shop']			=imgsize_list($rs['shop'],'shop_logo',100);
        $rs['type_name']	=$type_name[$rs['type']];
        $rs['status_name']	=$status_name[$rs['status']];
        $goods=M('orders_goods')
        ->where(['id' => $rs['orders_goods_id'], 's_no' => $rs['s_no']])
        ->field('id,score_ratio,refund_action_num,refund_num,(num-(refund_num+service_num)) as can_num')
        ->find();
        //已申请退款中的商品的数量和金额
        $total=M('refund')
        ->where([
            'orders_goods_id'   => $rs['orders_goods_id'],
            's_no'              => $rs['s_no'],
            'orders_status'     => $rs['orders_status'],
            'status'            => ['not in','100,20'],
            'id'				=> ['neq',$rs['id']],
        ])
        ->field('sum(num) as num')
        ->find();
        //计算还可以退款的数量和金额
        $rs['can_num'] = $goods['can_num'] - $total['num'];
        
        if ($rs['status'] == 3) {
            $rs['address'] = M('refund_logs')->where(['status' => $rs['status'], 'r_no' => $rs['r_no']])->getField('remark');
        }
        $this->apiReturn(1,['data' => $rs]);
    }
    
    public function lists() {
        //频繁请求限制,间隔2秒
        $this->_request_check();
        
        //必传参数检查
        $this->need_param=array('openid','sign');
        $this->_need_param();
        $this->_check_sign();
        
        $type_name	=['','换货','维修'];
        $status_name=[1=>'售后中',2=>'卖家拒绝售后',3=>'卖家同意售后',4=>'买家寄回商品',5=>'卖家收到商品',6=>'卖家寄出商品', 10 => '等待仲裁',20=>'取消售后',100=>'售后完成'];
        
        $map['uid']	= $this->uid;
        $map['orders_status']   =   ['gt', 3];
        if (isset($_POST['s_no']) && !empty(I('post.s_no'))) $map['s_no'] = I('post.s_no');
        if (isset($_POST['r_no']) && !empty(I('post.r_no'))) $map['r_no'] = I('post.r_no');
        if (isset($_POST['nick']) && !empty(I('post.nick'))) $map['nick'] = I('post.nick');
        if (isset($_POST['goods_name']) && !empty(I('post.goods_name'))) $map['goods_name'] = I('post.goods_name');
        if (isset($_POST['status']) && !empty(I('post.status'))) {
            if (strpos(I('post.status'), ',') !== false) {
                $map['status'] = ['in', I('post.status')];
            } else {
                $map['status'] = I('post.status');
            }
        }
        if (!empty($_POST['sday']) || !empty($_POST['eday'])) {
            if (empty(I('post.sday'))) {
                $map['atime'] = ['lt', I('post.eday')];
            } elseif (empty(I('post.eday'))) {
                $map['atime'] = ['gt', I('post.sday')];
            } else {
                $map['atime'] = ['between', I('post.sday') . ',' . I('post.eday')];
            }
        }
        $pagesize=I('post.pagesize')?I('post.pagesize'):12;
        
        $order=I('post.order')?I('post.order'):'id desc';
        if(I('post.sort')){
            $order=str_replace('-', ' ', I('post.sort'));
        }
        
        $pagelist=pagelist(array(
            'table'     		=>'Common/RefundRelation',
            'do'        		=>'D',
            'map'       		=>$map,
            'order'     		=>'atime desc',
            //'fields'    =>'',
            'order'     		=>$order,
            'pagesize'  		=>$pagesize,
            'relation'  		=>['seller','shop','orders_goods'],
            'action'            =>I('post.action'),
            'query'             =>I('post.query')?query_str_(I('post.query')):'',
            'p'                 =>I('post.p'),
            //'cache_name'        =>md5(implode(',',$_POST).__SELF__),
            //'cache_time'        =>C('CACHE_LEVEL.L'),
        
        ));
        
        
        if($pagelist['list']){
            foreach($pagelist['list'] as $i => $val){
                $pagelist['list'][$i]['orders_goods']	=imgsize_list($val['orders_goods'],'images',160);
                $pagelist['list'][$i]['shop']			=imgsize_list($val['shop'],'shop_logo',100);
                $pagelist['list'][$i]['type_name']		=$type_name[$val['type']];
                $pagelist['list'][$i]['status_name']	=$status_name[$val['status']];
            }
        
            $this->apiReturn(1,array('data' => $pagelist));
        }else{
            $this->apiReturn(3);
        }
    }
}