<?php
/**
 * Created by PhpStorm.
 * User: dttx
 * Date: 2017/1/16
 * Time: 16:14
 */

namespace Common\Model;


use Think\Model;

class GoodsCommentReplyModel extends Model
{
    protected $tableName = 'orders_goods_comment_reply';

    protected $_validate = [
        ['content', 'require', '回复内容不能为空', 1],
        ['comment_id', 'require', '回复对象不能为空', 1],
    ];

}