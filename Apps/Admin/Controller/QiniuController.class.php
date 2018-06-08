<?php
namespace Admin\Controller;
use Think\Controller;
class QiniuController extends CommonController {
	function _initialize() {
		parent::_initialize();
		
		auto_load('./ThinkPHP/Library/Vendor/Qiniu','listpath.php');
		C('qiniu',C('cfg.qiniu'));

		
	}		
    public function index(){
		$auth = new \Qiniu\Auth(C('qiniu.ak'), C('qiniu.sk'));
		$token = $auth->uploadToken(C('qiniu.bucket'));
		$qn=new \Qiniu\Storage\BucketManager($auth);
		
		$p=$_GET['p']?$_GET['p']:'';
		$list=$qn->listFiles(C('qiniu.bucket'),'',$p,240);
		
		
		usort($list[0],function($a,$b){
			if($a['putTime']==$b['putTime']) return 0;
			return ($a['putTime']>$b['putTime'])?-1:1;
		});
	
		
		
		$this->assign('list',$list);
		//dump($list);
		$pages=qiniu_page(array('prev'=>$p,'next'=>$list[1]));
		$this->assign('pages',$pages);
		
		$this->display();
    }
	
	public function upload_save(){	
		$auth = new \Qiniu\Auth(C('qiniu.ak'), C('qiniu.sk'));
		$token = $auth->uploadToken(C('qiniu.bucket'));
		$Config=new \Qiniu\Config();
		
		
		$qn = new \Qiniu\Storage\FormUploader();
		
		//foreach($_FILES as $key=>$val){
			//copy($_FILES[$key]['tmp_name'],'./Uploads/'.basename($_FILES[$key]['name']));
			
		//}
		//$this->success('上传成功！');
		list($ret, $err) = $qn->putFile($token, null, $_FILES['file_data']['tmp_name'],$Config);
		//file_put_contents('a.txt',var_export($_FILES,true));
		
		$this->success('上传成功！');
		
		/*
		if ($err != null) {
			echo "上传失败。错误消息：".$err->message();
		}else{
			echo "上传成功。Key：".$ret["key"];
		}		
		*/

	}	
	
	public function delete(){
		$auth = new \Qiniu\Auth(C('qiniu.ak'), C('qiniu.sk'));
		$token = $auth->uploadToken(C('qiniu.bucket'));
		$qn=new \Qiniu\Storage\BucketManager($auth);

		foreach(I('post.id') as $val){
			$qn->delete(C('qiniu.bucket'),$val);
		}
		//$this->ajaxReturn(array('status'=>success));
		//echo '{error: \'test error.\'}';
	}

    /**
     * 编辑器图片上传
     * */
    public function ueditorUpload(){

        $result = A('Home/Run')->ueditorUpload();
        /* 输出结果 */
        if (isset($_GET["callback"])) {
            if (preg_match("/^[\w_]+$/", $_GET["callback"])) {
                echo htmlspecialchars($_GET["callback"]) . '(' . $result . ')';
            } else {
                echo json_encode(array(
                    'state'=> 'callback参数不合法'
                ));
            }
        } else {
            echo $result;
        }
    }

    public function getToken(){
        $key = I('key', '', 'htmlspecialchars,strip_tags,trim');
        if(!$key){
            return '';
        }

        $result = A('Home/Run')->getToken();
        echo $result;
    }
	
	public function test(){
		file_put_contents('t.txt',var_export(I('post.'),true));
	}
}