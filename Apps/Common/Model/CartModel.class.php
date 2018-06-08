<?php
namespace Common\Model;
use Think\Model;
class CartModel extends Model {
	protected $tableName='cart';
	protected $_validate = array(
        array('uid','require','买家ID不能为空!',1,'regex',3), 
        array('seller_id','require','卖家ID不能为空!',1,'regex',3), 
        array('shop_id','require','店铺ID不能为空!',1,'regex',3), 
        array('goods_id','require','商品ID不能为空!',1,'regex',3), 
        array('attr_list_id','require','库存ID不能为空!',1,'regex',3), 
        array('attr_name','require','商品属性不能为空!',1,'regex',3), 
        array('price','checkform','商品单价不能为空且必须是正数!',1,'function',3,array('gt',0)), 
        array('num','checkform','商品数量必须大于0的正整数!',1,'function',3,array(array('is_positive_number'),array('gt',0))),
	);
	
	protected $_auto = array (
		array('ip','get_client_ip',1,'function'),	
	);



}
?>