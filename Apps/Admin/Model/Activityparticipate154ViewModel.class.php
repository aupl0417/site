<?php
/**
* 此文件为自动生成
*/
namespace Admin\Model;
use Think\Model\ViewModel;
class Activityparticipate154ViewModel extends ViewModel {
    public $viewFields = array(
'activity_participate' => array('*'),
'activity_type' => array('activity_name', '_on' => 'activity_participate.type_id = activity_type.id'),
'user' => array('nick', '_on' => 'activity_participate.uid = user.id'),
'shop' => array('shop_name', '_on' => 'activity_participate.shop_id = shop.id'),
    );
}
?>