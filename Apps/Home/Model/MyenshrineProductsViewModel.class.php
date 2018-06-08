<?php
namespace Home\Model;
use Think\Model\ViewModel;
class MyenshrineProductsViewModel extends ViewModel {
	public $viewFields = array(
	    'myenshrine'	=>array('*'),
	    'products'		=>array('name','images','price','_on'=>'myenshrine.enshrine_id=products.id'),
	);


}
?>