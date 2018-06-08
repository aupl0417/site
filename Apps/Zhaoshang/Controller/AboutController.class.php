<?php
namespace Zhaoshang\Controller;
use Think\Controller;
class AboutController extends InitController {
    public function index(){
        C('seo',['title' => '关于乐兑 - 招商频道']);
		$this->display();
    }


}