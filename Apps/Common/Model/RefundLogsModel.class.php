<?php
namespace Common\Model;
use Think\Model;
class RefundLogsModel extends Model {
	protected $tableName='refund_logs';
	protected $_validate = array(
                array('uid','require','用户ID不能为空！',1), 
                array('r_id','require','退款ID不能为空！',1),
                array('r_no','require','退款流水号不能为空！',1),
                array('status','require','退款状态不能为空！',1),
                array('type','require','退款类型不能为空！',1),
                array('remark','require','描述不能为空！',1),
	);
	
	protected $_auto = array (
		array('ip','get_client_ip',1,'function'),	
	);



}
?>