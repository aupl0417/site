<?php
/**
 * Created by PhpStorm.
 * User: dttx
 * Date: 2017/4/28
 * Time: 14:07
 */

namespace Admin\Model;


use Think\Model\ViewModel;

class Luckdraw1PrizeViewModel extends ViewModel
{
    protected $tableName = 'luckdraw1_prize';

    protected $viewFields= [
        'luckdraw1_prize'       =>  ['*'],
        //'luckdraw1_prize_list'  =>  ['images', '_on' => 'luckdraw1_prize_list.id = luckdraw1_prize.prize_id']
    ];
}