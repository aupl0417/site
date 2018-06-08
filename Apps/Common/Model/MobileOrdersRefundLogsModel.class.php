<?php
namespace Common\Model;
use Think\Model;
class MobileOrdersRefundLogsModel extends Model {
	protected $tableName='mobile_orders_refund_logs';
	protected $_validate = array(
        array('r_no','require','退款单号不能为空!',1,'regex',3),
        array('r_id','require','退款ID不能为空!',1,'regex',3),
        array('uid','require','用户ID不能为空!',1,'regex',3),
        array('money','require','退款金额不能为空!',1,'regex',3),
        array('status','require','退款状态不能为空!',1,'regex',3),
	);
	
	protected $_auto = array (
		array('ip','get_client_ip',1,'function'),	
	);



}
?>