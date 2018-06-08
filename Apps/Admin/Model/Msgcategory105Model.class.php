<?php
/**
* 此文件为自动生成
*/
namespace Admin\Model;
use Think\Model;
class Msgcategory105Model extends Model {
	//protected $patchValidate = true; //批量验证
	protected $tableName='msg_category';
	protected $_validate = array(
        array('category_name','require','分类名称不能为空!',0,'',3), 

	);
	
	protected $_auto = array (
		array('ip','get_client_ip',1,'function'),	
	);
	

}
?>