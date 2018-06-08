<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2014 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
namespace Behavior;
/**
 * 系统行为扩展：模板内容输出替换
 */
class SeoBehavior {

    // 行为扩展的执行入口必须是run
    public function run(&$param=null){
    	$default = C('cfg.seo');
    	$seo  = C('seo');
		$scheme = strtolower($_SERVER['HTTPS']) == 'on' ? 'https' : 'http';
		$url =  strtolower($scheme . '://'.$_SERVER['HTTP_HOST'] . $_SERVER['REDIRECT_URL']);
		$one = M('seo')->cache(true,86400)->where(['status' => 1,'url'=>$url ])->field('title,keywords,description')->find();
		
		$meta = [];
		$name = ' - ' . C('cfg.site')['name'];
		$meta['title'] 			= $seo['title'] 		? $seo['title'] . $name : ($one['title'] 		? $one['title'] . $name : $default['title']);
		$meta['keywords'] 		= $seo['keywords'] 		? $seo['keywords']		: ($one['keywords'] 	? $one['keywords'] 		: $default['keywords']);
		$meta['description'] 	= $seo['description'] 	? $seo['description']	: ($one['description'] 	? $one['description'] 	: $default['description']);
		
    	C('seo',$meta);
    }



}