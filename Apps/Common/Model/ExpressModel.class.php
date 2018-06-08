<?php
namespace Common\Model;
use Think\Model;
class ExpressModel extends Model {
	//protected $patchValidate = true; //批量验证
	protected $tableName='express';
	protected $_validate = array(
        array('uid','require','用户ID不能为空!',1,'regex',3), 
        array('express_name','require','运费模板不能为空!',1,'regex',3), 
        array('express_company_id','require','快递公司ID不能为空!',1,'regex',3), 
        array('unit','require','计量单位不能为空!',1,'regex',3), 
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