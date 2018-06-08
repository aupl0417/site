<?php
namespace Common\Model;
use Think\Model\ViewModel;
class ShopViewModel extends ViewModel {
    protected $tableName    =   'shop';
    public $viewFields      =   [
        'shop'          =>  ['*'],
        'user'          =>  ['openid', '_on' => 'shop.uid = user.id'],
    ];
}