<?php
/**
* 此文件为自动生成
*/
namespace Admin\Model;
use Think\Model\ViewModel;
class Messagenotice195ViewModel extends ViewModel {
    public $viewFields = array(
'message_notice' => ['*'],
'user' => ['nick', '_on' => 'message_notice.uid = user.id'],
'msg_tpl' => ['tpl_name', '_on' => 'msg_tpl.id = message_notice.tpl_id']
    );
}
?>