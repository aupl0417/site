<?php
/**
* 此文件为自动生成
*/
namespace Admin\Model;
use Think\Model;
class Zhaoshangcred176Model extends Model {
	//protected $patchValidate = true; //批量验证
	protected $tableName='zhaoshang_cred';
	protected $_validate = array(
        array('type','require','资质类别不能为空!',1,'regex',3), 
        array('cred_name','require','资质名称不能为空!',1,'regex',3), 

	);
	
	protected $_auto = array (
		array('ip','get_client_ip',1,'function'),	
	);
	

}
?>