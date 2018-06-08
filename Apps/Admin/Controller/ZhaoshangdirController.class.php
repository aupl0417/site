<?php
/**
* 此文件为根据表单模板创建
*/
namespace Admin\Controller;
use Think\Controller;
class ZhaoshangdirController extends CommonModulesController {
	protected $name 			='招商目录';	//控制器名称
    protected $formtpl_id		=178;			//表单模板ID
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
    	$this->_category(2);

        //列表字段
        $btn=array('title'=>'操作','type'=>'html','html'=>'<a href="'.__CONTROLLER__.'/edit/id/[id]" class="btn btn-sm btn-info btn-rad btn-trans btn-block m0">设置</a> ','td_attr'=>'width="100" class="text-center"','norder'=>1);
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
        $_POST['cred_flagship']     = $this->_post_cmp('cred_flagship');
        $_POST['cred_franchised']   = $this->_post_cmp('cred_franchised');
        $_POST['cred_exclusive']    = $this->_post_cmp('cred_exclusive');
        $_POST['cred_personal']     = $this->_post_cmp('cred_personal');
        $_POST['cred_individual']   = $this->_post_cmp('cred_individual');

        if(empty($_POST['cred_flagship'])) $this->ajaxReturn(['status' => 'warning','msg' => '请设置旗舰店资质！']);
        if(empty($_POST['cred_franchised'])) $this->ajaxReturn(['status' => 'warning','msg' => '请设置专营店资质！']);
        if(empty($_POST['cred_exclusive'])) $this->ajaxReturn(['status' => 'warning','msg' => '请设置专卖店资质！']);
        if(empty($_POST['cred_personal'])) $this->ajaxReturn(['status' => 'warning','msg' => '请设置个人店资质！']);
        if(empty($_POST['cred_individual'])) $this->ajaxReturn(['status' => 'warning','msg' => '请设置个体店资质！']);

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
	public function active_change_select(){
		$result=$this->_active_change_select();

		$this->ajaxReturn($result);		
	}

	/**
	* 排序
	*/
	public function setsort(){
		$result=$this->_setsort();
		$this->ajaxReturn($result);
	}

	/**
	* 类目转移
	*/
	public function sid_change_select(){
		$result=$this->_sid_change_select();
		$this->ajaxReturn($result);
	}


    /**
     * 处理资质选项
     */
    public function _post_cmp($field){
        $tmp  = I('post.'.$field);

        if(empty($tmp)) return;

        return serialize($tmp);
    }

    /**
     * 店铺资质
     */
    public function cred(){
        $shop_type = ['','cred_flagship','cred_franchised','cred_exclusive','cred_personal','cred_individual'];
        $this->assign('shop_type',$shop_type[I('get.index')]);

        //$cred = M('zhaoshang_cred')->where(['status' => 1,'type' => 3,'_string' => 'find_in_set ('.I('get.cid').',category_id)'])->field('atime,etime,ip',true)->select();

        $category = array(
            //array('id' => 1,'name' => '公司资质'),
            array('id' => 4,'name' => '会员资质'),
            array('id' => 2,'name' => '品牌资质'),
            array('id' => 3,'name' => '行业资质'),
        );

        foreach ($category as $key => $val){
            switch($val['id']){
                case 4:
                    $cred = M('zhaoshang_cred')->where(['status' => 1,'type' => 4])->field('atime,etime,ip',true)->select();
                    break;
                case 2:
                    $cred = M('zhaoshang_cred')->where(['status' => 1,'type' => 2])->field('atime,etime,ip',true)->select();
                    break;
                case 3:
                    $cred = M('zhaoshang_cred')->where(['status' => 1,'type' => 3,'_string' => 'find_in_set ('.I('get.cid').',category_id)'])->field('atime,etime,ip',true)->select();
                    break;
            }

            $category[$key]['dlist'] = $cred;
        }

        $this->assign('category',$category);

        //dump($category);

        $do=M($this->fcfg['table']);
        $rs=$do->where('id='.I('get.cid'))->find();

        if($rs['cred_flagship']){
            $rs['cred_flagship'] = unserialize(html_entity_decode($rs['cred_flagship']));
        }
        if($rs['cred_franchised']){
            $rs['cred_franchised'] = unserialize(html_entity_decode($rs['cred_franchised']));
        }
        if($rs['cred_exclusive']){
            $rs['cred_exclusive'] = unserialize(html_entity_decode($rs['cred_exclusive']));
        }
        if($rs['cred_personal']){
            $rs['cred_personal'] = unserialize(html_entity_decode($rs['cred_personal']));
        }
        if($rs['cred_individual']){
            $rs['cred_individual'] = unserialize(html_entity_decode($rs['cred_individual']));
        }
        //dump($rs);
        $this->assign('rs',$rs);

        $this->display();
    }

}