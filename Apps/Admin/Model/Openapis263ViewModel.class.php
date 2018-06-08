<?php
/**
* 此文件为自动生成
*/
namespace Admin\Model;
use Think\Model\ViewModel;
class Openapis263ViewModel extends ViewModel {
    public $viewFields = array(
'open_apis' => ['*'],
'open_api_category' => ['name' => 'category_name', '_on' => 'open_apis.category_id = open_api_category.id']
    );
}
?>