<?php
/**
+----------------------------------------------------------------------
| 快递鸟电子面单生成接口
+----------------------------------------------------------------------
| Author: lazycat <673090083@qq.com>
+----------------------------------------------------------------------
| 2016-10-14
+----------------------------------------------------------------------
 */
namespace Kdniao;

class ExpressBill{
    protected $config = array(  //接口信息
        'appid'         => 1266900, //商户ID
        'appkey'        => 'ec8c5dbb-c67d-48bd-a149-661d0eb83dd4',              //KEY
        'apiurl'        => 'http://api.kdniao.cc/api/EOrderService',            //正式接口
        'apiurl_test'   => 'http://testapi.kdniao.cc:8081/api/Eorderservice',   //测试接口
        'is_test'       => 1,
    );

    /**
     * 架构函数
     * @access public
     * @param string $this->str  数据
     */
    public function __construct($config=null) {
        if(!is_null($config)) $this->config = $config;

        //使用测试地址
        if($this->config['is_test'] == 1) $this->config['apiurl'] = $this->config['apiurl_test'];
    }

    /**
     * 生成电子面单用于打印
     * @param array $eorder 快递单信息
     * @param array $sender 发货人信息
     * @param array $receiver 收货人信息
     * @param array $commodity 附加信息
     */
    public function create_express_bill($eorder,$sender,$receiver,$commodity){
        //构造电子面单提交信息
        /*
        $eorder = [];
        $eorder["ShipperCode"] = "SF";
        $eorder["OrderCode"] = "PM201604062341";
        $eorder["PayType"] = 1;
        $eorder["ExpType"] = 1;

        $sender = [];
        $sender["Name"] = "李先生";
        $sender["Mobile"] = "18888888888";
        $sender["ProvinceName"] = "李先生";
        $sender["CityName"] = "深圳市";
        $sender["ExpAreaName"] = "福田区";
        $sender["Address"] = "赛格广场5401AB";

        $receiver = [];
        $receiver["Name"] = "李先生";
        $receiver["Mobile"] = "18888888888";
        $receiver["ProvinceName"] = "李先生";
        $receiver["CityName"] = "深圳市";
        $receiver["ExpAreaName"] = "福田区";
        $receiver["Address"] = "赛格广场5401AB";

        $commodityOne = [];
        $commodityOne["GoodsName"] = "其他";
        $commodity = [];
        $commodity[] = $commodityOne;

        $eorder["Sender"] = $sender;
        $eorder["Receiver"] = $receiver;
        $eorder["Commodity"] = $commodity;

        $eorder['IsReturnPrintTemplate']=1;
        */

        $eorder["Sender"]       = $sender;
        $eorder["Receiver"]     = $receiver;
        $eorder["Commodity"]    = $commodity;
        $eorder['IsReturnPrintTemplate']=1;
        $eorder['IsNotice']     = $eorder['IsNotice'] ? $eorder['IsNotice'] : 1; //0通知快递上门收件，1不通知
        //$eorder['DataType']     = 2;    //DataType：1-XML,2-Json

        //dump($eorder);

        //调用电子面单
        $jsonParam = json_encode($eorder, JSON_UNESCAPED_UNICODE);
        //dump($jsonParam);
        $jsonResult = $this->submitEOrder($jsonParam);

        //解析电子面单返回结果
        $result = json_decode($jsonResult, true);
        //dump($result);

        return $result;


    }


    /**
     * Json方式 查询订单物流轨迹
     */
    protected function submitEOrder($requestData){
        $datas = array(
            'EBusinessID' => $this->config['appid'],
            'RequestType' => '1007',
            'RequestData' => urlencode($requestData) ,
            'DataType' => '2',
        );
        $datas['DataSign'] = $this->encrypt($requestData, $this->config['appkey']);
        $result=$this->sendPost($this->config['apiurl'], $datas);

        //根据公司业务处理返回的信息......

        return $result;
    }


    /**
     *  post提交数据
     * @param  string $url 请求Url
     * @param  array $datas 提交的数据
     * @return url响应返回的html
     */
    protected function sendPost($url, $datas) {
        $temps = array();
        foreach ($datas as $key => $value) {
            $temps[] = sprintf('%s=%s', $key, $value);
        }
        $post_data = implode('&', $temps);
        $url_info = parse_url($url);
        if($url_info['port']=='')
        {
            $url_info['port']=80;
        }
        //echo $url_info['port'];
        $httpheader = "POST " . $url_info['path'] . " HTTP/1.0\r\n";
        $httpheader.= "Host:" . $url_info['host'] . "\r\n";
        $httpheader.= "Content-Type:application/x-www-form-urlencoded\r\n";
        $httpheader.= "Content-Length:" . strlen($post_data) . "\r\n";
        $httpheader.= "Connection:close\r\n\r\n";
        $httpheader.= $post_data;
        $fd = fsockopen($url_info['host'], $url_info['port']);
        fwrite($fd, $httpheader);
        $gets = "";
        $headerFlag = true;
        while (!feof($fd)) {
            if (($header = @fgets($fd)) && ($header == "\r\n" || $header == "\n")) {
                break;
            }
        }
        while (!feof($fd)) {
            $gets.= fread($fd, 128);
        }
        fclose($fd);

        return $gets;
    }

    /**
     * 电商Sign签名生成
     * @param data 内容
     * @param appkey Appkey
     * @return DataSign签名
     */
    protected function encrypt($data, $appkey) {
        return urlencode(base64_encode(md5($data.$appkey)));
    }


}
