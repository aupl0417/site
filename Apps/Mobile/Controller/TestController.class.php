<?php
/**
 * -------------------------------------------------
 * 测试
 * -------------------------------------------------
 * Create by Lazycat <673090083@qq.com>
 * -------------------------------------------------
 * 2017-03-02
 * -------------------------------------------------
 */
namespace Mobile\Controller;
use Think\Controller;
use Think\Exception;
use Vendor\Beanstalkd\Queue;
class TestController extends CommonController {
	public function test(){
        $res = $this->curl_get('https://cashier.dttx.com/poll/getPayGroupList');
        $res = json_decode($res,true);
        dump($res);

	}

	public function test2(){
        $data = [
            ['黑色','红色','蓝色', 'zise'],
            ['S','M','L'],
            ['棉','布','羊毛'],
        ];

        $count = count($data);
        $key = 1;
        while ($key < $count){
            $tmp = [];
            foreach($data[$key] as $val){
                foreach($data[$key-1] as $k => $v){
                    $tmp[] = $val.','.$v;
                }
            }
            $data[$key] = $tmp;
            //dump($tmp);exit;
            $key++;
        }

        dump($data[$count-1]);

        array (
            'openid' => 'ac31f44db28e74ebe356da6d71bd7c0d',
            'fraction_service' => '5',
            'fraction_speed' => '5',
            'is_anonymous' => '0',
            's_no' => '2017030817494372226623',
            'goods_rate' =>
                array (
                    0 =>
                        array (
                            'orders_goods_id' => '22424',
                            'fraction_desc' => '3',
                            'content' => '三星调来调，来得及发代理分离，酸辣粉了的流量费。的看法苦咖啡看看',
                            'images' => 'https://img.trj.cc/Fk-01yQZQl9-dBZwZS7bMSSgHfFu',
                        ),
                ),
            'token' => '3c3f027fceb2a3c7de212840f626948d',
            'sign' => '6b8bc2677386c0c51a93acf85b9470b7',
            'random' => 'h2krq3ospf0kfhaes16cn2hof1',
        );

        $tmp = array (
            0 =>
                array (
                    'orders_goods_id' => '22424',
                    'fraction_desc' => '3',
                    'content' => '三星调来调，来得及发代理分离，酸辣粉了的流量费。的看法苦咖啡看看',
                    'images' => 'https://img.trj.cc/Fk-01yQZQl9-dBZwZS7bMSSgHfFu',
                ),
        );
        $tmp = json_encode($tmp,JSON_UNESCAPED_UNICODE);
        dump($tmp);

    }

    public function test3(){
        $data['type']   = 'orders_confirm';
        $data['val']    = '2017032310190301832515';
        $res = $this->curl_post('http://rest.dtshop.com/CronV2/job',$data);
        dump($res);
    }


    public function test4(){
        //C('DEBUG_API',true);
        $data['openid'] = 'ac31f44db28e74ebe356da6d71bd7c0d';
        $data['s_no'] = '2017033111211209443164';
        $res = $this->doApi2('/BuyerRefund/can_refund_goods',$data);
        dump($res);
    }

    public function test5(){
        $data['s_no'] = '2017032416131315477059';
        $res = $this->doApi2('/Erp/orders_in_erp_status',$data);
        dump($res);
    }

    public function test6(){
        C('DEBUG_API',true);
        $data['day'] = '2017-04-08';
        $data['time'] = '08:00';
        //$res = $this->doApi2('/Miaosha/auto_activity',$data);
        $res = $this->doApi2('/Miaosha/auto_activity',['day' => '2017-04-10']);




        dump($res);
    }

    public function test7(){
        C('DEBUG_API',true);
        $data['erp_uid'] = 'bd13a59e7068fe067dc32a56e0dbb67a';
        $res = $this->doApi2('/App/token',$data);
        dump($res);
    }

    public function test8(){
        C('DEBUG_API',true);
        $data['erp_uid'] = 'bd13a59e7068fe067dc32a56e0dbb67a';
        $res = $this->doApi2('/App/ad',$data);
        dump($res);
    }

