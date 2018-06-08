<?php
namespace Home\Controller;
use Think\Controller;
class NiepanController extends Controller {

	public function index(){
        $url = 'http';
        if ($_SERVER["HTTPS"] == "on"){
            $url .= "s";
        }
        $url .= '://item.'.C('domain').'/';
        $this->assign('url',$url);
		$this->display();

	}







}