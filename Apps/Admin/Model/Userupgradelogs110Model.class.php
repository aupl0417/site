<?php
/**
* 此文件为自动生成
*/
namespace Admin\Model;
use Think\Model;
class Userupgradelogs110Model extends Model {
	//protected $patchValidate = true; //批量验证
	protected $tableName='user_upgrade_logs';
	protected $_validate = array(
        array('u_no','require','流水号不能为空!',1,'regex',3), 
        array('uid','require','用户ID不能为空!',1,'regex',3), 
        array('money','require','升级费用不能为空!',1,'regex',3), 
        array('level_id','require','会员级别ID不能为空!',1,'regex',3), 

	);
	
	protected $_auto = array (
		array('ip','get_client_ip',1,'function'),	
	);
	

}
?>