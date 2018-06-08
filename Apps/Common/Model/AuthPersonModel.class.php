<?php
namespace Common\Model;
use Think\Model;
class AuthPersonModel extends Model {
	//protected $patchValidate = true; //批量验证
	protected $tableName='auth_person';
	protected $_validate = array(
        array('uid','require','用户ID不能为空!',1,'regex',3), 
        array('name','require','姓名不能为空!',1,'regex',3), 
        array('card_no','require','请填写正确的证件号码!',1,'regex',3), 
	    array('card_no','','证件号码已被使用!',1,'unique',3), 
        array('card_type','require','证件类型不能为空!',1,'regex',3), 
        array('card_pic','require','证件正面图片不能为空!',1,'regex',3), 
        array('card_pic2','require','证件背面图片不能为空!',1,'regex',3), 
        array('card_pic3','require','手持证件图片不能为空!',1,'regex',3), 
	);
	
	protected $_auto = array (
		array('ip','get_client_ip',1,'function'),	
	    array('status', 0, 3)
	);
	

}
?>