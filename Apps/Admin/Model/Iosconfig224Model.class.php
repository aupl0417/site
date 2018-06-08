<?php
/**
* 此文件为自动生成
*/
namespace Admin\Model;
use Think\Model;
class Iosconfig224Model extends Model {
	//protected $patchValidate = true; //批量验证
	protected $tableName='ios_config';
	protected $_validate = array(
        array('content','require','内容不能为空!',1,'regex',3), 

	);
	
	protected $_auto = array (
		array('ip','get_client_ip',1,'function'),	
	);
	

}
?>