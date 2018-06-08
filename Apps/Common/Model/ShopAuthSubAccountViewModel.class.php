<?php
/**
 * Created by PhpStorm.
 * User: dttx
 * Date: 2017/3/23
 * Time: 11:55
 */

namespace Common\Model;


use Think\Model\ViewModel;

/**
 * 子账号视图模型
 *
 * Class ShopAuthSubAccountViewModel
 * @package Common\Model
 */

class ShopAuthSubAccountViewModel extends ViewModel
{
    protected $tableName = 'user';

    protected $viewFields= [
        'user'              =>  ['*'],
        'shop_auth_group'   =>  ['group_name', '_on' => 'user.shop_auth_group_id = shop_auth_group.id'],
    ];
}