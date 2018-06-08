<?php
/**
* 此文件为自动生成
*/
namespace Admin\Model;
use Think\Model;
class Apidoc107Model extends Model {
	//protected $patchValidate = true; //批量验证
	protected $tableName='api_doc';
	protected $_validate = array(
        array('sid','require','目录ID不能为空!',1,'regex',3), 
        array('title','require','接口标题不能为空!',0,'',3), 

	);
	
	protected $_auto = array (
		array('ip','get_client_ip',1,'function'),	
	);
	

}
?>