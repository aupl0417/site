<?php
/**
* 此文件为自动生成
*/
namespace Admin\Model;
use Think\Model\ViewModel;
class Shopmaketemplates199ViewModel extends ViewModel {
    public $viewFields = array(
'shop_make_templates' => ['*'],
'user' => ['nick', '_on' => 'shop_make_templates.uid = user.id'],
'shop' => ['shop_name', '_on' => 'shop_make_templates.shop_id = shop.id'],
    );
}
?>