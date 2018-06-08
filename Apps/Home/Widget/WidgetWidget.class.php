<?php
// 本类由系统自动生成，仅供测试用途
namespace Home\Widget;
use Think\Controller;
class WidgetWidget extends Controller {
	public function create_form($param=array()){
		$param['tpl']=$param['tpl']?$param['tpl']:'vform';
		//dump($param['tpl']);
		$this->assign('param',$param);
		$this->display('Widget:'.$param['tpl']);
	}

	public function form_group($param=array()){
		$this->assign('param',$param);
		//dump($param);
		$this->display('Widget:'.$param['tpl']);
	}
	
	public function checkbox_more($param=array()){
		$param['tpl']=$param['tpl']?$param['tpl']:'checkbox_more';
		$this->assign('vo',$param['vo']);
		$this->assign('data',$param['data']);

		$this->display('Widget:'.$param['tpl']);
	}

	//分类格式化为select、radio、checkbox选项
	public function list2form($param=array()){
		$result=list2form($param);
		echo $result;
	}

	//数据表字段输出为表单select、radio、checkbox选项
	public function table_field_form($param=array()){
		$table=strtolower($param['table']);

		$do=D('FieldView');
		$map['active']=1;
		$map['table_name']=$table;
		$list=$do->where($map)->order('tables_field.sid asc,tables_field.sort asc,tables_field.id asc')->select();

		//echo $do->getLastSQL();
		
		$html=$param['first'];
		foreach($list as $val){
			$html.='<option value="'.$val['name'].'" '.($param['value']==$val['name']?'selected':'').'>'.$val['title'].'</option>';
		}

		echo $html;

	}
	

	public function js(){
		$path=RUNTIME_PATH.'js';
		@mkdir($path);

		$cache_name=MODULE_NAME.'-'.CONTROLLER_NAME.'-'.ACTION_NAME;
		$filename=md5($cache_name).'.js';
		//echo $filename;
		
		$dir[]='Public/Jquery/';
		$url[]=array(
			'jQueryRedactor/lib/jquery-1.7.min.js',
			'jquery-validation/js/jquery.validate.min.js',
			'artTemplate/dist/template.js',
			'toastr/toastr.js',
			'jquery.form.js',
			'jquery.SuperSlide.2.1.1.js',
			'jquery.lazyload.min.js',
			'scrollto/jquery.scrollTo.js',
		);	
	
		$dir[]='Public/CSS/flatdream/';
		$url[]=array(
			'js/jquery.cookie/jquery.cookie.js',
			'js/jquery.nanoscroller/jquery.nanoscroller.js',
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

		$js=F($cache_name);
		//$js='';
		if(empty($js)){
			$js='var sub_domain='.json_encode(C('sub_domain')).';';
			foreach($url as $key=>$val){
				foreach($val as $v){
					if(file_exists($dir[$key].$v)==true){
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

					}
				}
			}

			//file_put_contents($path.'/'.$filename,$js);
			F($cache_name,$js);
		}

		if(!file_exists($path.'/'.$filename)){
			file_put_contents($path.'/'.$filename,$js);
		}
		echo '<script src="/Apps/Runtime/js/'.$filename.'"></script>';

	}
	
	

                                        
}