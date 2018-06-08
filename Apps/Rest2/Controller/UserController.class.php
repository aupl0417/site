<?php
/**
 * ----------------------------------------------------------
 * RestFull API V2.0
 * ----------------------------------------------------------
 * 用户相关接口
 * ----------------------------------------------------------
 * Author:Lazycat <673090083@qq.com>
 * ----------------------------------------------------------
 * 2017-01-03
 * ----------------------------------------------------------
 */
namespace Rest2\Controller;
use Think\Controller\RestController;
class UserController extends ApiController {
    protected $action_logs = array();
    public function getuser(){
        $this->check('token,openid',false);
        $res = $this->_getuser($this->post);
        $this->apiReturn($res);
    }

    public function _getuser($param=null){
        if($param['id']) $map['id'] = $param['id'];
        elseif($param['openid']) $map['openid'] = $param['openid'];
        elseif($param['erp_uid']) $map['erp_uid'] = $param['erp_uid'];

        if(empty($map)) goto error;

        $rs = M('user')->where($map)->field('id,level_id,nick,is_auth,shop_type,erp_uid,shop_id,is_receive_msg')->find();
		$rs['is_receive_msg'] = json_decode($rs['is_receive_msg'],true);			
		
        if(empty($rs)) goto error;

        return ['code' => 1,'data' => $rs];

        error:
        return ['code' => 0,'msg' => '找不到用户！'];
    }
	
	
	
}