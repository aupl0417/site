<?php
namespace Ad\Controller;

class UploadOneController extends AuthController {
	function _initialize() {
		parent::_initialize();
		
		$do=D('Config');
		$cfg=$do->config(array('ac'=>array('qiniu'),'cache_name'=>'qiniu'));		
		C('qiniu',$cfg['qiniu']);	
		auto_load('./ThinkPHP/Library/Vendor/Qiniu','listpath.php');


		
	}		
    public function index(){
		/*
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
		*/
		$map['memberid']=session('user.id');

		$pagelist=pagelist(array(
			'table'		=>'images',
			'pagesize'	=>180,
			'map'		=>$map,
			'order'		=>'etime desc,id desc',
		));

		$this->assign('pagelist',$pagelist);
		
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
		//file_put_contents('a.txt',var_export($ret,true));
		
		
		
		
		if ($err != null) {
			//echo "上传失败。错误消息：".$err->message();
			$this->error('上传失败。错误消息：'.$err->message());
		}else{
			//echo "上传成功。Key：".$ret["key"];
			$do=M('images');
			if($rs=$do->where(array('fkey'=>$ret['key'],'memberid'=>session('user.id')))->find()){
				$do->where('id='.$rs['id'])->setField('etime',time());
			}else{
				$do->add(array(
					'atime'		=>time(),
					'etime'		=>time(),
					'ip'		=>get_client_ip(),
					'memberid'	=>session('user.id'),
					'name'		=>$_FILES['file_data']['name'],
					'fsize'		=>filesize($_FILES['file_data']['tmp_name']),
					'fkey'		=>$ret['key'],
					'url'		=>C('qiniu.domain').'/'.$ret['key']
				));
			}
			$this->success('上传成功！');
		}		
		

	}	
	
	public function delete(){
		$auth = new \Qiniu\Auth(C('qiniu.ak'), C('qiniu.sk'));
		$token = $auth->uploadToken(C('qiniu.bucket'));
		$qn=new \Qiniu\Storage\BucketManager($auth);
		
		$do=M('images');
		foreach(I('post.id') as $val){
			if(1==$count=$do->where(array('fkey'=>$val))->count()){			
				$qn->delete(C('qiniu.bucket'),$val);
			}

			$do->where(array('fkey'=>$val,'memberid'=>session('user.id')))->delete();
		}
		$this->ajaxReturn(array('status'=>success));
		//echo '{error: \'test error.\'}';
	}
	

	public function upload(){
		$this->display();
	}

}