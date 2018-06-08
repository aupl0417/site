<?php
/**
* 此文件为自动生成
*/
namespace Admin\Model;
use Think\Model;
class Seo57Model extends Model {
	//protected $patchValidate = true; //批量验证
	protected $tableName='seo';
	protected $_validate = array(
        array('url','require','链接不能为空!',1,'regex',3), 

	);
	
	protected $_auto = array (
		array('ip','get_client_ip',1,'function'),	
	);
	

}
?>