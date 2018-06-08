<?php
namespace Common\Model;
use Think\Model;
class OrdersLogsModel extends Model {
	protected $tableName='orders_logs';
	protected $_validate = array(
                array('o_id','require','订单ID不能为空!',1,'regex',3), 
                array('o_no','require','订单号不能为空!',1,'regex',3), 
                array('s_id','require','商家订单ID不能为空!',1,'regex',3), 
                array('s_no','require','商家订单号不能为空!',1,'regex',3), 
                array('status','require','订单状态不能为空!',1,'regex',3), 
                array('remark','require','备注不能为空!',1,'regex',3), 

	);
	
	protected $_auto = array (
		array('ip','get_client_ip',1,'function'),	
	);



}
?>