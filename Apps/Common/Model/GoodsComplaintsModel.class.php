<?php
/**
 * Created by PhpStorm.
 * User: dttx
 * Date: 2017/3/21
 * Time: 10:36
 */

namespace Common\Model;

/**
 * 商品举报模型
 */

use Think\Model;

class GoodsComplaintsModel extends Model
{
    protected $tableName = 'goods_complaints';

    protected $_validate = [
        ['uid', 'require', '用户不能为空', 1],
        ['type', 'require', '举报类型不能为空', 1],
        ['content', 'require', '举报内容不能为空', 1],
        ['shop_id', 'require', '举报店铺不能为空', 1],
        ['attr_id', 'require', '举报商品属性不能为空', 1],
        ['goods_id', 'require', '举报商品不能为空', 1],
    ];

    protected $_auto     = [
        ['ip', 'get_client_ip', 1, 'function'],
        ['status', 1, 1],
    ];
}