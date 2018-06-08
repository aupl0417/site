<?php
namespace Common\Model;
use Think\Model;
class MobileOrdersRefundModel extends Model {
	protected $tableName='mobile_orders_refund';
	protected $_validate = array(
        array('r_no','require','退款单号不能为空!',1,'regex',3),
        array('uid','require','用户ID不能为空!',1,'regex',3),
        array('seller_id','require','卖家ID不能为空!',1,'regex',3),
        array('shop_id','require','店铺ID不能为空!',1,'regex',3),
        array('s_id','require','订单ID不能为空!',1,'regex',3),
        array('s_no','require','订单号不能为空!',1,'regex',3),
        array('money','require','退款金额不能为空!',1,'regex',3),
        array('status','require','退款状态不能为空!',1,'regex',3),
        array('orders_status','require','订单状态不能为空!',1,'regex',3),
        array('reason','require','退款原因不能为空!',1,'regex',3),
	);
	
	protected $_auto = array (
		array('ip','get_client_ip',1,'function'),	
	);



}
?>