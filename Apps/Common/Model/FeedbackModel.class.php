<?php
namespace Common\Model;
use Think\Model;
class FeedbackModel extends Model {
	//protected $patchValidate = true; //批量验证
	protected $tableName='feedback';
	protected $_validate = array(
        array('email','checkform','请填写正确的邮箱地址!',1,'function',3,array('is_email')), 
        array('content','checkform','意见建议内容在15~300个字符之间(一个汉字为3字符)！',1,'function',3,array('string_range',15,1000)), 

	);
	
	protected $_auto = array (
		array('ip','get_client_ip',1,'function'),	
	);
	

}
?>