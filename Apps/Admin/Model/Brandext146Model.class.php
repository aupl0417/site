<?php
/**
* 此文件为自动生成
*/
namespace Admin\Model;
use Think\Model;
class Brandext146Model extends Model {
	//protected $patchValidate = true; //批量验证
	protected $tableName='brand_ext';
	protected $_validate = array(
        array('name','require','品牌名称不能为空!',1,'regex',3), 
        array('logo','require','品牌logo不能为空!',1,'regex',3), 
        array('tag','require','标签不能为空!',1,'regex',3), 
        array('uid','require','用户ID不能为空!',1,'regex',3), 
        array('about','require','品牌介绍不能为空!',1,'regex',3), 
        array('images','require','品牌形象图不能为空!',1,'regex',3), 
        array('brand_id','require','品牌ID不能为空!',1,'regex',3), 
        array('category_id','require','主营类目不能为空!',1,'regex',3), 

	);
	
	protected $_auto = array (
		array('ip','get_client_ip',1,'function'),	
	);
	

}
?>