<?php
/**
+----------------------------------------------------------------------
| RestFull API 
+----------------------------------------------------------------------
| 买家中心
+----------------------------------------------------------------------
| Author: lazycat <673090083@qq.com>
+----------------------------------------------------------------------
*/
namespace Rest\Controller;
use Think\Controller\RestController;
class BuyerController extends CommonController {
    public function index(){
    	redirect(C('sub_domain.www'));
    }

    /**
    * 买家中心首页基础数据统计 
    * @param string $_POST['openid']        用户openid
    */
    public function total(){
        //频繁请求限制
        //$this->_request_check();

        //必传参数检查
        $this->need_param=array('openid','sign');
        $this->_need_param();
        $this->_check_sign();

        $res=A('Total')->buyer_ucenter($this->uid);

        $this->apiReturn(1,['data' => $res]);
    }

}