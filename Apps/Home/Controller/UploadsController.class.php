<?php
namespace Home\Controller;
use Think\Controller;
class UploadsController extends Controller {
	function _initialize() {

		auto_load('./ThinkPHP/Library/Vendor/Qiniu');

		$do=D('Config');
		$cfg=$do->config(array('ac'=>array('qiniu','cache_name'=>'cfg_qiniu')));
		C('qiniu',$cfg['qiniu']);
		
	}		
    public function index(){
        //$assitUrl='http://'.$_SERVER['HTTP_HOST'].'/Public/Jquery/wangEditor/php/wangEditor_uploadImg_assist.html';
        $auth = new \Qiniu\Auth(C('qiniu.ak'), C('qiniu.sk'));
        $token = $auth->uploadToken(C('qiniu.bucket'));
        $Config=new \Qiniu\Config();
        
        
        $qn = new \Qiniu\Storage\FormUploader();
        
        list($ret, $err) = $qn->putFile($token, null, $_FILES['wangEditor_uploadImg']['tmp_name'],$Config);
        
        //file_put_contents('a.txt',var_export(session(),true));
        
        if ($err != null) {
            //echo "上传失败。错误消息：".$err->message();
            //$url=$assitUrl.'#上传失败！';
            //$str='<iframe src="'.$url.'"></iframe>';
            $data   =   [
                'originalName'=>'xs_5.jpg',
                'name'=>'14690004894836.jpg',
                'url'=>'upload/20160720/14690004894836.jpg',
                'size'=>'4447',
                'type'=>'.jpg',
                'state'=>'SUCCESS',
            ];
        }else{
            //echo "上传成功。Key：".$ret["key"];
            //$url=$assitUrl.'#ok|'.C('qiniu.domain').'/'.$ret['key'];
            //$str='<iframe src="'.$url.'"></iframe>';
            //echo 0;
            $data   =   [
                'msg'   =>  '上次失败',
                'state'=>'ERROR',
            ];
        }
        $this->ajaxReturn($data);
    }
	
	public function upload(){
		$assitUrl='http://'.$_SERVER['HTTP_HOST'].'/Public/Jquery/wangEditor/php/wangEditor_uploadImg_assist.html';

		$auth = new \Qiniu\Auth(C('qiniu.ak'), C('qiniu.sk'));
		$token = $auth->uploadToken(C('qiniu.bucket'));
		$Config=new \Qiniu\Config();
		
		
		$qn = new \Qiniu\Storage\FormUploader();
		
		list($ret, $err) = $qn->putFile($token, null, $_FILES['wangEditor_uploadImg']['tmp_name'],$Config);
		
		//file_put_contents('a.txt',var_export(session(),true));
		
		if ($err != null) {
			//echo "上传失败。错误消息：".$err->message();
			$url=$assitUrl.'#上传失败！';
			$str='<iframe src="'.$url.'"></iframe>';
			echo $str;
		}else{
			//echo "上传成功。Key：".$ret["key"];
			$url=$assitUrl.'#ok|'.C('qiniu.domain').'/'.$ret['key'];
			$str='<iframe src="'.$url.'"></iframe>';
			echo $str;
		}		
	}
}