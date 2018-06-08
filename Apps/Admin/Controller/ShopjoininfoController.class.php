<?php
/**
* 此文件为根据表单模板创建
*/
namespace Admin\Controller;
use Think\Controller;
class ShopjoininfoController extends CommonModulesController {
	protected $name 			='开店申请';	//控制器名称
    protected $formtpl_id		=143;			//表单模板ID
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

    	//$btn=array('title'=>'操作','type'=>'html','html'=>'<a href="'.__CONTROLLER__.'/edit/id/[id]" class="btn btn-sm btn-info btn-rad btn-trans btn-block m0">修改</a> <div data-url="'.__CONTROLLER__.'/view/id/[id]" data-id="[id]" class="btn btn-sm btn-primary btn-rad btn-trans btn-block m0 btn-view">审核</div>','td_attr'=>'width="100" class="text-center"','norder'=>1);

    	$btn=array('title'=>'操作','type'=>'html','html'=>'<div data-url="'.__CONTROLLER__.'/view/id/[id]" data-id="[id]" class="btn btn-sm btn-primary btn-rad btn-trans btn-block m0 btn-view">审核</div>','td_attr'=>'width="100" class="text-center"','norder'=>1);
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
	* 详情
	*/
	public function view(){
		$do=D('ShopJoinInfoRelation');
		$rs=$do->relation(true)->where(array('id'=>I('get.id')))->find();
/*
		foreach($rs['category'] as $i => $val){
			$rs['category'][$i]['category_name'] = get_key_by_list(array('table'=>'goods_category','field'=>'id,category_name','key_val'=>$val['category_id'],'cache_name'=>'table_goods_category'));

			$val['category_second']=explode(',',$val['category_second']);
			foreach($val['category_second'] as $j=>$v){
				$rs['category'][$i]['category_second_name'][]=get_key_by_list(array('table'=>'goods_category','field'=>'id,category_name','key_val'=>$v,'cache_name'=>'table_goods_category'));
			}
		}
*/       
 		
		$rs['categoryName']   =   $this->getCategoryName($rs['category']['cates']);
		$rs['cert']           =   M('goods_category_cert')->where(['category_id' => ['in', $rs['category']['cates']]])->field('id,cert_name,category_id')->select();
		
		if ($rs['cert']) {
    		foreach ($rs['cert'] as $k => $v) {
    		    $rs['cert'][$k]['category_name']   =   M('goods_category')->where(['id' => $v['category_id']])->getField('category_name');
    		    $rs['cert'][$k]['child']           =   M('shop_join_category_cert')->where(['cert_id' => $v['id'], 'uid' => $rs['uid']])->field('cert_images,expire,atime')->find();
    		}
		}
		
		//产品信息
		$rs['product']    =   M('shop_join_products')->where(['uid' => $rs['uid']])->find();
		if ($rs['user']['type'] == 1) {
		    $rs['bank']   =   M('shop_join_bank')->where(['uid' => $rs['uid']])->find();
		}
		
		$rs['bankCert']   =   M('shop_join_cert')->where(['uid' => $rs['uid']])->field('id,brand_id,reg_type,reg_people,reg_no,is_proxy,proxy_expire,proxy_cert,apply_people,apply_no,reg_expire,reg_date,is_import,license_images')->order('id asc')->select();
		$this->assign('notPass', $this->getSteps($rs['user']['type']));
		$this->assign('rs',$rs);
		$this->display();
	}

