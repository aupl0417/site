<?php
namespace Common\Model;
use Think\Model;
class AuthPersonLogsModel extends Model {
	//protected $patchValidate = true; //批量验证
	protected $tableName='auth_person_logs';
	protected $_validate = array(
        array('status','require','审核状态不能为空!',1,'regex',3), 
        array('auth_id','require','审核记录ID不能为空!',1,'regex',3), 
        array('a_uid','require','雇员ID不能为空!',1,'regex',3), 

	);
	
	protected $_auto = array (
		array('ip','get_client_ip',1,'function'),	
	);
	

}
?>