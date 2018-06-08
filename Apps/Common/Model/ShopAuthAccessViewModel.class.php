<?php
/**
 * Created by PhpStorm.
 * User: dttx
 * Date: 2017/3/27
 * Time: 15:54
 */

namespace Common\Model;


use Think\Model\ViewModel;

/**
 * 获取权限组权限视图模型
 *
 * Class ShopAuthAccessViewModel
 * @package Common\Model
 */

class ShopAuthAccessViewModel extends ViewModel
{
    protected $tableName = 'shop_auth_function';

    public $viewFields= [
        'shop_auth_function'=>  ['*'],
        'kongzhiqi'        =>  ['name' => 'controller_name', '_as' => 'kongzhiqi', '_table' => '__SHOP_AUTH_MODULE__', '_on' => 'shop_auth_function.cid = kongzhiqi.id AND kongzhiqi.status = 1'],
        'module'            =>  ['name' => 'module_name', '_as' => 'module', '_table' => '__SHOP_AUTH_MODULE__', '_on' => 'shop_auth_function.mid = module.id AND module.status = 1'],
    ];
}