    public function test9(){
        C('DEBUG_API',true);
        $data['erp_uid'] = 'bd13a59e7068fe067dc32a56e0dbb67a';
        $res = $this->doApi2('/App/version',[]);
        dump($res);
    }


    public function test10(){
        //C('DEBUG_API',true);
        $data['openid'] = 'f6e4b1ddb643ed6907b41adf71315e03';
        $data['o_no']       ='2017040521313161430755';
        $data['paytype']    =2;
        $res = $this->doApi2('/Cashier/create_multi_form',$data);
        echo $res['data'];
        //dump($res);
    }
    public function test11(){
        //C('DEBUG_API',true);
        $data['openid'] = 'f6e4b1ddb643ed6907b41adf71315e03';
        $data['s_no']       ='2017040522121462923847';
        $data['paytype']    =2;
        $res = $this->doApi2('/Cashier/create_single_form',$data);
        echo $res['data'];
        //dump($res);
    }

    public function test12(){
        //C('DEBUG_API',true);
        $data['notid']    ='1,2';
        $res = $this->doApi2('/Cashier/paytype',$data);
        dump($res);
    }

    public function test13(){
        //C('DEBUG_API',true);
        $data['openid'] = 'f6e4b1ddb643ed6907b41adf71315e03';
        $data['o_no']       ='2017040614324037962136';
        $data['paytype']    =3;
        $res = $this->doApi2('/Cashier/create_multi_url',$data);

        dump($res);
    }

    public function test14(){
        C('DEBUG_API',true);
        $data['s_no']   = '2017040522121462923847';
        $res = $this->doApi2('/Orders/paying',$data);

        dump($res);
    }

    public function csv_orders(){
        $file = file('./Apps/Mobile/error.csv');
        //dump($file);
        $html = '';
        foreach($file as $key => $val){
            $val = trim($val);
            if($key > 0){
                $val = explode(',',$val);
                $rs = M('orders_shop')->where(['s_no' => $val[1]])->field('score')->find();
                $val[5] = $rs['score'];
                $val[1] = (string) $val[1];
                $val= implode(',',$val);
            }

            $html .= $val.PHP_EOL;
        }

        echo $html;
    }

    public function test15(){
        $data['id'] = 3469;
        C('DEBUG_API',true);
        $res = $this->doApi2('/ToolsRate/check_rate_item',$data);

        dump($res);
    }

    public function rate_scan(){
        $map['status']      = 1;
        $map['rate']        = 1;
        $map['is_shuadan']  = 0;
        $shop_ids = [3864,160,161,166,243,1581,2600,288];   //不进行扫描的店铺
        $map['shop_id']     = ['not in',$shop_ids];

        $count = M('orders_goods_comment')->where($map)->count();
        $page = ceil($count/200);

        $p=I('get.p') ? I('get.p') : 1;
        $list =M('orders_goods_comment')->where($map)->page($p)->limit(200)->order('id asc')->getField('id',true);
        if(empty($list)) {
            echo '更新完成！';
            exit();
        }
        //C('DEBUG_API',true);
        foreach($list as $val){
            $res = $this->doApi2('/ToolsRate/check_rate_item',['id' => $val]);
            dump($res);
            usleep(1000);
        }

        usleep(1000);
        gourl('/Test/rate_scan/p/'.($p+1));
    }

    public function orders_scan(){
        $map['status']      = ['in','5,6'];
        //$map['is_shuadan']  = 0;
        $shop_ids = [3864,160,161,166,243,1581,2600,288];   //不进行扫描的店铺
        $map['shop_id']     = ['not in',$shop_ids];
        $map['atime']       = ['gt','2017-04-01 00:00:00'];

        $count = M('orders_shop')->where($map)->count();
        $page = ceil($count/200);

        $p=I('get.p') ? I('get.p') : 1;
        $list =M('orders_shop')->where($map)->page($p)->limit(200)->order('id desc')->getField('s_no',true);
        if(empty($list)) {
            echo '更新完成！';
            exit();
        }

        //dump($list);
        //C('DEBUG_API',true);
        foreach($list as $val){
            $res = $this->doApi2('/ToolsRate/check_orders_shuadan',['s_no' => $val]);
            dump($res);
            usleep(1000);
        }

        usleep(1000);
        gourl('/Test/orders_scan/p/'.($p+1));
    }

