<?php
namespace Common\Model;
use Think\Model;
class UserRelation2Model extends Model {
	//protected $patchValidate = true; //批量验证
	protected $tableName='user_relation';
	protected $_validate = array(
        array('uid','require','用户ID不能为空!',1,'regex',3), 
        array('upuid_list','require','上级用户ID不能为空!',1,'regex',3), 
        array('level','require','层级不能为空!',1,'regex',3), 
	);
	
	protected $_auto = array (
		array('ip','get_client_ip',1,'function'),	
	);
	

}
?>