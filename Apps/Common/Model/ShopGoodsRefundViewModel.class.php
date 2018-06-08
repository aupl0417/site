<?php
namespace Common\Model;
use Think\Model\ViewModel;

class ShopGoodsRefundViewModel extends ViewModel
{
	protected $tableName = 'orders_shop';
	public $viewFields = [
		'orders_shop' => ['status'],
		'orders_goods' => ['*','_on' => 'orders_shop.s_no = orders_goods.s_no'],
	];

}