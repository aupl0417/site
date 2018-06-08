<?php
namespace Common\Model;
use Think\Model\ViewModel;

class WithdrawAccountViewModel extends ViewModel
{
	public $viewFields = [
		'withdraw_account' => ['*'],
		'bank_name' => ['logo', '_on' => 'withdraw_account.bank_id=bank_name.id'],
	];
}