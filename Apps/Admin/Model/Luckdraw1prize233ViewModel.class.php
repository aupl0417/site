<?php
/**
* 此文件为自动生成
*/
namespace Admin\Model;
use Think\Model\ViewModel;
class Luckdraw1prize233ViewModel extends ViewModel {
    public $viewFields = array(
'luckdraw1_prize' => ['*'],
'luckdraw1' => ['luckdraw_name', 'game_images', '_on' => 'luckdraw1_prize.luckdraw_id = luckdraw1.id'],
'luckdraw1_prize_category' => ['name', '_on' => 'luckdraw1_prize_category.id = luckdraw1_prize.type_id']
    );
}
?>