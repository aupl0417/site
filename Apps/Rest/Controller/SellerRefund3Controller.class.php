<?php
namespace Rest\Controller;
class SellerRefund3Controller extends CommonController {
    protected $action_logs = array('accept','notreceipt','reject','accept1', 'send_express');
    
    /**
     * 同意售后
     * @param string $_POST['openid']	用户openid
     * @param string $_POST['s_no']		订单号
     * @param string $_POST['r_no']		退款单号
     */
    public function accept(){
        //频繁请求限制,间隔300毫秒
        $this->_request_check();
    
        //必传参数检查
        $this->need_param=array('openid','s_no','r_no','password_pay', 'address_id','sign');
        $this->_need_param();
        $this->_check_sign();
    
        $this->check_password_pay(I('post.password_pay'));
    
        $orders=new \Common\Controller\SellerRefund3Controller(['seller_id' => $this->uid,'s_no' => I('post.s_no')]);
        $res=$orders->accept(I('post.r_no'));
        $this->apiReturn($res['code'],array('data'=>$res['data']),1,$res['msg']);
    }
    
    /**
     * 拒绝售后
     * @param string $_POST['openid']    用户openid
     * @param string $_POST['s_no']      订单号
     * @param string $_POST['r_no']      退款单号
     * @param string $_POST['reason']    拒绝理由
     */
    public function reject(){
        //频繁请求限制,间隔300毫秒
        $this->_request_check();
    
        //必传参数检查
        $this->need_param=array('openid','s_no','r_no','reason','sign');
        $this->_need_param();
        $this->_check_sign();
    
        $orders=new \Common\Controller\SellerRefund3Controller(['seller_id' => $this->uid,'s_no' => I('post.s_no')]);
        $res=$orders->reject(I('post.'));
        $this->apiReturn($res['code'],array('data'=>$res['data']),1,$res['msg']);
    }
    
    /**
     * 已收到售后商品
     * @param string $_POST['openid']    用户openid
     * @param string $_POST['s_no']      订单号
     * @param string $_POST['r_no']      退款单号
     * @param string $_POST['password_pay']      安全密码
     */
    public function accept1(){
        //频繁请求限制,间隔300毫秒
        $this->_request_check();
    
        //必传参数检查
        $this->need_param=array('openid','s_no','r_no','password_pay','sign');
        $this->_need_param();
        $this->_check_sign();
         
        $this->check_password_pay(I('post.password_pay'));
    
        $orders=new \Common\Controller\SellerRefund3Controller(['seller_id' => $this->uid,'s_no' => I('post.s_no')]);
        $res=$orders->accept1(I('post.r_no'));
        $this->apiReturn($res['code'],array('data'=>$res['data']),1,$res['msg']);
    }
    
    /**
     * 寄出商品
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
        $this->need_param=array('openid','express_company_id','express_code','r_no','s_no','sign');
        $this->_need_param();
        $this->_check_sign();
    
        $orders=new \Common\Controller\SellerRefund3Controller(['seller_id' => $this->uid,'s_no' => I('post.s_no')]);
        $res=$orders->send_express(I('post.r_no'));
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
        $status_name=[1=>'买家申请售后',2=>'卖家拒绝售后',3=>'卖家同意售后',4=>'买家寄回商品',5=>'卖家收到商品',6=>'卖家寄出商品', 10 => '等待仲裁',20=>'买家取消售后',100=>'售后完成'];
    
        $do=D('Common/RefundRelation');
        $rs=$do->relation(['seller','shop','orders_goods','user'])->where(['r_no' => I('post.r_no'),'seller_id' => $this->uid])->field('etime,ip',true)->find();
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
            $rs['logs'][$i]['status_name']  =   $status_name[$val['status']];
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
        $status_name=[1=>'售后中',2=>'卖家拒绝售后',3=>'卖家同意售后',4=>'买家寄回商品',5=>'卖家收到商品', 10 => '等待仲裁',6=>'卖家寄出商品',20=>'取消售后',100=>'售后完成'];
        
        $map['seller_id']	= $this->uid;
        $map['orders_status']   =   ['gt', 3];
        if (isset($_POST['s_no']) && !empty(I('post.s_no'))) $map['s_no'] = I('post.s_no');
        if (isset($_POST['r_no']) && !empty(I('post.r_no'))) $map['r_no'] = I('post.r_no');
        if (isset($_POST['nick']) && !empty(I('post.nick'))) {
            $uid=M('user')->cache(true)->where(['nick' => I('post.nick')])->getField('id');
            if($uid) $map['uid']    =$uid;
        }
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
            'relation'  		=>['uid','orders_goods','user'],
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