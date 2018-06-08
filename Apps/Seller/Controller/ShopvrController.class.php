<?php
namespace Seller\Controller;


class ShopvrController extends AuthController {
    public function _initialize() {
        parent::_initialize();
    }
	
    public function index(){
    	$data=I('get.');
    	if (!$data['status']) $data['status']=0;
    	$this->authApi('/SellerShopvr/vrlist',$data);
		//dump($this->_data);
    	$this->assign('list',$this->_data['data']);
    	if (I('get.rules_type')){
    		$this->assign('wd',$this->_get_wrongdoing(I('get.rules_type')));
    	}else {
    		$this->assign('wd',$this->_get_wrongdoing());
    	}
    	$this->authApi('/SellerShopvr/total_sv');
    	$this->assign('total',$this->_data['data']);
    	
    	$this->authApi('/SellerShopvr/total_vr');
    	$this->assign('point',$this->_data['data']);
    	$this->assign('parm',$data);
    	if ($data['rules_type']!=''||$data['wrongdoing']!=''||$data['stime']!=''||$data['etime']!=''){
    		$this->assign('is_s',1);
    	}else {
    		$this->assign('is_s',2);
    	}
    	$this->display();
    }
    
    /**
     * 根据严重程度获取违规规则
     */
    public function get_wrongdoing(){
    	$id=I('get.id',0);
    	$list=M('shop_rules')->field('id,reason,remark')->where(['status'=>1,'rules_type'=>$id])->select();
    	$this->ajaxReturn($list,'JSON');
    }
    
    /**
     * 根据严重程度获取违规规则
     */
    public function _get_wrongdoing($t=0){
    	$where['status']=1;
    	if ($t) $where['rules_type']=$t;
    	$list=M('shop_rules')->field('id,reason,remark')->where($where)->select();
    	return $list;
    }
    
    /**
     * 申诉内容提交
     */
    public function su(){
    	$this->authApi('/SellerShopvr/get_shopvr',I('get.'));
    	$this->assign('info',$this->_data['data']);
    	$this->display();
    }
    
    /**
     * 申诉详情
     */
    public function info(){
    	$this->authApi('/SellerShopvr/get_shopvr',I('get.'));
    	$this->assign('info',$this->_data['data']);
    	$this->display();
    }
    
}