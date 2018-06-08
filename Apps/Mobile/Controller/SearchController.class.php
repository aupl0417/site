<?php
/**
 * -------------------------------------------------
 * 商品搜索
 * -------------------------------------------------
 * Create by Lazycat <673090083@qq.com>
 * -------------------------------------------------
 * 2017-01-09
 * -------------------------------------------------
 */
namespace Mobile\Controller;
use Think\Controller;
class SearchController extends CommonController {

    public function index(){
        $res = $this->doApi2('/Search/hot_keywords');
        $this->assign('keywords',$res['data']);
        //print_r($res);

		$this->display();
    }


    /**
     * 搜索商品
     */
    public function goods(){
		// 商品搜索，话费，充值，流量
        if(preg_match('/话费|流量|充值|手机/',I("get.q"))){
            $this->assign('recharge',1);
        }
        //C('DEBUG_API',true);
        $_GET['pagesize'] = 20;
        $res = $this->doApi2('/Search/goods',I('get.'));
        $this->assign('pagelist',$res['data']);
        //print_r($res['data']);

        $city = $this->doApi2('/City/city_level',['level' => 2]);
        $this->assign('city',$city['data']);

        //print_r($res['data']);

        $this->display();
    }

    public function goods_page(){
        $_GET['pagesize'] = 20;
        $res = $this->doApi2('/Search/goods',I('get.'));
        $this->ajaxReturn($res);
    }

}