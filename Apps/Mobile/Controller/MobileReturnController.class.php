<?php
/**
 * -------------------------------------------------
 * 话费、流量充值异步
 * -------------------------------------------------
 * Create by Lazycat <673090083@qq.com>
 * -------------------------------------------------
 * 2017-05-10
 * -------------------------------------------------
 */
namespace Mobile\Controller;
Vendor('cashierSDK2.lib.notify#class');
class MobileReturnController extends CommonController {
    protected $single;          //单订单支付句柄
    protected $dtpay_config;    //支付配置参数
    protected $config;          //话费接口参数

    public function _initialize() {
        parent::_initialize();

        //支付配置参数
        $dtpay_config = C('DTPAY_CONFIG');
        if(C('DTPAY_TEST')){    //是否启用支付测试
            $dtpay_config['Single']['submit_gateway'] = C('DTPAY_SINGLE.TEST');
            $dtpay_config['Multi']['submit_gateway'] = C('DTPAY_MULTI.TEST');
        }else{
            $dtpay_config['Single']['submit_gateway'] = C('DTPAY_SINGLE.ONLINE');
            $dtpay_config['Multi']['submit_gateway'] = C('DTPAY_MULTI.ONLINE');
        }

        $this->dtpay_config = $dtpay_config;
    }


    /**
     * 同步认证
     *
     * @param $string
     * @param $token
     * @return bool
     */
    public function check_return($string, $token)
    {
        return md5( $string . '0.13820200 1481726001' ) === $token;
    }

    /**
     * 单个订单异步
     * Create by laycat
     * 2017-05-10
     */
    public function notify_single(){
        $post = I('post.');
        $notify = new \dtpayNotify('Single',$this->dtpay_config);

        $result = $notify->verifySign($post);
        //log_add('mobile_notify_'.date('Ym'), array('atime' => date('Y-m-d H:i:s'),'type' => 'single','subject' => '收银台异步通知(test)','code' => 1,'res' => $result,'status' => $post['state'],'post' => var_export($post,true)));  //写入日志

        //测试
        //$result = true;
        //$post = ['payerID' => 'df9a407441a8a57dea36c852ab8c2aa9','orderID' => '2017010422211483315603','state' =>3];
        if ($result) {
            if ($post['state'] == 0) {  //未支付
                //TO DO YOUR CODING
            } elseif ($post['state'] == 3) {    //已支付
                $this->orders_single_update($post,I('get.terminal'));
            }

            log_add('mobile_notify_'.date('Ym'), array('atime' => date('Y-m-d H:i:s'),'type' => 'single','subject' => '收银台异步通知(成功)','code' => 1,'res' => $result,'status' => $post['state'],'post' => var_export($post,true)));  //写入日志
            echo "success";		//请不要修改或删除

        } else {
            //验证失败
            log_add('mobile_notify_'.date('Ym'), array('atime' => date('Y-m-d H:i:s'),'type' => 'single','subject' => '收银台异步通知(失败)','code' => 0,'res' => $result,'status' => $post['state'],'post' => var_export($post,true)));  //写入日志
            echo "fail";

            //调试用，写文本函数记录程序运行情况是否正常
            //logResult("这里写入想要调试的代码变量值，或其他运行的结果记录");
        }
    }

    /**
     * 更新订单
     * Create by lazycat
     * 2017-05-10
     * @param array $post 接收到的异步内容
     * @param int $terminal 终端
     */
    public function orders_single_update($post,$terminal=1){
        $ors = M('mobile_orders')->lock(true)->where(['s_no' => $post['shopOrderID']])->find();
        if($ors['status'] != 1) {
            log_add('mobile_error',['atime' => date('Y-m-d H:i:s'),'subject' => '异步通知处理订单失败！','code' => 0,'s_no' => $post['shopOrderID'],'msg' => '错误的订单状态！']);
            return false;
        }

        $this->config = $this->_get_config($ors['recharge_type']);  //接口参数

        $data['openid'] = M('user')->where(['id' => $ors['uid']])->getField('openid');
        $data['s_no']   = $ors['s_no'];

        //更新订单
        //$do = M();
        //$do->startTrans();

        if(!$this->sw[] = M('mobile_orders')->where(['id' => $ors['id']])->save(['status' => 2,'pay_time' => date('Y-m-d H:i:s'),'pay_type' => $post['onlyPay'],'dtpay_no' => $post['orderID'],'next_time' => date('Y-m-d H:i:s',time() + $this->config['limit_time']),'terminal' => $terminal])){
            $msg = '更新订单状态失败！';
            goto error;
        }

        log_add('mobile_success',['atime' => date('Y-m-d H:i:s'),'subject' => '异步通知处理订单成功（'.($ors['recharge_type'] == 1 ? '充话费' : '充流量').'）！','code' => 1,'s_no' => $post['shopOrderID'],'recharge_type' => $ors['recharge_type'],'msg' => '支付成功！' ,'sw' => @implode(',',$this->sw)]);

        //提交至充值平台进行充值，是否充值成功，可通过队列检测处理
        $this->doApi2('/MobileRecharge/recharge',$data);

        return true;

        error:
        log_add('mobile_error',['atime' => date('Y-m-d H:i:s'),'subject' => '异步通知处理订单失败（'.($ors['recharge_type'] == 1 ? '充话费' : '充流量').'）！','code' => 0,'s_no' => $post['shopOrderID'],'recharge_type' => $ors['recharge_type'],'msg' => $msg ,'sw' => @implode(',',$this->sw)]);
        return false;

    }

