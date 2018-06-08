<?php
/**
* 此文件为自动生成
*/
namespace Admin\Model;
use Think\Model;
class Userrelation73Model extends Model {
	//protected $patchValidate = true; //批量验证
	protected $tableName='user_relation';
	protected $_validate = array(
        array('upuid_list','require','上级用户ID,逗号隔开不能为空!',1,'regex',3), 

	);
	
	protected $_auto = array (
		array('ip','get_client_ip',1,'function'),	
	);
	

}
?>