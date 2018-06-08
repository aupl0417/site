<?php
/**
* 此文件为自动生成
*/
namespace Admin\Model;
use Think\Model\ViewModel;
class Shopauthfunction214ViewModel extends ViewModel {
    public $viewFields = array(
'shop_auth_function' => ['*'],
'shop_auth_module' => ['title', 'name', '_on' => 'shop_auth_function.cid = shop_auth_module.id'],
'module' => ['title' => 'm_title', 'name' => 'm_name', '_as' => 'module', '_table' => '__SHOP_AUTH_MODULE__', '_on' => 'shop_auth_function.mid = module.id']
    );
}
?>