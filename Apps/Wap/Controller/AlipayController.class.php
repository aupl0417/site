<?php
namespace Wap\Controller;
use Think\Controller;
class AlipayController extends CommonController {
    private $alipay_config = array();
    public function _initialize(){
        parent::_initialize();
        $filepath = './ThinkPHP/Library/Vendor/AlipayWap/lib';
        import('alipay_core#function', $filepath, '.php');
        import('alipay_rsa#function', $filepath, '.php');
        import('alipay_md5#function', $filepath, '.php');
        import('alipay_notify#class', $filepath, '.php');
        import('alipay_submit#class', $filepath, '.php');

        $alipay_config= C('cfg.alipay');        

        $alipay_config['sign_type']             = 'MD5';
        $alipay_config['input_charset']         = 'utf-8';
        $alipay_config['cacert']                = getcwd().'\\cacert.pem';
        $alipay_config['transport']             = 'http';

        $this->alipay_config=$alipay_config;
    }

    /**
    * 支付宝充值
    */
    public function pay(){
    	if(empty($_SESSION['user']['id'])) redirect('/Index/index?url=/Login/index');

    	$rs=$do=M('orders_shop')->where(['s_no' => I('get.s_no'),'uid' => session('user.id')])->field('id,status,s_no,pay_price')->find();

    	if($rs['status']!=1) {
    		echo '订单已支付，不可再次付款！';
    		exit;
    	}

    	//创建充值订单
		$apiurl				='/Recharge/add';
		$data['openid']		=session('user.openid');
		$data['money']		=$rs['pay_price'];
		$data['s_no']		=$rs['s_no'];
		$data['paytype']	=1;

		$res=$this->doApi($apiurl,$data,'s_no');
		if($res->code!=1) {
			echo $res->msg;
			exit;
		}


        if($_SERVER['HTTPS']=='on') $scheme='https://';
        else $scheme='http://';

        //dump($rs);exit;

        /**************************调用授权接口alipay.wap.trade.create.direct获取授权码token**************************/
            
        //返回格式
        $format = "xml";
        //必填，不需要修改

        //返回格式
        $v = "2.0";
        //必填，不需要修改

        //请求号
        $req_id = date('Ymdhis');
        //必填，须保证每次请求都是唯一

        //**req_data详细信息**

        //服务器异步通知页面路径
        $notify_url = $scheme.$_SERVER['HTTP_HOST'].'/Alipay/notify_url';
        //需http://格式的完整路径，不允许加?id=123这类自定义参数

        //页面跳转同步通知页面路径
        $call_back_url = $scheme.$_SERVER['HTTP_HOST'].'/Alipay/return_url';
        //需http://格式的完整路径，不允许加?id=123这类自定义参数

        //操作中断返回地址
        $merchant_url = $scheme.$_SERVER['HTTP_HOST'].'/Index/index?url=/Orders/index';
        //用户付款中途退出返回商户的地址。需http://格式的完整路径，不允许加?id=123这类自定义参数

        //卖家支付宝帐户
        $seller_email = $this->alipay_config['seller_email'];
        //必填

        //商户订单号
        $out_trade_no = $res->data->r_no;
        //商户网站订单系统中唯一订单号，必填

        //订单名称
        $subject = '唐人街订购商品[订单号#'.$rs['s_no'].'][ERP异动号#'.$res->data->erp_no.']';
        //必填

        //付款金额
        $total_fee = $rs['pay_price'];
        //必填

        //请求业务参数详细
        $req_data = '<direct_trade_create_req><notify_url>' . $notify_url . '</notify_url><call_back_url>' . $call_back_url . '</call_back_url><seller_account_name>' . $seller_email . '</seller_account_name><out_trade_no>' . $out_trade_no . '</out_trade_no><subject>' . $subject . '</subject><total_fee>' . $total_fee . '</total_fee><merchant_url>' . $merchant_url . '</merchant_url></direct_trade_create_req>';
        //必填

        /************************************************************/

        //构造要请求的参数数组，无需改动
        $para_token = array(
                "service" => "alipay.wap.trade.create.direct",
                "partner" => trim($this->alipay_config['partner']),
                "sec_id" => trim($this->alipay_config['sign_type']),
                "format"    => $format,
                "v" => $v,
                "req_id"    => $req_id,
                "req_data"  => $req_data,
                "_input_charset"    => trim(strtolower($this->alipay_config['input_charset']))
        );

        //建立请求
        $alipaySubmit = new \AlipaySubmit($this->alipay_config);
        $html_text = $alipaySubmit->buildRequestHttp($para_token);

        //URLDECODE返回的信息
        $html_text = urldecode($html_text);

        //解析远程模拟提交后返回的信息
        $para_html_text = $alipaySubmit->parseResponse($html_text);

        //获取request_token
        $request_token = $para_html_text['request_token'];


        /**************************根据授权码token调用交易接口alipay.wap.auth.authAndExecute**************************/

        //业务详细
        $req_data = '<auth_and_execute_req><request_token>' . $request_token . '</request_token></auth_and_execute_req>';
        //必填

        //构造要请求的参数数组，无需改动
        $parameter = array(
                "service" => "alipay.wap.auth.authAndExecute",
                "partner" => trim($this->alipay_config['partner']),
                "sec_id" => trim($this->alipay_config['sign_type']),
                "format"    => $format,
                "v" => $v,
                "req_id"    => $req_id,
                "req_data"  => $req_data,
                "_input_charset"    => trim(strtolower($this->alipay_config['input_charset']))
        );

        //建立请求
        $alipaySubmit = new \AlipaySubmit($this->alipay_config);
        $html_text = $alipaySubmit->buildRequestForm($parameter, 'get', '确认');
        header('Content-type: text/html;charset=UTF-8');
        echo $html_text;
    }


