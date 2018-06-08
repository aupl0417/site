<?php
namespace Common\Model;
use Think\Model;
class SupplierPackageModel extends Model {
	protected $tableName='supplier_package';
	protected $_validate = array(
		array('uid','number','没有用户',1),
        array('package','chack_package','请选择套餐',0,'callback'),
        array('money','currency','金额错误',0,'regex'),
        array('gold_num','number','金积分商品数量错误',0,'regex'),
        array('slive_num','number','银积分商品数量错误',0,'regex'),
        array('cash_num','number','现金商品数量错误',0,'regex'),
		
		array('pay_type','check_pay_type','付款方式错误',0,'callback',2),
		array('pay_price','currency','付款金额错误',0,'regex',2),
		array('voucher','require','请上传付款凭证',0,'regex',2),

		array('bank','require','请选择付款银行',0,'regex',2),
		array('bank_user_name','require','请填写户名',0,'regex',2),
		array('bank_account','require','请填写卡号',0,'regex',2),
		
		array('pay_account','require','请填写转账账户',0,'regex',2),
	);
	
	protected $_auto = array (
		array('ip','get_client_ip',1,'function'),	
	);


    public function chack_package($data){
        if (!$data) {
            return false;
        }
        $data = explode(',',$data);
        foreach($data as $v){
            if($v == ''){
                return false;
            }
            if(!in_array($v,['1','2','4'])){
                return false;
            }
        }
        return true;
    }

	//检查付款类型
	public function check_pay_type($data){
		if(!in_array($data,['1','2','3'])){
			return false;
		}else{
			return true;
		}
	}
	

    
}
?>