	/** 
	* 保存审核记录
	*/
	public function logs_add(){
		if(I('post.status')==2 && I('post.reason')==''){
			$this->ajaxReturn(array('status'=>'warning','msg'=>'请输入拒绝原因！'));
		}

		//详情
		$do=D('ShopJoinInfoRelation');
		$rs=$do->relation(true)->where(array('id'=>I('post.shop_join_id')))->find();
		if($rs['status']==5) $this->ajaxReturn(['status'=>'warning','msg' =>'店铺已通过审核且开店成功，不可再次操作！']);
        /*
		$category_id[]=110845542;	//其它类目
		foreach($rs['category'] as $val){
			$category_id[]=$val['category_id'];
			$category_second[]=$val['category_second'];
		}*/

		//创建操作日志
		$do=D('ShopJoinLogs');
		$_POST['a_uid']=session('admin.id');

		$do->startTrans();
		if($sw1=$do->create()) $sw1=$do->add();
		else{
			$msg[]=$do->getError();
			goto error;
		}

		if(false==M('shop_join_info')->where(['uid' => $rs['uid']])->save(array('status'=>I('post.status'),'dotime'=>date('Y-m-d H:i:s'),'not_pass' => implode(',', I('post.not_pass'))))) goto error;

		if(I('post.status')==5){
			//创建店铺记录
			$data=[
				'status'		=>1,
				'shop_name'		=>$rs['shop_name'].$rs['shop_type']['type_name'],
				'uid'			=>$rs['uid'],
				'type_id'		=>$rs['type_id'],
				'max_best'		=>$rs['shop_type']['max_best'],
				'max_goods'		=>$rs['shop_type']['max_goods'],
				'inventory_type'=>$rs['inventory_type'],
				'about'			=>$rs['about'],
				'province'		=>$rs['province'],
				'city'			=>$rs['city'],
				'district'		=>$rs['district'],
				'town'			=>$rs['town'],
				'street'		=>$rs['street'],
				'mobile'		=>$rs['mobile'],
				//'tel'			=>$rs['contact']['tel'],
				//'email'		=>$rs['contact']['mobile'],
				'qq'			=>$rs['contact']['qq'],
				'category_id'		=>$rs['category']['cates'] . ',100845547',
				//'category_second'	=>implode(',', $category_second)
			];
			//print_r($data);
			if(!D('Common/Shop')->create($data)){
				$msg=D('Common/Shop')->getError() . $rs['mobile'];
				goto error;
			}

			if(!D('Common/Shop')->add()){
				goto error;
			}

			$shop_id=D('Common/Shop')->getLastInsID();

			if(false===M('user')->where(['id' => $rs['uid']])->save(['shop_type' => $rs['type_id'],'shop_id' => $shop_id])) goto error;

			//创建退货地址
			$data=[
				'uid'			=>$rs['uid'],
				'linkname'		=>$rs['contact']['rf_linkname'],
				'province'		=>$rs['contact']['rf_province'],
				'city'			=>$rs['contact']['rf_city'],
				'district'		=>$rs['contact']['rf_district'],
				'town'			=>$rs['contact']['rf_town'],
				'street'		=>$rs['contact']['rf_street'],
				'mobile'		=>$rs['contact']['rf_mobile'],
				'tel'			=>$rs['contact']['rf_tel'],				
				'postcode'		=>$rs['contact']['rf_postcode'],
				'is_default'	=>1
			];
			//print_r($data);

			if(!D('Common/SendAddress')->create($data)){
				$msg=D('Common/SendAddress')->getError();
				goto error;
			}

			if(!D('Common/SendAddress')->add()){
				goto error;
			}

			$brand=M('shop_join_brand')->where(['uid' => $rs['uid']])->select();
			if($brand){
				foreach($brand as $i => $val){
					unset($brand[$i]['id']);
					$brand[$i]['shop_id']	=$shop_id;
				}

				if(!M('brand')->addAll($brand)) goto error;
			}

            $step   =   M('shop_join_step')->where(['uid' => $rs['uid']])->save(['step' => 10]);
            if (!$step) {
                goto error;
            }
		}
		
		if (I('post.status')==2 && empty(I('post.not_pass'))) {
		    $msg  =   '请选择未通过的步骤！';
		    goto error;
		}
		if (I('post.status')==2) {
		    $step   =   M('shop_join_step')->where(['uid' => $rs['uid']])->save(['step' => 9, 'etime'=>date('Y-m-d H:i:s')]);
		    if (!$step) {
		        goto error;
		    }
		}

		success:
			$do->commit();
			if(I('post.status')==5){
            	$openid = M('user')->where(['id' => $rs['uid']])->getField('openid');
            	$res = $this->doApi('/Make/api/method/create_shop',['openid' => $openid]);
				
				//发送消息
				$msg_data = ['tpl_tag'=>'shop_open_success','uid'=>$rs['uid']];
				tag('send_msg',$msg_data);
				
			}else if(I('post.status')==2){
				//发送消息
				$msg_data = ['tpl_tag'=>'shop_open_faile','uid'=>$rs['uid']];
				tag('send_msg',$msg_data);
			}

			$result['status']='success';
			$result['msg']='操作成功！';
			$this->ajaxReturn($result);

		error:
			$do->rollback();
			$result['status']='warning';
			$result['msg']='操作失败！'.$msg;
			$this->ajaxReturn($result);

	}
	
	/**
	 * 获取分类
	 * @return mixed
	 */
	private function getCategory() {
	    $data   =   S('seller_opens_getCategory');
	    if (!$data) {
	        $field  =   'id,category_name';
	        $data   =   M('goods_category')->where(['status' => 1, 'sid' => 0])->order('sort asc')->field($field)->select();
	        foreach($data as &$val) {
	            $val['child']  =   (M('goods_category')->where(['status' => 1, 'sid' => $val['id']])->order('sort asc')->getField($field));
	        }
	        $data   =   serialize($data);
	        S('seller_opens_getCategory', $data);
	    }
	    return unserialize($data);
	}
	
	/**
	 * 获取分类名称
	 * @param unknown $cate
	 */
	private function getCategoryName($cate) {
	    $cates  =   '';
	    if (is_string($cate)) {
	        $cate   =   explode(',', $cate);
	    }
	    foreach ($this->getCategory() as $k => $v) {
	        foreach ($v as $key => $val) {
	            foreach ($val as $keys => $vals) {
	                if (in_array($keys, $cate)) {
	                    $cates .= $vals . ',';
	                }
	            }
	        }
	    }
	    unset($k,$v,$val,$key,$vals,$keys,$cate);
	    return trim($cates, ',');
	}
	
	/**
	 * 获取开店步骤
	 * @param int $type 认证类型，1企业，0个人
	 */
	private function getSteps($type) {
	    $data  =   [
	       [1,'资质认证'],
	       [2,'选择店铺类型'],
	       [3,'添加主营类目'],
	       [4,'开店认证'],
	       [5,'缴纳保证金'],
	       [6,'完善店铺信息'],
	       [7,'设置结算方式'],
	    ];
	    //if ($type == 1) {
	        unset($data[4]);
	    //}
	    return $data;
	}
}