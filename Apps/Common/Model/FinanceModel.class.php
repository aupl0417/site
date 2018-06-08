<?php
namespace Common\Model;
use Think\Model;
class FinanceModel extends Model {
	//protected $patchValidate = true; //批量验证
	protected $tableName='finance';
	protected $_validate = array(
        array('f_no','require','流水号不能为空!',1,'regex',3), 
        array('uid','require','用户ID不能为空!',1,'regex',3), 
        array('score','checkform','理财积分不能为空且须大于0!',1,'function',3,array('gt0')), 
        array('money','require','附加金额不能为空且须大于0!',1,'function',3,array('gt0')), 
        array('ratio','require','附加金额比例不能为空!',1,'regex',3), 

	);
	
	protected $_auto = array (
		array('ip','get_client_ip',1,'function'),	
	);
	

}
?>