    //异步返回
    public function notify_url(){
        //计算得出通知验证结果
        $alipay_config=$this->alipay_config;
        //dump($alipay_config);

        $alipayNotify = new \AlipayNotify($alipay_config);
        $verify_result = $alipayNotify->verifyNotify();

        if($verify_result) {//验证成功
            /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
            //请在这里加上商户的业务逻辑程序代

            
            //——请根据您的业务逻辑来编写程序（以下代码仅作参考）——
            //获取支付宝的通知返回参数，可参考技术文档中服务器异步通知参数列表
            
            //解析notify_data
            //注意：该功能PHP5环境及以上支持，需开通curl、SSL等PHP配置环境。建议本地调试时使用PHP开发软件
            $doc = new \DOMDocument();   
            if ($alipay_config['sign_type'] == 'MD5') {
                $doc->loadXML($_POST['notify_data']);
            }
            
            if ($alipay_config['sign_type'] == '0001') {
                $doc->loadXML($alipayNotify->decrypt($_POST['notify_data']));
            }

            
            if( ! empty($doc->getElementsByTagName( "notify" )->item(0)->nodeValue) ) {
                //商户订单号
                $out_trade_no = $doc->getElementsByTagName( "out_trade_no" )->item(0)->nodeValue;
                //支付宝交易号
                $trade_no = $doc->getElementsByTagName( "trade_no" )->item(0)->nodeValue;
                //交易状态
                $trade_status = $doc->getElementsByTagName( "trade_status" )->item(0)->nodeValue;
                
                if($trade_status == 'TRADE_FINISHED') {
                    //判断该笔订单是否在商户网站中已经做过处理
                        //如果没有做过处理，根据订单号（out_trade_no）在商户网站的订单系统中查到该笔订单的详细，并执行商户的业务程序
                        //如果有做过处理，不执行商户的业务程序
                            
                    //注意：
                    //该种交易状态只在两种情况下出现
                    //1、开通了普通即时到账，买家付款成功后。
                    //2、开通了高级即时到账，从该笔交易成功时间算起，过了签约时的可退款时限（如：三个月以内可退款、一年以内可退款等）后。
            
                    //调试用，写文本函数记录程序运行情况是否正常
                    //logResult("这里写入想要调试的代码变量值，或其他运行的结果记录");



                    //echo "success";     //请不要修改或删除
                }
                else if ($trade_status == 'TRADE_SUCCESS') {
                    //判断该笔订单是否在商户网站中已经做过处理
                        //如果没有做过处理，根据订单号（out_trade_no）在商户网站的订单系统中查到该笔订单的详细，并执行商户的业务程序
                        //如果有做过处理，不执行商户的业务程序
                            
                    //注意：
                    //该种交易状态只在一种情况下出现——开通了高级即时到账，买家付款成功后。
            
                    //调试用，写文本函数记录程序运行情况是否正常
                    //logResult("这里写入想要调试的代码变量值，或其他运行的结果记录");


                    //echo "success";     //请不要修改或删除
                }

                $do=D('Common/RechargeRelation');
                $rs=$do->relation(true)->where(['r_no' => $out_trade_no])->field('id,uid,r_no,s_no')->find();

		    	//更新订单状态
                $apiurl                 ='/Recharge/update_status';
                $data['openid']         =$rs['openid'];
                $data['r_no']           =$rs['r_no'];
                $data['trade_status']   =$trade_status;
                $data['trade_no']       =$trade_no;

                $res=$this->doApi($apiurl,$data,'trade_status');

                //不管充值订单是否更新成功都偿试进行支付
                //订单付款
                $apiurl                 ='/Erp/orders_pay_other';
                $data                   =array();
                $data['openid']         =$rs['openid'];                 
                $data['s_no']           =$rs['s_no'];
                $data['paytype']        =1;
                $data['other_paytype']  =3;
                $res=$this->doApi($apiurl,$data,'other_paytype');
                
            }
			
			$do=new \Think\Model\MongoModel(C('DB_MONGO_CONFIG.DB_PREFIX').'alipay',null,C('DB_MONGO_CONFIG'));
			$do->add(array(
				'ip'			=>get_client_ip(),
				'atime'			=>date('Y-m-d H:i:s'),
				'orderno'		=>$out_trade_no,
				'url'			=>__SELF__,
				'type'			=>1,
                'pay_res'       =>$res->code,
				'res'			=>'success',
				'type_name'		=>'异步通知，付款成功'
			));	
			
			
			echo "success";     //请不要修改或删除
            //——请根据您的业务逻辑来编写程序（以上代码仅作参考）——
            
            /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        }
        else {
            //验证失败
            echo "fail";

			$do=new \Think\Model\MongoModel(C('DB_MONGO_CONFIG.DB_PREFIX').'alipay',null,C('DB_MONGO_CONFIG'));
			$do->add(array(
				'ip'			=>get_client_ip(),
				'atime'			=>date('Y-m-d H:i:s'),
				'orderno'		=>'',
				'url'			=>__SELF__,
				'type'			=>1,
				'res'			=>'fail',
				'type_name'		=>'异步通知，付款失败'
			));	
            //调试用，写文本函数记录程序运行情况是否正常
            //logResult("这里写入想要调试的代码变量值，或其他运行的结果记录");
        }
    
    }


