<?php
/**
* 此文件为自动生成
*/
namespace Admin\Model;
use Think\Model;
class Cart123Model extends Model {
	//protected $patchValidate = true; //批量验证
	protected $tableName='cart';
	protected $_validate = array(
        array('price','checkform','单价不能为空!',1,'function',3,array('gt',0)), 
        array('goods_id','require','宝贝ID不能为空!',1,'regex',3), 
        array('attr_list_id','require','库存ID不能为空!',1,'regex',3), 
        array('attr_id','require','属性组合ID不能为空!',1,'regex',3), 
        array('attr_name','require','宝贝库存属性不能为空!',1,'regex',3), 
        array('seller_id','require','卖家ID不能为空!',1,'regex',3), 
        array('uid','require','买家ID不能为空!',1,'regex',3), 
        array('shop_id','require','店铺ID不能为空!',1,'regex',3), 
        array('num','checkform','订购数量不能为空!',1,'function',3,array('gt',0)), 
        array('total_price','checkform','合计金额不能为空!',1,'function',3,array('gt',0)), 

	);
	
	protected $_auto = array (
		array('ip','get_client_ip',1,'function'),	
	);
	

}
?>