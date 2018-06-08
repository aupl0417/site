<?php
/**
 * --------------------------------------------
 * 大唐支付配置文件
 * --------------------------------------------
 * Create by Lazycat <673090083@qq.com>
 * --------------------------------------------
 * 2017-01-04
 * --------------------------------------------
 */

return array(
    'DTPAY_TEST'    => true,    //支付启用测试通道,false为使用正式环境
    'busID'         => '10205,40205',
    'DTPAY_CONFIG'     => array(
        //渠道号，16位
        'channelID'             => 'C000000000000005',
        //商户的私钥
        'private_key_path'      => VENDOR_PATH . 'cashierSDK2/key/rsa_private_key.pem',
        //大唐支付的公钥
        'public_key_path'       => VENDOR_PATH . 'cashierSDK2/key/rsa_public_key.pem',
        //签名方式：RSA/MD5/DES
        'sign_type'             => 'RSA',
        //MD5签名方式的盐值
        'salt'                  => 'QE151SD1A1Q5W1E6565QE511A13A5W1A',
        //字符编码格式 目前支持 gbk 或 utf-8
        'input_charset'         => strtolower( 'utf-8' ),
        //访问模式,根据自己的服务器是否支持ssl访问，若支持请选择https；若不支持请选择http
        'transport'             => $_SERVER['HTTPS']=='on'?'https':'http',
        // 支付类型：pc/web/wap/app
        'payment_from'          => 'pc',
    ),
    'DTPAY_SINGLE'  => array(   //单订单 提交地址
        'TEST'      => 'http://cashier.dterptest.com/submit/Single',
        'ONLINE'    => 'https://cashier.dttx.com/submit/Single',
    ),
    'DTPAY_MULTI'  => array(    //合并订单提交地址
        'TEST'      => 'http://cashier.dterptest.com/submit/Multiple',
        'ONLINE'    => 'https://cashier.dttx.com/submit/Multiple',
    ),
);