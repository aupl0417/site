<?php
/**
* 此文件为自动生成
*/
namespace Admin\Model;
use Think\Model;
class Synonym202Model extends Model {
	//protected $patchValidate = true; //批量验证
	protected $tableName='synonym';
	protected $_validate = array(
        array('sid','require','分类不能为空!',1,'regex',3), 
        array('word','require','关键词不能为空!',1,'regex',3), 
        array('synonym','require','同义词不能为空!',1,'regex',3), 

	);
	
	protected $_auto = array (
		array('ip','get_client_ip',1,'function'),	
	);
	

}
?>