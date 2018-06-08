<?php

require_once( "functions.php" );

class dtpayNotify
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

    //验签
    public function verifySign($data)
    {
        //获取公钥
        $publicKey = $this->config[ 'public_key_path' ];

        //对待验签参数数组排序
        $filtedData = paraFilter( $data );
        $filtedData = argSort( $filtedData );
        //将数组拼成字符串
        $string = createLinkstring( $filtedData );

        return rsaVerify( $string, $publicKey, urldecode( $data[ 'sign' ] ) );
    }
}