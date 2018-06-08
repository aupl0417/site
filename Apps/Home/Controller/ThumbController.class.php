<?php
namespace Home\Controller;
use Think\Controller;
class ThumbController extends Controller {
	
	public function index(){
		//$_GET['src']=str_replace('-','/',$_GET['src']);
		//dump($_GET);exit;
		require_cache('./Apps/Common/Common/timthumb.php');

	}







}