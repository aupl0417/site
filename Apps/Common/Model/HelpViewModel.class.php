<?php
namespace Common\Model;
use Think\Model\ViewModel;

class HelpViewModel extends ViewModel
{

	public $viewFields = [
		'help' => ['id', 'atime', 'hit', 'name', 'category_id', 'status', 'content'],
		'help_category' => ['category_name', '_on' => 'help.category_id = help_category.id'],
	];


}