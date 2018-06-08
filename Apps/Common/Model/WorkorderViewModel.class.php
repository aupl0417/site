<?php
namespace Common\Model;
use Think\Model\ViewModel;

class WorkorderViewModel extends ViewModel
{

	protected $tableName = 'workorder';

	public $viewFields = [
		'workorder' 		=> ['*'],
		'user' 				=> ['nick', '_on' => 'workorder.uid = user.id'],
		# 'workorder_type' 	=> ['name'=>'type_name' , '_on' => 'workorder.type = workorder_type.id'],
		# 'workorder_type' 	=> ['id'=>'type2_name' , '_on' => 'workorder.type = workorder_type.id'],
	];


}