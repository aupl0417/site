<?php
return array(
	//'app_begin' 		=> array('Behavior\\CheckLangBehavior'),			//多语言支持
	//'app_end'			=>array('Behavior\\CronRunBehavior'), // 定时任务
	//去除支付密码行为控件
	//'view_filter' 		=>array('Behavior\TokenBuildBehavior'),	//表单令牌
	'view_begin'		=>  array('Behavior\\SeoBehavior'), 			// SEO设置
	'send_msg'           =>['Common\Behavior\InitMsgBehavior'],  
);