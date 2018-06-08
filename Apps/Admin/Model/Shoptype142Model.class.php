<?php
/**
* 此文件为自动生成
*/
namespace Admin\Model;
use Think\Model;
class Shoptype142Model extends Model {
	//protected $patchValidate = true; //批量验证
	protected $tableName='shop_type';
	protected $_validate = array(
        array('type_name','require','店铺类型不能为空!',1,'regex',3), 
        array('icon','require','类型标志不能为空!',1,'regex',3), 
        array('bond_price','require','保证金不能为空!',1,'regex',3), 
        array('max_goods','require','充许商品数量不能为空!',1,'regex',3), 
        array('max_brand','require','充许品牌数量不能为空!',1,'regex',3), 
        array('max_category','require','充许经营一级类目数量不能为空!',1,'regex',3), 
        array('max_second_category','require','充许经营二级类目数量不能为空!',1,'regex',3), 
        array('content','require','店铺介绍不能为空!',1,'regex',3), 

	);
	
	protected $_auto = array (
		array('ip','get_client_ip',1,'function'),	
	);
	

}
?>