<?php
namespace Seller\Model;
use Think\Model;
class ShopJoinBankModel extends Model {
    protected $tableName    =   'shop_join_bank';
    protected $_validate    =   [
        ['bank_id', 'require', '开户银行不能为空', 1],
        ['bank_account', 'require', '开户银行账户不能为空', 1],
        ['bank_license', 'require', '银行开户许可证不能为空', 1],
        ['bank_name', 'require', '开户名不能为空', 1],
        ['bank_no', 'require', '支行联号不能为空', 1],
        ['province', 'require', '开户银行所在省份不能为空', 1],
        ['city', 'require', '开户银行所在城市不能为空', 1],
        ['district', 'require', '开户银行所在地区不能为空', 1],
//         ['tax_cert', 'require', '纳税资格证不能为空', 1],
    ];
    
    protected $_auto        =   [
        ['uid', 'getUid', 1, 'function'],
    ];
}