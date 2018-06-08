<?php
namespace Common\Model;
use Think\Model;
class WithdrawAccountModel extends Model {
	//protected $patchValidate = true; //批量验证
	protected $tableName='withdraw_account';
	protected $_validate = array(
		array('bank_id','require','银行不能为空!',1,'regex',3), 
		array('type','require','银行类别不能为空!',1,'regex',3), 
		array('bank_name','require','银行名称不能为空!',1,'regex',3), 
		array('master','require','户主不能为空!',1,'regex',3), 
		array('account','require','账号不能为空!',1,'regex',3), 

	);
	
	protected $_auto = array (
		array('ip','get_client_ip',1,'function'),	
	);
	

}
?>