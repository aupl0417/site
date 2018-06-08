<?php
/**
* 此文件为自动生成
*/
namespace Admin\Model;
use Think\Model;
class Bankname113Model extends Model {
	//protected $patchValidate = true; //批量验证
	protected $tableName='bank_name';
	protected $_validate = array(
        array('bank_name','require','名称不能为空!',1,'regex',3), 
        array('bank_code','require','代码不能为空!',1,'regex',3), 

	);
	
	protected $_auto = array (
		array('ip','get_client_ip',1,'function'),	
	);
	

}
?>