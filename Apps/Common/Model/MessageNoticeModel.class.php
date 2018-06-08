<?php
namespace Common\Model;
use Think\Model;
class MessageNoticeModel extends Model {
    
    protected $tableName = 'message_notice';
    
    protected $_validate = [
        ['uid', 'require', '接收用户不能为空', 1],
        //['tpl_id', 'require', '消息模板不能为空', 1],
        ['content', 'require', '消息内容不能为空', 1]
    ];
}