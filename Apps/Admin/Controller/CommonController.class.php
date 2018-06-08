<?php
namespace Admin\Controller;
use Think\Controller;
class CommonController extends Controller {
	protected $api_url	='';		//API请求地址前缀
	protected $api_cfg 	=array(); 	//API接口配置
    protected $token;               //接口授权token
    protected $sw;                  //记录事务处理结果
    protected $admin_sid    = array(1,100810427);  //管理员组ID
	public function _initialize() {
		
		//保持用户登录状态
		$cookie_user=cookie('admin');
		if($cookie_user && empty($_SESSION['admin']['id'])){
			$jm=new \Think\Crypt\Driver\Crypt();
			$uid=$jm::decrypt($cookie_user['uid'],C('CRYPT_PREFIX'));

			$do=D('AdminView');
			$rs=$do->where(array('id'=>$uid))->find();
			session('admin',$rs);
			cookie('admin',array('uid'=>$cookie_user['uid']));		
		}

	
		//检测登录
		$this->check_login();
		C('sub_domain',sub_domain());
		
		
		//$admin=S('admin');
		if(empty($admin)){
			$do=D('AdminView');
			$admin=$do->where(array('id'=>session('admin.id')))->find();			
			//S('admin',$admin);
		}		
		C('admin',$admin);
		$this->assign('admin',$admin);
		
		//dump($admin);
		//操作权限
		$this->check_action();

        //保持在线状态
        $this->online_update();
		

		
		//设置
		$do=D('Config');
		$cfg=$do->config(array('cache_name'=>'cfg'));
		$cfg['api']['apiurl']	= DM('rest');
		C('cfg',$cfg);

		//api接口初始信息
		$tmp=C('cfg.api');
		unset($tmp['apiurl']);
		$this->api_cfg=$tmp;
		$this->api_url=C('cfg.api')['apiurl'];
		//dump($cfg);

		//加载错误代码库
		//S('error_code',null);
		$error_code=D('Common/ErrorCode')->error_code();
		C('error_code',$error_code);

        //订单状态代码
        $do=D('Common/OrdersCodeRelation');
        $orders_code=$do->relation(true)->cache('orders_code')->field('id')->select();
        $norders_code=array();
        foreach($orders_code as $val){
            $norders_code[$val['id']]=$val['orders_code'];
        }
        C('orders_code',$norders_code);

        //兼容RestFull V2.0接口
        $res = $this->doApi2('/Auth/token',$this->api_cfg);
        if($res['code'] == 1){
            $this->token = $res['data']['token'];
        }

	}
	
	//检测用户是否登录
	public function check_login(){
	    //$online_admin = S('online_admin');
		//if(empty($_SESSION['admin']) || empty($online_admin[$_SESSION['admin']['id']])) {
        if(empty($_SESSION['admin'])) {
			$js='<script>top.location.href="/Login";</script>';
			echo $js;
            exit();
		}
		/*
		else{
		    $status = S('admin_login_'.session('admin.username'));
            if(empty($status)){
                $js='<script>top.location.href="/Login";</script>';
                echo $js;
                exit();
            }
        }
		*/
	}
	
	//验证操作权限
	public function check_action(){
		$action=array();
		if(C('admin.action')) $action=eval(html_entity_decode(C('admin.action')));

		//dump(C('admin.action'));
		
		$do=M('controller');
		$rs=$do->where(array('controller'=>CONTROLLER_NAME))->field('controller,action')->find();
		//dump($rs);
		//echo $do->getLastSQL();
		if($rs){
			$action_arr=array();
			if($rs['action']) $action_arr=eval(html_entity_decode($rs['action']));
			//dump($action_arr);
			if($action_arr[ACTION_NAME]){
				$is_ajax=false;
				$is_action=false;
				foreach($action_arr[ACTION_NAME] as $val){
					if(in_array(CONTROLLER_NAME.':'.$val,$action)) $is_action=true;
					if($val=='Ajax') $is_ajax=true;
				}
				
				//echo $is_ajax.'<br>';
				//echo $is_action;
				
				if($is_action==false){
					if($is_ajax==true) $this->ajaxReturn(array('status'=>'warning','msg'=>'您无权限执行此操作！'));
					else {
						$this->display('Index:error');
						exit;
					}
				}
			}
	
			
		}
		
	}


	/**
	* 获取数据表
	*/
	public function get_tables(){
		$do=\Think\Db::getInstance();
		return $do->getTables();
	}

	/**
	* 获取字段
	* @param string $table
	*/
	public function get_fields($table){
		$do=\Think\Db::getInstance();
		//$table=C('DB_PREFIX').$table;
		return $do->getFields($table);
	}


	/**
	* 错误return
	* @param string $status  状态，success|warning
	* @param string $msg 	提示信息
	*/
	public function errorReturn($status,$msg){
		$this->ajaxReturn(array('status'=>$status,'msg'=>$msg));
	}

