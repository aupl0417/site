<?php
/**
* 此文件为自动生成
*/
namespace Admin\Model;
use Think\Model;
class Shoptemplates161Model extends Model {
	//protected $patchValidate = true; //批量验证
	protected $tableName='shop_templates';
	protected $_validate = array(
        array('status','require','状态不能为空!',1,'regex',3), 
        array('tpl_name','require','模板名称不能为空!',1,'regex',3), 
        array('tpl_url','require','模板路径不能为空!',1,'regex',3), 
        array('images','require','图片不能为空!',1,'regex',3), 

	);
	
	protected $_auto = array (
		array('ip','get_client_ip',1,'function'),	
	);
	

}
?>