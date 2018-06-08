<?php
/**
 * Created by PhpStorm.
 * User: dttx
 * Date: 2017/3/25
 * Time: 11:40
 */

namespace Common\Model;


use Think\Model;

/**
 * 子账号申请增额验证模型
 *
 * Class ShopAuthAccountApplyNumModel
 * @package Common\Model
 */

class ShopAuthAccountApplyNumModel extends Model
{
    protected $tableName = 'shop_auth_account_num_reply';

    protected $_validate = [
        ['shop_id', 'require', '商家不能为空', 1, 'regex', 1],
        ['uid', 'require', '用户不能为空', 1, 'regex', 1],
        ['num', 'require', '申请数量不能为空', 1],
        ['reason', 'require', '申请理由不能为空', 1]
    ];
}