    //同步返回
    public function return_url(){
        //计算得出通知验证结果
        $alipay_config=$this->alipay_config;
        $alipayNotify = new \AlipayNotify($alipay_config);
        $verify_result = $alipayNotify->verifyReturn();

        if($verify_result) {//验证成功
            /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
            //请在这里加上商户的业务逻辑程序代码
            
            //——请根据您的业务逻辑来编写程序（以下代码仅作参考）——
            //获取支付宝的通知返回参数，可参考技术文档中页面跳转同步通知参数列表

            //商户订单号
            $out_trade_no = $_GET['out_trade_no'];

            //支付宝交易号
            $trade_no = $_GET['trade_no'];

            //交易状态
            $result = $_GET['result'];

			$do=D('Common/RechargeRelation');
			$rs=$do->relation(true)->where(['r_no' => $out_trade_no])->field('id,uid,r_no,s_no')->find();

			//更新订单状态
			$apiurl					='/Recharge/update_status';
			$data['openid']			=$rs['openid'];
            $data['r_no']           =$rs['r_no'];
			$data['trade_status']	=$trade_status;
			$data['trade_no']		=$trade_no;

			$res=$this->doApi($apiurl,$data,'trade_status');

			//不管充值订单是否更新成功都偿试进行支付
			//订单付款
			$apiurl					='/Erp/orders_pay_other';
			$data 					=array();
			$data['openid']			=$rs['openid'];					
			$data['s_no']			=$rs['s_no'];
            $data['paytype']        =1;
            $data['other_paytype']  =3;
			$res=$this->doApi($apiurl,$data,'other_paytype');

            if($res->code==1){
                redirect('/Index/index?url=/Orders/index');
            }else{
                redirect('/Index/index?url=/Orders/alipay_error');                
            }

            //$this->display('success');


            //判断该笔订单是否在商户网站中已经做过处理
                //如果没有做过处理，根据订单号（out_trade_no）在商户网站的订单系统中查到该笔订单的详细，并执行商户的业务程序
                //如果有做过处理，不执行商户的业务程序
                
            //echo "验证成功<br />";

            //——请根据您的业务逻辑来编写程序（以上代码仅作参考）——
            
            /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        }
        else {
            //验证失败
            //如要调试，请看alipay_notify.php页面的verifyReturn函数
            //echo "验证失败";
            redirect('/Index/index?url=/Orders/alipay_error');
        }
        
    }




