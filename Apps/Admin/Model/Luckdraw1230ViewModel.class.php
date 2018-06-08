<?php
/**
* 此文件为自动生成
*/
namespace Admin\Model;
use Think\Model\ViewModel;
class Luckdraw1230ViewModel extends ViewModel {
    public $viewFields = array(
'luckdraw1' => ['*'],
'luckdraw1_category' => ['name', '_on' => 'luckdraw1_category.id = luckdraw1.cid']
    );
}
?>