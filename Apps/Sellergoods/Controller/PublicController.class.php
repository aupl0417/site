<?php
/**
 * Created by PhpStorm.
 * User: dttx
 * Date: 2017/3/28
 * Time: 17:16
 */

namespace Sellergoods\Controller;


use Think\Controller;

class PublicController extends Controller
{
    /**
     * subject: 无权限显示
     * api: noAccess
     * author: Mercury
     * day: 2017-03-28 17:17
     * [字段名,类型,是否必传,说明]
     */
    public function noAccess() {
        $this->display();
    }
}