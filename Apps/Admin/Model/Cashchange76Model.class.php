<?php
/**
* 此文件为自动生成
*/
namespace Admin\Model;
use Think\Model;
class Cashchange76Model extends Model {
	//protected $patchValidate = true; //批量验证
	protected $tableName='cash_change';
	protected $_validate = array(
        array('orderno','require','异动流水号不能为空!',1,'regex',3), 

	);
	
	protected $_auto = array (
		array('ip','get_client_ip',1,'function'),	
	);
	

}
?>