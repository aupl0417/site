<?php
return array(
	'TMPL_PARSE_STRING'		=>array(
		 '__JS__'			=> '/Public/Jquery', // 增加新的JS类库路径替换规则
		 '__APP_JS__'		=> '/Public/Apps', // 增加新的JS类库路径替换规则
		 '__UPLOAD__'		=> '/Uploads', // 增加新的上传路径替换规则
		 '__CSS__'			=> '/Public/CSS',	//CSS框架
		 '../Public'		=> '/Apps/'.MODULE_NAME.'/View/default/Public',
		 '__IMAGES__'		=>'/Public/Images',
	),

	//'ADMIN_LOG'				=>true,	//记录雇员操作日志
	//'TOKEN_ON'				=>true, //开启表单令牌

	'MODULES_PATH'			=>'./Apps/Admin/Modules', //模块模板文件
	'MODEL_PATH'			=>'./Apps/Admin/Model',	//模型文件目录

	'DEBUG_API'				=>false,
);