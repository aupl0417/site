<?php
/**
 * ----------------------------------------------------------
 * RestFull API V2.0
 * ----------------------------------------------------------
 * category: 授权接口
 * ----------------------------------------------------------
 * Author:Lazycat <673090083@qq.com>
 * ----------------------------------------------------------
 * 2016-12-07
 * ----------------------------------------------------------
 */
namespace Rest2\Controller;
use Think\Controller\RestController;
class AuthController extends ApiController {
    /**
     * subject: 接口授权生成Token
     * api: /Auth/token
     * content: 生成的Token有效期为60分钟
     * author: Lazycat
     * day: 2016-12-07
     *
     * [字段名,类型,是否必传,说明]
     * param: appid,int,1,应用接口id
     * param: access_key,string,1,接口Access key
     * param: secret_key,string,1,接口Secret key
     * param: sign_code,string,1,用于签名的字符串盐
     * param: device_id,string,0,设备ID,没有请传入session_id
     * param: erp_uid,string,0,ERPUID
     */

    public function token1()
    {
        $this->check($this->_field('device_id,erp_uid','appid,access_key,secret_key,sign_code'),false);

        $res = $this->_token($this->post);
        $this->apiReturn($res);
    }

    public function token(){
        $this->check($this->_field('device_id,erp_uid','appid,access_key,secret_key,sign_code'),false);

        $res = $this->_token($this->post);
        $this->apiReturn($res);
    }

    public function _token($param=null){
        $do = M('apps_user');
        $rs = $do->where(['appid' => $param['appid'],'access_key' => $param['access_key'],'secret_key' => $param['secret_key'],'sign_code' => $param['sign_code']])->field('id,terminal')->find();
        if(!$rs) return ['code' => 0,'msg' => '接口参数错误，授权失败！'];

        $param['terminal']   = $rs['terminal'];
        $do->where(['id' => $param['appid']])->setInc('num',1,60);

        if($param['erp_uid']) {
            $user = M('user')->cache(true)->where(['erp_uid' => $param['erp_uid'],'status' => 1])->field('id,openid,nick,erp_uid')->find();
            if(empty($user)){
                $res = A('Rest2/App')->_auth(['erp_uid' => $param['erp_uid']]);
                if($res['code'] == 1){
                    $user = [
                        'id'        => $res['data']['id'],
                        'openid'    => $res['data']['openid'],
                        'nick'      => $res['data']['nick'],
                        'erp_uid'   => $param['erp_uid'],
                    ];
                }
            }
        }

        $token = md5(implode(',',$param));
        $cache_token = 'api_token_' . $token;
        S($cache_token,['atime' => time(),'token' => $token,'data' => $param],C('CACHE_LEVEL.XXL'));

        return ['code' =>1,'data' => ['token' => $token,'user' => ($user ? $user : null)]];
    }
}