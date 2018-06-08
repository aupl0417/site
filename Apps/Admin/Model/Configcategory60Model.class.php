<?php
/**
* 此文件为自动生成
*/
namespace Admin\Model;
use Think\Model;
class Configcategory60Model extends Model {
	//protected $patchValidate = true; //批量验证
	protected $tableName='config_category';
	protected $_validate = array(
        array('name','require','分组名称不能为空!',1,'请选择附加规则',3), 

	);
	
	protected $_auto = array (
		array('ip','get_client_ip',1,'function'),	
	);
	

}
?>