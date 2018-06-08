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
	),

	'ADMIN_LOG'				=>true,	//记录雇员操作日志
    'LOG_NOT_RECORD_TABLE'  =>array('ylh_admin_online'),  //不记录日志的表
	//'TOKEN_ON'				=>true, //开启表单令牌

	'MODULES_PATH'			=>'./Apps/Admin/Modules', //模块模板文件
	'MODEL_PATH'			=>'./Apps/Admin/Model',	//模型文件目录

	'SHOW_PAGE_TRACE' 		=>false,	// 显示页面Trace信息
	'SHOW_ERROR_MSG'        =>true,    // 显示错误信息

	'APP_STATUS'			=>true,
	'APP_DEBUG' 			=>true,	
);