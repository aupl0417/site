<?php
/**
* 此文件为根据表单模板创建
*/
namespace Admin\Controller;
use Think\Controller;
class Apidoc2Controller extends CommonModulesController {
	protected $name 			='Rest2接口文档';	//控制器名称
    protected $formtpl_id		=206;			//表单模板ID
    protected $fcfg				=array();		//表单配置
    protected $do 				='';			//实例化模型
    protected $map				=array();		//where查询条件

    /**
    * 初始化
    */
    public function _initialize() {
    	parent::_initialize();

    	//初始化表单模板
    	$this->_initform();

    	//dump($this->fcfg);

    }

    /**
    * 列表
    */
    public function index($param=null){
    	$this->_index();
    	$btn=array('title'=>'操作','type'=>'html','html'=>'<div data-url="'.__CONTROLLER__.'/view/id/[id]" data-id="[id]" class="btn btn-sm btn-primary btn-rad btn-trans btn-block m0 btn-view">详情</div>','td_attr'=>'width="100" class="text-center"','norder'=>1);
        $this->assign('fields',$this->plist(null,$btn));

		$this->display();
    }

    /**
    * 添加记录
    */
    public function add($param=null){
    	$this->display();
    }
	
	/**
	* 保存新增记录
	*/
	public function add_save($param=null){
		$result=$this->_add_save();

		$this->ajaxReturn($result);
	}

	/**
	* 修改记录
	*/
	public function edit($param=null){
		$this->_edit();
		$this->display();
	}

	/**
	* 保存修改记录
	*/
	public function edit_save($param=null){
		$result=$this->_edit_save();

		$this->ajaxReturn($result);
	}

	/**
	* 删除选中记录
	*/
	public function delete_select($param=null){
		$result=$this->_delete_select();
		$this->ajaxReturn($result);
	}

	/**
	* 批量更改状态
	*/
	public function active_change_select($param=null){
		$result=$this->_active_change_select();

		$this->ajaxReturn($result);		
	}

	/**
	 * view
	 */
	public function view($id){
		$one = M('api_doc2')->find($id);
		foreach ($one as $key => $value) {
			$one[$key] = html_entity_decode($value);
		}
		$one['param'] 		= json_decode($one['param'], true);
		$one['other_param'] = json_decode($one['other_param'], true);
		$one['example'] 	= json_decode($one['example'], true);
		$one['category'] 	= M('api_category2')->where(['id' => $one['sid']])->getField('category_name');
		$this->assign('one', $one);
		$this->display();
	}

	public function fileList(){
		$this->assign('fileList',$this->parseDirForList());
		$this->display('file_list');
	}

	/**
	 * 新入库
	 */
	public function create($controller){
		$apis = $this->parseDirForApi($controller);
		$this->assign('apis',$apis);
		$this->display();
	}

	public function create_save(){
		$create = explode("," ,I('post.apis'));
		$apis 	= $this->parseDirForApi(I('controller'));
		$category = I('post.category');
		if(! $category){
			$this->ajaxReturn(['status' => 'warnning', 'msg' => '请选择类目']);	
		}
		$insert = [];
		foreach ($create as $v) {
			$api_url = '/' . str_replace("Controller", "", I('controller')) . '/' . $v;
			if(isset($apis[$api_url])){
				$insert[] = array_merge($apis[$api_url],['sid' => $category]);	
			}
		}
		$data = [];
		foreach ($insert as $value) {
			$value['param'] 	= $value['param'] ? $value['param'] : [];
			$value['content'] 	= $value['content'] ? $value['content'] : '';
			$data[] = array(
				'status' 		=> 1,
				'title'			=> (string) $value['title'],
				'api_url'		=> (string) $value['api_url'],
				'param'			=> json_encode($value['param']),
				'content'		=> $value['content'],
				'ip'			=> get_client_ip(),
				'sid'			=> (int)$value['sid'],
				'other_param' 	=> json_encode($value['other_param'] ? $value['other_param'] : []),
				'example' 		=> $this->apiexample($value['api_url'],$value['param']),
			);
		}
		M('api_doc2')->addAll($data);
		S(md5('rest2-module-apis-lbzy'), null);
		$this->ajaxReturn(['status' => 'success', 'msg' => '操作成功。']);
	}


	/**
	 * 生成例子
	 */
	private function apiexample($api_url,$param){
		$example 	= [];
		$explame[] 	= '$api = "' . $api_url . '";';
		$explame[] 	= '$data = array();';
		if($api_url != '/Auth/token') $explame[] = '$data["token"] = "your token";';
		$nfield = [];
		foreach ($param as $value) {
			$explame[] = '$data["' . $value['name'] . '"] = ' . $this->apiParseType($value['type']) . ';';
			if(! $value['need']){
				$nfield[] = $value['name'];
			}
		}
		$explame[] = '$nfield = "' . implode(",", $nfield) . '";';
		$explame[] = '$return_array = true;';
		$explame[] = '$res = $this->doApi2($api, $data, $nfield, $return_array);';
		$explame[] = 'dump($res);';
		return json_encode($this->apiParseAlign($explame));
	}

