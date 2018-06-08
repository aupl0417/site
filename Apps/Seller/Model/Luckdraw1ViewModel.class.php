<?php
/**
 * Created by PhpStorm.
 * User: dttx
 * Date: 2017/4/21
 * Time: 16:48
 */

namespace Seller\Model;


use Think\Model\ViewModel;

/**
 * 抽奖游戏视图模型
 *
 * Class Luckdraw1ViewModel
 * @package Seller\Model
 */

class Luckdraw1ViewModel extends ViewModel
{
    protected $tableName = 'luckdraw1';

    protected $viewFields= [
        'luckdraw1' =>  ['*'],
        //'luckdraw1_apply'       =>  ['uid', '_on' => 'luckdraw1_apply.luckdraw_id = luckdraw1.id', '_type' => 'LEFT'],
        'luckdraw1_category'    =>  ['name', '_on' => 'luckdraw1_category.id = luckdraw1.cid'],
    ];
}