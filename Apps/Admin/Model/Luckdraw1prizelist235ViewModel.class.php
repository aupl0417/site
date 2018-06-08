<?php
/**
* 此文件为自动生成
*/
namespace Admin\Model;
use Think\Model\ViewModel;
class Luckdraw1prizelist235ViewModel extends ViewModel {
    public $viewFields = array(
'luckdraw1_prize_list' => ['*'],
'luckdraw1_prize_category' => ['name', '_on' => 'luckdraw1_prize_category.id = luckdraw1_prize_list.type_id']
    );
}
?>