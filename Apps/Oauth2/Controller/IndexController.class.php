<?php
/**
同步登录ERP系统-用户授权获取
*/
namespace Oauth2\Controller;
use Think\Cache\Driver\Memcached;
use Think\Controller;
class IndexController extends Controller {
    //获取ERP 用户
    public function login(){
        $code=$_POST['code'];

        $openid=S($code);
        if($openid){
            $result['status']=1;
            $result['openid']=$openid;
        }else{
            $result['status']=2;
            $result['msg']  ='获取失败！';
        }

        $logs=array(
                'atime' =>date('Y-m-d H:i:s'),
                'ip'    =>get_client_ip(),
                'post'  =>var_export(I('post.'),true),
                'url'   =>__SELF__,
                'res'   =>var_export($result,true)
            );
        log_add('api',$logs);
        //return json_encode($result);
        echo json_encode($result);
    }


}