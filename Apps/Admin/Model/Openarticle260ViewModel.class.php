<?php
/**
* 此文件为自动生成
*/
namespace Admin\Model;
use Think\Model\ViewModel;
class Openarticle260ViewModel extends ViewModel {
    public $viewFields = array(
'open_article' => ['*'],
'open_article_category' => ['name', '_on' => 'open_article.cid = open_article_category.id']
    );
}
?>