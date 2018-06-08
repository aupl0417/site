<?php
namespace Common\Model;
use Think\Model;
class ZhaoshangUpgradeLogsModel extends Model {
	//protected $patchValidate = true; //批量验证
	protected $tableName='zhaoshang_upgrade_logs';
	protected $_validate = array(
        array('zhaoshang_upgrade_id','require','申请记录ID不能为空!',1,'regex',3),
        array('status','require','状态不能为空!',1,'regex',3),
        array('a_uid','require','雇员ID不能为空!',1,'regex',3),
	);
	
	protected $_auto = array (
		array('ip','get_client_ip',1,'function'),	
	);
	

}
?>