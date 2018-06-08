<?php
/**
 * Created by PhpStorm.
 * User: dttx
 * Date: 2017/3/23
 * Time: 11:52
 */

namespace Common\Model;

use Think\Model;

/**
 * 商家用户组验证模型
 *
 * Class ShopAuthGroupModel
 * @package Common\Model
 */

class ShopAuthGroupModel extends Model
{
    protected $tableName = 'shop_auth_group';

    protected $_validate = [
        ['group_name', 'require', '组名不能为空', 1],
        ['shop_id', 'require', '店铺不能为空', 1, 'regex', 1],
        ['uid', 'require', '卖家不能为空', 1, 'regex', 1],
        ['fun_ids', 'require', '权限不能为空', 1],
    ];
}