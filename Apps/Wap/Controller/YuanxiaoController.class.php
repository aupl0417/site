<?php
namespace Wap\Controller;
use Think\Controller;
class YuanxiaoController extends CommonController {

    public function index(){
        $url = 'http';
        if ($_SERVER["HTTPS"] == "on"){
            $url .= "s";
        }
        $url .= '://wap.'.C('domain').'/Index/index?url=';
        $this->assign('url',$url);
		$this->display();
    }
    public function detail(){

        $this->display();
    }


}