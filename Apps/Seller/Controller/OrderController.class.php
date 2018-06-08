<?php
/**
 * Created by PhpStorm.
 * User: dttx
 * Date: 2016/12/22
 * Time: 11:30
 */

namespace Seller\Controller;


class OrderController extends AuthController
{
    public function index() {
        $this->display();
    }
}