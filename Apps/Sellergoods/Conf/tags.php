<?php

return array(
    //'app_begin'         =>  ['Common\Behavior\InitActivityStartBehavior', 'Common\Behavior\InitActivityEndBehavior'],     //活动
	'view_filter' 		=>array('Behavior\\TokenBuildBehavior', 'Common\Behavior\InitCouponExpireBehavior'),	//表单令牌
    'app_access'       =>  ['Common\Behavior\InitCheckAccessBehavior'],    //权限
);
