<?php
namespace Common\Model;
use Think\Model\ViewModel;


class NewsViewModel extends ViewModel
{

	public $viewFields = [
		'news' => ['id', 'atime', 'hit', 'name', 'category_id', 'status', 'content','is_top'],
		'news_category' => ['category_name', '_on' => 'news.category_id = news_category.id'],
	];

}