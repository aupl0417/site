<?php
/**
* 此文件为自动生成
*/
namespace Admin\Model;
use Think\Model;
class Zhaoshangbrand175Model extends Model {
	//protected $patchValidate = true; //批量验证
	protected $tableName='zhaoshang_brand';
	protected $_validate = array(
        array('name','require','品牌名不能为空!',1,'regex',3), 
        array('category_id','require','类目不能为空!',1,'regex',3), 

	);
	
	protected $_auto = array (
		array('ip','get_client_ip',1,'function'),	
	);
	

}
?>