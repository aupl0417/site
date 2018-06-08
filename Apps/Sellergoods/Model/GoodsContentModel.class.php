<?php
/**
 * Created by PhpStorm.
 * User: dttx
 * Date: 2017/3/4
 * Time: 9:31
 */

namespace Sellergoods\Model;


use Think\Model;

class GoodsContentModel extends Model
{
    protected $tableName = 'goods_content';

    protected $_validate = [
    //['collocations', ]
        //['goods_id', 'require', '商品id不能为空', 1, 'regex', 2],
        ['content', 'require', '商品详情不能为空', 1],
        //['content', '5,10000', '描述长度必须在5-100000个字符内', 1, 'length']
    ];
}