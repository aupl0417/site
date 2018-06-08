<?php
namespace Common\Model;
use Think\Model;
class ExpressAreaModel extends Model {
	//protected $patchValidate = true; //批量验证
	protected $tableName='express_area';
	protected $_validate = array(
        array('express_id','require','运费模板ID不能为空!',1,'regex',3), 
        array('city_ids','require','城市ID不能为空!',1,'regex',3), 
        array('first_unit','require','首重/件不能为空!',1,'regex',3), 
        array('first_price','require','首得/件金额不能为空!',1,'regex',3), 
        array('next_unit','require','续重/件不能为空!',1,'regex',3), 
        array('next_price','require','续重/件金额不能为空!',1,'regex',3), 

	);
	
	protected $_auto = array (
		array('ip','get_client_ip',1,'function'),	
	);
	

}
?>