<?php
/**
 * -------------------------------------------------
 * 抽奖页面
 * -------------------------------------------------
 * Create by Lizuheng
 * -------------------------------------------------
 * 2017-04-18
 * -------------------------------------------------
 */
namespace Mobile\Controller;
use Think\Controller;
class LuckdrawController extends CommonController {
    public function index(){
        $url = DM('m', '/drawluck/item/id/13');
        redirect($url);
		$this->display();
    }
}