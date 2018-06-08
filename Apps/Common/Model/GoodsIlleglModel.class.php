<?php
namespace Common\Model;
use Think\Model;
class GoodsIlleglModel extends Model {
	//protected $patchValidate = true; //批量验证
	protected $tableName='goods_illegl';
	protected $_validate = array(
        array('shop_id','require','店铺ID不能为空!',1,'regex',3), 
        array('uid','require','用户ID不能为空!',1,'regex',3), 
        array('goods_id','require','商品ID不能为空!',1,'regex',3), 
        array('reason','require','违规原因不能为空!',1,'regex',3),

	);
	
	protected $_auto = array (
		array('ip','get_client_ip',1,'function'),	
	);
	

}
?>