	/**
	 * 根据类型生成测试数据
	 */
	private function apiParseType($type){
		switch ($type) {
			case 'string':
				return '"test string"';
				break;
			case 'int':
				return 123456;
				break;
			case 'array':
				return '"array()"';
				break;
			case 'number':
				return '1.2345';
				break;
			case 'bool':
				return 'false';
				break;
			default:
				return '""';
				break;
		}
	}

	/**
	 * 对齐例子
	 */
	private function apiParseAlign($example){
		$max = 0;
		foreach ($example as $key => $value) {
			if(strpos($value, "= ")){
				$length = strpos($value, "=");
				if($length > $max) $max = $length;
			}
		}
		$jiange = ($max) % 4;
		switch ($jiange) {
			case 0:
				$max += 4;
				break;
			case 1:
				$max += 3;
				break;
			case 2:
				$max += 2;
				break;
			case 3:
				$max += 1;
				break;
			default:
				# code...
				break;
		}
		foreach ($example as $key => $value) {
			if($jiange = strpos($value, "= ")){
				$blank = "";
				for($i = $max - $jiange;$i > 0 ;$i--){
					$blank .= " ";
				}
				$example[$key] = str_replace("= ", $blank . "= ", $value);
			}
		}
		return $example;
	}

	/**
	 * 选择类目
	 */
	public function choose_category(){
		$list = M('api_category2')->where(['status' => 1])->order('sort asc')->select();
		# var_dump(arrayTrees($list,0,'id','sid'));
		$this->assign('category', arrayTrees($list,0,'id','sid'));
		$this->display();
	}

	private function parseDirForList(){
		$module = 'Rest2';
		$files 	= scandir(APP_PATH . $module . '/Controller');
		$list 	= [];
		foreach($files as $key => $file){
			if(strpos($file, 'Controller.class.php')){
				$controller = trim(str_replace('.class.php', '', $file));#aaaController
				$like 		= "/" . str_replace("Controller", "", $controller) . "/%";
				$list[] = array(
					'controller' 	=> $controller,
					'insert'		=> count($this->parseDirForApi($controller)),
					'update' 		=> count(M('api_doc2')->where(['api_url' =>['like',$like],'sid' =>['gt',0]])->field('api_url')->select()),
				);
			}
		}
		return $list;
	}

	/**
	 * 解析目录找出api列表
	 */
	private function parseDirForApi($controller){
		$module = 'Rest2';
		
		$apis 	= [];
		$dbapi	= [];
		foreach (M('api_doc2')->where(['sid' => ['gt' , 0]])->field('api_url')->select() as $value) {
			$dbapi[] = $value['api_url'];
		}

		# 通过反射类获得方法列表
		$class 		= $module .'\\Controller\\' . $controller;# Rest2\Controller\***Controller
		$methods 	= (new \ReflectionClass($class))->getMethods();

		foreach ($methods as $method){
			# 方法中的class是否是自己，排除继承的父类中的方法等
			if($class == $method->class){
				$api_url = '/' . str_replace("Controller", "", $controller) . '/' . $method->name;
				# 是否已经存在数据库
				if(false == in_array($api_url, $dbapi)){
					# echo $api_url;
					# 获取注释解析结果
					$api = $this->parseApiMethodDoc($method->getDocComment());
					if($api){
						$api['api_url'] = $api_url;
						$api['action']	= $method->name;
						$apis[$api_url] = $api;
					}
				}
			}
		}
			
		
		return $apis;
	}

	/**
	 * 解析注释
	 */
	private function parseApiMethodDoc($doc){
		if(empty($doc)) return false;
		
		$doc = explode("\n", $doc);
		array_shift($doc);
		array_pop($doc);
		
		$result = [];
		foreach($doc as $line){
			if(strpos($line, ":")){
				$line 	= explode(":", $line);
				$key 	= trim(trim(trim($line[0]), '*'));
				$value 	= trim($line[1]);
				switch ($key) {
					case 'subject':
						$result['title'] 	= $value;
						break;
					case 'api':
						$result['api_url'] 	= $value;
						break;
					case 'content':
						$result['content'] 	= $value;
						break;
					case 'param':
						$value = explode(',', $value);
						$param = array(
							'name' 		=> array_shift($value),
							'type' 		=> array_shift($value),
							'need' 		=> array_shift($value),
							'descript' 	=> implode(",", $value),
						);
						$result['param'][] 	= $param;
						break;
					case 'author':
						$result['other_param']['author'] 	= $value;
						break;
					case 'day':
						$result['other_param']['day'] 		= $value;
						break;
					default:
						break;
				}
			}
		}
		return isset($result['title']) && isset($result['api_url']) ? $result : false;
	}

