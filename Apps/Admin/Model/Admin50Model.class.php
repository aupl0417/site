<?php
/**
* 此文件为自动生成
*/
namespace Admin\Model;
use Think\Model;
class Admin50Model extends Model {
	//protected $patchValidate = true; //批量验证
	protected $tableName='admin';
	protected $_validate = array(
        array('username','require','账号不能为空!',1,'regex',3), 
        array('password','require','密码不能为空!',1,'regex',3), 

	);
	
	protected $_auto = array (
		array('ip','get_client_ip',1,'function'),	
	);
	

}
?>