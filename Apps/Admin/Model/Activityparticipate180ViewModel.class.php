<?php
/**
* 此文件为自动生成
*/
namespace Admin\Model;
use Think\Model\ViewModel;
class Activityparticipate180ViewModel extends ViewModel {
    public $viewFields = array(
'activity_participate' => array('*'),
'orders_shop' => array('status' => 'orders_status', 'atime' => 'o_atime', 'pay_time', 'receipt_time', 'total_price', 'pay_price', 'express_price_edit', 'goods_price_edit', 'goods_price_edit', 'pay_type', '_on' => 'orders_shop.s_no = activity_participate.s_no AND orders_shop.status > 3 AND orders_shop.pay_type != 2'),
'shop' => array('shop_name', '_on' => 'shop.id=activity_participate.shop_id'),
'user' => array('nick', '_on' => 'user.id=activity_participate.uid'),
    );
}
?>