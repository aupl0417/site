<?php
/**
* 此文件为自动生成
*/
namespace Admin\Model;
use Think\Model;
class Expresscompany82Model extends Model {
	//protected $patchValidate = true; //批量验证
	protected $tableName='express_company';
	protected $_validate = array(
        array('company','require','快递公司名称不能为空!',1,'regex',3), 
        array('sub_name','require','简称不能为空!',1,'regex',3), 
        array('tel','require','电话不能为空!',1,'regex',3), 
        array('website','require','快递公司网址不能为空!',1,'regex',3), 
        array('logo','require','快递公司logo不能为空!',1,'regex',3), 
        array('code','require','编号不能为空!',1,'regex',3), 

	);
	
	protected $_auto = array (
		array('ip','get_client_ip',1,'function'),	
	);
	

}
?>