<?php
namespace Home\Model;
use Think\Model\ViewModel;
class BrandViewModel extends ViewModel {
	public $viewFields = array(
	    'brand'		=>array('*'),
	    'store'		=>array('name'=>'shop_name','qq','wang','domain','_on'=>'brand.memberid=store.memberid'),
	);
}
?>