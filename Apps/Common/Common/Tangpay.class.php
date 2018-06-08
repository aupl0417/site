<?php

/**
 * Created by PhpStorm.
 * User: dttx
 * Date: 2016/12/27
 * Time: 10:02
 */
namespace Common\Common;
class Tangpay
{
    const PAY_TYPE      =   [
        'Money'     =>  1,  //余额支付
        'Tangbao'   =>  2,  //唐宝支付
        'Alipay'    =>  3,  //支付宝支付
        'Wxpay'     =>  4,  //微信支付
        'Allinpay'  =>  5,  //通联支付
        'Unionpay'  =>  6,  //银联在线
        'Unionpay2' =>  7,  //网银支付
        'Wingpay'   =>  8,  //微赢支付
        'AllinWxpay'=>  9,  //通联微信支付
        'AllinAlipay'   =>  10, //通联支付宝支付
        'AllinQuickpay' =>  11, //通联快捷支付
        'AllinBankpay'  =>  12, //通联网关支付
        'WingAlipay'    =>  13, //微赢支付宝支付
        'WingWxpay'     =>  14, //微赢微信支付
    ];
}