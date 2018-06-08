<?php
namespace Zhaoshang\Controller;
use Think\Controller;
class GuideController extends InitController {
    public function index(){
        C('seo',['title' => '入驻指南 - 招商频道']);
		$this->display();
    }


}