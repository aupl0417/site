<?php
/**
* 此文件为自动生成
*/
namespace Admin\Model;
use Think\Model\ViewModel;
class Luckdraw1apply232ViewModel extends ViewModel {
    public $viewFields = array(
'luckdraw1_apply' => ['*'],
'luckdraw1' => ['luckdraw_name', '_on' => 'luckdraw1.id = luckdraw1_apply.luckdraw_id'],
'user' => ['nick', '_on' => 'user.id = luckdraw1_apply.uid'],
'shop' => ['shop_name', '_on' => 'luckdraw1_apply.shop_id = shop.id']
    );
}
?>