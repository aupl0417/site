<?php
namespace Common\Widget;
use Think\Controller;
class ScriptWidget extends Controller {
    public function js(){
		$path=RUNTIME_PATH.'js';
		@mkdir($path);

		$cache_name=MODULE_NAME.'-'.CONTROLLER_NAME.'-'.ACTION_NAME;
		$filename=md5($cache_name).'.js';
		//echo $filename;

		$dir[]='Public/Jquery/';
		$url[]=array(
			'jQueryRedactor/lib/jquery-1.7.min.js',
			'jquery-validation/js/jquery.validate.js',
            'jquery-validation/js/localization/messages_zh.js',
			'artTemplate/dist/template.js',
			'toastr/toastr.js',
			'jquery.form.js',
			//'jquery.cookie.js',
		);	
		$dir[]='Public/CSS/flatdream/';
		$url[]=array(
			'js/jquery.gritter/js/jquery.gritter.js',
			'js/jquery.icheck/icheck.min.js',
			'js/bootstrap/dist/js/bootstrap.min.js',
			'js/jquery.niftymodals/js/jquery.modalEffects.js',
			'js/jquery.magnific-popup/dist/jquery.magnific-popup.min.js',
			'js/jasny.bootstrap/extend/js/jasny-bootstrap.min.js',
			
		);

		$dir[]='Public/Apps/';
		$url[]=array(
			'global.js',
		);



		$dir[]='Public/Apps/'.MODULE_NAME.'/';
		$url[]=array(
			'global.js',
		);

		$dir[]='Public/Apps/'.MODULE_NAME.'/'.CONTROLLER_NAME.'/';
		$url[]=array(
			ACTION_NAME.'.js',
		);

		//$js=F($cache_name);
		$js='';
		if(empty($js)){
			//$js='var sub_domain='.json_encode(C('sub_domain')).';';
			foreach($url as $key=>$val){
				foreach($val as $v){
					if(file_exists($dir[$key].$v)==true){
						/*
						$body=trim(compress(file_get_contents($dir[$key].$v)));

						$tmp=explode(chr(13).chr(10),$body);
						$file=array();
						foreach($tmp as $l){
							$l=trim($l);
							if($l!=''){
								$file[]=$l;
							}
						}

						//file_put_contents('mobile/'.basename($v),implode(chr(13).chr(10),$file));

						$js.=implode(chr(13).chr(10),$file);
						*/
						$js.='<script src="/'.$dir[$key].$v.'"></script>';
					}
				}
			}

			//file_put_contents($path.'/'.$filename,$js);
			//F($cache_name,$js);
		}

		/*
		if(!file_exists($path.'/'.$filename)){
			file_put_contents($path.'/'.$filename,$js);
		}
		*/
		//echo '<script src="/Runtime/js/'.$filename.'"></script>';
		echo $js;

	}
    
    public function css() {
        
    }
}