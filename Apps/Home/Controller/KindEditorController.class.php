<?php
// 本类由系统自动生成，仅供测试用途
namespace Home\Controller;
use Think\Controller;
use Think\Other;
class KindEditorController extends Other {
	public function _initialize(){
		$do=D('Cart/Config');
		$cfg=$do->config(array('ac'=>array('qiniu'),'cache_name'=>'qiniu'));		
		C('qiniu',$cfg['qiniu']);	
		auto_load('./ThinkPHP/Library/Vendor/Qiniu','listpath.php');  		
	}

	public function index(){
		
	}

	public function upload(){
		$auth = new \Qiniu\Auth(C('qiniu.ak'), C('qiniu.sk'));
		$token = $auth->uploadToken(C('qiniu.bucket'));
		$Config=new \Qiniu\Config();		
		$qn = new \Qiniu\Storage\FormUploader();

		$ext_arr = array(
			'image' => array('gif', 'jpg', 'jpeg', 'png', 'bmp'),
			'flash' => array('swf', 'flv'),
			'media' => array('swf', 'flv', 'mp3', 'wav', 'wma', 'wmv', 'mid', 'avi', 'mpg', 'asf', 'rm', 'rmvb'),
			'file' => array('doc', 'docx', 'xls', 'xlsx', 'ppt', 'htm', 'html', 'txt', 'zip', 'rar', 'gz', 'bz2'),
		);
		//最大文件大小 5M
		$max_size = 5000000;		
		//PHP上传失败
		if (!empty($_FILES['imgFile']['error'])) {
			switch($_FILES['imgFile']['error']){
				case '1':
					$error = '超过php.ini允许的大小。';
					break;
				case '2':
					$error = '超过表单允许的大小。';
					break;
				case '3':
					$error = '图片只有部分被上传。';
					break;
				case '4':
					$error = '请选择图片。';
					break;
				case '6':
					$error = '找不到临时目录。';
					break;
				case '7':
					$error = '写文件到硬盘出错。';
					break;
				case '8':
					$error = 'File upload stopped by extension。';
					break;
				case '999':
				default:
					$error = '未知错误。';
			}
			$this->ajaxReturn(array('error'=>1,'message'=>$error));
		}

		//有上传文件时
		if (empty($_FILES) === false) {
			//原文件名
			$file_name = $_FILES['imgFile']['name'];
			//服务器上临时文件名
			$tmp_name = $_FILES['imgFile']['tmp_name'];
			//文件大小
			$file_size = $_FILES['imgFile']['size'];
			//检查文件名
			if (!$file_name) {
				$error='请选择文件。';
				$this->ajaxReturn(array('error'=>1,'message'=>$error));
			}

			//检查文件大小
			if ($file_size > $max_size) {
				$error='上传文件大小超过限制。';
				$this->ajaxReturn(array('error'=>1,'message'=>$error));
			}


			//获得文件扩展名
			$temp_arr = explode(".", $file_name);
			$file_ext = array_pop($temp_arr);
			$file_ext = trim($file_ext);
			$file_ext = strtolower($file_ext);
			//检查扩展名
			if (in_array($file_ext, $ext_arr[$dir_name]) === false) {
				$error="上传文件扩展名是不允许的扩展名。\n只允许" . implode(",", $ext_arr[$dir_name]) . "格式。";
				$this->ajaxReturn(array('error'=>1,'message'=>$error));
			}


			list($ret, $err) = $qn->putFile($token, null, $_FILES['imgFile']['tmp_name'],$Config);

			if(is_null($err)){
				$this->ajaxReturn(array('error'=>0,'url'=>C('qiniu.domain').'/'.$ret['key']));
			}else{
				$error='上传失败！';
				$this->ajaxReturn(array('error'=>1,'message'=>$error));
			}

		}


	}

}