<?php
return array(
	'TMPL_PARSE_STRING'		=>array(
		 '__JS__'			=> '/Public/Jquery', // 增加新的JS类库路径替换规则
		 '__APP_JS__'		=> '/Public/Apps', // 增加新的JS类库路径替换规则
		 '__UPLOAD__'		=> '/Uploads', // 增加新的上传路径替换规则
		 '__CSS__'			=> '/Public/CSS',	//CSS框架
		 '__FLATDREAM__'	=> '/Public/CSS/flatdream',	//CSS框架
		 '__PROUI__'		=>	'/Public/CSS/proui',	//CSS框架
		 '../Public'		=> '/Apps/'.MODULE_NAME.'/View/default/Public',
		 '__IMAGES__'		=>'/Public/images',
		 '__NEW_DESIGN__'	=> '/Public/new_design/'
	),
    //启用路由功能
	'URL_ROUTER_ON'		=>	true,
	//路由定义
	'URL_ROUTE_RULES'	=> 	array( 
		'/^sid\/(\d+)$/'	=> 'Index/index?sid=:1',
		'/^item\/(\d+)$/'	=> 'Index/index?id=:1',

	), //动态规则
	'URL_CMP'			=>array(
		'Index/index/sid'			=>'/sid/[sid]',
		'Index/index/id'			=>'/item/[id]',
	),
	'URL_MAP_RULES'     =>  array(

	), // URL映射定义规则		
);