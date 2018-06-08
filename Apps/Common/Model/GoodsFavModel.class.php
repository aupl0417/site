<?php
namespace Common\Model;
use Think\Model;
class GoodsFavModel extends Model {
	//protected $patchValidate = true; //批量验证
	protected $tableName='goods_fav';
	protected $_validate = array(
        array('uid','require','用户ID不能为空!',1,'regex',3),
        array('goods_id','require','商品ID不能为空！',1,'regex',3), 
	);
	
	protected $_auto = array (
		array('ip','get_client_ip',1,'function'),	
	);
	

}
?>