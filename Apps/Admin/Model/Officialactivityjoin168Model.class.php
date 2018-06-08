<?php
/**
* 此文件为自动生成
*/
namespace Admin\Model;
use Think\Model;
class Officialactivityjoin168Model extends Model {
	//protected $patchValidate = true; //批量验证
	protected $tableName='officialactivity_join';
	protected $_validate = array(
        array('activity_id','require','活动ID不能为空!',1,'regex',3), 
        array('day','require','活动日期不能为空!',1,'regex',3), 
        array('status','require','状态不能为空!',1,'regex',3), 
        array('subject','require','活动标题不能为空!',1,'regex',2), 
        array('num','require','库存不能为空!',1,'regex',3), 
        array('price','require','活动价不能为空!',1,'regex',3), 
        array('goods_id','require','商品ID不能为空!',1,'regex',3), 
        array('uid','require','用户ID不能为空!',1,'regex',3), 
        array('shop_id','require','店铺ID不能为空!',1,'regex',3), 
        array('images','require','活动图片，序列化保存不能为空!',1,'regex',3), 

	);
	
	protected $_auto = array (
		array('ip','get_client_ip',1,'function'),	
	);
	

}
?>