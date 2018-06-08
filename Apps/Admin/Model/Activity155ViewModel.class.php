<?php
/**
* 此文件为自动生成
*/
namespace Admin\Model;
use Think\Model\ViewModel;
class Activity155ViewModel extends ViewModel {
    public $viewFields = array(
'activity' => array('*'),
'activity_type' => array('activity_name', '_on' => 'activity.type_id = activity_type.id'),
'user' => array('nick', '_on' => 'activity.uid = user.id'),
'shop' => array('shop_name', '_on' => 'activity.shop_id = shop.id'),
    );
}
?>