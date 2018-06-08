<?php
namespace Rest\Controller;
use Think\Controller\RestController;
class OtherController extends RestController {
    //md5('16416fwefaw34efg6aw4erf6w#$#@$@#')
    protected $_md5Str = '05cc223e66e1a10c39fb985ca70ec7da';
    
    /**
     * 数据签名
     * @return string
     */
    protected function signData() {
        $data = $this->_post;
        unset($data['sign']);
        ksort($data);
        $string = http_build_query($data);
        return md5($string.$this->_md5Str);
    }
    
    /**
     * 数据返回
     * @param unknown $data
     */
    protected function apiReturn($data = []) {
        G('end');
        $logs['atime']	=date('Y-m-d H:i:s');
        $logs['ip']		=get_client_ip();
        $logs['code']	=$data['code'];
        $logs['msg']	=$data['msg'];
        $logs['dotime']	=G('begin','end');
        $logs['url']	=($_SERVER['HTTPS']=='on'?'https://':'http://').$_SERVER['HTTP_HOST'].__SELF__;
        $logs['sw']		=@implode(',',$this->sw);
        $logs['post']	=@var_export($this->_post,true);
        $logs['res']	=@var_export($data['data'],true);
        log_add('notify',$logs);
        $this->ajaxReturn(['code' => $data['code'], 'msg' => $data['msg'], 'data' => $data['data']]);
    }
}