<?php
/* *
 * 功能：大唐支付服务器异步通知页面
 * 版本：1.0
 * 日期：2016-12-13


 *************************页面功能说明*************************
 * 创建该页面文件时，请留心该页面文件中无任何HTML代码及空格。
 * 该页面不能在本机电脑测试，请到服务器上做测试。请确保外部可以访问该页面。
 * 该页面调试工具请使用写文本函数logResult，该函数已被默认关闭，见dtpay_notify_class.php中的函数verifyNotify
 * 如果没有收到该页面返回的 success 信息，大唐支付会在24小时内按一定的时间策略重发通知
 */

require_once( "lib/notify.class.php" );

$dtpayNotify = new dtpayNotify();
$post = json_decode( file_get_contents( './notify.txt' ), TRUE );

if( $dtpayNotify->verifySign( $post ) )
{
    echo '验签成功<br/>更新本地订单......';
} else
{
    echo '验签失败..';
}