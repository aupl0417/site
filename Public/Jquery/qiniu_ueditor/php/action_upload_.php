<?php
/**
 * 上传附件和上传视频
 * User: Jinqn
 * Date: 14-04-09
 * Time: 上午10:17
 */
//include "Uploader.class.php";
//include "Qiniu_upload.php";
include "Qiniu.php";
include "conf.php";
/* 上传配置 */
$base64 = "upload";
switch (htmlspecialchars($_GET['action'])) {
    case 'uploadimage':
        $config = array(
            "pathFormat" => $CONFIG['imagePathFormat'],
            "maxSize" => $CONFIG['imageMaxSize'],
            "allowFiles" => $CONFIG['imageAllowFiles']
        );
        $fieldName = $CONFIG['imageFieldName'];
        break;
    case 'uploadscrawl':
        $config = array(
            "pathFormat" => $CONFIG['scrawlPathFormat'],
            "maxSize" => $CONFIG['scrawlMaxSize'],
            "allowFiles" => $CONFIG['scrawlAllowFiles'],
            "oriName" => "scrawl.png"
        );
        $fieldName = $CONFIG['scrawlFieldName'];
        $base64 = "base64";
        break;
    case 'uploadvideo':
        $config = array(
            "pathFormat" => $CONFIG['videoPathFormat'],
            "maxSize" => $CONFIG['videoMaxSize'],
            "allowFiles" => $CONFIG['videoAllowFiles']
        );
        $fieldName = $CONFIG['videoFieldName'];
        break;
    case 'uploadfile':
    default:
        $config = array(
            "pathFormat" => $CONFIG['filePathFormat'],
            "maxSize" => $CONFIG['fileMaxSize'],
            "allowFiles" => $CONFIG['fileAllowFiles']
        );
        $fieldName = $CONFIG['fileFieldName'];
        break;
}



		$auth = new \Qiniu\Auth($QINIU_ACCESS_KEY, $QINIU_SECRET_KEY);
		$token = $auth->uploadToken($BUCKET);
		$Config=new \Qiniu\Config();
		
		
		$qn = new \Qiniu\Storage\FormUploader();
		//file_put_contents('ab.txt',var_export($Config,true));
		//foreach($_FILES as $key=>$val){
			//copy($_FILES[$key]['tmp_name'],'./Uploads/'.basename($_FILES[$key]['name']));
			
		//}
        copy($_FILES[$fieldName]['tmp_name'],'../../../../Uploads/'.basename($_FILES[$fieldName]['name']));
		//$this->success('上传成功！');
		list($ret, $err) = $qn->putFile($token, null, $_FILES[$fieldName]['tmp_name'],$Config);
		//file_put_contents('a.txt',var_export($ret,true));
		
		
		
		
		if ($err != null) {
			//echo "上传失败。错误消息：".$err->message();
			$url=$HOST.'/'.$ret["key"];
			/*构建返回数据格式*/
			$FileInfo = array(
                      "state" => "SUCCESS",         
                      "url"   => $url,           
                      "title" => $ret['key'],         
                      "original" => $_FILES[$fieldName]['name'],       
                      "type" => $_FILES[$fieldName]['type'],            
                      "size" => $_FILES[$fieldName]['size'],           
                  );
			return json_encode($FileInfo);
		}else{
			//echo "上传成功。Key：".$ret["key"];

		}	




//$up = new Uploader($fieldName, $config, $base64);

/**
 * 得到上传文件所对应的各个参数,数组结构
 * array(
 *     "state" => "",          //上传状态，上传成功时必须返回"SUCCESS"
 *     "url" => "",            //返回的地址
 *     "title" => "",          //新文件名
 *     "original" => "",       //原始文件名
 *     "type" => ""            //文件类型
 *     "size" => "",           //文件大小
 * )
 */

/* 返回数据 */
//return json_encode($up->getFileInfo());