	/**
	* 表单模板初始化
	*/
	public function _initform(){
    	//取表单模板
    	if(!$formtpl=M('formtpl')->where(array('id'=>$this->formtpl_id))->field('id,tables,action_type,fields,pagesize,list_fields,order,search_col,export_fields')->find()){
    		E('获取不到表单模板信息！');
    	}
    	$this->fcfg=$formtpl;
    	$this->fcfg['order']			=$formtpl['order']?$formtpl['order']:'id desc';
    	$this->fcfg['table']			=substr($formtpl['tables'],strlen(C('DB_PREFIX')));	//当前操作数据表
    	$this->fcfg['modelname']		=$this->fcfg['table'];	//当然操作模型
    	$this->fcfg['verify_model']		=ucfirst(str_replace('_','',$this->fcfg['table'])).$this->formtpl_id;		//验证模型
    	$this->fcfg['view_model']		=$this->fcfg['verify_model'].'View';		//视图模型
    	$this->fcfg['relation_model']	=$this->fcfg['verify_model'].'Relation';	//关联模型

    	if(I('get.orderby')){
    		$this->fcfg['order']=str_replace('-', ' ', I('get.orderby'));
    	}
    	switch ($formtpl['action_type']) {
    		case 1:
    			$do='D';
    			$this->fcfg['modelname']=$this->fcfg['view_model'];
    			break;
    		
    		case 2:
    			$do='D';
    			$this->fcfg['modelname']=$this->fcfg['relation_model'];
    			break;
    		
    		default:
    			$do='M';
    			break;
    	}
    	$this->fcfg['do']=$do;
    	$this->do=$do($this->fcfg['modelname']);

    	$do=M('formtpl_fields');
    	$fields=$do->where(array('formtpl_id'=>$this->formtpl_id))->field('atime,etime,ip',true)->order('sort asc')->select();
    	$this->fcfg['allfields']=$fields;
    	
    	foreach($fields as $val){
			//html转码
			foreach($val as $vkey=>$v){
				if($v) $val[$vkey]=html_entity_decode($v);
			}
    		if($val['is_list']) {
    			//$select_fields[]=$val['name'];
    			$list_fields[]=array('label'=>$val['label'],'name'=>$val['name']);
    		}
    		if($val['is_search']) $this->fcfg['search_fields'][$val['name']]=$val['label'];
    		if($val['active']) $this->fcfg['form_fields'][]=$val;
    		if($val['is_verify'] && $val['active']) $this->fcfg['verify_fields'][]=$val;
    		$allfields[]=$val['name'];
    	}




        //列表字段格式字段
        if($formtpl['list_fields']) $formtpl['list_fields']=eval(html_entity_decode($formtpl['list_fields']));

        if(empty($formtpl['list_fields'])){
        	$formtpl['list_fields']=$list_fields;
        	//dump($formtpl['list_fields']);    
        }
        $this->fcfg['list_fields']=$formtpl['list_fields'];

        //dump($this->fcfg['list_fields']);

        //SQL列表字段
        foreach($this->fcfg['list_fields'] as $val){
        	$select_fields[]=$val['name'];
        }
    	$this->fcfg['fields']=@implode(',', $select_fields);

    	$this->assign('fcfg',$this->fcfg);
    	C('fcfg',$this->fcfg);
    	//dump($this->fcfg);

    	//搜索表单
        $this->map=$this->sql_where();

        //dump($this->map);
	}

	/**
	* $_POST数据处理
	*/
	public function post_cmp(){
		if(IS_POST){
			/*
			foreach($_POST as $key=>$val){
				//处理checkbox为空时
				if(substr($key,0,10)=='_checkbox_'){
					if(empty($_POST[substr($key,10)])) $_POST[substr($key,10)]='';
					unset($_POST[$key]);
				}

				//radio-switch处理
				if(substr($key,0,14)=='_radio_switch_'){
					if(empty($_POST[substr($key,14)])) $_POST[substr($key,14)]='0';
					unset($_POST[$key]);
				}

				//密码处理
				if(substr($key,0,10)=='_password_'){
					//echo $key.'<br>';
					if($_POST[substr($key,10)]!=$val || (empty($val) && !empty($_POST[substr($key,10)]))) $_POST[substr($key,10)]=md5($_POST[substr($key,10)]);
					unset($_POST[$key]);
				}
			}
			*/

			foreach($this->fcfg['form_fields'] as $val){
				if($val['fun_before'] && isset($_POST[$val['name']])){
					$_POST[$val['name']]=eval($val['fun_before']);
				}else{
					switch($val['formtype']){
						case 'checkbox':
							if(empty($_POST[$val['name']])) $_POST[$val['name']]='';
							else $_POST[$val['name']]=implode(',',$_POST[$val['name']]);
						break;
						case 'password':
							if($_POST[$val['name']] && $_POST[$val['name']]!=$_POST['_password_'.$val['name']]) $_POST[$val['name']]=md5($_POST[$val['name']]);
						break;
					}
				}
			}
		}
	}


