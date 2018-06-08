<?php
namespace Common\Model;
use Think\Model\ViewModel;
class ExpressViewModel extends ViewModel {
    public $viewFields = array(
		'express'=>array('*'),
		'express_company'=>array('company','sub_name','logo','_on'=>'express.express_company_id=express_company.id'),
    );
}
?>