<?php
/**
* 此文件为根据表单模板创建
*/
namespace Admin\Controller;
use Think\Controller;
class ZhaoshangjoinController extends CommonModulesController {
	protected $name 			='入驻申请';	//控制器名称
    protected $formtpl_id		=179;			//表单模板ID
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
	    $where['map']['status'] = ['not in','3,5'];
		$result=$this->_delete_select($where);
		$this->ajaxReturn($result);
	}

	/**
	* 批量更改状态
	*/
	public function active_change_select($param=null){
		$result=$this->_active_change_select();

		$this->ajaxReturn($result);		
	}

	public function view(){
        $do = D('Zhaoshangjoin179Relation');
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

        $rs['logs'] = M('zhaoshang_logs')->where(['zhaoshang_join_id' => I('get.id')])->order('id desc')->select();
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
        # $rs = $this->do->where(['id' => I('post.zhaoshang_join_id')])->field('id,status,step,mobile')->find();
        $rs = D('Zhaoshangjoin179Relation')->relation(true)->where(['id' => I('post.zhaoshang_join_id')])->find();

        if($rs['status'] != 1){
            $this->ajaxReturn(['status' => 'warning','msg' => '当前状态下不充许执行此审核操作！']);
        }

        $data['status'] = 2;# 拒绝审核
        $auditPass = false;
        if(I('post.step_1') == 1 && I('post.step_2') == 1 && I('post.step_3') == 1 && I('post.step_4') == 1){
            $data['status'] = 5;# 现在直接开店
            $auditPass = true;
        }

        $tmp = I('post.');
        unset($tmp['remark']);

        $data['content']    = serialize($tmp);
        $data['a_uid']      = session('admin.id');
        $data['remark']     = I('post.remark');
        $data['zhaoshang_join_id']     = I('post.zhaoshang_join_id');
        $do = D('Common/ZhaoshangLogs');


        $do->startTrans();

        if($sw[] = false === M('zhaoshang_join')->where(['id' => I('post.zhaoshang_join_id')])->save(['status' => $data['status'],'dotime' => date('Y-m-d H:i:s')])) goto error;
        if(!$sw[] = $do->create($data)) goto error;
        if(!$sw[] = $do->add()) goto error;

        # 开始开店
        if($auditPass === true){
            $shop_name = $rs['shop_name'].$rs['shop_type']['type_name'];
            if(M('shop')->where(['uid' => ['neq',$rs['uid']],'shop_name' => $shop_name])->getField('id')){
                $this->ajaxReturn(['status' => 'warning','msg' => '店铺名称已存在，请联系客户更换店铺名称！']);
            }
            # 其它类目
            $other_id = sortid(['table' => 'goods_category','sid' => 100845542]);

            # 创建店铺记录
            $data = [
                'status'        => 1,
                'shop_name'     => $shop_name,
                'shop_logo'     => $rs['shop_logo'],
                'uid'           => $rs['uid'],
                'type_id'       => $rs['shop_type_id'],
                'max_best'      => $rs['shop_type']['max_best'],
                'max_goods'     => $rs['shop_type']['max_goods'],
                'inventory_type'=> $rs['inventory_type'],
                'about'         => $rs['about'],
                'linkname'      => $rs['linkname'],
                'province'      => $rs['province'],
                'city'          => $rs['city'],
                'district'      => $rs['district'],
                'town'          => $rs['town'],
                'street'        => $rs['street'],
                'mobile'        => $rs['mobile'],
                'tel'           => $rs['tel'],
                'email'         => $rs['email'],
                'qq'            => $rs['qq'],
                'category_id'   => $rs['first_category'] . ',100845542',
                'category_second'   =>$rs['second_category'].($other_id ? ','.implode(',',$other_id) : ''),
            ];

            if(!$sw[] = D('Common/Shop')->create($data)){
                $msg=D('Common/Shop')->getError();
                goto error;
            }

            if(!$sw[] = D('Common/Shop')->add()){
                goto error;
            }

            $shop_id = D('Common/Shop')->getLastInsID();
            if(false === $sw[] = M('user')->where(['id' => $rs['uid']])->save(['shop_type' => $rs['shop_type_id'],'shop_id' => $shop_id])) goto error;

            # 创建退货地址
            $data = array(
                'uid'           => $rs['uid'],
                'linkname'      => $rs['linkname'],
                'province'      => $rs['province'],
                'city'          => $rs['city'],
                'district'      => $rs['district'],
                'town'          => $rs['town'],
                'street'        => $rs['street'],
                'mobile'        => $rs['mobile'],
                'tel'           => $rs['tel'],
                'is_default'    => 1
            );

            if(!$sw[] = D('Common/SendAddress')->create($data)){
                $msg=D('Common/SendAddress')->getError();
                goto error;
            }

            if(!$sw[] = D('Common/SendAddress')->add()){
                goto error;
            }

            if($rs['brand_cred']){
                $brand = unserialize(html_entity_decode($rs['brand_cred']));
                foreach($brand as $val){
                    $bdata = [
                        'atime'     => date('Y-m-d H:i:s'),
                        'uid'       => $rs['uid'],
                        'status'    => 1,
                        'b_name'    => $val['name'],
                        'b_logo'    => $val['logo'],
                        'b_code'    => $val['code'],
                        'shop_id'   => $shop_id,
                        'category_id' => $rs['second_category'],
                    ];
                    if(!$sw[] = M('brand')->add($bdata)) goto error;
                }
            }
            $do->commit();

            # 创建店铺模板
            $openid = M('user')->where(['id' => $rs['uid']])->getField('openid');
            $res = $this->doApi('/Make/api/method/create_shop',['openid' => $openid]);
        }else{
            $do->commit();
        }
        


        

        # 发送短信通知
        if(I('post.is_sms') == 1){
            $tpl_id = 17;   # 短信模板ID
            if($auditPass === true) $tpl_id = 19;
            $sms_data['mobile'] = $rs['mobile'];
            $sms_data['content']= $this->sms_tpl($tpl_id);
            sms_send($sms_data);
        }
		
		//发送消息
		$msg_data = ['tpl_tag'=>'shop_open_faile','uid'=>$rs['uid']];
		if($auditPass === true) $msg_data['tpl_tag'] = 'shop_open_success';
		tag('send_msg',$msg_data);

        $this->ajaxReturn(['status' => 'success','msg' => '操作成功！']);

        error:
            $do->rollback();
            $this->ajaxReturn(['status' => 'warning','msg' => '操作失败！']);
    }
















    /**
     * 审核开店
     */
    public function logs_open(){
        $rs = D('Zhaoshangjoin179Relation')->relation(true)->where(['id' => I('post.zhaoshang_join_id')])->find();
        if($rs['status'] != 3){
            $this->ajaxReturn(['status' => 'warning','msg' => '当前状态下不充许执行此审核操作！']);
        }

        //拒绝开店
        if(I('post.status') == 4){
            if(empty($_POST['remark'])) $this->ajaxReturn(['status' => 'warning','msg' => '请输入原因！']);

            $data['status']     = 4;
            $data['a_uid']      = session('admin.id');
            $data['remark']     = I('post.remark');
            $data['zhaoshang_join_id']     = I('post.zhaoshang_join_id');
            $do = D('Common/ZhaoshangLogs');

            $do->startTrans();

            if($sw[] = false === M('zhaoshang_join')->where(['id' => I('post.zhaoshang_join_id')])->save(['status' => $data['status'],'dotime' => date('Y-m-d H:i:s')])) goto error1;
            if(!$sw[] = $do->create($data)) goto error1;
            if(!$sw[] = $do->add()) goto error1;

            $do->commit();
            //发送短信通知
            if(I('post.is_sms') == 1){
                $tpl_id = 20;   //短信模板ID
                $sms_data['mobile'] = $rs['mobile'];
                $sms_data['content']= $this->sms_tpl($tpl_id);
                sms_send($sms_data);
            }
			
			//发送消息
			$msg_data = ['tpl_tag'=>'shop_open_faile','uid'=>$rs['uid']];
			tag('send_msg',$msg_data);
			
            $this->ajaxReturn(['status' => 'success','msg' => '操作成功！']);

            error1:
            $do->rollback();
            $this->ajaxReturn(['status' => 'warning','msg' => '操作失败！']);
        }elseif(I('post.status') == 5){
            $data['status']     = 5;
            $data['a_uid']      = session('admin.id');
            $data['remark']     = I('post.remark');
            $data['zhaoshang_join_id']     = I('post.zhaoshang_join_id');
            $do = D('Common/ZhaoshangLogs');


            //店铺是否存在同名
            $shop_name = $rs['shop_name'].$rs['shop_type']['type_name'];
            if(M('shop')->where(['uid' => ['neq',$rs['uid']],'shop_name' => $shop_name])->getField('id')){
                $this->ajaxReturn(['status' => 'warning','msg' => '店铺名称已存在，请联系客户更换店铺名称！']);
            }

            $do->startTrans();

            if(false === $sw[] = M('zhaoshang_join')->where(['id' => I('post.zhaoshang_join_id')])->save(['status' => $data['status'],'dotime' => date('Y-m-d H:i:s')])) goto error;
            if(!$sw[] = $do->create($data)) goto error;
            if(!$sw[] = $do->add()) goto error;


            //其它类目
            $other_id = sortid(['table' => 'goods_category','sid' => 100845542]);

            //创建店铺记录
            $data=[
                'status'		=>1,
                'shop_name'		=>$shop_name,
                'shop_logo'     =>$rs['shop_logo'],
                'uid'			=>$rs['uid'],
                'type_id'		=>$rs['shop_type_id'],
                'max_best'		=>$rs['shop_type']['max_best'],
                'max_goods'		=>$rs['shop_type']['max_goods'],
                'inventory_type'=>$rs['inventory_type'],
                'about'			=>$rs['about'],
                'linkname'		=>$rs['linkname'],
                'province'		=>$rs['province'],
                'city'			=>$rs['city'],
                'district'		=>$rs['district'],
                'town'			=>$rs['town'],
                'street'		=>$rs['street'],
                'mobile'		=>$rs['mobile'],
                'tel'			=>$rs['tel'],
                'email'			=>$rs['email'],
                'qq'			=>$rs['qq'],
                'category_id'   =>$rs['first_category']. ',100845542',
                'category_second'	=>$rs['second_category'].($other_id ? ','.implode(',',$other_id) : ''),
            ];
            //print_r($data);
            if(!$sw[] = D('Common/Shop')->create($data)){
                $msg=D('Common/Shop')->getError();
                goto error;
            }

            if(!$sw[] = D('Common/Shop')->add()){
                goto error;
            }

            $shop_id=D('Common/Shop')->getLastInsID();
            if(false === $sw[] = M('user')->where(['id' => $rs['uid']])->save(['shop_type' => $rs['shop_type_id'],'shop_id' => $shop_id])) goto error;

            //创建退货地址
            $data=[
                'uid'			=>$rs['uid'],
                'linkname'		=>$rs['linkname'],
                'province'		=>$rs['province'],
                'city'			=>$rs['city'],
                'district'		=>$rs['district'],
                'town'			=>$rs['town'],
                'street'		=>$rs['street'],
                'mobile'		=>$rs['mobile'],
                'tel'			=>$rs['tel'],
                'is_default'	=>1
            ];
            //print_r($data);

            if(!$sw[] = D('Common/SendAddress')->create($data)){
                $msg=D('Common/SendAddress')->getError();
                goto error;
            }

            if(!$sw[] = D('Common/SendAddress')->add()){
                goto error;
            }

            if($rs['brand_cred']){
                $brand = unserialize(html_entity_decode($rs['brand_cred']));

                foreach($brand as $val){
                    $bdata = [
                        'atime'     => date('Y-m-d H:i:s'),
                        'uid'       => $rs['uid'],
                        'status'    => 1,
                        'b_name'    => $val['name'],
                        'b_logo'    => $val['logo'],
                        'b_code'    => $val['code'],
                        'shop_id'   => $shop_id,
                        'category_id' => $rs['second_category']
                    ];

                    if(!$sw[] = M('brand')->add($bdata)) goto error;

                }
            }

            //dump($sw);


            $do->commit();
            //创建店铺模板
            $openid = M('user')->where(['id' => $rs['uid']])->getField('openid');
            $res = $this->doApi('/Make/api/method/create_shop',['openid' => $openid]);

            //发送短信通知
            if(I('post.is_sms') == 1){
                $tpl_id = 19;   //短信模板ID
                $sms_data['mobile'] = $rs['mobile'];
                $sms_data['content']= $this->sms_tpl($tpl_id);
                sms_send($sms_data);
            }
			
			//发送消息
			$msg_data = ['tpl_tag'=>'shop_open_success','uid'=>$rs['uid']];
			tag('send_msg',$msg_data);
			
            $this->ajaxReturn(['status' => 'success','msg' => '操作成功！']);

            error:
            $do->rollback();
            $this->ajaxReturn(['status' => 'warning','msg' => '操作失败！']);

        }else{
            $this->ajaxReturn(['status' => 'warning','msg' => '请不要乱操作！']);
        }


    }
}