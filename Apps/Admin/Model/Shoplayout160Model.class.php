<?php
/**
* 此文件为自动生成
*/
namespace Admin\Model;
use Think\Model;
class Shoplayout160Model extends Model {
	//protected $patchValidate = true; //批量验证
	protected $tableName='shop_layout';
	protected $_validate = array(
        array('name','require','名称不能为空!',1,'regex',3), 
        array('type','require','类型不能为空!',1,'regex',3), 
        array('col','require','栏数不能为空!',1,'regex',3), 
        array('col_0','require','第一栏宽度不能为空!',1,'regex',3), 

	);
	
	protected $_auto = array (
		array('ip','get_client_ip',1,'function'),	
	);
	

}
?>