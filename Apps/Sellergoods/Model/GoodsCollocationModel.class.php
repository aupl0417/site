<?php
/**
 * Created by PhpStorm.
 * User: dttx
 * Date: 2017/3/4
 * Time: 9:32
 */

namespace Sellergoods\Model;


use Think\Model;

class GoodsCollocationModel extends Model
{
    protected $tableName = 'goods_collocation';

    protected $_validate = [
        //['collocations', ]
        //['goods_id', 'require', '商品id不能为空', 1, 'regex', 2],
    ];

}