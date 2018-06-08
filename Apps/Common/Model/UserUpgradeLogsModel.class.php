<?php
namespace Common\Model;
use Think\Model;
class UserUpgradeLogsModel extends Model {
	protected $tableName='user_upgrade_logs';
	protected $_validate = array(
		array('u_no','require','升级缴纳费用流水号不能为空!',1,'regex',3), 
		array('level_id','require','升级级别不能为空!',1,'regex',3), 
		array('uid','require','用户ID不能为空!',1,'regex',3), 
		array('money','checkform','升级缴纳费用不能为空且须大于0!',1,'function',3,array('gt0')), 
	);
	
	protected $_auto = array (
		array('ip','get_client_ip',1,'function'),	
	);



}
?>