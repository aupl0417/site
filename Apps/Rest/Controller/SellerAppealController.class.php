<?php
namespace Rest\Controller;
class SellerAppealController extends CommonController {
    
    public function index() {
        $this->_request_check();
        //必传参数检查
        $this->need_param=array('openid','r_no','sign', 's_no', 'remark');
        $this->_need_param();
        $this->_check_sign();
        $data   =   ['uid' => $this->uid, 'check_type' => 1];
        $res    =   (new \Common\Controller\SellerAppealController(array_merge($data, I('post.'))))->run();
        $this->apiReturn($res['code'],array('data'=>$res['data']),1,$res['msg']);
    }
}