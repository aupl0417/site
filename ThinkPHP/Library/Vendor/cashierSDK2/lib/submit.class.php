<?php
/* *
 * 类名：dtpaySubmit
 * 功能：大唐支付各接口请求提交类
 * 详细：构造大唐支付各接口表单HTML文本，获取远程HTTP数据
 * 版本：1.0
 * 日期：2016-12-13
 * 说明：
 * 以下代码只是为了方便商户测试而提供的样例代码，商户可以根据自己网站的需要，按照技术文档编写,并非一定要使用该代码。
 * 该代码仅供学习和研究大唐支付接口使用，只是提供一个参考。
 */
require_once( "functions.php" );

class dtpaySubmit
{
    protected $config = NULL;

    protected $mode = NULL;

    function __construct($mode = 'Single',$config=null)
    {
        $this->mode = $mode;
		if(!is_null($config) && !empty($config)) $this->config = $config;
        else $this->config = C('DTPAY');
    }

    function dtpaySubmit($mode = 'Single')
    {
        $this->mode = $mode;
        //$this->config = C('DTPAY');
    }

    /**
     * 生成签名结果
     *
     * @param $para_sort array 已排序要签名的数组
     *
     * @return string 签名结果字符串
     */
    function buildRequestMysign($para_sort)
    {
        //把数组所有元素，按照“参数=参数值”的模式用“&”字符拼接成字符串
        $prestr = createLinkstring( $para_sort );

        $mysign = NULL;
        switch(strtoupper( trim( $this->config[ 'sign_type' ] ) ))
        {
            case "RSA" :
                $mysign = rsaSign( $prestr, $this->config[ 'private_key_path' ] );
                break;
            default :
                $mysign = "";
        }

        return $mysign;
    }

    /**
     * 生成要请求给大唐支付的参数数组
     *
     * @param $para_temp array 请求前的参数数组
     *
     * @return array 要请求的参数数组
     */
    function buildRequestPara($para_temp)
    {
        //除去待签名参数数组中的空值和签名参数
        $para_temp[ 'disabledPay' ] = $this->config[ 'disabledPay' ];//不支持的支付方式
        $para_temp[ 'timestamp' ] = time();//商户服务器当前时间戳
        $para_filter = paraFilter( $para_temp );

        //对待签名参数数组排序
        $para_sort = argSort( $para_filter );

        //生成签名结果
        $mysign = $this->buildRequestMysign( $para_sort );

        //签名结果与签名方式加入请求提交参数组中
        $para_sort[ 'sign' ] = urlencode( $mysign );
        $para_sort[ 'sign_type' ] = strtoupper( trim( $this->config[ 'sign_type' ] ) );

        return $para_sort;
    }

    /**
     * 生成要请求给大唐支付的参数数组
     *
     * @param $para_temp array 请求前的参数数组
     *
     * @return string 要请求的参数数组字符串
     */
    function buildRequestParaToString($para_temp)
    {
        //待请求参数数组
        $para = $this->buildRequestPara( $para_temp );

        //把参数组中所有元素，按照“参数=参数值”的模式用“&”字符拼接成字符串，并对字符串做urlencode编码
        $request_data = createLinkstringUrlencode( $para );

        return $request_data;
    }

    /**
     * 建立请求，以表单HTML形式构造（默认）
     *
     * @param $para_temp   array 请求参数数组
     *
     * @return string 提交表单HTML文本
     */
    function buildRequestForm($para_temp)
    {
        //待请求参数数组
        $para = $this->buildRequestPara( $para_temp );
        $sHtml = "<form action='{$this->config[$this->mode]['submit_gateway']}' method='post'>";
        while(list ( $key, $val ) = each( $para ))
        {
            $sHtml .= "<input type='hidden' name='" . $key . "' value='" . $val . "'/>";
        }

        //submit按钮控件请不要含有name属性
        $sHtml .= '</form>';

        $sHtml .= "<script>document.forms[0].submit();</script>";

        return $sHtml;
    }
}