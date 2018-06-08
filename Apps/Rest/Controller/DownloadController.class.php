<?php
/**
+----------------------------------------------------------------------
| RestFull API 
+----------------------------------------------------------------------
| 安卓APP自动更新
+----------------------------------------------------------------------
| Author: lazycat <673090083@qq.com>
+----------------------------------------------------------------------
*/
namespace Rest\Controller;
use Think\Controller\RestController;
class DownloadController extends RestController {
	/**
	* android下载
	*/
	public function android(){
		$do=M('app_upgrade');
		$rs=$do->where(array('id'=>I('get.id')))->field('id,down_url')->find();
		
		if($rs['down_url']){
			//下载文件
			header('Content-type: application/octet-stream');
			header('location:'.$rs['down_url']);
			$do->where(array('id'=>I('get.id')))->setInc('down_num',1,60);
		}else{
			header("Content-type: text/html; charset=utf-8");
			echo '文件不存在！';
		}
		
	}
}