    /**
     * 话费充值异步
     * Create by lazycat
     * 2017-05-10
     */
    public function callback(){
        $options = explode('|',I('post.Orderinfo'));
        $recharge_type = M('mobile_orders')->where(['s_no' => $options[2]])->getField('recharge_type');
        $this->config = $this->_get_config($recharge_type);

        $sign = strtoupper(md5(I('post.Orderinfo').'|'.$this->config['key']));
        if($sign == I('post.Sign')){    //签名验证
            if(in_array($options[3],[1,29])){  //充值成功
                M('mobile_orders')->where(['s_no' => $options[2]])->save(['status' => 3,'express_time' => date('Y-m-d H:i:s'),'next_time' => date('Y-m-d H:i:s',time() + $this->config['confirm_time']),'return_status' => $options[3],'return_time' => date('Y-m-d H:i:s',strtotime($options[10])),'trade_no' => $options[11],'notify_time' => date('Y-m-d H:i:s')]);
            }else{  //失败，因为会有延迟到账的情况，在这不做处理，通过队列监测处理
                M('mobile_orders')->where(['s_no' => $options[2]])->save(['return_status' => $options[3],'return_time' => date('Y-m-d H:i:s',strtotime($options[10])),'trade_no' => $options[11],'notify_time' => date('Y-m-d H:i:s')]);
            }

            log_add('mobile_callback',['atime' => date('Y-m-d H:i:s'),'subject' => ($recharge_type == 1 ? '话费':'流量').'充值异步（成功）','code' => 1,'msg' => '签名较验通过！','recharge_type' => $recharge_type,'post' => var_export(I('post.'),true)]);
            echo 'ok';
        }else{
            log_add('mobile_callback',['atime' => date('Y-m-d H:i:s'),'subject' => '充值异步（失败）','code' => 0,'msg' => '签名较验失败！','recharge_type' => $recharge_type,'post' => var_export(I('post.'),true)]);
            echo 'fail';
        }

    }


    /**
     * 单订单同步返回
     * Create by lazycat
     * 2017-05-10
     */
    public function return_single(){
        $id     = I('get.id');
        $token  = I('get._s');
        if ($this->check_return($id, $token) === true) {
            $res = $this->_single_orders_check($id);
            if($res['code'] == 1) echo '成功！';
            else echo '失败！';

            exit();
        }
        echo '失败！';
    }

    /**
     * 订单支付状态检测及修复
     * Create by lazycat
     * 2017-05-10
     * @param $s_no string 订单号
     */
    public function _single_orders_check($s_no){
        $result = ['code' => 0,'msg' => '支付失败！','s_no' => $s_no];

        $rs = M('mobile_orders')->where(['s_no' => $s_no])->field('id,status,s_no,uid')->find();
        if($rs['status'] == 2){
            $result['code'] = 1;
            $result['msg']  = '支付成功！';
        }elseif($rs['status'] == 1){
            //在此次可考虑再次与ERP中的订单较对，防止异步出问题是可以修正
            //do samething……

            $res = $this->doApi2('/Erp/orders_in_erp_status',['s_no' => $s_no]);
            if($res['data']['o_orderState'] == 1){
                $tmp = $this->fix_orders($s_no,$res['data']);
                if($tmp == true){
                    $result = ['code' => 1,'msg' => '支付成功！','s_no' => $s_no];
                }
            }

        }

        return $result;
    }


