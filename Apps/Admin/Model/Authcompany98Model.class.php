<?php
/**
* 此文件为自动生成
*/
namespace Admin\Model;
use Think\Model;
class Authcompany98Model extends Model {
	//protected $patchValidate = true; //批量验证
	protected $tableName='auth_company';
	protected $_validate = array(
        array('uid','require','用户ID不能为空!',0,'',3), 
        array('company','require','公司名称不能为空!',0,'',3), 
        array('com_type','require','公司类型不能为空!',0,'',3), 
        array('reg_address','require','注册地址不能为空!',0,'',3), 
        array('business_address','require','经营地址不能为空!',0,'',3), 
        array('reg_capital','require','注册资本不能为空!',0,'',3), 
        array('reg_day','require','注册日期不能为空!',0,'',3), 
        array('end_day','require','成立日期不能为空!',0,'',3), 
        array('license_code','require','营业执照编号不能为空!',0,'',3), 
        array('license_pic','require','营业执行副本图片不能为空!',0,'',3), 
        array('org_code','require','组织机构代码证编号不能为空!',0,'',3), 
        array('tax_code','require','税务登记证编号不能为空!',0,'',3), 
        array('legal','require','法人姓名不能为空!',0,'',3), 
        array('legal_card_type','require','法人证件类型不能为空!',0,'',3), 
        array('legal_card_no','require','法人证件号码不能为空!',0,'',3), 
        array('legal_pic3','require','法人手持证件图片不能为空!',0,'',3), 
        array('legal_pic2','require','法人证件反面图片不能为空!',0,'',3), 

	);
	
	protected $_auto = array (
		array('ip','get_client_ip',1,'function'),	
	);
	

}
?>