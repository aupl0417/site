<?php
/* *
 * 配置文件
 * 版本：1.0.0
 * 修改日期：2016-12-8
 * 说明：
 * 以下代码只是为了方便商户测试而提供的样例代码，商户可以根据自己网站的需要，按照技术文档编写,并非一定要使用该代码。
 * 该代码仅供学习和研究大唐支付接口使用，只是提供一个参考。

 * 安全校验码查看时，输入支付密码后，页面呈灰色的现象，怎么办？
 * 解决方法：
 * 1、检查浏览器配置，不让浏览器做弹框屏蔽设置
 * 2、更换浏览器或电脑，重新登录查询。
 */
return array(
    //渠道号，16位
    'channelID'           => C('tangpay_channelID'),
    //商户的私钥
    'private_key_path'    => VENDOR_PATH . 'cashierSDK/key/rsa_private_key.pem',
    //大唐支付的公钥
    'public_key_path' => VENDOR_PATH . 'cashierSDK/key/rsa_public_key.pem',
    //签名方式：RSA/MD5/DES
    'sign_type'           => 'RSA',
    //MD5签名方式的盐值
    'salt'                => 'QE151SD1A1Q5W1E6565QE511A13A5W1A',
    //字符编码格式 目前支持 gbk 或 utf-8
    'input_charset'       => strtolower( 'utf-8' ),
    //访问模式,根据自己的服务器是否支持ssl访问，若支持请选择https；若不支持请选择http
    'transport'           => $_SERVER['HTTPS']=='on'?'https':'http',
    // 支付类型：pc/web/wap/app
    'payment_from'        => 'pc',

    'Single' => array(
        //单订单 提交地址
        'submit_gateway'  => $conf['tangpay_url_single'],
        //'submit_gateway' => 'http://cashier.dterptest.com/order/Single',
        // 单订单 异步通知 路径。需外网可访问
        'notify_url'     => DM('cart', '/tangpay/notifyUrl'),     //同步通知地址
        // 单订单 同步返回 路径（外面自定义）
        //'return_url'     => DM('cart', '/tangpay/returnUrl'),     //同步通知地址
    ),
    'Multi'  => array(
        'submit_gateway'  => $conf['tangpay_url_multi'],
        //组合订单 提交地址
        //'submit_gateway' => 'http://cashier.dterptest.com/order/Multiple',
        //组合订单 异步通知地址
        'notify_url'     => DM('cart', '/tangpay/notifyUrl'),     //同步通知地址
        //组合订单同步返回地址(外面自定义)
        //'return_url'     => DM('cart', '/tangpay/returnUrl'),     //同步通知地址
    ),
);
