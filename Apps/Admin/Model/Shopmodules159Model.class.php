<?php
/**
* 此文件为自动生成
*/
namespace Admin\Model;
use Think\Model;
class Shopmodules159Model extends Model {
	//protected $patchValidate = true; //批量验证
	protected $tableName='shop_modules';
	protected $_validate = array(
        array('mod_name','require','模块名称不能为空!',1,'regex',3), 
        array('status','require','状态不能为空!',1,'regex',3), 
        array('sid','require','模块类型不能为空!',1,'regex',3), 
        array('templates_id','require','模板ID不能为空!',1,'regex',3), 
        array('mod_url','require','模板路径不能为空!',1,'regex',3), 

	);
	
	protected $_auto = array (
		array('ip','get_client_ip',1,'function'),	
	);
	

}
?>