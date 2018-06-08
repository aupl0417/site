<?php
/*
+--------------------------
+ PC首页相关
+---------------------------
*/
namespace Rest\Controller;
use Think\Controller\RestController;
class HomeController extends CommonController {

    public function index(){
    	redirect(C('sub_domain.www'));
    }


}