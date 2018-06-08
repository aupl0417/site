<?php
namespace Seller\Model;
use Think\Model\ViewModel;
class ShopJoinViewModel extends ViewModel {
   protected $tableName     =   'shop_join_contact';
            
   public $viewFields       =   [
        'shop_join_contact' =>  ['*','_type'=>'LEFT'],
        'shop_join_bank'    =>  ['bank_id', 'bank_license', 'bank_name', 'bank_account', 'bank_no', 'province', 'city', 'district', 'town', 'tax_expire', 'tax_cert', '_on' => 'shop_join_contact.uid = shop_join_bank.uid','_type'=>'LEFT'],
    ];
}