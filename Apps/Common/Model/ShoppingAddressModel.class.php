<?php
namespace Common\Model;
use Think\Model;
class ShoppingAddressModel extends Model {
	protected $tableName='shopping_address';
	protected $_validate = array(
        array('uid','require','用户ID不能为空！',1,'regex',3), 
        array('linkname','require','联系人姓名不能为空!',1,'regex',3), 
        array('mobile','check_mobile','手机号码格式错误!',1,'callback',3),
        array('tel','check_tel','电话号码格式错误!',1,'callback',3),
        array('postcode','checkform','邮编格式错误!',2,'function',3,array('is_zip')),
        array('province','require','省份ID不能为空!',1,'regex',3), 
        array('city','require','城市ID不能为空!',1,'regex',3), 
        array('district','require','区县ID不能为空!',1,'regex',3), 
        // array('town','require','街道/镇ID不能为空!',1,'regex',3), 
        array('street','require','详细地址不能为空!',1,'regex',3),
	);
	
	protected $_auto = array (
		array('ip','get_client_ip',1,'function'),	
	);

    /**
     * subject: 手机号码和电话号码必须填写一个
     * author: liangfeng
     * day: 2017-08-14
     */
	public function check_mobile(){
	    return $this->_check_phone('mobile');
    }
    public function check_tel(){
        return $this->_check_phone('tel');
    }
    private function _check_phone($val){
        if(!empty($_POST['mobile']) || !empty($_POST['tel'])){
            if($val == 'mobile' && !empty($_POST['mobile'])){
                if(checkform($_POST['mobile'],'is_mobile') == false){
                    return false;
                }else{
                    return true;
                }
            }
            if($val == 'tel' && !empty($_POST['tel'])){
                if(checkform($_POST['tel'],'is_phone') == false){
                    return false;
                }else{
                    return true;
                }
            }
        }else{
            return false;
        }
    }
}
?>