<?php
return array(
	//'app_begin' 		=> array('Behavior\\CheckLangBehavior'),			//多语言支持
	//'app_end'			=>array('Behavior\\CronRunBehavior'), // 定时任务
	//去除支付密码行为控件
	//'view_filter' 		=>array('Behavior\TokenBuildBehavior'),	//表单令牌
    'app_terminal'         =>  ['Common\Behavior\InitTerminalBehavior'],   // 终端判断
);