<?php
namespace Common\Model;
use Think\Model\ViewModel;

class ShopJoinShopTypeViewModel extends ViewModel
{
	public $viewFields = [
		'shop_type' => ['*'],
		'shop_join_contact' => ['type_id','_on' => 'shop_type.id=shop_join_contact.type_id'],
	];
}