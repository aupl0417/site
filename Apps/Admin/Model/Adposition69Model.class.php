<?php
/**
* 此文件为自动生成
*/
namespace Admin\Model;
use Think\Model;
class Adposition69Model extends Model {
	//protected $patchValidate = true; //批量验证
	protected $tableName='ad_position';
	protected $_validate = array(
        array('type','require','类型不能为空!',1,'请选择附加规则',3), 
        array('device','require','投放设备不能为空!',1,'请选择附加规则',3), 
        array('position_name','require','位置名称不能为空!',1,'请选择附加规则',3), 
        array('channel','require','频道不能为空!',0,'请选择附加规则',3), 
        array('width','require','图片宽度不能为空!',1,'请选择附加规则',3), 
        array('height','require','图片高度不能为空!',1,'请选择附加规则',3), 
        array('num','require','充许图片数量不能为空!',1,'请选择附加规则',3), 
        array('price','require','日单价不能为空!',0,'请选择附加规则',3), 
        array('images','require','广告位截图不能为空!',1,'regex',3), 
        array('default_images','require','缺省图片不能为空!',1,'regex',3), 
        array('url','require','缺省链接不能为空!',1,'regex',3), 

	);
	
	protected $_auto = array (
		array('ip','get_client_ip',1,'function'),	
	);
	

}
?>