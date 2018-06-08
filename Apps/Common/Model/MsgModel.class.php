<?php
namespace Common\Model;
use Think\Model;
class MsgModel extends Model {
    
    protected $tableName = 'msg';
    
    protected $_validate = [
        ['from_uid', 'require', '发送用户不能为空', 1],
        ['from_nick', 'require', '发送用户不能为空', 1],
        ['to_uid', 'require', '接收用户不能为空', 1],
        ['to_nick', 'require', '接收用户不能为空', 1],
        ['category_id', 'require', '消息类别不能为空', 1],
        ['title', 'require', '消息标题不能为空', 1],
        ['content', 'require', '消息内容不能为空', 1]
    ];
}