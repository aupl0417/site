<?php
/**
* 此文件为自动生成
*/
namespace Admin\Model;
use Think\Model;
class Hot203Model extends Model {
	//protected $patchValidate = true; //批量验证
	protected $tableName='hot';
	protected $_validate = array(
        array('category_id','require','商品一级分类不能为空!',1,'regex',3), 
        array('name','require','标题不能为空!',1,'regex',3), 
        array('url','require','链接不能为空!',1,'regex',3), 
        array('images','require','图片不能为空!',1,'regex',3), 

	);
	
	protected $_auto = array (
		array('ip','get_client_ip',1,'function'),	
	);
	

}
?>