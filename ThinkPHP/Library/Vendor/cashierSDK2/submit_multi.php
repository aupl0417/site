<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>多订单提交跳转页 - 大唐收银台SKD</title>
</head>
<body>
<?php
/* *
 * 功能：即时到账交易接口接入页
 * 版本：3.4
 * 修改日期：2016-03*08
 * 说明：
 * 以下代码只是为了方便商户测试而提供的样例代码，商户可以根据自己网站的需要，按照技术文档编写,并非一定要使用该代码。
 * 该代码仅供学习和研究支付宝接口使用，只是提供一个参考。

 *************************注意*****************
 
 *如果您在接口集成过程中遇到问题，可以按照下面的途径来解决
 *1、开发文档中心（https://doc.open.alipay.com/doc2/detail.htm?spm=a219a.7629140.0.0.KvddfJ&treeId=62&articleId=103740&docType=1）
 *2、商户帮助中心（https://cshall.alipay.com/enterprise/help_detail.htm?help_id=473888）
 *3、支持中心（https://support.open.alipay.com/alipay/support/index.htm）

 *如果想使用扩展功能,请按文档要求,自行添加到parameter数组即可。
 **********************************************
 */
$orders = $_POST[ 'orders' ];

$multiOrder = array(
    //业务类型：“余额业务ID,唐宝业务ID”
    'busID'       => '1020201,4020201',
    //来源渠道
    'channelID'   => 'C000000000000005',
    //买家ID
    'buyerID'     => '8fb700bae03896e49c030cafbb95a43c',
    //组合订单号
    'gOrderID'    => str_repeat( time(), 2 ) . '00000',
    //TODO:订单数量
    'orderNum'    => count( $orders ),
    //TODO:组合订单数据
    'orders'      => json_encode( $orders ),
    //不允许用户使用的支付方式：余额Money，唐宝Tangbao，第三方支付Alipay,Wingpay,Wxpay,Unionpay。多个值用英文逗号组合 Money,Tangbao,Wxpay。区分大小写。可为空
    'disabledPay' => '',
    //TODO:所有分订单的 代购手续费 之和。以“分”为单位。没有则传0
    'buyAgentFee' => array_sum( array_column( $orders, 'buyAgentFee' ) ),
    //TODO:所有分订单的 订单金额 之和。人民币，以分为单位
    'orderAmount' => array_sum( array_column( $orders, 'orderAmount' ) ),
    'giveScore'   => array_sum( array_column( $orders, 'giveScore' ) ),
    'payChannel'  => '',
    'goodsName'   => '组合支付大礼包',
    'remark'      => 'haha',
);

require_once( "lib/submit.class.php" );

$dtpaySubmit = new dtpaySubmit( 'Multi' );
echo $dtpaySubmit->buildRequestForm( $multiOrder );
?>
</body>
</html>