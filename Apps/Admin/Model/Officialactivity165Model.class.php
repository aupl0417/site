<?php
/**
* 此文件为自动生成
*/
namespace Admin\Model;
use Think\Model;
class Officialactivity165Model extends Model {
	//protected $patchValidate = true; //批量验证
	protected $tableName='officialactivity';
	protected $_validate = array(
        array('category_id','require','活动类型不能为空!',1,'regex',3), 
        array('activity_name','require','活动名称不能为空!',1,'regex',3), 
        array('status','require','状态不能为空!',1,'regex',3), 
        array('icon','require','活动图标不能为空!',1,'regex',3), 
        array('accept_category','require','招商类目不能为空!',1,'regex',3), 
        array('join_type','require','报名形式不能为空!',1,'regex',3), 
        array('max_buy','require','每个ID限购数量不能为空!',1,'regex',3), 
        array('shop_map','require','对店铺的要求不能为空!',1,'regex',3), 
        array('goods_map','require','对商品的要求不能为空!',1,'regex',3), 
        array('imgsize','require','图片尺寸要求不能为空!',1,'regex',3), 
        array('content','require','活动介绍不能为空!',1,'regex',3), 

	);
	
	protected $_auto = array (
		array('ip','get_client_ip',1,'function'),	
	);
	

}
?>