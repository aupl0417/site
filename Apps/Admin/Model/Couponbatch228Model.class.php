<?php
/**
* 此文件为自动生成
*/
namespace Admin\Model;
use Think\Model;
class Couponbatch228Model extends Model {
	//protected $patchValidate = true; //批量验证
	protected $tableName='coupon_batch';
	protected $_validate = array(
        array('sday','require','生效时间不能为空!',1,'regex',3), 
        array('eday','require','失效时间不能为空!',1,'regex',3), 
        array('price','require','面值不能为空!',1,'regex',3), 
        array('num','require','发行数量不能为空!',1,'regex',3), 
        array('min_price','require','最低使用限度不能为空!',1,'regex',3), 
        array('max_num','require','每人最多领取数量不能为空!',1,'regex',3), 
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