    /**
    +----------------------------------------------
    + 订单合并支付 
    +----------------------------------------------
    */

    /**
    * 支付宝充值
    */
    public function group_pay(){
    	if(empty($_SESSION['user']['id'])) redirect('/Index/index?url=/Login/index');

    	$rs=$do=M('orders')->where(['o_no' => I('get.o_no'),'uid' => session('user.id')])->field('id,status,o_no,pay_price')->find();

    	if($rs['status']!=1) {
    		echo '订单已支付，不可再次付款！';
    		exit;
    	}

    	//创建充值订单
		$apiurl				='/Recharge/add';
		$data['openid']		=session('user.openid');
		$data['money']		=$rs['pay_price'];
		$data['o_no']		=$rs['o_no'];
		$data['paytype']	=1;

		$res=$this->doApi($apiurl,$data,'o_no');
		if($res->code!=1) {
			echo $res->msg;
			exit;
		}



        if($_SERVER['HTTPS']=='on') $scheme='https://';
        else $scheme='http://';

        //dump($rs);exit;

        /**************************调用授权接口alipay.wap.trade.create.direct获取授权码token**************************/
            
        //返回格式
        $format = "xml";
        //必填，不需要修改

        //返回格式
        $v = "2.0";
        //必填，不需要修改

        //请求号
        $req_id = date('Ymdhis');
        //必填，须保证每次请求都是唯一

        //**req_data详细信息**

        //服务器异步通知页面路径
        $notify_url = $scheme.$_SERVER['HTTP_HOST'].'/Alipay/group_notify_url';
        //需http://格式的完整路径，不允许加?id=123这类自定义参数

        //页面跳转同步通知页面路径
        $call_back_url = $scheme.$_SERVER['HTTP_HOST'].'/Alipay/group_return_url';
        //需http://格式的完整路径，不允许加?id=123这类自定义参数

        //操作中断返回地址
        $merchant_url = $scheme.$_SERVER['HTTP_HOST'].'/Index/index?url=/Orders/index';
        //用户付款中途退出返回商户的地址。需http://格式的完整路径，不允许加?id=123这类自定义参数

        //卖家支付宝帐户
        $seller_email = $this->alipay_config['seller_email'];
        //必填

        //商户订单号
        $out_trade_no = $res->data->r_no;
        //商户网站订单系统中唯一订单号，必填

        //订单名称
        $subject = '唐人街订购商品[合并订单号#'.$rs['o_no'].'][ERP异动号#'.$res->data->erp_no.']';
        //必填

        //付款金额
        $total_fee = $rs['pay_price'];
        //必填

        //请求业务参数详细
        $req_data = '<direct_trade_create_req><notify_url>' . $notify_url . '</notify_url><call_back_url>' . $call_back_url . '</call_back_url><seller_account_name>' . $seller_email . '</seller_account_name><out_trade_no>' . $out_trade_no . '</out_trade_no><subject>' . $subject . '</subject><total_fee>' . $total_fee . '</total_fee><merchant_url>' . $merchant_url . '</merchant_url></direct_trade_create_req>';
        //必填

        /************************************************************/

        //构造要请求的参数数组，无需改动
        $para_token = array(
                "service" => "alipay.wap.trade.create.direct",
                "partner" => trim($this->alipay_config['partner']),
                "sec_id" => trim($this->alipay_config['sign_type']),
                "format"    => $format,
                "v" => $v,
                "req_id"    => $req_id,
                "req_data"  => $req_data,
                "_input_charset"    => trim(strtolower($this->alipay_config['input_charset']))
        );

        //建立请求
        $alipaySubmit = new \AlipaySubmit($this->alipay_config);
        $html_text = $alipaySubmit->buildRequestHttp($para_token);

        //URLDECODE返回的信息
        $html_text = urldecode($html_text);

        //解析远程模拟提交后返回的信息
        $para_html_text = $alipaySubmit->parseResponse($html_text);

        //获取request_token
        $request_token = $para_html_text['request_token'];


        /**************************根据授权码token调用交易接口alipay.wap.auth.authAndExecute**************************/

        //业务详细
        $req_data = '<auth_and_execute_req><request_token>' . $request_token . '</request_token></auth_and_execute_req>';
        //必填

        //构造要请求的参数数组，无需改动
        $parameter = array(
                "service" => "alipay.wap.auth.authAndExecute",
                "partner" => trim($this->alipay_config['partner']),
                "sec_id" => trim($this->alipay_config['sign_type']),
                "format"    => $format,
                "v" => $v,
                "req_id"    => $req_id,
                "req_data"  => $req_data,
                "_input_charset"    => trim(strtolower($this->alipay_config['input_charset']))
        );

        //建立请求
        $alipaySubmit = new \AlipaySubmit($this->alipay_config);
        $html_text = $alipaySubmit->buildRequestForm($parameter, 'get', '确认');
        header('Content-type: text/html;charset=UTF-8');
        echo $html_text;
    }


