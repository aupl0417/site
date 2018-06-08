<?php
/**
* 此文件为自动生成
*/
namespace Admin\Model;
use Think\Model;
class Express118Model extends Model {
	//protected $patchValidate = true; //批量验证
	protected $tableName='express';
	protected $_validate = array(
        array('express_company_id','require','快递公司ID不能为空!',1,'regex',3), 
        array('express_name','require','模板名称不能为空!',1,'regex',3), 
        array('unit','require','计量单位不能为空!',1,'regex',3), 
        array('first_unit','require','首重数量不能为空!',1,'regex',3), 
        array('first_price','require','首重费用不能为空!',1,'regex',3), 
        array('next_unit','require','续重数量不能为空!',1,'regex',3), 
        array('next_price','require','续重费用不能为空!',1,'regex',3), 

	);
	
	protected $_auto = array (
		array('ip','get_client_ip',1,'function'),	
	);
	

}
?>