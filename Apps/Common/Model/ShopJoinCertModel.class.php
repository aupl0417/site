<?php
namespace Common\Model;
use Think\Model;
class ShopJoinCertModel extends Model {
	//protected $patchValidate = true; //批量验证
	protected $tableName='shop_join_cert';
	protected $_validate = array(
		array('uid','require','用户ID不能为空!',1,'regex',3), 
        array('category_id','require','资质类目不能为空!',1,'regex',3), 
        array('cert_name','require','资质名称不能为空!',1,'regex',3), 
        array('cert_images','require','资质证书照片不能为空!',1,'regex',3), 

	);
	
	protected $_auto = array (
		array('ip','get_client_ip',1,'function'),	
	);
	

}
?>