	/**
	 * 更新
	 */
	public function update($controller){
		$like = '/' . str_replace("Controller", "", $controller) . '/%';
		$list = M('api_doc2')->where(['api_url' => ['like', $like],['sid' => ['gt',0]]])->select();
		$category = M('api_category2')->order('sort asc')->field('id,category_name,sid')->select();
		foreach($category as $value){
			$_category[$value['id']] = $value;
		}
		$this->assign('category',$_category);
		$this->assign('apis',$list);
		$this->display();
	}

	/**
	 * 更新设置
	 */
	public function update_set($id){
		$one = M('api_doc2')->find($id);
		$list = M('api_category2')->where(['status' => 1])->order('sort asc')->select();
		$this->assign('category', arrayTrees($list,0,'id','sid'));
		$this->assign('one', $one);
		$this->display();
	}

	/**
	 * 更新接口
	 */
	public function update_set_save(){
		$post = I('post.');
		if(empty($post['title'])){
			# $this->ajaxReturn(['status' => 'warnning', 'msg' => '接口标题必须填写']);
		}
		if(empty($post['sid'])){
			$this->ajaxReturn(['status' => 'warnning', 'msg' => '接口类目必须选择']);
		}
		if(empty($post['example'])){
			# $this->ajaxReturn(['status' => 'warnning', 'msg' => '接口例子必须填写']);
		}
		$param = array_values(isset($post['param']) ? $post['param'] : []);
		foreach($param as $key => $value){
			# if($value['name'] == '')$this->ajaxReturn(['status' => 'warnning', 'msg' => '第' . ($key + 1) . '行参数名不能为空']);
			# if($value['type'] == '')$this->ajaxReturn(['status' => 'warnning', 'msg' => '第' . ($key + 1) . '行参数类型不能为空']);
			# if($value['need'] == '')$this->ajaxReturn(['status' => 'warnning', 'msg' => '第' . ($key + 1) . '行参数是否必须不能为空']);
		}
		$id = $post['id'];
		$data = array(
			# 'title' 	=> $post['title'],
			# 'content' 	=> $post['content'],
			'sid'		=> $post['sid'],
			# 'param'		=> json_encode($param),
			# 'example'	=> json_encode(explode("\n", $post['example'])),
			# 'return'	=> json_encode(explode("\n", str_replace(" ", "&nbsp;", $post['return']))),
		);
		$result = M('api_doc2')->where(['id' => $id])->data($data)->save();
		if(false !== $result){
			$this->ajaxReturn(['status' => 'success', 'msg' => '更新完成']);	
		}else{
			$this->ajaxReturn(['status' => 'warnning', 'msg' => '更新失败']);
		}
		
	}

	/**
	 * 更新参数、例子、content、title
	 */
	public function update_apis($id = ''){
		set_time_limit(0);
		if(preg_match('/^[0-9]+$/', $id)){
			$list = M('api_doc2')->where(['id' => $id])->field('id,api_url')->select();
		}else{
			$list = M('api_doc2')->field('id,api_url')->select();
		}
		$parseTure = [];
		$parseFalse = [];
		foreach($list as $value){

			$apiUrl = $value['api_url'];

			$api = $this->parseApiurl($apiUrl);
			
			if($api){
				$data = array(
					'title' 	=> $api['title'],
					'content' 	=> $api['content'],
					'param' 	=> json_encode(isset($api['param']) ? $api['param'] : []),
					'example' 	=> $this->apiexample($apiUrl, isset($api['param']) ? $api['param'] : []),
				);
				$res = M('api_doc2')->where(['id' => $value['id']])->data($data)->save();
				
				$parseTure[] = $value['id'];
			}else{
				M('api_doc2')->where(['id' => $value['id']])->data(['sid' => (0 - 1)])->save();
				$parseFalse[] = $value['id'];
			}
		}
		echo '成功解析：',implode(",", $parseTure);
		echo '<hr/>';
		echo '失败解析：',implode(",", $parseFalse);
	}

	private function parseApiurl($api_url){
		$controller = explode("/", trim($api_url,"/"))[0];
		$action 	= explode("/", trim($api_url,"/"))[1];
		
		$class 		= 'Rest2\\Controller\\' . $controller . 'Controller';
		if(method_exists($class, $action) == false){
			return false;
		}
		$method 	= (new \ReflectionMethod($class,$action));

		$doc = $method->getDocComment();
		$api = $this->parseApiMethodDoc($doc);
		if($api){
			return $api;
		}else{
			return false;
		}
	}

}