    public function rate_scan2(){
        $map['status']  = 1;
        $map['rate']    = ['neq',0];
        $map['is_shuadan']  = 0;
        $map['id']      = ['gt',132572];
        $count = M('orders_goods_comment')->where($map)->count();
        $page = ceil($count/200);

        $p=I('get.p') ? I('get.p') : 1;
        $list =M('orders_goods_comment')->where($map)->page($p)->limit(200)->order('id asc')->getField('id',true);
        if(empty($list)) {
            echo '更新完成！';
            exit();
        }

        //dump($list);
        C('DEBUG_API',true);
        foreach($list as $val){
            $res = $this->doApi2('/ToolsRate/rate_point',['id' => $val]);
            dump($res);
            usleep(1000);
        }

        usleep(1000);
        gourl('/Test/rate_scan2/p/'.($p+1));
    }

    public function shop_list(){
        $count = M('shop')->where($map)->count();
        $page = ceil($count/200);

        $p=I('get.p') ? I('get.p') : 1;
        $list =M('shop')->where($map)->page($p)->limit(200)->order('id asc')->getField('id',true);
        if(empty($list)) {
            echo '更新完成！';
            exit();
        }

        //dump($list);
        C('DEBUG_API',true);
        foreach($list as $val){
            $res = $this->doApi2('/ToolsRate/shop_point',['id' => $val]);
            dump($res);
            $res = $this->doApi2('/ToolsRate/shop_fraction',['id' => $val]);
            dump($res);

            //没有何评价
            if(M('orders_goods_comment')->where(['shop_id' => $val])->count() == 0){
                M('shop')->where(['id' => $val])->save(['fraction_speed' => 4.8,'fraction_service' => 4.8,'fraction_desc' => 4.8,'fraction' => 4.8]);
            }

            usleep(1000);
        }

        usleep(1000);
        gourl('/Test/shop_list/p/'.($p+1));
    }

    public function create_no($prefix='',$uid=''){
        $str = $prefix.session_id().microtime(true).uniqid(md5(microtime(true)),true);
        $str = md5($str);
        $prefix = $prefix.date('YmdHis').$uid;

        $code   = $prefix.substr(uniqid($str,true),-8,8);
        return $code;
    }

    public function create_test(){
        set_time_limit(0);
        for($i=0;$i=100000;$i++){
            echo $this->create_no('T').'<br>';
        }
    }

    public function test17(){
        C('DEBUG_API',true);
        $res = $this->doApi2('/App/ios_config',[]);

        dump($res);
    }

    public function test18(){
        C('DEBUG_API',true);
        $res = $this->curl_post('http://rest.dtshop.com/CronV2/job',['type' => 'orders_confirm','val' =>'DD201704121170535792958503']);

        dump($res);
    }

    public function test19(){
        C('DEBUG_API',true);
        $data['shop_id']    = 376;
        $data['goods_id']   = 19538;
        $res = $this->doApi2('/Coupon/get_batch',$data);

        //dump($res);
    }

    public function test20(){
        set_time_limit(0);
        $list = M('coupon')->where(['short_code' => ''])->field('id,code')->select();
        foreach($list as $val){
            M('coupon')->where(['id' => $val['id']])->setField('short_code',shortUrl($val['code']));
        }
    }

    public function test21(){
        C('DEBUG_API',true);
        $data['openid'] = 'f6e4b1ddb643ed6907b41adf71315e03';
        $data['s_no']  = 'MB201705111010491948216843';
        $res = $this->doApi2('/MobileRecharge/recharge',$data);


    }
    public function test22(){
        C('DEBUG_API',true);
        $data['openid'] = 'f6e4b1ddb643ed6907b41adf71315e03';
        $data['fare']  = '30M';
        $data['type']   = 1;
        $data['mobile'] = '13667383886';
        $data['recharge_type']  = 2;
        $res = $this->doApi2('/MobileRecharge/create_orders',$data);

        dump($res);
        echo $res['form']['data'];
    }

