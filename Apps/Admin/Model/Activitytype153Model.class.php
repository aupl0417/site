<?php
/**
* 此文件为自动生成
*/
namespace Admin\Model;
use Think\Model;
class Activitytype153Model extends Model {
	//protected $patchValidate = true; //批量验证
	protected $tableName='activity_type';
	protected $_validate = array(
        array('activity_name','require','活动类型名称不能为空!',1,'regex',3), 
        array('intro','require','活动类型介绍不能为空!',1,'regex',3), 

	);
	
	protected $_auto = array (
		array('ip','get_client_ip',1,'function'),	
	);
	

}
?>