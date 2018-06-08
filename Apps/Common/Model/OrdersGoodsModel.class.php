<?php
namespace Common\Model;
use Think\Model;
class OrdersGoodsModel extends Model {
	protected $tableName='orders_goods';
	protected $_validate = array(
                array('s_id','require','卖家订单ID不能为空!',1,'regex',3), 
                array('s_no','require','卖家订单号不能为空!',1,'regex',3), 
                array('o_no','require','主订单号不能为空!',1,'regex',3), 
                array('uid','require','买家ID不能为空!',1,'regex',3), 
                array('seller_id','require','卖家ID不能为空!',1,'regex',3), 
                array('shop_id','require','店铺ID不能为空!',1,'regex',3), 
                array('goods_id','require','商品ID不能为空!',1,'regex',3), 
                array('attr_list_id','require','库存ID不能为空!',1,'regex',3), 
                array('attr_name','require','商品属性不能为空!',1,'regex',3), 
                array('price','checkform','商品单价不能为空且必须是正数!',1,'function',3,array('gt',0)), 
                array('num','checkform','商品数量必须大于0的正整数!',1,'function',3,array(array('is_positive_number'),array('gt',0))),
                array('weight','require','重量不能为空!',1),
                array('total_price','checkform','合计金额不能为空且须大于等于0.1以上!',1,'function',3,array('egt',0.1)),
                array('total_weight','require','合计重量不能为空!',1),
                array('goods_name','require','商品标题不能为空!',1),
                array('images','require','商品主图不能为空!',1),
	);
	
	protected $_auto = array (
		array('ip','get_client_ip',1,'function'),	
	);

}
?>