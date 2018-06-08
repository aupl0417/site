<?php
namespace Common\Model;
use Think\Model;
class ImagesModel extends Model {
	//protected $patchValidate = true; //批量验证
	protected $tableName='images';
	protected $_validate = array(
        array('name','require','标题不能为空!',1,'regex',3), 
        array('url','require','链接不能为空!',1,'regex',3), 
        array('fkey','require','Key不能为空!',1,'regex',3), 

	);
	
	protected $_auto = array (
		array('ip','get_client_ip',1,'function'),	
	);
	

}
?>