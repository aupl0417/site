<?php
namespace Cart\Controller;
use Common\Builder\Pays;
class AlipayController extends AuthController {
    private $alipay_config = array();
    public function _initialize(){
        parent::_initialize();

        $filepath = './ThinkPHP/Library/Vendor/Alipay/lib';
        auto_load($filepath);

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
		if(empty($_SESSION['user']['id'])) redirect(DM('user'));

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

		$res=$this->authApi($apiurl,$data,'s_no');
		if($this->_data['code']!=1) {
			echo $this->_data['msg'];
			exit;
		}
		
		if($_SERVER['HTTPS']=='on') $scheme='https://';
        else $scheme='http://';

        //支付类型
        $payment_type = "1";
        //必填，不能修改
        //服务器异步通知页面路径
        $notify_url =$scheme.$_SERVER['HTTP_HOST'].'/Alipay/notify_url';
        //需http://格式的完整路径，不能加?id=123这类自定义参数

        //页面跳转同步通知页面路径
        $return_url = $scheme.$_SERVER['HTTP_HOST'].'/Alipay/return_url';
        //需http://格式的完整路径，不能加?id=123这类自定义参数，不能写成http://localhost/

        //商户订单号
        $out_trade_no =$this->_data['data']['r_no'];
        //商户网站订单系统中唯一订单号，必填

        //订单名称
        $subject = '唐人街订购商品[订单号#'.$rs['s_no'].'][ERP异动号#'.$this->_data['data']['erp_no'].']';
        //必填

        //付款金额
        $total_fee = $rs['pay_price'];
        //必填

        //订单描述

        $body = $subject;
        //商品展示地址
        $show_url = DM('my','/orders');
        //需以http://开头的完整路径，例如：http://www.商户网址.com/myorder.html

        //防钓鱼时间戳
        $anti_phishing_key = "";
        //若要使用请调用类文件submit中的query_timestamp函数

        //客户端的IP地址
        $exter_invoke_ip = "";
        //非局域网的外网IP地址，如：221.0.0.1

        /************************************************************/

        //构造要请求的参数数组，无需改动
        $parameter = array(
                "service" => "create_direct_pay_by_user",
                "partner" => trim($this->alipay_config['partner']),
                "seller_email" => trim($this->alipay_config['seller_email']),
                "payment_type"  => $payment_type,
                "notify_url"    => $notify_url,
                "return_url"    => $return_url,
                "out_trade_no"  => $out_trade_no,
                "subject"   => $subject,
                "total_fee" => $total_fee,
                "body"  => $body,
                "show_url"  => $show_url,
                "anti_phishing_key" => $anti_phishing_key,
                "exter_invoke_ip"   => $exter_invoke_ip,
                "_input_charset"    => trim(strtolower($this->alipay_config['input_charset']))
        );

        //dump($this->alipay_config);
        //dump($parameter);exit;

        //建立请求
        $alipaySubmit = new \AlipaySubmit($this->alipay_config);
        $html_text = $alipaySubmit->buildRequestForm($parameter,"get", "正在跳转至支付页面……");
        header('Content-Type:text/html;charset=utf-8');
        echo $html_text;        
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

			$res=$this->authApi($apiurl,$data,'trade_status');
			//不管充值订单是否更新成功都偿试进行支付
			//订单付款
			$apiurl					='/Erp/orders_pay_other';
			$data 					=array();
			$data['openid']			=$rs['openid'];					
			$data['s_no']			=$rs['s_no'];
            $data['paytype']        =1;
            $data['other_paytype']  =3;
			$res=$this->authApi($apiurl,$data,'other_paytype');	

            if($this->_data['code']==1){
                redirect('/success');
            }else{
                redirect('/success/error');                      
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
            redirect('/success');
        }
        
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

				$res=$this->authApi($apiurl,$data,'trade_status');
                //不管充值订单是否更新成功都偿试进行支付
                //订单付款
                $apiurl                 ='/Erp/orders_pay_other';
                $data                   =array();
                $data['openid']         =$rs['openid'];                 
                $data['s_no']           =$rs['s_no'];
                $data['paytype']        =1;
                $data['other_paytype']  =3;
                $res=$this->authApi($apiurl,$data,'other_paytype');	
                
            }
			
			$do=new \Think\Model\MongoModel(C('DB_MONGO_CONFIG.DB_PREFIX').'alipay',null,C('DB_MONGO_CONFIG'));
			$do->add(array(
				'ip'			=>get_client_ip(),
				'atime'			=>date('Y-m-d H:i:s'),
				'orderno'		=>$out_trade_no,
				'url'			=>__SELF__,
				'type'			=>2,
                'pay_res'       =>$this->_data['code'],
				'res'			=>'success',
				'type_name'		=>'PC-异步通知，订单付款成功'
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
				'type_name'		=>'PC-异步通知，合并订单付款失败'
			));	
            //调试用，写文本函数记录程序运行情况是否正常
            //logResult("这里写入想要调试的代码变量值，或其他运行的结果记录");
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
		
