<?php
/**
* 此文件为自动生成
*/
namespace Admin\Model;
use Think\Model;
class Area54Model extends Model {
	//protected $patchValidate = true; //批量验证
	protected $tableName='area';
	protected $_validate = array(
        array('sid','require','级别不能为空!',1,'regex',3), 
        array('a_name','require','名称不能为空!',1,'regex',3), 
        array('a_num','require','区号不能为空!',1,'regex',3), 
        array('a_postcode','require','邮编不能为空!',1,'regex',3), 

	);
	
	protected $_auto = array (
		array('ip','get_client_ip',1,'function'),	
	);
	

}
?>