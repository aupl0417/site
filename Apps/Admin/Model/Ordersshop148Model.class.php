<?php
/**
* 此文件为自动生成
*/
namespace Admin\Model;
use Think\Model;
class Ordersshop148Model extends Model {
	//protected $patchValidate = true; //批量验证
	protected $tableName='orders_shop';
	protected $_validate = array(
        array('terminal','require','终端不能为空!',1,'regex',3), 
        array('status','require','状态不能为空!',1,'regex',3), 
        array('s_no','require','商家订单号不能为空!',1,'regex',3), 
        array('uid','require','买用户ID不能为空!',1,'regex',3), 
        array('seller_id','require','卖家用户ID不能为空!',1,'regex',3), 
        array('shop_id','require','店铺ID不能为空!',1,'regex',3), 
        array('express_type','require','发货方式不能为空!',1,'regex',3), 
        array('goods_price','require','商品金额不能为空!',1,'regex',3), 
        array('express_price','require','运费金额不能为空!',1,'regex',3), 
        array('express_price_edit','require','修改后的运费不能为空!',1,'regex',3), 
        array('total_price','require','订单总金额不能为空!',1,'regex',3), 
        array('pay_price','require','实付金额不能为空!',1,'regex',3), 
        array('score','require','赠送积分不能为空!',1,'regex',3), 
        array('inventory_type','require','库存结算方式不能为空!',1,'regex',3), 

	);
	
	protected $_auto = array (
		array('ip','get_client_ip',1,'function'),	
	);
	

}
?>