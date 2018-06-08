<?php
/**
+----------------------------------------------------------------------
| RestFull API 
+----------------------------------------------------------------------
| 文件上传
+----------------------------------------------------------------------
| Author: lazycat <673090083@qq.com>
+----------------------------------------------------------------------
*/

namespace Rest\Controller;
use Think\Controller\RestController;
class UploadController extends CommonController {
    function _initialize() {
        
        parent::_initialize(); 
        C('qiniu',C('cfg.qiniu'));   
        auto_load('./ThinkPHP/Library/Vendor/Qiniu','listpath.php');


        
    }    
    public function index(){
    	redirect(C('sub_domain.www'));
    }

    /**
    * Curl模式上传图片
    * @param stream     $_POST['imageData'] 文件流
    */
    public function curlUpload(){
        //频繁请求限制
        //$this->_request_check();
        //必传参数检查
        $this->need_param=array('sign');
        $this->_need_param();
        $this->_check_sign();
        $imageData  =   unserialize($_POST['imageData']);
        //(unserialize($_POST['imageData']));
        //充许上传格式
        $ext_arr    =array('gif','jpg','jpeg','png', 'docx');
        $file_ext   =strtolower(pathinfo($imageData['name'], PATHINFO_EXTENSION));
        if(!in_array($file_ext,$ext_arr)){
            $this->apiReturn(52);            
        }


        //充许上传文件大小，限制3M
        $maxsize=1024*1024*3;
        $filesize=filesize($imageData['tmp_name']);
        if($filesize>$maxsize){
            $this->apiReturn(51);
        }


        //七牛接口初始化
        $auth = new \Qiniu\Auth(C('qiniu.ak'), C('qiniu.sk'));
        $token = $auth->uploadToken(C('qiniu.bucket'));
        $Config=new \Qiniu\Config(); 
        $qn = new \Qiniu\Storage\FormUploader();

        list($ret, $err) = $qn->putFile($token, null, $imageData['tmp_name'],$Config);
        //file_put_contents('a.txt',var_export($ret,true));
        
        if ($err != null) {
            //echo "上传失败。错误消息：".$err->message();
            $this->apiReturn(4,'',1,'上传失败！'.$err->message());
        }else{
            //echo "上传成功。Key：".$ret["key"];
            $do=D('Common/Images');
            if($rs=$do->where(array('fkey'=>$ret['key'],'uid'=>$this->uid))->field('id,url,name,fsize,fkey')->find()){
                $do->where('id='.$rs['id'])->setField('etime',date('Y-m-d H:i:s'));
                $result['data']=$rs;
            }else{
                $data['uid']    =$this->uid;
                $data['name']   =$imageData['name'];
                $data['fsize']  =$filesize;
                $data['fkey']   =$ret['key'];
                $data['url']    =C('qiniu.domain').'/'.$ret['key'];
                if($do->create($data)) $do->add();
                $result['data']=$data;
            }
            $this->apiReturn(1,$result);
        }

    }


    /**
    * Curl模式上传图片
    * @param stream     $_POST['imageData'] 文件流
    */
    public function fileUpload(){
        //频繁请求限制
        //$this->_request_check();
        //必传参数检查
        $this->need_param=array('sign');
        $this->_need_param();
        $this->_check_sign();
        $imageData  =   unserialize($_POST['imageData']);
        //(unserialize($_POST['imageData']));
        //充许上传格式
        $ext_arr    = array('gif','jpg','jpeg','png','zip','rar','doc','docx');
        $file_ext   = strtolower(pathinfo($imageData['name'], PATHINFO_EXTENSION));
        if(!in_array($file_ext,$ext_arr)){
            $this->apiReturn(52,['msg' => '只允许上传gif,jpg,jpeg,png,zip,rar,doc,docx格式的文件']);           
        }


        //充许上传文件大小，限制3M
        $maxsize=1024*1024*3;
        $filesize=filesize($imageData['tmp_name']);
        if($filesize>$maxsize){
            $this->apiReturn(51);
        }


        //七牛接口初始化
        $auth = new \Qiniu\Auth(C('qiniu.ak'), C('qiniu.sk'));
        $token = $auth->uploadToken(C('qiniu.bucket'));
        $Config=new \Qiniu\Config(); 
        $qn = new \Qiniu\Storage\FormUploader();

        list($ret, $err) = $qn->putFile($token, null, $imageData['tmp_name'],$Config);
        //file_put_contents('a.txt',var_export($ret,true));
        
        if ($err != null) {
            //echo "上传失败。错误消息：".$err->message();
            $this->apiReturn(4,'',1,'上传失败！'.$err->message());
        }else{
            //echo "上传成功。Key：".$ret["key"];
            $do=D('Common/Images');
            if($rs=$do->where(array('fkey'=>$ret['key'],'uid'=>$this->uid))->field('id,url,name,fsize,fkey')->find()){
                $do->where('id='.$rs['id'])->setField('etime',date('Y-m-d H:i:s'));
                $result['data']=$rs;
            }else{
                $data['uid']    =$this->uid;
                $data['name']   =$imageData['name'];
                $data['fsize']  =$filesize;
                $data['fkey']   =$ret['key'];
                $data['url']    =C('qiniu.domain').'/'.$ret['key'];

                if($do->create($data)) $do->add();
                $result['data']=$data;
            }            
            
            $this->apiReturn(1,$result);
        }

    }
    
