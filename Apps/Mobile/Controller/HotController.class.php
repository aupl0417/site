<?php
/**
 * -------------------------------------------------
 * 推荐
 * -------------------------------------------------
 * Create by Lazycat <673090083@qq.com>
 * -------------------------------------------------
 * 2017-01-13
 * -------------------------------------------------
 */
namespace Mobile\Controller;
use Think\Controller;
class HotController extends CommonController {

    /**
     * 搜索商品
     */
    public function index(){
        //C('DEBUG_API',true);
        $res = $this->doApi2('/Hot/hot',I('get.'));
        $this->assign('pagelist',$res['data']);

        //print_r($res['data']);

        $this->display();
    }

    public function hot_page(){
        $res = $this->doApi2('/Hot/hot',I('get.'));
        $this->ajaxReturn($res);
    }

}