	/**
	* 查询条件
	*/
	public function sql_where(){
		//是否有设置查询表单
		$do=M('formtpl_search_fields');
		$list=$do->cache(true)->where(array('active'=>1,'formtpl_id'=>$this->formtpl_id))->field('atime,etime,ip',true)->order('sort asc')->select();
		if($list){
			$this->assign('search_fields',$list);
			foreach($list as $val){
				if($_REQUEST[$val['name']]!=''){
					if($val['fun_read']) {
						if($tmp=eval(html_entity_decode($val['fun_read']))){
							$_REQUEST[$val['name']]=$tmp;
						}
					}

					if($val['search_type']==1) $map[$val['map_field']?$val['map_field']:$val['name']]=array('like','%'.trim($_REQUEST[$val['name']]).'%');
					else $map[$val['map_field']?$val['map_field']:$val['name']]=trim($_REQUEST[$val['name']]);
				}
			}
		}else{        
	        //varchar 和 text字段用模糊搜索
	        $table_fields=$this->get_fields(C('DB_PREFIX').$this->fcfg['table']);
	        foreach($_REQUEST as $key=>$val){
	        	//if(in_array($key,$allfields) && $val!='') $map[$key]=$val;
	        	if(isset($table_fields[$key]) && trim($val)!=''){
	        		if(strstr($table_fields[$key]['type'],'varchar') || strstr($table_fields[$key]['type'],'text')) $map[$key]=array('like','%'.trim($val).'%');
	        		else $map[$key]=$val;
	        	}
	        }
    	}
    	//通用查询条件
    	//日期区间
    	$day_field=I('get.day_field')?I('get.day_field'):'atime';
    	if(I('sday') && I('eday')) $map[$day_field]=array('between',array(I('sday'),I('eday')));
        if(I('sday') && I('eday')=='') $map[$day_field]=array('egt',I('sday'));
        if(I('sday')=='' && I('eday')) $map[$day_field]=array('elt',I('eday'));

        //数值区间
        $num_field=I('get.num_field')?I('get.num_field'):'';
    	if(I('snum') && I('enum') && $num_field) $map[$num_field]=array('between',array(I('snum'),I('enum')));
        if(I('snum') && I('enum')=='' && $num_field) $map[$num_field]=array('egt',I('snum'));
        if(I('snum')=='' && I('enum') && $num_field) $map[$num_field]=array('elt',I('enum'));

        if(I('q') && I('field')) $map[I('field')]=array('like','%'.trim(I('q')).'%');

        $unset = ['day_field','sday','eday','num_field','snum','enum'];
        foreach ($unset as $item) {
            if(isset($map[$item])) unset($map[$item]);
        }

		return $map;
	}




	/**
	* 列表格式化输出
	*/
	public function plist($first=null,$end=null){
		if(is_null($first) || empty($first)) $result[]=array('title'=>'选择','type'=>'html','html'=>'<input type="checkbox" class="i-red-square" name="id[]" id="id[]" value="[id]">','td_attr'=>'width="60" class="text-center"','norder'=>1);
		else $result[]=$first;

        foreach($this->fcfg['list_fields'] as $val){
        	$result[]=array('title'=>$val['label'],'field'=>$val['name'],'td_attr'=>$val['attr'],'function'=>$val['function']);
        }

        if(is_null($end)) $result[]=array('title'=>'操作','type'=>'html','html'=>'<a href="'.__CONTROLLER__.'/edit/id/[id]" class="btn btn-sm btn-info btn-rad btn-trans btn-block m0">修改</a>','td_attr'=>'width="100" class="text-center"','norder'=>1);
        else $result[]=$end;
		return $result;
	}

    /**
    * 缓存数据表数据
    */
    public function cache_table($table){
        $cache_table=[
            'admin_sort'        =>'id,group_name',
            'api_category'      =>'id,category_name',
            'area'              =>'id,a_name',
            'config_sort'       =>'id,name',
            'express_category'  =>'id,category_name',
            'express_company'   =>'id,sub_name',
            'goods_cfg'         =>'id,cfg_name',
            'help_category'     =>'id,category_name',
            'msg_category'      =>'id,category_name',
            'modules'           =>'id,module_name',
            'news_category'     =>'id,category_name',
            'search_keyword'    =>'id,keyword',
            'shop_type'         =>'id,type_name',
            'shop_notdomain'    =>'id,domain',
            'shop_notname'      =>'id,name',
            'user_level'        =>'id,level_name',
            'goods_category'    =>'id,category_name',
        ];

        $list=S('table_'.$table);
        if(empty($list)){
            $do=M($table);
            $list=$do->cache('table_'.$table,0)->getField($cache_table[$table],true);
        }

        return $list;
    }

    /**
     * 雇员保持在线状态
     */
	public function online_update(){
	    /*
	    $online = M('admin_online')->where(['admin_id' => session('admin.id')])->field('session_id,ip')->find();

        if($online['session_id'] != session_id() || $online['ip'] != get_client_ip()){
            session('admin',null);
            cookie('admin',null);
            $js='<script>top.location.href="/Login";</script>';
            echo $js;
            exit();
        }
	    $res = M('admin_online')->where(['admin_id' => session('admin.id'),'session_id' => session_id()])->save(['etime' => time()]);
        //dump($res);exit();
        //清除长时间未刷新时间的雇员
        M('admin_online')->where(['etime' => ['lt',time() - 120]])->delete();
        //dump(M('admin_online')->getLastSql());
        */
    }
}