    /**
     * 修复订单
     * Create by lazycat
     * 2017-05-10
     * @param string $s_no 订单号
     * @param array $status 在ERP中的状态
     * @param int $terminal 操作终端
     */
    public function fix_orders($s_no,$status,$terminal=1){
        $ors = M('mobile_orders')->lock(true)->where(['s_no' => $s_no])->find();
        if($ors['status'] != 1) {
            log_add('mobile_error',['atime' => date('Y-m-d H:i:s'),'is_fix' => 1,'subject' => '订单修复失败（'.($ors['recharge_type'] == 1 ? '充话费' : '充流量').'）！','code' => 0,'s_no' => $s_no,'recharge_type' => $ors['recharge_type'],'msg' => '错误的订单状态！' ,'sw' => '']);
            return false;
        }

        $this->config = $this->_get_config($ors['recharge_type']);  //接口参数

        $data['openid'] = M('user')->where(['id' => $ors['uid']])->getField('openid');
        $data['s_no']   = $ors['s_no'];

        //更新订单
        //$do = M();
        //$do->startTrans();

        if(!$this->sw[] = M('mobile_orders')->where(['id' => $ors['id']])->save(['status' => 2,'pay_time' => date('Y-m-d H:i:s'),'pay_type' => $status['o_payType'],'dtpay_no' => $status['o_id'],'next_time' => date('Y-m-d H:i:s',time() + $this->config['limit_time']),'terminal' => $terminal])){
            $msg = '更新订单状态失败！';
            goto error;
        }

        log_add('mobile_success',['atime' => date('Y-m-d H:i:s'),'is_fix' => 1,'subject' => '订单修复成功（'.($ors['recharge_type'] == 1 ? '充话费' : '充流量').'）！','code' => 1,'s_no' => $s_no,'recharge_type' => $ors['recharge_type'],'msg' => '支付成功！' ,'sw' => @implode(',',$this->sw)]);

        //提交至充值平台进行充值，是否充值成功，可通过队列检测处理
        $this->doApi2('/MobileRecharge/recharge',$data);

        return true;

        error:
        log_add('mobile_error',['atime' => date('Y-m-d H:i:s'),'is_fix' => 1,'subject' => '订单修复失败（'.($ors['recharge_type'] == 1 ? '充话费' : '充流量').'）！','code' => 0,'s_no' => $s_no,'recharge_type' => $ors['recharge_type'],'msg' => '支付成功！' ,'sw' => @implode(',',$this->sw)]);
        return false;
    }


    /**
     * 获取接口参数
     * @param int $recharge_type 1=话费充值，2=流量充值
     */
    private function _get_config($recharge_type=1){
        if($recharge_type == 1){
            if(C('cfg.mobile_fare')['status'] == 1){    //正式环境
                $config = [
                    'mb_max_fare'       => C('cfg.mobile_fare')['mb_max_fare'],
                    'user_max_fare'     => C('cfg.mobile_fare')['user_max_fare'],
                    'limit_time'        => C('cfg.mobile_fare')['limit_time'],
                    'confirm_time'      => C('cfg.mobile_fare')['confirm_time'],
                    'ratio'             => C('cfg.mobile_fare')['ratio']+1,
                    'trade_url'         => C('cfg.mobile_fare')['trade_url'],
                    'query_url'         => C('cfg.mobile_fare')['query_url'],
                    'mid'               => C('cfg.mobile_fare')['mid'],
                    'account'           => C('cfg.mobile_fare')['account'],
                    'key'               => C('cfg.mobile_fare')['key'],
                ];
            }else{  //测试环境
                $config = [
                    'mb_max_fare'       => C('cfg.mobile_fare')['mb_max_fare'],
                    'user_max_fare'     => C('cfg.mobile_fare')['user_max_fare'],
                    'limit_time'        => C('cfg.mobile_fare')['limit_time'],
                    'confirm_time'      => C('cfg.mobile_fare')['confirm_time'],
                    'ratio'             => C('cfg.mobile_fare')['ratio']+1,
                    'trade_url'         => C('cfg.mobile_fare')['test_trade_url'],
                    'query_url'         => C('cfg.mobile_fare')['test_query_url'],
                    'mid'               => C('cfg.mobile_fare')['test_mid'],
                    'account'           => C('cfg.mobile_fare')['test_account'],
                    'key'               => C('cfg.mobile_fare')['test_key'],
                ];
            }
        }else{
            if(C('cfg.mobile_flow')['status'] == 1){    //正式环境
                $config = [
                    'mb_max_flow'       => C('cfg.mobile_flow')['mb_max_flow'],
                    'user_max_flow'     => C('cfg.mobile_flow')['user_max_flow'],
                    'limit_time'        => C('cfg.mobile_flow')['limit_time'],
                    'confirm_time'      => C('cfg.mobile_flow')['confirm_time'],
                    'ratio'             => C('cfg.mobile_flow')['ratio']+1,
                    'trade_url'         => C('cfg.mobile_flow')['trade_url'],
                    'query_url'         => C('cfg.mobile_flow')['query_url'],
                    'mid'               => C('cfg.mobile_flow')['mid'],
                    'account'           => C('cfg.mobile_flow')['account'],
                    'key'               => C('cfg.mobile_flow')['key'],
                ];
            }else{  //测试环境
                $config = [
                    'mb_max_flow'       => C('cfg.mobile_flow')['mb_max_flow'],
                    'user_max_flow'     => C('cfg.mobile_flow')['user_max_flow'],
                    'limit_time'        => C('cfg.mobile_flow')['limit_time'],
                    'confirm_time'      => C('cfg.mobile_flow')['confirm_time'],
                    'ratio'             => C('cfg.mobile_flow')['ratio']+1,
                    'trade_url'         => C('cfg.mobile_flow')['test_trade_url'],
                    'query_url'         => C('cfg.mobile_flow')['test_query_url'],
                    'mid'               => C('cfg.mobile_flow')['test_mid'],
                    'account'           => C('cfg.mobile_flow')['test_account'],
                    'key'               => C('cfg.mobile_flow')['test_key'],
                ];
            }
        }

        return $config;
    }

}