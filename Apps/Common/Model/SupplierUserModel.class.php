<?php
namespace Common\Model;
use Think\Model;
class SupplierUserModel extends Model {
	protected $tableName='supplier_user';
	protected $_validate = array(
        array('no','require','编号错误',1,'unique',1),
        array('type','number','类型没有选择',1),
        array('uid','number','没有用户',1),
        array('name','require','姓名不能为空',1),
        array('company_name','require','公司名称不能为空',0),
        array('id_no','check_ID','请填写正确的身份证号码',1,'callback'),
        array('mobile','check_mobile','请填写正确的联系电话',1,'callback'),
		
		array('province','number','请选择省份',1),
        array('city','number','请选择城市',1),
        array('district','number','请选择区/县',1),
        array('town','number','请选择街道',2),
        array('street','require','请填写详细地址',1),
		array('credit_code','require','请填写社会信用代码',0),
		
        array('id_pic1','require','请上传身份证正面',1),
        array('id_pic2','require','请上传身份证反面',1),
        array('id_pic3','require','请上传手持身份证',1),
        array('business_pic','require','请上传营业执照',0),
        
        
        array('bank','number','请选择开户银行',1),
        array('bank_province','number','请选择开户银行省份',1),
        array('bank_city','number','请选择开户银行城市',1),
        array('bank_open','require','请填写开户支行',1),
        array('bank_user','require','请填写开户人',1),
        array('bank_no','require','请填写卡号',1),
	);
	
	protected $_auto = array (
		array('ip','get_client_ip',1,'function'),	
	);



	public function check_ID($data){
		if (!$data) {
            return false;
        }
        return preg_match('#^[\d]{15}$|^[\d]{18}$#', $data) ? true : false;
	}
	
	public function check_mobile($data){
		if (!is_numeric($data)) {
			return false;
		}
		return preg_match('#^13[\d]{9}$|^14[5,7]{1}\d{8}$|^15[^4]{1}\d{8}$|^17[0,6,7,8]{1}\d{8}$|^18[\d]{9}$#', $data) ? true : false;
	}
    
}
?>