<?php
/**
* 此文件为自动生成
*/
namespace Admin\Model;
use Think\Model\ViewModel;
class Shopmenu216ViewModel extends ViewModel {
    public $viewFields = array(
'shop_menu' => ['*'],
'menu' => ['name' => 'parent_name', '_as' => 'menu', '_table' => '__SHOP_MENU__', '_on' => 'shop_menu.sid = menu.id']
    );
}
?>