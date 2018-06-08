<?php
/**
* 此文件为根据表单模板创建
*/
namespace Admin\Controller;
use Think\Controller;
class ZhaoshangupdateController extends CommonModulesController {
	protected $name 			='店铺升级';	//控制器名称
    protected $formtpl_id		=192;			//表单模板ID
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
        $btn=array('title'=>'操作','type'=>'html','html'=>'<a href="'.__CONTROLLER__.'/edit/id/[id]" class="btn btn-sm btn-info btn-rad btn-trans btn-block m0">修改</a> <div data-url="'.__CONTROLLER__.'/view/id/[id]" data-id="[id]" class="btn btn-sm btn-primary btn-rad btn-trans btn-block m0 btn-view">审核</div>','td_attr'=>'width="100" class="text-center"','norder'=>1);
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
		# $result=$this->_add_save();

		# $this->ajaxReturn($result);
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
		# $result=$this->_delete_select();
		# $this->ajaxReturn($result);
	}

	public function view(){
        $do = D('Zhaoshangupgrade192Relation');
        $rs = $do->relation(true)->where(['id' => I('get.id')])->find();

        $area = $this->cache_table('area');
        $rs['province'] = $area[$rs['province']];
        $rs['city']     = $area[$rs['city']];
        $rs['district'] = $area[$rs['district']];
        $rs['town']     = $area[$rs['town']];

        if($rs['brand']) $rs['brand'] = unserialize(html_entity_decode($rs['brand']));
        if($rs['brand_cred']) $rs['brand_cred'] = unserialize(html_entity_decode($rs['brand_cred']));
        if(isset($rs['brand_cred']['edit'])) unset($rs['brand_cred']['edit']);

        foreach($rs['brand_cred'] as $key => $val){
            foreach($val['cred'] as $k => $v){
                $tmp['images']   = explode(',',$v);
                $tmp['cred']     = M('zhaoshang_cred')->cache(true)->where(['id' => $k])->field('atime,etime,ip',true)->find();
                //dump($tmp);
                $rs['brand_cred'][$key]['cred'][$k] = $tmp;
            }
        };

        if($rs['industry_cred']) $rs['industry_cred'] = unserialize(html_entity_decode($rs['industry_cred']));
        if(isset($rs['industry_cred']['edit'])) unset($rs['industry_cred']['edit']);
        //dump($rs['industry_cred']);
        //dump($rs['brand_cred']);
        $res = $this->doApi('/Zhaoshang/get_industry_cred',['shop_type_id' => $rs['shop_type_id'],'second_category' => $rs['second_category']],'',1);
        $cred = $res['data'];

        foreach($cred as $key => $val){
            foreach($rs['industry_cred'] as $k => $v){
                if($key == $k) {
                    //dump($k);
                    if($v) $cred[$key]['cred_images'] = explode(',',$v);
                }
            }
        }
        $this->assign('cred',$cred);

        $rs['logs'] = M('zhaoshang_upgrade_logs')->where(['zhaoshang_upgrade_id' => I('get.id')])->order('id desc')->select();
        foreach($rs['logs'] as $key => $val){
            if($val['content']) $rs['logs'][$key]['content'] = unserialize(html_entity_decode($val['content']));
        }
        //dump($rs['logs']);

        //店铺是否存在同名
        $shop_name = $rs['shop_name'].$rs['shop_type']['type_name'];
        if(M('shop')->where(['uid' => ['neq',$rs['uid']],'shop_name' => $shop_name])->getField('id')){
            $this->assign('is_same',1);
        }

        $this->assign('rs',$rs);
	    $this->display();
    }

	/**
     * 审核日志
     */
    public function logs_add(){
        $rs = $this->do->where(['id' => I('post.zhaoshang_upgrade_id')])->field('id,uid,status,step,mobile,shop_type_id,shop_name,first_category,second_category')->find();

        if($rs['status'] != 1){
            $this->ajaxReturn(['status' => 'warning','msg' => '当前状态下不充许执行此审核操作！',$rs]);
        }

        $data['status'] = 2;    //拒绝审核
        if(I('post.step_1') == 1 && I('post.step_2') == 1 && I('post.step_3') == 1 && I('post.step_4') == 1){
            # 直接开店成功
            $data['status'] = 5;    //通过审核
        }

        $tmp = I('post.');
        unset($tmp['remark']);

        $data['content']    = serialize($tmp);
        $data['a_uid']      = session('admin.id');
        $data['remark']     = I('post.remark');
        $data['zhaoshang_upgrade_id']     = I('post.zhaoshang_upgrade_id');
        $do = D('Common/ZhaoshangUpgradeLogs');

        $do->startTrans();

        if($sw[] = false === M('zhaoshang_upgrade')->where(['id' => I('post.zhaoshang_upgrade_id')])->save(['status' => $data['status'],'dotime' => date('Y-m-d H:i:s')])) goto error;
        if($sw[] = false === $do->create($data)) goto error;
        if($sw[] = false === $do->add()) goto error;
        
        if($data['status'] == 5){
            # 更改店铺信息
            $shop_name = (string) M('shop_type')->where(['id' => $rs['shop_type_id']])->getField('type_name');
            if($sw[] = false === M('shop')->where(['uid' => $rs['uid']])->data(['shop_name' => $rs['shop_name'] . $shop_name,'type_id' => $rs['shop_type_id'],'category_id' => $rs['first_category'],'category_second' => $rs['second_category']])->save()) goto error;
            # 用户表shop_type
            if($sw[] = false === M('user')->where(['id' => $rs['uid']])->data(['shop_type' => $rs['shop_type_id']])->save()) goto error;
        }
        
        $do->commit();

        //发送短信通知
        if(I('post.is_sms') == 1){
            $tpl_id = 22;   //短信模板ID
            if($data['status'] == 5) $tpl_id = 23;

            $sms_data['mobile'] = $rs['mobile'];
            # $sms_data['mobile'] = 18377502575;
            $sms_data['content'] = $this->sms_tpl($tpl_id);
            sms_send($sms_data);
        }

        $this->ajaxReturn(['status' => 'success','msg' => '操作成功！']);

        error:
            $do->rollback();
            $this->ajaxReturn(['status' => 'warning','msg' => '操作失败！']);
    }

	/**
	* 批量更改状态
	*/
	public function active_change_select($param=null){
		$result=$this->_active_change_select();

		$this->ajaxReturn($result);
	}
}
