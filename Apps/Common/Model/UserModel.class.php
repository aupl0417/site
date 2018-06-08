<?php
namespace Common\Model;
use Think\Model;
class UserModel extends Model {
	protected $tableName='user';
	protected $_validate = array(
        array('nick','checkform','账号不能为空，且在6~40个字符之间',1,'function',3,array('username',6,40)), 
        array('password','require','密码不能为空!',1,'regex',3), 
        array('mobile','checkform','手机号码格式错误!',1,'function',3,array('is_mobile')),
	);
	
	protected $_auto = array (
		array('ip','get_client_ip',1,'function'),	
	);



}
?>