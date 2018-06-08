<?php
/**
 * Created by PhpStorm.
 * User: dttx
 * Date: 2017/3/29
 * Time: 9:41
 */

namespace Home\Controller;


use Think\Controller;

class NoAccessController extends Controller
{
    public function index()
    {
        $this->display();
    }
}