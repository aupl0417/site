<?php
namespace Common\Model;
use Think\Model;
class RefundExpressModel extends Model {
	protected $tableName='refund';
	protected $_validate = array(
        array('uid','require','用户ID不能为空！',1), 
        array('seller_id','require','卖家ID不能为空！',1),
        array('s_id','require','商家订单ID不能为空！',1),
        array('s_no','require','卖家订单号不能为空！',1),
        array('r_no','require','退款流水号不能为空！',1),
        array('money','require','退款金额不能为空！',1),
        array('score','require','退款积分不能为空！',1),
        array('orders_status','require','商家订单状态不能为空！',1),
        array('status','require','退款状态不能为空！',1),
        array('type','require','退款类型不能为空！',1),
        array('reason','require','退款原因不能为空！',1),
	);
	
	protected $_auto = array (
		array('ip','get_client_ip',1,'function'),	
	);



}
?>