    //异步返回
    public function group_notify_url(){
        //计算得出通知验证结果
        $alipay_config=$this->alipay_config;
        //dump($alipay_config);

        $alipayNotify = new \AlipayNotify($alipay_config);
        $verify_result = $alipayNotify->verifyNotify();

        if($verify_result) {//验证成功
            /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
            //请在这里加上商户的业务逻辑程序代

            
            //——请根据您的业务逻辑来编写程序（以下代码仅作参考）——
            //获取支付宝的通知返回参数，可参考技术文档中服务器异步通知参数列表
            
            //解析notify_data
            //注意：该功能PHP5环境及以上支持，需开通curl、SSL等PHP配置环境。建议本地调试时使用PHP开发软件
            $doc = new \DOMDocument();   
            if ($alipay_config['sign_type'] == 'MD5') {
                $doc->loadXML($_POST['notify_data']);
            }
            
            if ($alipay_config['sign_type'] == '0001') {
                $doc->loadXML($alipayNotify->decrypt($_POST['notify_data']));
            }

            
            if( ! empty($doc->getElementsByTagName( "notify" )->item(0)->nodeValue) ) {
                //商户订单号
                $out_trade_no = $doc->getElementsByTagName( "out_trade_no" )->item(0)->nodeValue;
                //支付宝交易号
                $trade_no = $doc->getElementsByTagName( "trade_no" )->item(0)->nodeValue;
                //交易状态
                $trade_status = $doc->getElementsByTagName( "trade_status" )->item(0)->nodeValue;
                
                if($trade_status == 'TRADE_FINISHED') {
                    //判断该笔订单是否在商户网站中已经做过处理
                        //如果没有做过处理，根据订单号（out_trade_no）在商户网站的订单系统中查到该笔订单的详细，并执行商户的业务程序
                        //如果有做过处理，不执行商户的业务程序
                            
                    //注意：
                    //该种交易状态只在两种情况下出现
                    //1、开通了普通即时到账，买家付款成功后。
                    //2、开通了高级即时到账，从该笔交易成功时间算起，过了签约时的可退款时限（如：三个月以内可退款、一年以内可退款等）后。
            
                    //调试用，写文本函数记录程序运行情况是否正常
                    //logResult("这里写入想要调试的代码变量值，或其他运行的结果记录");



                    //echo "success";     //请不要修改或删除
                }
                else if ($trade_status == 'TRADE_SUCCESS') {
                    //判断该笔订单是否在商户网站中已经做过处理
                        //如果没有做过处理，根据订单号（out_trade_no）在商户网站的订单系统中查到该笔订单的详细，并执行商户的业务程序
                        //如果有做过处理，不执行商户的业务程序
                            
                    //注意：
                    //该种交易状态只在一种情况下出现——开通了高级即时到账，买家付款成功后。
            
                    //调试用，写文本函数记录程序运行情况是否正常
                    //logResult("这里写入想要调试的代码变量值，或其他运行的结果记录");


                    //echo "success";     //请不要修改或删除
                }

                $do=D('Common/RechargeRelation');
                $rs=$do->relation(true)->where(['r_no' => $out_trade_no])->field('id,uid,r_no,o_no')->find();

		    	//更新订单状态
				$apiurl					='/Recharge/update_status';
                $data['openid']         =$rs['openid'];
                $data['r_no']           =$rs['r_no'];
				$data['trade_status']	=$trade_status;
				$data['trade_no']		=$trade_no;

				$res=$this->doApi($apiurl,$data,'trade_status');


				$apiurl					='/Erp/orders_group_pay_other';
				$data 					=array();
				$data['openid']			=$rs['openid'];					
				$data['o_no']			=$rs['o_no'];
				$data['paytype']		=1;
                $data['other_paytype']  =3;
				$res=$this->doApi($apiurl,$data,'other_paytype');	

                
            }
			
			$do=new \Think\Model\MongoModel(C('DB_MONGO_CONFIG.DB_PREFIX').'alipay',null,C('DB_MONGO_CONFIG'));
			$do->add(array(
				'ip'			=>get_client_ip(),
				'atime'			=>date('Y-m-d H:i:s'),
				'orderno'		=>$out_trade_no,
				'url'			=>__SELF__,
				'type'			=>2,
                'pay_res'       =>$res->code,
				'res'			=>'success',
				'type_name'		=>'异步通知，合并订单付款成功'
			));	
			
			
			echo "success";     //请不要修改或删除
            //——请根据您的业务逻辑来编写程序（以上代码仅作参考）——
            
            /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        }
        else {
            //验证失败
            echo "fail";

			$do=new \Think\Model\MongoModel(C('DB_MONGO_CONFIG.DB_PREFIX').'alipay',null,C('DB_MONGO_CONFIG'));
			$do->add(array(
				'ip'			=>get_client_ip(),
				'atime'			=>date('Y-m-d H:i:s'),
				'orderno'		=>'',
				'url'			=>__SELF__,
				'type'			=>2,
				'res'			=>'fail',
				'type_name'		=>'异步通知，合并订单付款失败'
			));	
            //调试用，写文本函数记录程序运行情况是否正常
            //logResult("这里写入想要调试的代码变量值，或其他运行的结果记录");
        }
    
    }


