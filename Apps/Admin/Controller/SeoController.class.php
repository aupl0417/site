<?php
/**
* 此文件为根据表单模板创建
*/
namespace Admin\Controller;
use Think\Controller;
class SeoController extends CommonModulesController {
	protected $name 			='SEO管理';	//控制器名称
    protected $formtpl_id		=57;			//表单模板ID
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
	 * 设置默认
	 */
	public function set_default(){
		$sid = M('config_sort')->cache(true)->where(['ac' => 'seo'])->getField('id');
		$tmp = M('config')->where(['sid' => $sid])->getField('name,value');

		foreach($tmp as $key => $val){
			$data[$key] = $val;
		}

		$this->assign('data', $data);
		$this->display();
	}

	/**
	 * 保存默认修改
	 */
	public function set_default_save(){
		$sid = M('config_sort')->cache(true)->where(['ac' => 'seo'])->getField('id');

		$do = M('config');
		$do->where(['sid' => $sid,'name' => 'title'])->setField('value',I('post.title'));
		$do->where(['sid' => $sid,'name' => 'keywords'])->setField('value',I('post.keywords'));
		$do->where(['sid' => $sid,'name' => 'description'])->setField('value',I('post.description'));

		$this->ajaxReturn(['status' => 'success','msg' => '设置成功！']);

	}

	/**
	 * 百度链接推送
	 */
	public function baidu_url(){
		$lists = $this->doApi('/BaiduUrlPush/lists','','',1);

		$this->assign('list', $lists['code'] == 1 ? $lists['data'] : []);
		$this->display();
	}

	/**
	 * 推送百度链接
	 */
	public function pushUrl($type, $p = 0){
		$result = $this->doApi('/BaiduUrlPush/push',['type' =>$type,'p'=>$p],'',1);
		# print_r($result);exit;
		if(isset($result['p'])){
			// if($result['p'] == 2){
			// 	echo $result['p'];exit;
			// }
			$url = U('/Seo/pushUrl',['type' => $type,'p' => $result['p']]);
			echo '<script>window.location.href="' . $url . '"</script>';
			// $this->redirect('pushUrl',['type' => $type,'p' => $result['p']]);
		}else{
			echo '更新完成,打开百度站长查看是否推送成功';
		}
	}

	/**
	 * 百度链接直接推送
	 */
	public function pushUrlZhiJie(){
		$urls = I('post.urls');
		if(!$urls){
			$this->ajaxReturn(['status' => 'warning','msg' => "推送失败，请输入正确的链接!"]);
		}
		$result = $this->doApi('/BaiduUrlPush/zhijie',['urls' =>$urls],'',1);
		if($result['code'] == 1){
			$this->ajaxReturn(['status' => 'success','msg' => "推送成功",'result' => $result]);
		}else{
			$this->ajaxReturn(['status' => 'warning','msg' => "推送失败",'result' => $result]);
		}
	}


	/**
	 * sitemap接口调用
	 */
	public function sitemap(){
	    $this->display();
	}


	/**
	 * 生成前段链接sitemap
	 */
	public function get_sitemap(){
        $res  = $this->doApi('/Sitemap/sitemap');
        if ($res->code ==1){
    	    $this->ajaxReturn(['status' => 'success','msg' => '请求成功!']);
        }else{
            $this->ajaxReturn(['status' => 'warning','msg' => "生成链接失败!"]);
        }
	}


	public function up_sitemap(){
		$res  = $this->doApi('/Sitemap/up_sitemap');
		if ($res->code == 1){
    	    $this->ajaxReturn(['status' => 'success','msg' => '请求成功!']);
        }else{
            $this->ajaxReturn(['status' => 'warning','msg' => "更新失败"]);
        }
	}



}
