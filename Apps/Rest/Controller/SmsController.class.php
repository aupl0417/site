<?php
/**
+----------------------------------------------------------------------
| RestFull API 
+----------------------------------------------------------------------
| 短信验证码功能
+----------------------------------------------------------------------
| Author: lazycat <673090083@qq.com>
+----------------------------------------------------------------------
*/

namespace Rest\Controller;
use Think\Controller\RestController;
class SmsController extends CommonController {
    public function index(){
    	redirect(C('sub_domain.www'));
    }
    /**
    * 发送短信验证码
    * @param string $_POST['mobile']    手机号码
    * @param int    $_POST['tplid']     短信模板ID
    */
    public function smscode(){
        //频繁请求限制
        $this->_request_check(10);

        //必传参数检查
        $this->need_param=array('mobile','tplid','sign');
        $this->_need_param();
        $this->_check_sign();

        $cache_name='sms_vcode_'.trim(I('post.mobile')).'_'.I('post.tplid');

        $code=S($cache_name);
        //dump($code);exit;
        if(empty($code)) {
            $code['code']=rand(100000,999999);
            $code['atime']=time();
        }else{
            if(time()-$code['atime'] < 60){ //60秒内只能发送一次
                //请不要频繁发送！
                $this->apiReturn(42);
            }
                    
            $code['atime']=time();
        }
            
        if(false==$content=$this->sms_tpl(I('post.tplid'),'{code}',$code['code'])){
            //短信模板错误！
            $this->api_result(41);
        }

        $data['content']=$content;
        $data['userid']=C('cfg.sms')['userid'];
        $data['account']=C('cfg.sms')['account'];
        $data['password']=C('cfg.sms')['password'];
        $data['action']='send';
        $data['mobile']=trim(I('post.mobile'));
            //$this->ajaxReturn(array('status'=>'success','msg'=>'发送成功！'));
        //dump($cache_name);
        //dump($data);exit;
        $api=C('cfg.sms')['sms'];
        $res=$this->curl_post($api,$data);
        //dump($res);
        $xml = simplexml_load_string($res);
        if($xml->returnstatus=='Success'){
            S($cache_name,$code,60*10); //10分钟内有效
            $this->apiReturn(1);
        }else{
            $this->apiReturn(0);
        } 
    }

    /**
    * 检查验证码是否正确
    * @param string $_POST['mobile']    手机号码
    * @param int    $_POST['tplid']     短信模板ID
    * @param string $_POST['smscode']   验证码
    */
    public function check_smscode(){
        //频繁请求限制
        $this->_request_check();

        //必传参数检查
        $this->need_param=array('mobile','tplid','smscode','sign');
        $this->_need_param();
        $this->_check_sign();

        $cache_name='sms_vcode_'.trim(I('post.mobile')).'_'.I('post.tplid');
        $code=S($cache_name);
        if($code['code']==I('post.smscode')){
            S($cache_name.'_status',1,60*10);
            $this->apiReturn(1,'',1,'验证成功！');
        }else{
            S($cache_name.'_status',null);
            $this->apiReturn(0,'',1,'短信验证码错误！');
        }
    }
}