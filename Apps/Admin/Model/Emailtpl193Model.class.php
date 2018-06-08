<?php
/**
* 此文件为自动生成
*/
namespace Admin\Model;
use Think\Model;
class Emailtpl193Model extends Model {
	//protected $patchValidate = true; //批量验证
	protected $tableName='email_tpl';
	protected $_validate = array(

	);
	
	protected $_auto = array (
		array('ip','get_client_ip',1,'function'),	
	);
	

}
?>