<?php
namespace Common\Model;
use Think\Model\ViewModel;

class OrdersGoodsOfficialactivityViewModel extends ViewModel
{
	public $viewFields = [
		'orders_goods' => ['*','_type' => 'LEFT'],
		'officialactivity' => ['activity_name', '_on' => 'orders_goods.officialactivity_id=officialactivity.id'],
	];
}