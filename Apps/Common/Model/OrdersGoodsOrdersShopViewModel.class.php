<?php
namespace Common\Model;
use Think\Model\ViewModel;

class OrdersGoodsOrdersShopViewModel extends ViewModel
{
	public $viewFields = [
		'orders_goods' => ['*'],
		'orders_shop' => ['status', '_on' => 'orders_goods.s_id=orders_shop.id'],
	];
}