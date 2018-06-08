<?php
/**
 * -------------------------------------------------
 * 下载页面
 * -------------------------------------------------
 * Create by Lizuheng
 * -------------------------------------------------
 * 2017-04-15
 * -------------------------------------------------
 */
namespace Mobile\Controller;
use Think\Controller;
class DownloadController extends CommonController {
    public function index(){		
		$this->display();
    }

    public function apk(){
        $url = 'http://down.dttx.com/trj.apk';
        header('Content-type: application/octet-stream');
        header('location:'.$url);
    }
}