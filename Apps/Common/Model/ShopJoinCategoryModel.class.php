<?php
namespace Common\Model;
use Think\Model;
class ShopJoinCategoryModel extends Model {
	//protected $patchValidate = true; //批量验证
	protected $tableName='shop_join_category';
	protected $_validate = array(
        array('category_id','require','一级类目不能为空!',1,'regex',3), 
        //array('category_second','require','二级类目不能为空!',1,'regex',3), 
        array('uid','require','用户uid不能为空!',1,'regex',3), 

	);
	
	protected $_auto = array (
		array('ip','get_client_ip',1,'function'),	
	);
	

}
?>