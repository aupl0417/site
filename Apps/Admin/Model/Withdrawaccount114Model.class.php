<?php
/**
* 此文件为自动生成
*/
namespace Admin\Model;
use Think\Model;
class Withdrawaccount114Model extends Model {
	//protected $patchValidate = true; //批量验证
	protected $tableName='withdraw_account';
	protected $_validate = array(
        array('bank_id','require','银行ID不能为空!',1,'regex',3), 
        array('master','require','户主不能为空!',1,'regex',3), 
        array('account','require','账号不能为空!',1,'regex',3), 

	);
	
	protected $_auto = array (
		array('ip','get_client_ip',1,'function'),	
	);
	

}
?>