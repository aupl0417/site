<?php
namespace Common\Model;
use Think\Model;
class AdSucaiModel extends Model {
	protected $tableName='ad_sucai';
	protected $_validate = array(
        array('uid','require','用户ID不能为空!',1,'regex',3), 
        array('sucai_name','require','素材标题不能为空!',1,'regex',3), 
        array('images','require','素材图片不能为空!',1,'regex',3), 
        array('width','require','素材宽度不能为空!',1,'regex',3), 
        array('height','require','素材高度不能为空!',1,'regex',3), 
        array('category_id','require','投放类目ID不能为空!',1,'regex',3), 
	);
	
	protected $_auto = array (
		array('ip','get_client_ip',1,'function'),	
	);



}
?>