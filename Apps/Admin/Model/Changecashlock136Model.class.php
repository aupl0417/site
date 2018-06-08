<?php
/**
* 此文件为自动生成
*/
namespace Admin\Model;
use Think\Model;
class Changecashlock136Model extends Model {
	//protected $patchValidate = true; //批量验证
	protected $tableName='change_cash_lock';
	protected $_validate = array(
        array('uid','require','用户ID不能为空!',1,'regex',3), 
        array('type_id','require','异动类型ID不能为空!',1,'regex',3), 
        array('status','require','状态不能为空!',1,'regex',3), 
        array('w_no','require','异动流水号不能为空!',1,'regex',3), 
        array('money','require','异动金额不能为空!',1,'regex',3), 
        array('from_uid','require','来源用户ID不能为空!',1,'regex',3), 
        array('from_account','require','来源用户账号余额不能为空!',1,'regex',3), 
        array('from_flag','require','来源用户子账户不能为空!',1,'regex',3), 
        array('to_uid','require','接收用户ID不能为空!',1,'regex',3), 
        array('to_account','require','接收用户账号余额不能为空!',1,'regex',3), 
        array('to_flag','require','接收用户子账户不能为空!',1,'regex',3), 

	);
	
	protected $_auto = array (
		array('ip','get_client_ip',1,'function'),	
	);
	

}
?>