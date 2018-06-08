<?php
/**
* 此文件为自动生成
*/
namespace Admin\Model;
use Think\Model;
class User72Model extends Model {
	//protected $patchValidate = true; //批量验证
	protected $tableName='user';
	protected $_validate = array(
        array('nick','require','昵称不能为空!',1,'regex',3), 
        array('password','require','密码不能为空!',1,'regex',3), 
        array('mobile','checkform','手机号码不能为空!',1,'function',3,array('is_mobile')), 

	);
	
	protected $_auto = array (
		array('ip','get_client_ip',1,'function'),	
	);
	

}
?>