    public function test23(){
        C('DEBUG_API',true);
        $data['openid'] = 'f6e4b1ddb643ed6907b41adf71315e03';
        $data['s_no']   = 'MB201705111111045412720033';
        $data['paytype']    =2;
        $res = $this->doApi2('/MobileRecharge/create_single_form',$data);

        echo $res['form']['data'];
    }

    public function test24(){
        $str = '20170510162428';
        dump(date('Y-m-d H:i:s',strtotime($str)));
    }

    public function test25(){
        //C('DEBUG_API',true);
        $data['openid'] = 'f6e4b1ddb643ed6907b41adf71315e03';
        $res = $this->doApi2('/MobileRecharge/orders',$data);

        print_r($res);
    }

    public function test26(){
        C('DEBUG_API',true);
        $data['openid'] = 'f6e4b1ddb643ed6907b41adf71315e03';
        $data['s_no']   = 'MB201705101313554450968387';
        $res = $this->doApi2('/MobileRecharge/view',$data);

        print_r($res);
    }

    public function test27(){
        C('DEBUG_API',true);
        $data['openid'] = 'f6e4b1ddb643ed6907b41adf71315e03';
        $data['s_no']   = 'MB201705121616312276868251';
        $res = $this->doApi2('/MobileRecharge/refund_add',$data);

        print_r($res);
    }

    public function test28(){
        C('DEBUG_API',true);
        $data['openid'] = 'f6e4b1ddb643ed6907b41adf71315e03';
        $data['r_no']   = 'MT201705121010141042623833';
        $data['is_logs']    = 1;
        $res = $this->doApi2('/MobileRecharge/refund_view',$data);

        print_r($res);
    }

    public function test29(){
        C('DEBUG_API',true);
        $data['openid'] = 'f6e4b1ddb643ed6907b41adf71315e03';
        $data['r_no']   = 'MT201705121010141042623833';
        $res = $this->doApi2('/MobileRecharge/refund_cancel',$data);

        print_r($res);
    }

    public function test30(){
        C('DEBUG_API',true);
        $data['openid'] = 'f6e4b1ddb643ed6907b41adf71315e03';
        $data['r_no']   = 'MT201705121616354035077395';
        $data['pay_password']   = $this->password('123456');
        $res = $this->doApi2('/Erp/mobile_recharge_refund',$data);

        print_r($res);
    }

    public function test31(){
        C('DEBUG_API',true);
        $data['openid'] = 'f6e4b1ddb643ed6907b41adf71315e03';
        $data['s_no']   = 'MB201705111515234000232069';
        $res = $this->doApi2('/MobileRecharge/orders_close',$data);

        print_r($res);
    }

    public function test32(){
        C('DEBUG_API',true);
        $data['openid'] = 'f6e4b1ddb643ed6907b41adf71315e03';
        $data['s_no']   = 'MB201705121616305087574932';
        $data['pay_password']   = $this->password('123456');
        $res = $this->doApi2('/Erp/mobile_orders_confirm',$data);

        print_r($res);
    }

    public function test33(){
        try {
            throw new Exception("no code!!A");
            throw new Exception("no code!!B");
        } catch (Exception $e) {
            print("Code='" . $e->getMessage() . "'");
        }
    }

    public function test34(){
        C('DEBUG_API',true);
        $data = $this->api_cfg;
        $data['erp_uid']    = '33c2027991d666da0d8e5b83c0b7c6d9';
        $res = $this->doApi2('/Auth/token',$data);
        print_r($res);
    }

    public function test35(){
        C('DEBUG_API',true);
        $res = $this->doApi2('/MobileRecharge/fare_list',[]);
    }

    public function test36(){
        dump($_SERVER);
    }
}