    //同步返回
    public function group_return_url(){
        //计算得出通知验证结果
        $alipay_config=$this->alipay_config;
        $alipayNotify = new \AlipayNotify($alipay_config);
        $verify_result = $alipayNotify->verifyReturn();

        if($verify_result) {//验证成功
            /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
            //请在这里加上商户的业务逻辑程序代码
            
            //——请根据您的业务逻辑来编写程序（以下代码仅作参考）——
            //获取支付宝的通知返回参数，可参考技术文档中页面跳转同步通知参数列表

            //商户订单号
            $out_trade_no = $_GET['out_trade_no'];

            //支付宝交易号
            $trade_no = $_GET['trade_no'];

            //交易状态
            $result = $_GET['result'];

			$do=D('Common/RechargeRelation');
			$rs=$do->relation(true)->where(['r_no' => $out_trade_no])->field('id,uid,r_no,o_no')->find();

			//更新订单状态
			$apiurl					='/Recharge/update_status';
			$data['openid']			=$rs['openid'];
            $data['r_no']           =$rs['r_no'];
			$data['trade_status']	=$trade_status;
			$data['trade_no']		=$trade_no;

			$res=$this->doApi($apiurl,$data,'trade_status');


			//订单付款
			$apiurl					='/Erp/orders_group_pay_other';
			$data 					=array();
			$data['openid']			=$rs['openid'];					
			$data['o_no']			=$rs['o_no'];
			$data['paytype']		=1;
            $data['other_paytype']  =3;
			$res=$this->doApi($apiurl,$data,'other_paytype');	

			
            if($res->code==1){
                redirect('/Index/index?url=/Orders/index');
            }else{
                redirect('/Index/index?url=/Orders/alipay_error');                
            }

            //$this->display('success');

            //判断该笔订单是否在商户网站中已经做过处理
                //如果没有做过处理，根据订单号（out_trade_no）在商户网站的订单系统中查到该笔订单的详细，并执行商户的业务程序
                //如果有做过处理，不执行商户的业务程序
                
            //echo "验证成功<br />";

            //——请根据您的业务逻辑来编写程序（以上代码仅作参考）——
            
            /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        }
        else {
            //验证失败
            //如要调试，请看alipay_notify.php页面的verifyReturn函数
            //echo "验证失败";
            redirect('/Index/index?url=/Orders/alipay_error');
        }
        
    }



}