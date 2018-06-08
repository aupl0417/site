<?php
namespace Common\Model;
use Think\Model;
class BrandExtModel extends Model {
	protected $tableName='brand_ext';
	protected $_validate = array(
                array('uid','require','用户ID不能为空!',1,'regex',3), 
                array('brand_id','require','品牌ID不能为空!',1,'regex',3), 
                array('name','require','品牌中文名不能为空!',1,'regex',3), 
                array('logo','require','品牌logo不能为空!',1,'regex',3), 
                array('images','require','品牌形象图片不能为空!',1,'regex',3), 
                array('about','require','品牌介绍不能为空!',1,'regex',3), 
                array('category_id','require','品牌类目ID不能为空!',1,'regex',3), 
                array('tag','require','标签不能为空!',1,'regex',3), 
	);
	
	protected $_auto = array (
		array('ip','get_client_ip',1,'function'),	
	);



}
?>