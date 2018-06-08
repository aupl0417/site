<?php
/**
* 此文件为自动生成
*/
namespace Admin\Model;
use Think\Model;
class Goodsattrlist97Model extends Model {
	//protected $patchValidate = true; //批量验证
	protected $tableName='goods_attr_list';
	protected $_validate = array(
        array('goods_id','require','商品ID不能为空!',1,'regex',3), 
        array('attr','require','库存属性值不能为空!',1,'regex',3), 
        array('attr_id','require','库存属性ID不能为空!',1,'regex',3), 
        array('num','checkform','库存数量必须大于0',1,'function',3,array('egt')), 
        array('price','checkform','价格不得小于0.1元',1,'function',3,array('egt',0.1)), 

	);
	
	protected $_auto = array (
		array('ip','get_client_ip',1,'function'),	
	);
	

}
?>