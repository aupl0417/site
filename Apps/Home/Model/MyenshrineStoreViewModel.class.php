<?php
namespace Home\Model;
use Think\Model\ViewModel;
class MyenshrineStoreViewModel extends ViewModel {
	public $viewFields = array(
	    'myenshrine'	=>array('*'),
	    'store'		=>array('name'=>'shop_name','memberid','qq','wang','domain','level_point','_on'=>'myenshrine.enshrine_id=store.id'),
	    'member'	=>array('username','is_name','is_xiaobao','_on'=>'store.memberid=member.id'),	    
	    'province'	=>array('name'=>'province','_table'=>'ylh_area','_as'=>'province','_on'=>'store.province=province.id'),
	    'city'		=>array('name'=>'city','_table'=>'ylh_area','_as'=>'city','_on'=>'store.city=city.id'),
	);


}
?>