<?php
/**
 * ----------------------------------------------------------
 * RestFull API V2.0
 * ----------------------------------------------------------
 * 文件上传接口
 * ----------------------------------------------------------
 * Author:Lazycat <673090083@qq.com>
 * ----------------------------------------------------------
 * 2017-02-21
 * ----------------------------------------------------------
 */
namespace Rest2\Controller;
use Think\Controller\RestController;
class UploadController extends ApiController {
    protected $action_logs = array();
    function _initialize() {
        parent::_initialize();
        C('qiniu',C('cfg.qiniu'));
        auto_load('./ThinkPHP/Library/Vendor/Qiniu','listpath.php');
    }

    /**
     * subject: base64格式文件上传
     * api: /Upload/upload_base64
     * author: Lazycat
     * day: 2017-02-21
     * [字段名,类型,是否必传,说明]
     * param: openid,string,1,用户openid
     * param: filebody,string,1,base64格式的数据，此字段不加入签名
     */
    public function upload_base64(){
        $this->check('openid,filebody',1,'filebody');

        $res = $this->_upload_base64($this->post);

        $this->apiReturn($res);
    }

    public function _upload_base64($param){
        $filebody = base64_decode($param['filebody']);

        //充许上传格式
        $ext_arr    = array('gif','jpg','png');

        //充许上传文件大小，限制3M
        $maxsize    = 1024 * 1024 * 3;
        $filesize   = strlen($filebody);
        if($filesize > $maxsize){
            return ['code' => 0,'msg' => '图片文件最大不能超过3M'];
        }


        //七牛接口初始化
        $auth   = new \Qiniu\Auth(C('qiniu.ak'), C('qiniu.sk'));
        $token  = $auth->uploadToken(C('qiniu.bucket'));
        $Config = new \Qiniu\Config();
        $qn     = new \Qiniu\Storage\UploadManager();

        list($ret, $err) = $qn->put($token, null, $filebody,$Config);
        //file_put_contents('a.txt',var_export($ret,true));

        if ($err != null) {
            //echo "上传失败。错误消息：".$err->message();
            return ['code' => 0,'msg' => '上传失败！'.$err->message()];
        }else{
            //echo "上传成功。Key：".$ret["key"];
            $do = D('Common/Images');
            if($rs = $do->where(array('fkey' => $ret['key'],'uid' => $this->user['id']))->field('id,url,name,fsize,fkey')->find()){
                $do->where('id='.$rs['id'])->setField('etime',date('Y-m-d H:i:s'));
                $result = $rs;
            }else{
                $data['uid']    = $this->user['id'];
                $data['name']   = 'app_upload.jpg';
                $data['fsize']  = strlen($filebody);
                $data['fkey']   = $ret['key'];
                $data['url']    = C('qiniu.domain').'/'.$ret['key'];

                if($do->create($data)) $do->add();
                $result = $data;
            }

            return ['code' => 1,'data' => $result];
        }
    }
}