﻿<?php

/**
 * 同时提交多个订单 示例
 **/
class MultiOrder
{
    /**
     * 一组订单数据
     *
     * @return array
     */
    public function &getOrders()
    {
        $orders = [NULL, NULL, NULL];
        foreach($orders as $key => $val) $orders[ $key ] = $this->getSingleOrderTemplate();

        return $orders;
    }

    /**
     * 单个订单数据
     *
     * @return array
     */
    private function getSingleOrderTemplate()
    {
        $time = time();

        return array(
            //来源渠道
            'channelID'   => 'C000000000000005',
            //收款人userID
            'recieverID'  => '1c99abc8463ca0c14910a55ed1daed16',
            //买家ID
            'buyerID'     => '8fb700bae03896e49c030cafbb95a43c',
            //商家订单号
            'merOrderID'  => str_repeat( $time, 2 ) . rand( 10000, 99999 ),
            //结算模式：1扣库存积分，2扣货款
            'settleMode'  => ( rand( 0, 100 ) % 2 ) + 1,
            //代购手续费，以“分”为单位。没有则传0
            'buyAgentFee' => rand( 0, 10 ),
            //订单金额：人民币，以分为单位
            'orderAmount' => rand( 0, 100 ),
            //赠送积分
            'giveScore'   => rand( 0, 100 ),
            //商户指定付款方式
            'payChannel'  => '',
            //是否自动收货
            'autoRecieve' => ( rand( 0, 100 ) % 2 ),
            //商品url
            'goodsUrl'    => 'goodsUrl',
            //商品名称
            'goodsName'   => 'goodsName',
            //备注，可为空，原样返回
            'remark'      => 'remark',
            //---TODO:以下字段值可以传空 ---
            'disabledPay' => 'http://cashier.erp.com/app/cashierSDK/index2.php',
            //同步通知地址
            'returnUrl'   => 'http://cashier.erp.com/app/cashierSDK/index2.php',
            //异步通知地址
            'notifyUrl'   => 'http://cashier.erp.com/app/cashierSDK/index2.php',
            //业务类型：“余额业务ID,唐宝业务ID”
            'busID'       => '1020201,4020201',
        );
    }
}

?>
<html>
<head>
    <title>组合订单提交示例 - 大唐收银台SDK</title>
    <style type="text/css">
        body * {
            font-size: 1.3em;
        }

        input {
            margin: 2px 0;
        }

        span {
            display: inline-block;
            width: 250px;
            text-align: right;
        }
    </style>
</head>
<body>
<form action="submit_multi.php" method="post" target="_blank">
    <?php $orders = ( new MultiOrder() )->getOrders();
    $totalFee = $totalAmount = $totalScore = 0;
    $i = -1; ?>

    <?php foreach($orders as $key => $order): $i++; ?>
        <p>第 <?php $totalFee += $order[ 'buyAgentFee' ];
            $totalAmount += $order[ 'orderAmount' ];
            echo $key; ?> 个订单</p>
        <?php foreach($order as $field => $value): ?>
            <span><?php echo $field; ?>: </span><input type="text" name="orders[<?php echo $i; ?>][<?php echo $field; ?>]" value='<?php echo $value; ?>'/><br/>
        <?php endforeach; ?>
        <hr/>
    <?php endforeach; ?>
    <span>手续费总额：</span><?php echo $totalFee ?><br/>
    <span>商品总额：</span><?php echo $totalAmount; ?><br/>
    <span>支付总额：</span><?php echo $totalFee + $totalAmount; ?><br/>
    <input type="submit"/>
</form>
</body>
</html>