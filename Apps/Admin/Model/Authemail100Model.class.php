<?php
/**
* 此文件为自动生成
*/
namespace Admin\Model;
use Think\Model;
class Authemail100Model extends Model {
	//protected $patchValidate = true; //批量验证
	protected $tableName='auth_email';
	protected $_validate = array(
        array('uid','require','用户ID不能为空!',0,'',3), 
        array('email','require','邮箱不能为空!',0,'',3), 
        array('code','require','验证串不能为空!',0,'',3), 

	);
	
	protected $_auto = array (
		array('ip','get_client_ip',1,'function'),	
	);
	

}
?>