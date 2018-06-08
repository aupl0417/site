<?php
/**
* 此文件为自动生成
*/
namespace Admin\Model;
use Think\Model;
class Ad164Model extends Model {
	//protected $patchValidate = true; //批量验证
	protected $tableName='ad';
	protected $_validate = array(
        array('position_id','require','广告位ID不能为空!',1,'regex',3), 
        array('status','require','状态不能为空!',1,'regex',3), 
        array('is_default','require','默认广告不能为空!',1,'regex',3), 
        array('name','require','广告标题不能为空!',1,'regex',3), 
        array('images','require','广告图片不能为空!',1,'regex',3), 
        array('type','require','投放类型不能为空!',1,'regex',3), 
        array('url','require','链接地址不能为空!',1,'regex',3), 

	);
	
	protected $_auto = array (
		array('ip','get_client_ip',1,'function'),	
	);
	

}
?>