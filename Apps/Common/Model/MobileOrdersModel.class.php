<?php
namespace Common\Model;
use Think\Model;
class MobileOrdersModel extends Model {
	protected $tableName='mobile_orders';
	protected $_validate = array(
        array('uid','require','用户ID不能为空!',1,'regex',3),
        array('s_no','require','订单号不能为空!',1,'regex',3),
        array('status','require','状态不能为空!',1,'regex',3),
        array('mobile','require','手机号码不能为空!',1,'regex',3),
        array('desc','require','描述不能为空!',1,'regex',3),
        array('fare','require','充值面值不能为空!',1,'regex',3),
        array('recharge_type','require','充值类型不能为空!',1,'regex',3),
        array('type','require','充值方式不能为空!',1,'regex',3),
        array('pay_price','require','实付金额不能为空!',1,'regex',3),
	);
	
	protected $_auto = array (
		array('ip','get_client_ip',1,'function'),	
	);

}
?>