    	if(empty($_SESSION['user']['id'])) redirect(DM('user'));

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

		$res=$this->authApi($apiurl,$data,'o_no');
        //dump($this->_data);

		if($this->_data['code']!=1) {
			echo $this->_data['msg'];
			exit;
		}



        if($_SERVER['HTTPS']=='on') $scheme='https://';
        else $scheme='http://';

        //支付类型
        $payment_type = "1";
        //必填，不能修改
        //服务器异步通知页面路径
        $notify_url =$scheme.$_SERVER['HTTP_HOST'].'/Alipay/group_notify_url';
        //需http://格式的完整路径，不能加?id=123这类自定义参数

        //页面跳转同步通知页面路径
        $return_url = $scheme.$_SERVER['HTTP_HOST'].'/Alipay/group_return_url';
        //需http://格式的完整路径，不能加?id=123这类自定义参数，不能写成http://localhost/

        //商户订单号
        $out_trade_no =$this->_data['data']['r_no'];
        //商户网站订单系统中唯一订单号，必填

        //订单名称
        $subject = '唐人街订购商品[合并订单号#'.$rs['o_no'].'][ERP异动号#'.$this->_data['data']['erp_no'].']';
        //必填

        //付款金额
        $total_fee = $rs['pay_price'];
        //必填

        //订单描述

        $body = $subject;
        //商品展示地址
        $show_url = DM('my','/orders');
        //需以http://开头的完整路径，例如：http://www.商户网址.com/myorder.html

        //防钓鱼时间戳
        $anti_phishing_key = "";
        //若要使用请调用类文件submit中的query_timestamp函数

        //客户端的IP地址
        $exter_invoke_ip = "";
        //非局域网的外网IP地址，如：221.0.0.1

        /************************************************************/

        //构造要请求的参数数组，无需改动
        $parameter = array(
                "service" => "create_direct_pay_by_user",
                "partner" => trim($this->alipay_config['partner']),
                "seller_email" => trim($this->alipay_config['seller_email']),
                "payment_type"  => $payment_type,
                "notify_url"    => $notify_url,
                "return_url"    => $return_url,
                "out_trade_no"  => $out_trade_no,
                "subject"   => $subject,
                "total_fee" => $total_fee,
                "body"  => $body,
                "show_url"  => $show_url,
                "anti_phishing_key" => $anti_phishing_key,
                "exter_invoke_ip"   => $exter_invoke_ip,
                "_input_charset"    => trim(strtolower($this->alipay_config['input_charset']))
        );

        //dump($this->alipay_config);
        //dump($parameter);exit;

        //建立请求
        $alipaySubmit = new \AlipaySubmit($this->alipay_config);
        $html_text = $alipaySubmit->buildRequestForm($parameter,"get", "正在跳转至支付页面……");
        header('Content-Type:text/html;charset=utf-8');
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

				$res=$this->authApi($apiurl,$data,'trade_status');


				$apiurl					='/Erp/orders_group_pay_other';
				$data 					=array();
				$data['openid']			=$rs['openid'];					
				$data['o_no']			=$rs['o_no'];
				$data['paytype']		=1;
                $data['other_paytype']  =3;
				$res=$this->authApi($apiurl,$data,'other_paytype');	

                
            }
			
			$do=new \Think\Model\MongoModel(C('DB_MONGO_CONFIG.DB_PREFIX').'alipay',null,C('DB_MONGO_CONFIG'));
			$do->add(array(
				'ip'			=>get_client_ip(),
				'atime'			=>date('Y-m-d H:i:s'),
				'orderno'		=>$out_trade_no,
				'url'			=>__SELF__,
				'type'			=>2,
                'pay_res'       =>$this->_data['code'],
				'res'			=>'success',
				'type_name'		=>'PC-异步通知，合并订单付款成功'
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
				'type_name'		=>'PC-异步通知，合并订单付款失败'
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

			$res=$this->authApi($apiurl,$data,'trade_status');


			//订单付款
			$apiurl					='/Erp/orders_group_pay_other';
			$data 					=array();
			$data['openid']			=$rs['openid'];					
			$data['o_no']			=$rs['o_no'];
			$data['paytype']		=1;
            $data['other_paytype']  =3;
			$res=$this->authApi($apiurl,$data,'other_paytype');	

			
            if($this->_data['code']==1){
                redirect('/success');
            }else{
                redirect('/success/error');          
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
            redirect('/success');
        }
        
    }



}