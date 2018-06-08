<?php
namespace Rest\Controller;
use Think\Controller\RestController;
class IndexController extends CommonController {
    public function index(){
    	redirect(C('sub_domain.www'));
    }
}