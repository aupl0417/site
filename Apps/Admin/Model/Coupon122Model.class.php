<?php
/**
* 此文件为自动生成
*/
namespace Admin\Model;
use Think\Model;
class Coupon122Model extends Model {
	//protected $patchValidate = true; //批量验证
	protected $tableName='coupon';
	protected $_validate = array(
        array('b_id','require','批次ID不能为空!',1,'regex',3), 
        array('uid','require','买家ID不能为空!',1,'regex',3), 
        array('price','require','面值不能为空!',1,'regex',3), 
        array('min_price','require','最低使用金额不能为空!',1,'regex',3), 
        array('sday','require','有效起时间不能为空!',1,'regex',3), 
        array('eday','require','有效结束时间不能为空!',1,'regex',3), 
        array('channel','require','领取通道不能为空!',1,'regex',3), 
        array('face_type','require','面值类型不能为空!',1,'regex',3), 
        array('type','require','类型不能为空!',1,'regex',3), 
        array('use_type','require','使用场景不能为空!',1,'regex',3), 

	);
	
	protected $_auto = array (
		array('ip','get_client_ip',1,'function'),	
	);
	

}
?>