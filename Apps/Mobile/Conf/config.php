<?php
return array(
	'TMPL_PARSE_STRING'		=>array(
		'__JS__'			=> '/Public/Jquery', // 增加新的JS类库路径替换规则
		'__APP_JS__'		=> '/Public/Apps', // 增加新的JS类库路径替换规则
		'__UPLOAD__'		=> '/Uploads', // 增加新的上传路径替换规则
		'__CSS__'			=> '/Apps/'.MODULE_NAME.'/View/default/Public/css',
        '__SUI__'           => '/Apps/'.MODULE_NAME.'/View/default/Public/SUI',   //SUI CSS框架
		'../Public'		    => '/Apps/'.MODULE_NAME.'/View/default/Public',
		'__IMAGES__'		=>'/Public/images',
	),


	'DEBUG_API'				=>false,
);