<?php
/**
* 此文件为自动生成
*/
namespace Admin\Model;
use Think\Model;
class Ordersgoodscomment135Model extends Model {
	//protected $patchValidate = true; //批量验证
	protected $tableName='orders_goods_comment';
	protected $_validate = array(
        array('status','require','状态不能为空!',1,'regex',3), 
        array('uid','require','评价者用户ID不能为空!',1,'regex',3), 
        array('seller_id','require','卖家ID不能为空!',1,'regex',3), 
        array('rate','require','评价得分不能为空!',1,'regex',3), 
        array('content','require','评价内容不能为空!',1,'regex',3), 
        array('attr_list_id','require','商品库存ID不能为空!',1,'regex',3), 
        array('goods_id','require','商品ID不能为空!',1,'regex',3), 
        array('orders_goods_id','require','订单商品ID不能为空!',1,'regex',3), 
        array('shop_id','require','店铺ID不能为空!',1,'regex',3), 
        array('s_no','require','商家订单号不能为空!',1,'regex',3), 
        array('s_id','require','商家订单ID不能为空!',1,'regex',3), 

	);
	
	protected $_auto = array (
		array('ip','get_client_ip',1,'function'),	
	);
	

}
?>