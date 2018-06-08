<?php
return array(
	'SHOW_PAGE_TRACE'		=>false,		// 显示页面Trace信息
	'SHOW_ERROR_MSG'		=>false,	// 显示错误信息

	//'APP_STATUS'			=>false,
	//'APP_DEBUG'			=>true,
	//'LOG_RECORD'			=>false,	
	//'LOG_LEVEL'			=>'SQL',

    'LOAD_EXT_CONFIG'       => 'channel,redis,dtpay',      //动态配置
	'URL_CASE_INSENSITIVE'	=> false,  // URL区分大小写	 
	'SHOP_CACHE_TIME'		=> 1200,	//店铺布局数据缓存时间
	'DATA_CACHE_TIME'		=> 3600,	  // 数据缓存有效期 0表示永久缓存
	'DATA_CACHE_COMPRESS'	=> false,	// 数据缓存是否压缩缓存
	'DATA_CACHE_CHECK'		=> false,	// 数据缓存是否校验缓存
	'DATA_CACHE_PATH'		=>	TEMP_PATH,		// 缓存路径设置 (仅对File方式缓存有效)
	'DATA_CACHE_SUBDIR'		=>	true,			// 使用子目录缓存 (自动根据缓存标识的哈希创建子目录)
	'DATA_PATH_LEVEL'		=>	3,				// 子目录缓存级别
	'DATA_CACHE_PREFIX'		=> 'ylh_',	   // 缓存前缀
	'DATA_CACHE_TYPE'		=> 'File',	// 数据缓存类型,支持:File|Db|Apc|Memcache|Shmop|Sqlite|Xcache|Apachenote|Eaccelerator
	'MEMCACHED_HOST'		=>'127.0.0.1',
	'MEMCACHED_PORT'		=>'11211',

	'MEMCACHE_HOST'			=>'127.0.0.1',
	'MEMCACHE_PORT'			=>'11211',

	//专门用于存放SESSION的MEMCACHED 服务器
	'MEMCACHED_SESSION_SERVER'	=>array(
		'HOST'		=>'127.0.0.1,127.0.0.1',
		'PORT'		=>'11211,11211',
	),
    'DOMAIN'				=>'testleduimall.com',
    //'DOMAIN'				=>'ledui.com',		 // 项目分组设定,多个组之间用逗号分隔,例如'Home,Admin'  // 分组模式 0 普通分组 1 独立分组
	'APP_GROUP_MODE'		=>	1,	// 分组模式 0 普通分组 1 独立分组


	'APP_SUB_DOMAIN_DEPLOY'	  =>	1, // 开启子域名配置
	'APP_SUB_DOMAIN_RULES'	  =>	array(
		'www'			=>'Home',
		'work'			=>'Admin',
		'ad'			=>'Ad',
		'm'				=>'Mobile',
		'wap'			=>'Wap',
		'my'			=>'My',
		'cart'			=>'Cart',
		'news'			=>'News',
        'rest'			=>'Rest',
        'rest2'			=>'Rest2',
		'cron'			=>'Cron',
		'apidoc'		=>'Apidoc',
		'apidoc2'		=>'Apidoc2',
		'faq'			=>'Faq',
		's'				=>'Search',
		'user'			=>'User',
		'item'			=>'Item',
		'brand'			=>'Brand',
		'seller'		=>'Seller',
		'click'			=>'Click',
		'oauth2'		=>'Oauth2',
		'make'			=>'Make',
		'miaosha'		=>'Miaosha',
		'zhaoshang'		=>'Zhaoshang',
        'tongji'		=>'Analysis',
        'web1'		    =>'Rest',
        'web2'		    =>'Rest',
        'web3'		    =>'Rest',
        'expressprint'  =>'Expressprint',
        'scm'           =>'Scm',
        'bags'          =>'Custom',
        'fashion'       =>'Custom',
        '3c'            =>'Custom',
        'baby'          =>'Custom',
        'jewellery'     =>'Custom',
        'house'         =>'Custom',
        'jiadian'       =>'Custom',
        'beauty'        =>'Custom',
        'food'          =>'Custom',
        'car'           =>'Custom',
		'sell'          =>'Sellergoods',
		'wapseller'		=>'Wapseller',
		'm.tuan'		=>'Waptuan',
		'tuan'			=>'Tuan',
        'game'			=>'Game',
    ),

    'APP_WRITE_TEXT_LOG'=>true,
	'MODULE_ALLOW_LIST'	   =>	 array('Home','Admin','Wap','Wapseller','Shop','Ucenter','Cart','News','Mobile','Rest','Cron','Apidoc','User','Item','Lists','Search','Oauth2','Make','Miaosha','Zhaoshang','Expressprint','Scm','Custom','Tuan','Waptuan','Game'),
	'DEFAULT_MODULE'	   =>	 'Shop',  // 默认模块

	'MODULE_DENY_LIST'		=>	array('Common','Runtime'), // 禁止访问的模块列表
	

	'DB_TYPE'				=>	'mysqli',	  // 数据库类型
	'DB_HOST'				=>	'203.195.236.57', // 服务器地址
	'DB_HOST'				=>	'192.168.142.128', // 服务器地址
	'DB_PORT'				=>	'3306',
	'DB_NAME'				=>	'ldmall',		   // 数据库名
	'DB_USER'				=>	'dtmall',	   // 用户名
	'DB_PWD'				=>	'dtmall',		   // 密码	


	'DB_TYPE'				=>	'mysqli',	  // 数据库类型
	'DB_HOST'				=>	'192.168.3.203', // 服务器地址
	'DB_PORT'				 =>	'3306',

	'DB_NAME'				=>	'ldmall',		   // 数据库名
	'DB_USER'				=>	'root',		 // 用户名
	'DB_PWD'				=>	'sql@8234ERe8',		   // 密码

	'DB_MONGO_CONFIG'		=> array(
		'DB_TYPE'			=> 'mongo', // 数据库类型
		'DB_HOST'			=> '192.168.3.205', // 服务器地址
		'DB_NAME'			=> 'leduimall', // 数据库名
		'DB_USER'			=> 'leduimall', // 用户名  ylsc
		'DB_PWD'			=> 'leduimall', // 密码 ylsc654321
		'DB_PORT'			=> 27017, // 端口
		'DB_PREFIX'			=> 'ylh_', // 数据库表前缀 
	),

    'BEANSTALKD'            => array(   //beanstalkd队列服务器
        'host'          => '10.0.0.90',
        'port'          => 11300,
        'timeout'       => 1,
        'logger'        => null,
    ),


	'DB_PREFIX'				=>	'ylh_',			// 数据库表前缀
	'DB_FIELDS_CACHE'		=>	false,			// 启用字段缓存
	'DB_SQL_BUILD_CACHE'	=>	true,			//sql缓存开启
	'DB_SQL_BUILD_LENGTH'	=>	20,				// SQL缓存的队列长度
	'DB_SQL_LOG'			=>	true,			// SQL执行日志记录


	'URL_MODEL'				=>	2,		 // URL访问模式,可选参数0、1、2、3,代表以下四种模式：// 0 (普通模式); 1 (PATHINFO 模式); 2 (REWRITE  模式); 3 (兼容模式)  默认为PATHINFO 模式

	'COOKIE_EXPIRE'			=>	3600 * 6,				// Cookie有效期 6个钟
	//'COOKIE_DOMAIN'			=>	'ledui.com',			// Cookie有效域名
    'COOKIE_DOMAIN'			=>	'testleduimall.com',			// Cookie有效域名
	'COOKIE_PATH'			=>	'/',					// Cookie路径
	'COOKIE_PREFIX'			=>	'ylh_',					// Cookie前缀 避免冲突
	'COOKIE_LOGIN'			=> 3600,	//登录后HTTP_REFERER地址保存时间

	'SESSION_OPTIONS'		=>array(
		'name'		=>	'session_id',	//多子域共享
		//'domain'	=>	'ledui.com',
        'domain'	=>	'testleduimall.com',
		'expire'	=>	3600,
		'path'		=>	'./Apps/Runtime/Cache',
		'type'		=>	'Memcache',
	),


	'URL_404_REDIRECT'		=>	'',						// 404 跳转页面 部署模式有效

	'TAGLIB_PRE_LOAD'		=>'Html',

	'TMPL_STRIP_SPACE'		=>	true,		// 是否去除模板文件里面的html空格与换行
	'TMPL_CACHE_ON'			=>	false,		  // 是否开启模板编译缓存,设为false则每次都会重新编译

	'DEFAULT_THEME'			=>	'default',	//默认Theme



	'CRYPT_PREFIX'			=>'yunkan@&^%$$#221389685fkhekdQkdLKHYKml398',	//加密码串用于cookie加密

	'LANG_SWITCH_ON'		=> true,		// 开启语言包功能
	'LANG_AUTO_DETECT'		=> true,		// 自动侦测语言 开启多语言功能后有效
	'LANG_LIST'				=> 'zh-cn',		// 允许切换的语言列表 用逗号分隔
	'VAR_LANGUAGE'			=> 'l',			// 默认语言切换变量

	'UPLOADS'				=>'/Uploads',		//文件上传目录

	'TOKEN_ON'				=>	false,	// 是否开启令牌验证 默认关闭
	'TOKEN_NAME'			=>	'__hash__',	   // 令牌验证的表单隐藏字段名称，默认为__hash__
	'TOKEN_TYPE'			=>	'md5',	//令牌哈希验证规则 默认为MD5
	'TOKEN_RESET'			=>	true,  //令牌验证出错后是否重置令牌 默认为true

	'TOKEN_TAG'				=>	'<!--token-->',	//页面中插入TOKEN标记的标签
	
	//Redis
	'REDIS_HOST'			=>	'192.168.95.131',	//redis服务器
	'REDIS_PORT'			=>	6379,				//redis端口
	'DATA_CACHE_TIMEOUT'	=>	0,					//redis数据缓存失效时间
	'REDIS_AUTH'			=>	'134115',			//redis认证密码
	
	'FORM_TYPE'		=>array(
		array('value'=>'input','name'=>'单行文本框'),
		array('value'=>'textarea','name'=>'多行文本框'),
		array('value'=>'editor','name'=>'编辑器'),
		array('value'=>'editor-wang','name'=>'Wang编辑器'),
		array('value'=>'edit-area','name'=>'代码编辑器'),		
		array('value'=>'tag','name'=>'标签输入框'),
		array('value'=>'radio','name'=>'单项选择'),
		array('value'=>'radio-switch','name'=>'单项选择-开关式'),
		array('value'=>'radio-tag','name'=>'标签式单项选择'),
		array('value'=>'checkbox','name'=>'多项选择'),
		array('value'=>'checkbox-more','name'=>'多项选择-分类[多级目录]'),
		array('value'=>'select','name'=>'下拉选择框'),
		array('value'=>'radio-table','name'=>'单项选择-关联数据表'),
		array('value'=>'images','name'=>'上传图片'),
		array('value'=>'date','name'=>'日期选择器(Y-m-d)'),
		array('value'=>'datetime','name'=>'日期选择器(Y-m-d H:i:s)'),
		array('value'=>'password','name'=>'密码输入框'),
		array('value'=>'vmodal', 'name' => '模态框'),
		//array('value'=>'city','name'=>'城市下拉选择框'),
		array('value'=>'year','name'=>'年份下拉选择框'),
		array('value'=>'month','name'=>'月份下拉选择框'),
		array('value'=>'day','name'=>'日下拉选择框'),

		array('value'=>'sku','name'=>'商品价格信息SKU'),
		array('value'=>'attr','name'=>'商品属性'),
		array('value'=>'express','name'=>'运费模板'),
		array('value'=>'city','name'=>'省份-城市-区域'),
        array('value'=>'images-file','name'=>'图片上传-带预览'),
        array('value'=>'html','name'=>'Html'),
		//array('value'=>'select_one','name'=>'关联表记录选择(单选)'),
		//array('value'=>'select_more','name'=>'关联表记录选择(多选)'),
	),
	'FIELD_TYPE'	=>array(
		array('value'=>'VARCHAR','name'=>'VARCHAR'),
		array('value'=>'TEXT','name'=>'TEXT'),
		array('value'=>'MEDIUMTEXT','name'=>'MEDIUMTEXT'),
		array('value'=>'LONGTEXT','name'=>'LONGTEXT'),
		array('value'=>'TINYINT','name'=>'TINYINT'),
		array('value'=>'INT','name'=>'INT'),
		array('value'=>'MEDIUMINT','name'=>'MEDIUMINT'),
		array('value'=>'BIGINT','name'=>'BIGINT'),
		array('value'=>'FLOAT','name'=>'FLOAT'),
		array('value'=>'DOUBLE','name'=>'DOUBLE'),
		array('value'=>'DATE','name'=>'DATE'),
		array('value'=>'DATETIME','name'=>'DATETIME'),
	),

	'VIEWMODEL_PATH'	=>'./Apps/Admin/Model',	 //数据表视图存放路径
	'FORM_PATH'			=>'./Apps/Admin/View/default/Widget/Form',	//数据表-数据表单存放路径
	'LISTYLE_PATH'		=>'./Apps/Admin/View/default/Widget/Listyle',  //数据表列表风格存放路径
	//缓存时间级别
	'CACHE_LEVEL'	=>array(
		'XXS'		=>10,
		'XS'		=>30,
		'S'			=>60,
		'M'			=>300,
		'L'			=>600,
		'XL'		=>1800,
		'XXL'		=>3600,
		'OneDay'	=>86400,
	),

);
