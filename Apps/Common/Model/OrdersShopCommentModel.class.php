<?php
namespace Common\Model;
use Think\Model;
class OrdersShopCommentModel extends Model {
	protected $tableName='orders_shop_comment';
	protected $_validate = array(
        array('s_id','require','订单ID不能为空!',1,'regex',3), 
        array('s_no','require','订单号不能为空!',1,'regex',3), 
        array('shop_id','require','店铺ID不能为空!',1,'regex',3), 
        array('uid','require','用户ID不能为空!',1,'regex',3), 
        array('seller_id','require','卖家ID不能为空!',1,'regex',3), 
        array('fraction_speed','require','物流速度评分不能为空!',1,'regex',3), 
        array('fraction_service','require','服务态度评分不能为空!',1,'regex',3), 
        array('fraction_desc','require','描述相符评分不能为空!',1,'regex',3), 
        array('fraction','require','综合得分不能为空!',1,'regex',3), 
	);
	
	protected $_auto = array (
		array('ip','get_client_ip',1,'function'),	
	);



}
?>