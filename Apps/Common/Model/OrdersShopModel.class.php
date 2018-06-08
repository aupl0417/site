<?php
namespace Common\Model;
use Think\Model;
class OrdersShopModel extends Model {
	protected $tableName='orders_shop';
	protected $_validate = array(
        array('o_no','require','主订单号不能为空!',1,'regex',3), 
        array('s_no','require','商家订单号不能为空!',1,'regex',3), 
        array('shop_id','require','店铺ID不能为空!',1,'regex',3), 
        array('uid','require','买家ID不能为空!',1,'regex',3), 
        array('seller_id','require','卖家ID不能为空!',1,'regex',3), 
        array('goods_price','checkform','总金额不能为空且须大于等于0.1以上！',1,'function',3,array('egt',0.1)), 
        array('express_price','require','商品单价不能为空且必须是正数！',1), 
        array('total_price','checkform','总金额不能为空且须大于等于0.1以上！',1,'function',3,array('egt',0.1)), 
        array('pay_price','checkform','实付金额不能为空且须大于等于0.1以上！',1,'function',3,array('egt',0.1)), 
        array('express_type','require','发货方式不能为空',1),
        //array('express_id','require','运费模板ID不能为这',1), 
        //array('express_company_id','require','快递公司ID不能为这',1), 
        //array('express_company','require','快递公司名称不能为这',1), 
	);
	
	protected $_auto = array (
		array('ip','get_client_ip',1,'function'),	
	);



}
?>