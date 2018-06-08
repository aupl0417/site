<?php
/**
* 此文件为自动生成
*/
namespace Admin\Model;
use Think\Model\ViewModel;
class Shopauthaccountnumrepl215ViewModel extends ViewModel {
    public $viewFields = array(
'shop_auth_account_num_reply' => ['*'],
'user' => ['nick', '_on' => 'shop_auth_account_num_reply.uid=user.id'],
'shop'=>['shop_name', '_on' => 'shop_auth_account_num_reply.shop_id = shop.id'],
    );
}
?>