<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>单个订单支付提交 - 大唐收银台SKD</title>
</head>

<style>
    html, body {
        width: 100%;
        min-width: 1200px;
        height: auto;
        padding: 0;
        margin: 0;
        font-family: "微软雅黑";
        background-color: #242736
    }

    .guanzhuqr img {
        margin-top: 10px;
        width: 80px
    }

    .navbar li {
        float: left;
        width: 100px;
        height: 40px
    }

    .navbar li a {
        display: inline-block;
        width: 100px;
        height: 40px;
        line-height: 40px;
        font-size: 16px;
        color: #1a1a1a;
        text-decoration: none;
        text-align: center
    }

    .navbar li a:hover {
        color: #00AAEE
    }

    .content {
        width: 100%;
        min-width: 1200px;
        min-height: 1000px;
        background-color: #fff;
    }

    .dtpayform {
        width: 800px;
        margin: 0 auto;
        border: 1px solid #0ae
    }

    .element {
        width: 600px;
        height: 60px;
        margin-left: 100px;
        font-size: 20px
    }

    .etitle, .einput {
        float: left;
        height: 26px
    }

    .etitle {
        width: 150px;
        line-height: 26px;
        text-align: right
    }

    .einput {
        margin-left: 20px
    }

    .einput input {
        width: 398px;
        height: 24px;
        border: 1px solid #0ae;
        font-size: 16px
    }

    .mark {
        margin-top: 10px;
        width: 500px;
        height: 30px;
        margin-left: 80px;
        line-height: 30px;
        font-size: 12px;
        color: #999
    }

    .legend {
        margin-left: 100px;
        font-size: 24px
    }

    .alisubmit {
        width: 400px;
        height: 40px;
        border: 0;
        background-color: #0ae;
        font-size: 16px;
        color: #FFF;
        cursor: pointer;
        margin-left: 170px
    }

    .footer-sub a, span {
        color: #808080;
        font-size: 12px;
        text-decoration: none
    }

    .footer-sub a:hover {
        color: #00aeee
    }

    .footer-sub span {
        margin: 0 3px
    }
</style>
<body>
<?php $time = time(); ?>
<div class="content">
    <form action="submit_single.php" class="dtpayform" method="post" target="_blank">
        <div class="element" style="margin-top:10px;">
            <div class="legend">大唐收银台SKD</div>
        </div>
        <div class="element">
            <div class="etitle">渠道编号:</div>
            <div class="einput"><input type="text" name="channelID" id="channelID" value="C000000000000005"></div>
            <div class="mark">注意：商户订单号必填(建议是英文字母和数字,不能含有特殊字符)，长度32位</div>
        </div>
        <div class="element">
            <div class="etitle">结算模式:</div>
            <div class="einput"><input type="text" name="settleMode" id="settleMode" value="1"></div>
            <div class="mark">注意：商户订单号必填(建议是英文字母和数字,不能含有特殊字符)，长度32位</div>
        </div>
        <div class="element">
            <div class="etitle">商户订单号:</div>
            <div class="einput"><input type="text" name="merOrderID" id="out_trade_no" value="<?php echo str_repeat( $time, 2 ) ?>00000"/></div>

            <div class="mark">注意：商户订单号必填(建议是英文字母和数字,不能含有特殊字符)，长度25位</div>
        </div>

        <div class="element">
            <div class="etitle">商品名称:</div>
            <div class="einput"><input type="text" name="goodsName" value="商品编号：<?php echo $time; ?>"></div>
            <div class="mark">注意：产品名称(subject)，必填(建议中文，英文，数字，不能含有特殊字符)</div>
        </div>
        <div class="element">
            <div class="etitle">代购手续费:</div>
            <div class="einput"><input type="text" name="buyAgentFee" value="1<?php echo substr( $time, -1 ); ?>"></div>
            <div class="mark">注意：代购手续费，必填，整数，以分为单位</div>
        </div>
        <div class="element">
            <div class="etitle">商品金额:</div>
            <div class="einput"><input type="text" name="orderAmount" value="1<?php echo substr( $time, -2 ); ?>"></div>
            <div class="mark">注意：付款金额(total_fee)，必填，整数，以分为单位</div>
        </div>
        <div class="element">
            <div class="etitle">支付方式:</div>
            <div class="einput"><input type="text" name="payChannel" value=""/></div>
            <div class="mark">可以指定支付方式。可为空</div>
        </div>
        <div class="element">
            <div class="etitle">赠送积分:</div>
            <div class="einput"><input type="text" name="giveScore" value="<?php echo rand(10,1000);?>"/></div>
            <div class="mark">可以指定支付方式。可为空</div>
        </div>
        <div class="element">
            <div class="etitle">是否自动收货:</div>
            <div class="einput"><input type="text" name="autoRecieve" value="<?php echo $time % 2; ?>"></div>
            <div class="mark">虚拟商品，无需物流，支付成功即自动收货</div>
        </div>
        <div class="element">
            <div class="etitle">业务ID:</div>
            <div class="einput"><input type="text" name="busID" value="1020201,4020201"></div>
        </div>
        <div class="element">
            <div class="etitle">收款者ID:</div>
            <div class="einput"><input type="text" name="recieverID" value="1c99abc8463ca0c14910a55ed1daed16"></div>
        </div>
        <div class="element">
            <div class="etitle">买家ID:</div>
            <div class="einput"><input type="text" name="buyerID" value="8fb700bae03896e49c030cafbb95a43c"></div>
        </div>
		<div class="element">
            <div class="etitle">买家昵称:</div>
            <div class="einput"><input type="text" name="buyerNick" value="qiye02"></div>
        </div>
        <div class="element">
            <div class="etitle">商品地址:</div>
            <div class="einput"><input type="text" name="goodsUrl" value="http://cashier.erp.com"></div>
        </div>
        <div class="element">
            <div class="etitle">备注:</div>
            <div class="einput"><input type="text" name="remark" value="<?php echo $time; ?>"></div>
        </div>
        <div class="element">
            <input type="submit" class="alisubmit" value="确认支付">
        </div>
    </form>
</div>
</body>
</html>