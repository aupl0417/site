<?php
namespace Seller\Model;
use Think\Model\ViewModel;
class ShopJoinBrandViewModel extends ViewModel {
    protected $tableName    =   'shop_join_brand';
    protected $viewFields   =   [
        'shop_join_brand'   =>  ['id', 'b_name', 'uid', '_type' => 'LEFT'],
        'shop_join_cert'    =>  ['id' => 'c_id', 'license_images', 'reg_people', 'reg_no', 'reg_type', 'is_import', '_on' => 'shop_join_brand.uid = shop_join_cert.uid AND shop_join_brand.id = shop_join_cert.brand_id', '_type' => 'RIGHT']
    ];
}