    /**
     * 上传图片
     * @param string $_POST['openid']   用户openid
     * @param stream $_POST['imageData'] 文件流
     */
    public function upload(){
        //频繁请求限制
        //$this->_request_check();
        //必传参数检查
        $this->need_param=array('openid','sign');
        $this->_need_param();
        $this->_check_sign();
        //充许上传格式
        $ext_arr    =array('gif','jpg','jpeg','png');
        $file_ext   =strtolower(pathinfo($_FILES['imagesData']['name'], PATHINFO_EXTENSION));
        if(!in_array($file_ext,$ext_arr)){
            $this->apiReturn(52);
        }
    
    
        //充许上传文件大小，限制3M
        $maxsize=1024*1024*3;
        $filesize=filesize($_FILES['imagesData']['tmp_name']);
        if($filesize>$maxsize){
            $this->apiReturn(51);
        }
    
    
        //七牛接口初始化
        $auth = new \Qiniu\Auth(C('qiniu.ak'), C('qiniu.sk'));
        $token = $auth->uploadToken(C('qiniu.bucket'));
        $Config=new \Qiniu\Config();
        $qn = new \Qiniu\Storage\FormUploader();
    
        list($ret, $err) = $qn->putFile($token, null, $_FILES['imagesData']['tmp_name'],$Config);
        //file_put_contents('a.txt',var_export($ret,true));
    
        if ($err != null) {
            //echo "上传失败。错误消息：".$err->message();
            $this->apiReturn(4,'',1,'上传失败！'.$err->message());
        }else{
            //echo "上传成功。Key：".$ret["key"];
            $do=D('Common/Images');
            if($rs=$do->where(array('fkey'=>$ret['key'],'uid'=>$this->uid))->field('id,url,name,fsize,fkey')->find()){
                $do->where('id='.$rs['id'])->setField('etime',date('Y-m-d H:i:s'));
                $result['data']=$rs;
            }else{
                $data['uid']    =$this->uid;
                $data['name']   =$_FILES['imagesData']['name'];
                $data['fsize']  =$filesize;
                $data['fkey']   =$ret['key'];
                $data['url']    =C('qiniu.domain').'/'.$ret['key'];
    
                if($do->create($data)) $do->add();
                $result['data']=$data;
            }
    
            $this->apiReturn(1,$result);
        }
    
    }


    /**
    * 上传图片,流格式上传
    * @param stream $_POST['content'] 文件流
    */
    public function upload2(){
        //频繁请求限制
        //$this->_request_check();

        //必传参数检查
        $this->need_param=array('sign');
        $this->_need_param();
        $this->_check_sign();

        
        /*
        //充许上传格式
        $ext_arr    =array('gif','jpg','jpeg','png');
        $file_ext   =strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        if(!in_array($file_ext,$ext_arr)){
            $this->apiReturn(52);            
        }
        */


        //充许上传文件大小，限制3M
        /*
        $maxsize=1024*1024*3;
        $filesize=strlen($content);
        if($filesize>$maxsize){
            $this->apiReturn(51);
        }
        */


        //七牛接口初始化
        $auth = new \Qiniu\Auth(C('qiniu.ak'), C('qiniu.sk'));
        $token = $auth->uploadToken(C('qiniu.bucket'));
        $Config=new \Qiniu\Config(); 
        $qn = new \Qiniu\Storage\UploadManager();

        list($ret, $err) = $qn->put($token, null, $_POST['content'],$Config);
        //file_put_contents('a.txt',var_export($ret,true));
        
        if ($err != null) {
            //echo "上传失败。错误消息：".$err->message();
            $this->apiReturn(4,'',1,'上传失败！'.$err->message());
        }else{
            //echo "上传成功。Key：".$ret["key"];
            $do=D('Common/Images');
            if($rs=$do->where(array('fkey'=>$ret['key'],'uid'=>$this->uid))->field('id,url,name,fsize,fkey')->find()){
                $do->where('id='.$rs['id'])->setField('etime',date('Y-m-d H:i:s'));
                $result['data']=$rs;
            }else{
                $data['uid']    =$this->uid;
                $data['name']   ='app_upload.jpg';
                $data['fsize']  =strlen($_POST['content']);
                $data['fkey']   =$ret['key'];
                $data['url']    =C('qiniu.domain').'/'.$ret['key'];

                if($do->create($data)) $do->add();
                $result['data']=$data;
            }            
            
            $this->apiReturn(1,$result);
        }

    }


    /**
    * 远程上传图片
    */
    public function upload_remote(){
        //必传参数检查
        $this->need_param=array('url','sign');
        $this->_need_param();
        $this->_check_sign();  
        
        //七牛接口初始化
        $auth   = new \Qiniu\Auth(C('qiniu.ak'), C('qiniu.sk'));
        $token  = $auth->uploadToken(C('qiniu.bucket'));
        $Config =new \Qiniu\Config(); 
        $qn     = new \Qiniu\Storage\UploadManager();

        if(!$content = $this->curl_get(I('post.url'))) $this->apiReturn(0);

        list($ret, $err) = $qn->put($token, null, $content,$Config);
        //file_put_contents('a.txt',var_export($ret,true));
        
        if ($err != null) {
            //echo "上传失败。错误消息：".$err->message();
            $this->apiReturn(4,'',1,'上传失败！'.$err->message());
        }else{
            //echo "上传成功。Key：".$ret["key"];
            $do=D('Common/Images');
            if($rs=$do->where(array('fkey'=>$ret['key'],'uid'=>$this->uid))->field('id,url,name,fsize,fkey')->find()){
                $do->where('id='.$rs['id'])->setField('etime',date('Y-m-d H:i:s'));
                $result['data']=$rs;
            }else{
                $data['uid']    =$this->uid;
                $data['name']   ='remote_'.basename(I('post.url'));
                $data['fsize']  =strlen($content);
                $data['fkey']   =$ret['key'];
                $data['url']    =C('qiniu.domain').'/'.$ret['key'];
                if($do->create($data)) $do->add();
                $result['data']=$data;
            }            
            
            $this->apiReturn(1,$result);
        }              
    }
    
}