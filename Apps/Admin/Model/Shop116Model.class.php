<?php
/**
* 此文件为自动生成
*/
namespace Admin\Model;
use Think\Model;
class Shop116Model extends Model {
	//protected $patchValidate = true; //批量验证
	protected $tableName='shop';
	protected $_validate = array(
        array('type_id','require','店铺类型ID不能为空!',1,'regex',3), 
        array('shop_name','require','店铺名称不能为空!',1,'regex',3), 
        array('inventory_type','require','库存结算方式不能为空!',1,'regex',3), 
        array('uid','require','用户ID不能为空!',1,'regex',3), 
        array('is_test','require','是否为测试店铺不能为空!',1,'regex',3), 
        array('about','require','店铺简介不能为空!',1,'regex',3), 
        array('category_second','require','二级类目ID不能为空!',1,'regex',3), 
        array('mobile','require','手机号码不能为空!',1,'regex',3), 
        array('qq','require','ＱＱ不能为空!',1,'regex',3), 
        array('province','require','省份ID不能为空!',1,'regex',3), 
        array('city','require','城市ID不能为空!',1,'regex',3), 
        array('district','require','区ID不能为空!',1,'regex',3), 
        array('street','require','详细地址不能为空!',1,'regex',3), 

	);
	
	protected $_auto = array (
		array('ip','get_client_ip',1,'function'),	
	);
	

}
?>