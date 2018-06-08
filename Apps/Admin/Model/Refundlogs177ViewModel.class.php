<?php
/**
* 此文件为自动生成
*/
namespace Admin\Model;
use Think\Model\ViewModel;
class Refundlogs177ViewModel extends ViewModel {
    public $viewFields = array(
'refund_logs'=>array('*', 'id' => 'logs_id'),
 'refund'=>array('s_no','atime', 'uid', 'seller_id', 'shop_id', 'num', 'status' => 'refund_status', 'id', '_on'=>'refund_logs.r_id=refund.id'),
 'orders_shop' => array('status' => 'orders_status', '_on' => 'orders_shop.s_no = refund.s_no'),
 'shop' => array('shop_name', '_on' => 'shop.id=refund.shop_id'),
 'user' => array('nick', '_on' => 'user.id=refund.uid'),
 'seller' => array('nick' => 'seller_nick', '_table' => 'ylh_user', '_on' => 'seller.id=refund.seller_id'),
    );
}
?>