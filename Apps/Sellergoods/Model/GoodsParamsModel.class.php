<?php
/**
 * Created by PhpStorm.
 * User: dttx
 * Date: 2017/3/4
 * Time: 9:32
 */

namespace Sellergoods\Model;


use Think\Model;

class GoodsParamsModel extends Model
{
    protected $tableName = 'goods_param';

    protected $_validate = [
        //['goods_id', 'require', '商品id不能为空', 1, 'regex', 2],
        ['option_id', 'require', '参数名不能为空', 1],
        ['param_value', 'require', '参数值不能为空', 1],
    ];

    protected $_auto = [
        ['ip', 'get_client_ip', 3, 'function'],
    ];
}