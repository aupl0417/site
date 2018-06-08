<?php
/**
* 此文件为根据表单模板创建
*/
namespace Admin\Controller;
use Think\Controller;
class ShopController extends CommonModulesController {
	protected $name 			='店铺管理';	//控制器名称
    protected $formtpl_id		=116;			//表单模板ID
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

    	$btn=array('title'=>'操作','type'=>'html','html'=>'<a href="'.__CONTROLLER__.'/edit/id/[id]" class="btn btn-sm btn-info btn-rad btn-trans btn-block m0">修改</a> <div data-url="'.__CONTROLLER__.'/shop_init/id/[id]" data-id="[id]" class="btn btn-sm btn-default btn-rad btn-trans btn-block m0 btn-view">初始化店铺模板</div> <div data-url="'.__CONTROLLER__.'/illegal/id/[id]" data-id="[id]" class="btn btn-sm btn-default btn-primary btn-trans btn-block m0 btn-view2">新增违规</div>','td_attr'=>'width="100" class="text-center"','norder'=>1);
    	$this->assign('fields',$this->plist(null,$btn));  

		$this->display();
    }
	
	/**
	 * 店铺违规
	 * @author liangfeng 
	 * 2017-06-01
	 */
    public function illegal(){
		
		//店铺id
		$shop_id = I('get.id');
		
		$shop_info = M('shop')->field('shop_name,uid')->find($shop_id);
		$this->assign('shop_info',$shop_info);
		
		$res = M('shop_rules')->field('id,reason,type')->where(['status'=>1])->select();
		foreach($res as $v){
			$rules[$v['id']] = array($v['id'],$v['reason']);
		}
		$this->assign('rules',$rules);
		
		//过往违规记录
		$illegas = M('shop_vr')->where(['uid'=>$shop_info['uid']])->order('atime desc')->select();
		foreach($illegas as $k => $v){
			$illegas[$k]['rules_title'] = $rules[$v['wrongdoing']][1];
			$illegas[$k]['type_name'] = [1=>'一般',2=>'严重',3=>'非常严重'][$v['type']];
			$illegas[$k]['status_name'] = [0=>'审核中',1=>'待审核',2=>'处罚生效',3=>'处罚取消'][$v['status']];
		}
		//dump($illegas);
		$this->assign('illegas',$illegas);
		
		$this->display();
	}
	
	/**
	 * 新增违规信息
	 * @author liangfeng 
	 * 2017-06-01
	 */
	public function ajax_illegal_add(){
		$data = I('post.');
		
		
		
		$data['mobile'] = M('user')->where(['id'=>$data['uid']])->getField('mobile');
		
		$rule_info = M('shop_rules')->find($data['wrongdoing']);
		
		if($rule_info['type'] == 1){
			$this->ajaxReturn(['status' => 'warning','msg' => '此违规为商品违规，请去商品页执行此操作']);
		}
		
		$data['rules_type'] = $rule_info['type'];
		$data['point'] = $rule_info['point'.$data['type']];
		$data['auto_punish_time'] = date('Y-m-d H:i:s',time()+86400*3);
		
		
		$mod = D('Shopvr256');
		if (!$mod->create($data)){
			// 如果创建失败 表示验证没有通过 输出错误提示信息
			$this->ajaxReturn(['status' => 'warning','msg' => $mod->getError()]);
		}
		
		$model = M();
		$model->startTrans();
		// 验证通过 可以进行其他数据操作
		if(false == $mod->add($data)){
			goto error;
		}
	
		$model->commit();	
		
		//发送消息
		$msg_data = ['tpl_tag'=>'shop_illegal','uid'=>$data['uid']];
		tag('send_msg',$msg_data);
	
/*
		if($data['point'] > 0){
			//发送扣分消息
			$msg_data = ['tpl_tag'=>'dec_point','uid'=>$data['uid'],'score'=>$data['point']];
			tag('send_msg',$msg_data);
		}	
*/		
		$this->ajaxReturn(['status' => 'success','msg' => '操作成功！']);
		
		error:
		$model->rollback();
		$this->ajaxReturn(['status' => 'warning','msg' => '操作失败']);
	}
	
	/**
	** 获取店铺申请记录
	*/
	public function get_shop_join(){
        $do = D('Zhaoshangjoin179Relation');
        $rs = $do->relation(true)->where(['uid' => I('get.uid')])->find();
		if($rs){
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

			$rs['logs'] = M('zhaoshang_logs')->where(['zhaoshang_join_id' => $rs['id']])->order('id desc')->select();
			foreach($rs['logs'] as $key => $val){
				if($val['content']) $rs['logs'][$key]['content'] = unserialize(html_entity_decode($val['content']));
			}
			//dump($rs['logs']);

			//店铺是否存在同名
			$shop_name = $rs['shop_name'].$rs['shop_type']['type_name'];
			if(M('shop')->where(['uid' => ['neq',$rs['uid']],'shop_name' => $shop_name])->getField('id')){
				$this->assign('is_same',1);
			}
			// dump($rs);
			$this->assign('rs',$rs);
			$this->display();
		}else{
			$do=D('ShopJoinInfoRelation');
			$rs=$do->relation(true)->where(array('uid'=>I('get.uid')))->find();       
			
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
			// dump($rs);
			$this->display();
		}

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
        $do=M($this->fcfg['table']);
        $rs=$do->where('id='.I('get.id'))->find();

        $rs['category_second'] .= $rs['category_id'] ? ','.$rs['category_id']:'';
        $this->assign('rs',$rs);


		$this->display();
	}

	/**
	* 保存修改记录
	*/
	public function edit_save($param=null){
	    //格式一级分类和二级分类
        $list = M('goods_category')->where(['id' =>['in',I('post.category_second')]])->field('id,sid')->select();
        foreach($list as $val){
            if($val['sid'] == 0) $first[] = $val['id'];
            else $second[] = $val['id'];
        }

        $_POST['category_id']       = implode(',',$first);
        $_POST['category_second']   = $second;

		$result=$this->_edit_save();
		
		if($result['status'] == 'success' && I('post.status') == '2'){
			//发送消息
			$msg_data = ['tpl_tag'=>'shop_close','uid'=>I('post.uid')];
			tag('send_msg',$msg_data);
		}
		
		shop_pr(I('post.id'));	//更新店铺PR

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
		if($result['status'] =='success' && I('get.toactive') == '2'){
			
			$map['id']=array('in',I('post.id'));
			$do=M($this->fcfg['table']);
			$shops = $do->where($map)->select();
			foreach($shops as $v){
				//发送消息
				$msg_data = ['tpl_tag'=>'shop_close','uid'=>$v['uid']];
				tag('send_msg',$msg_data);
			}
			
		}
		$this->ajaxReturn($result);		
	}

	/**
	* 初始化店铺模板
	*/
	public function shop_init(){
		$uid 	= M('shop')->where(['id' => I('get.id')])->getField('uid');
		$openid = M('user')->where(['id' => $uid])->getField('openid');


		$res = $this->doApi('/Make/api/method/create_shop',['openid' => $openid]);


		if($res->code==1) $this->ajaxReturn(['status' => 'success' ,'msg' => $res->msg]);
		else $this->ajaxReturn(['status' => 'warning' ,'msg' => $res->msg,'data' => $res->data]);
	}
	
	/**
	* 获取营业额数据
	*/
	public function get_data(){
		$shop_id = I('get.shop_id');
		$this->day = I('get.day_field')==''?date('Y-m',time()):I('get.day_field');
        $do=M('totals_shop');
		$time = $do->distinct(true)->field('date_format(day,"%Y-%m") as day')->order('day desc')->select();
		$data = $do->where('shop_id="'.$shop_id.'" and date_format(day,"%Y-%m")="'.$this->day.'"')->order('day desc')->select();
		
		foreach($data as $key => $val){
			$result['total_money_pay']      += $val['money_pay'];
			$result['total_money_refund']   += $val['money_refund'];
			$result['total_orders_pay_num'] += $val['orders_pay_num'];
			$result['total_goods_sale_num'] += $val['goods_sale_num'];
		}
		
        $this->assign('time',$time);
	    $this->assign('data',$data);
		$this->assign('result',$result);
		$this->assign('day',$this->day);
		$this->assign('shop_id',I('get.shop_id'));
		$this->display();
	}
}