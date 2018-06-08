<?php
/**
 * -------------------------------------------------
 * 秒杀
 * -------------------------------------------------
 * Create by Lazycat <673090083@qq.com>
 * -------------------------------------------------
 * 2017-03-31
 * -------------------------------------------------
 */
namespace Mobile\Controller;
use Think\Controller;
class MiaoshaController extends CommonController {
    /**
     * 秒杀首页
     * Create by Lazycat
     * 2017-03-31
     */
    public function index(){
        //C('DEBUG_API',true);
        $res = $this->doApi2('/Miaosha/goods_list',I('get.'));
        //print_r($res);

        $this->assign('times',$res['data']['times']);
        $this->assign('list',$res['data']['goods_list']);
        $this->display();
    }
}