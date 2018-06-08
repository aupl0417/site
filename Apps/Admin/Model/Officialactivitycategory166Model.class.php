<?php
/**
* 此文件为自动生成
*/
namespace Admin\Model;
use Think\Model;
class Officialactivitycategory166Model extends Model {
	//protected $patchValidate = true; //批量验证
	protected $tableName='officialactivity_category';
	protected $_validate = array(
        array('category_name','require','活动名称不能为空!',1,'regex',3), 
        array('status','require','状态不能为空!',1,'regex',3), 
        array('icon','require','图标不能为空!',1,'regex',3), 
        array('domain','require','域名前缀不能为空!',1,'regex',3), 
        array('content','require','介绍不能为空!',1,'regex',3), 

	);
	
	protected $_auto = array (
		array('ip','get_client_ip',1,'function'),	
	);
	

}
?>