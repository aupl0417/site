<?php
/**
 * Created by PhpStorm.
 * User: dttx
 * Date: 2017/4/21
 * Time: 16:50
 */

namespace Seller\Model;


use Think\Model\ViewModel;

class Luckdraw1ApplyViewModel extends ViewModel
{
    protected $tableName = 'luckdraw1_apply';
    protected $viewFields= [
        'luckdraw1_apply'   => ['*'],
        'luckdraw1'         => ['coupon_condition', 'start_time', 'end_time', 'rule', 'cid', 'images', 'luckdraw_name', '_on' => 'luckdraw1.id = luckdraw1_apply.luckdraw_id'],
        //'luckdraw1_category'    =>  ['name', '_on' => 'luckdraw1_category.id = luckdraw1.cid'],
    ];
}