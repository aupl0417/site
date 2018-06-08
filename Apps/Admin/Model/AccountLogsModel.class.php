<?php
/**
* 此文件为自动生成
*/
namespace Admin\Model;
use Think\Model;
class AccountLogsModel extends Model {
	//protected $patchValidate = true; //批量验证
	protected $tableName='account_logs';
	protected $_validate = array(
        array('uid','require','用户ID不能为空!',1,'regex',3), 
        array('a_uid','require','雇员ID不能为空!',1,'regex',3), 

	);
	
	protected $_auto = array (
		array('ip','get_client_ip',1,'function'),	
	);
	

}
?>