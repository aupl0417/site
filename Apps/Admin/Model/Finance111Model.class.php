<?php
/**
* 此文件为自动生成
*/
namespace Admin\Model;
use Think\Model;
class Finance111Model extends Model {
	//protected $patchValidate = true; //批量验证
	protected $tableName='finance';
	protected $_validate = array(
        array('f_no','require','流水号不能为空!',1,'regex',3), 
        array('uid','require','用户ID不能为空!',1,'regex',3), 
        array('score','require','理财积分不能为空!',1,'regex',3), 
        array('money','require','附加金额不能为空!',1,'regex',3), 
        array('ratio','require','附加金额比例不能为空!',1,'regex',3), 

	);
	
	protected $_auto = array (
		array('ip','get_client_ip',1,'function'),	
	);
	

}
?>