<?php
namespace Common\Builder;
class Pays {
    //1=余额,2=唐宝,3=支付宝,4=微信
    static public $_type    =   [
        1   =>  '使用余额支付',
        2   =>  '使用唐宝支付',
        3   =>  '使用支付宝支付',
        4   =>  '使用微信支付',
    ];
    
    static public function type () {
        
    }
}