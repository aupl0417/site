<?php
/**
 * Created by PhpStorm.
 * User: dttx
 * Date: 2017/2/17
 * Time: 17:05
 */

namespace Common\Model;


use Think\Model\ViewModel;

class FavShopViewModel extends ViewModel
{
    protected $tableName = 'shop_fav';

    public $viewFields = [
        'shop_fav'  =>  ['*', '_type' => 'LEFT'],
        'shop'      =>  ['uid' => 'seller_id','shop_name','shop_logo','qq','mobile','wang','about','domain','inventory_type', '_on' => 'shop.id = shop_fav.shop_id', '_type' => 'LEFT'],
        'user'      =>  ['nick', '_on' => 'shop.uid = user.id']
    ];
}