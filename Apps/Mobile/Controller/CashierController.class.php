<?php
/**
 * -------------------------------------------------
 * 收银台-供给APP调用
 * -------------------------------------------------
 * Create by lazycat <673090083@qq.com>
 * -------------------------------------------------
 * 2017-04-05
 * -------------------------------------------------
 */
namespace Mobile\Controller;
use Think\Controller;
class CashierController extends CommonController {
    /**
     * 合并订单支付
     * Create by lazycat
     * 2017-04-06
     */
    public function multi_pay(){
        $this->_check_login();

        $this->display();
    }

    public function multi_pay_iframe(){
        if(empty($_SESSION['user'])){
            $res['msg'] = '请先登录！';
            goto error;
        }

        //C('DEBUG_API',true);
        $res = $this->doApi2('/Cashier/create_multi_form',['openid' => session('user.openid'),'o_no' => I('get.o_no'),'paytype' => I('get.paytype')]);
        if($res['code'] == 1) {
            echo html_entity_decode($res['data']);
            exit();
        }

        error:
        $this->assign('msg',$res['msg']);
        $this->display('error');
    }


    public function single_pay(){
        $this->_check_login();

        $this->display();
    }

    public function single_pay_iframe(){
        if(empty($_SESSION['user'])){
            $res['msg'] = '请先登录！';
            goto error;
        }

        //C('DEBUG_API',true);
        $res = $this->doApi2('/Cashier/create_single_form',['openid' => session('user.openid'),'s_no' => I('get.s_no'),'paytype' => I('get.paytype')]);
        if($res['code'] == 1) {
            echo html_entity_decode($res['data']);
            exit();
        }

        error:
        $this->assign('msg',$res['msg']);
        $this->display('error');
    }


    public function _check_login(){
        if(empty($_SESSION['user'])){
            $this->assign('msg','请先登录！');
            $this->display('error');
            exit();
        }
    }
}