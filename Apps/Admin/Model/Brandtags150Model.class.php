<?php
/**
* 此文件为自动生成
*/
namespace Admin\Model;
use Think\Model;
class Brandtags150Model extends Model {
	//protected $patchValidate = true; //批量验证
	protected $tableName='brand_tags';
	protected $_validate = array(
        array('status','require','状态不能为空!',1,'regex',3), 
        array('tag_name','require','名称不能为空!',1,'regex',3), 

	);
	
	protected $_auto = array (
		array('ip','get_client_ip',1,'function'),	
	);
	

}
?>