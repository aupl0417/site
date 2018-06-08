<?php
/**
* 此文件为自动生成
*/
namespace Admin\Model;
use Think\Model;
class Appsuser93Model extends Model {
	//protected $patchValidate = true; //批量验证
	protected $tableName='apps_user';
	protected $_validate = array(
        array('access_key','require','access key不能为空!',1,'regex',3), 
        array('secret_key','require','secret key不能为空!',1,'regex',3), 

	);
	
	protected $_auto = array (
		array('ip','get_client_ip',1,'function'),	
	);
	

}
?>