<?php
namespace Home\Model;
use Think\Model\ViewModel;
class ViewhistoryViewModel extends ViewModel {
	public $viewFields = array(
	    'viewhistory'	=>array('*'),
	    'products'		=>array('name','images','price','_on'=>'viewhistory.infoid=products.id'),
	);


}
?>