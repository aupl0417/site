<?php
/**
 * -------------------------------------------------
 * 城市
 * -------------------------------------------------
 * Create by Lazycat <673090083@qq.com>
 * -------------------------------------------------
 * 2017-01-16
 * -------------------------------------------------
 */
namespace Mobile\Controller;
use Think\Controller;
class CityController extends CommonController {

    /**
     * 取城市子级
     */
    public function city_item(){
        //C('DEBUG_API',true);
        $res = $this->doApi2('/City/city_item',I('get.'));
        $this->ajaxReturn($res);
    }



}