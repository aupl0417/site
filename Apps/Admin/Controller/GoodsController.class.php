<?php
/**
* 此文件为根据表单模板创建
*/
namespace Admin\Controller;
use Common\Notice\Pushs;
use Common\Notice\System;
class GoodsController extends CommonModulesController {
	protected $name 			='商品管理';	//控制器名称
    protected $formtpl_id		=86;			//表单模板ID
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
        if(I('get.is_test')==1){
            $map['_string'] = 'shop_id in (select id from '.C('DB_PREFIX').'shop where is_test=1)';
        }

    	$this->_index(array('map' => $map));
        foreach($this->_data['list'] as &$v){
            $v['attr_list_id'] = $v['attr_list']['id'];
        }
        $this->assign('pagelist',$this->_data);
        //dump($this->_data);
        $url    = DM('m', '/goods/view/id/[attr_list_id]');
		//$btn=array('title'=>'操作','type'=>'html','html'=>'<a href="'.__CONTROLLER__.'/edit/id/[id]" class="btn btn-sm btn-info btn-rad btn-trans btn-block m0">修改</a> <div data-url="'.__CONTROLLER__.'/illegal/id/[id]" data-id="[id]" class="btn btn-sm btn-primary btn-rad btn-trans btn-block m0 btn-view">违规信息</div><a data-url="'.$url.'" data-id="[id]" class="btn btn-sm btn-success btn-rad btn-trans btn-block m0 btn-copy">复制链接</a>','td_attr'=>'width="100" class="text-center"','norder'=>1);
		$btn=array('title'=>'操作','type'=>'html','html'=>'<a href="'.__CONTROLLER__.'/edit/id/[id]" class="btn btn-sm btn-info btn-rad btn-trans btn-block m0">修改</a><a data-url="'.$url.'" data-id="[id]" class="btn btn-sm btn-success btn-rad btn-trans btn-block m0 btn-copy">复制链接</a>','td_attr'=>'width="100" class="text-center"','norder'=>1);
    	$this->assign('fields',$this->plist(null,$btn));
    	//dump($this->_data);
		$this->display();
    }

    /**
     * 查看供货商提交的商品
     * Create by liangfeng
     * 2017-09-12
     */
    public function supplier_index($param=null){

		$year = date('Y',time());
		$do=M('goods a');
		
		$map['supplier_id'] = ['neq','0'];

		$count = $do->field('a.id')->where($map)->select();
		$count = count($count);
		$page= new \Think\Page($count, 10);
		$limit=$page->firstRow.','.$page->listRows;
		$list=$do->field('*,a.id,a.status,a.examine_status,b.nick,c.shop_name')->join('ylh_user as b on a.supplier_id = b.id')->join('ylh_supplier_user as c on a.supplier_id = c.uid')->where($map)->order('a.status desc,a.id desc')->limit($limit)->select();
		foreach ($list as $k=>$v){			
			$list[$k]['price_purchase'] = M('goods_attr_list')->where(['goods_id'=>$v['id']])->order('price_purchase asc')->getField('price_purchase');
			
		}
		$result['list']=$list;
		$result['listnum']=count($list);
		$result['allnum']=$count;
		$result['page']=$page->show_btn();
		$result['allpage']=$page->allpage();
		
		$this->assign('pagelist',$result);

		$fields=[
				[
						'title'=>'选择',
						'type'=>'html',
						'html'=>'<input type="checkbox" class="i-red-square" name="id[]" id="id[]" value="[id]">',
						'td_attr'=>'width="60" class="text-center"',
						'norder'=>1
				],
				[
						'title'=>'ID',
						'field'=>'id',
				],
				[
						'title'=>'卖家',
						'field'=>'nick',
				],
				[
						'title'=>'卖家',
						'field'=>'shop_name',
				],
				[
						'title'=>'添加时间',
						'field'=>'atime',
				],
				[
						'title'=>'图片',
						'type'=>'html',
						'html'=>'<a class="image-zoom" href="[images]" title="大图"><img src="[images]" alt="图片" style="width:42px;height:42px;"></a>',
				],
				[
						'title'=>'商品名称',
						'field'=>'goods_name',
				],
				[
						'title'=>'成本价',
						'field'=>'price_purchase',
				],
				[
						'title'=>'交易价',
						'field'=>'price',
				],
				[
						'title'=>'返还比例',
						'field'=>'score_ratio',
						'function'=>'return ($val["score_ratio"]*100)."%";',
				],
				[
						'title'=>'交易类型',
						'field'=>'score_type',
						'function'=>'return ["1"=>"金积分","2"=>"现金","4"=>"银积分"][$val["score_type"]];',
				],
				[
						'title'=>'分类',
						'field'=>'category_id',
						'function'=>'return nav_sort(array("table"=>"goods_category","field"=>"id,sid,category_name","id"=>$val["category_id"],"key"=>"category_name","cache_name"=>"goods_category_".$val["category_id"]));',
				],
            /*
				[
						'title'=>'状态',
						'field'=>'status',
						'function'=>'return status($val["status"],array(0=>array("删除"),1=>array("已上架","btn-success"),2=>array("待上架","btn-info"),3=>array("主图缺失","btn-default"),4=>array("违规","btn-danger"),5=>array("异常","btn-warning")));',
				],
            */
                [
                    'title'=>'审核状态',
                    'field'=>'examine_status',
                    'function'=>'return status($val["examine_status"],array(1=>array("未提交","btn-default"),2=>array("待审核","btn-info"),3=>array("审核通过","btn-success"),4=>array("审核驳回","btn-warning")));',
                ],
				[
						'title'=>'操作',
						'type'=>'html',
						'html'=>'<div data-url="'.__CONTROLLER__.'/supplier_goods_examine/id/[id]" data-id="[id]" class="btn btn-view btn-sm btn-primary btn-rad btn-trans btn-block m0 btn-view">审核</div>',
						'td_attr'=>'width="100" class="text-center"',
						'norder'=>1
				]
		];
		$this->assign('fields',$fields);
		$this->display();
		
    }
    /**
     * 审核供货商商品
     * Create by liangfeng
     * 2017-09-12
     */
    public function supplier_goods_examine(){
        $id = I('get.id');
        $res = M('goods')->find($id);
        $res['examine_status_name'] = ['1'=>'未提交','2'=>'审核中','3'=>'已通过',4=>'已驳回'][$res['examine_status']];
        $this->assign('info',$res);
        $this->display();
    }
    /**
     * 审核供货商商品
     * Create by liangfeng
     * 2017-09-12
     */
	public function ajax_supplier_examine(){
        if(!in_array(I('post.examine_status'),['3','4'])) $this->ajaxReturn(['code'=>0,'msg'=>'请选择审核状态']);
	    if(I('post.examine_status') == 4 && empty(I('post.examine_reason'))) $this->ajaxReturn(['code'=>0,'msg'=>'驳回审核必须填写原因']);

        $goods_info = M('goods')->where(['id'=>I('post.id')])->find();
	    if($goods_info['examine_status'] != 2) $this->ajaxReturn(['code'=>0,'msg'=>'该商品不是待审核状态']);

        $data['examine_status'] = I('post.examine_status');
        $data['examine_reason'] = I('post.examine_reason');
	    if(I('post.examine_status') == 3){
            $data['status'] = 1;
        }
        M('goods')->where(['id'=>$goods_info['id']])->data($data)->save();
        $this->ajaxReturn(['code'=>1,'msg'=>'审核成功']);
	}
	
	/**
	 * 商品违规
	 * @author liangfeng 
	 * 2017-05-31
	 */
    public function illegal(){
		//商品id
		$id = I('get.id');
		
		//$shop_id = M('goods')->where(['id'=>$id])->getField('shop_id');
		$goods_info = M('goods')->field('shop_id,status')->find($id);
		$this->assign('goods_info',$goods_info);
		
		
		$shop_info = M('shop')->field('id,shop_name,uid')->find($goods_info['shop_id']);
		$this->assign('shop_info',$shop_info);
		
		$res = M('shop_rules')->field('id,reason')->where(['status'=>1,'type'=>1])->select();
		foreach($res as $v){
			$rules[$v['id']] = array($v['id'],$v['reason']);
		}
		$this->assign('rules',$rules);
		
		
		$illegas = M('shop_vr')->where(['goods_id'=>$id])->order('atime desc')->select();
		//dump($illegas);
		foreach($illegas as $k => $v){
			$illegas[$k]['rules_title'] = $rules[$v['wrongdoing']][1];
			$illegas[$k]['type_name'] = [1=>'一般',2=>'严重',3=>'非常严重'][$v['type']];
			$illegas[$k]['status_name'] = [0=>'审核中',1=>'待审核',2=>'处罚生效',3=>'处罚取消'][$v['status']];
		}
		$this->assign('illegas',$illegas);
		
		$this->display();
	}
	
	/**
	 * 新增违规信息
	 * @author liangfeng 
	 * 2017-05-31
	 */
	public function ajax_illegal_add(){
		$data = I('post.');

		$data['rules_type'] = 1;
		$data['mobile'] = M('user')->where(['id'=>$data['uid']])->getField('mobile');
		
		$rule_info = M('shop_rules')->find($data['wrongdoing']);
		$data['point'] = $rule_info['point'.$data['type']];
		$data['auto_punish_time'] = date('Y-m-d H:i:s',time()+86400*3);
		
		//判断是否存在未完成的违规
		if(M('shop_vr')->where(['goods_id'=>$data['goods_id'],'status'=>['in','0,1']])->find()){
			$this->ajaxReturn(['status' => 'warning','msg' => '此商品存在未完成的违规审核']);
		}
		
		
		if (!D('Shopvr256')->create($data)){
			// 如果创建失败 表示验证没有通过 输出错误提示信息
			$this->ajaxReturn(['status' => 'warning','msg' => D('Shopvr256')->getError()]);
		}
		
		$do = M();
		$do->startTrans();
	
		
		//商品下架
		if(false == M('goods')->where(['id'=>$data['goods_id']])->data(['status'=>4])->save()){
			goto error;
		}
		
		
		//添加商品违规表
		$data2=[
			'a_uid'			=>session('admin.id'),
			'uid'			=>$data['uid'],
			'shop_id'		=>$data['shop_id'],
			'goods_id'		=>$data['goods_id'],
			'status'		=>1,
			'reason'		=>$data['plot'],
			'illegl_point'	=>$data['point']
		];
		if(!D('Common/GoodsIllegl')->create($data2)) goto error;
		if(!$goods_illegl_id = D('Common/GoodsIllegl')->add()) goto error;
		
		//添加店铺违规表
		$data['goods_illegl_id'] = $goods_illegl_id;
		if(false == D('Shopvr256')->add($data)){
			goto error;
		}
		
	
		$do->commit();	
		
		//发送下架消息
		$msg_data = ['tpl_tag'=>'goods_down','uid'=>$data['uid']];
		tag('send_msg',$msg_data);
		
		$this->ajaxReturn(['status' => 'success','msg' => '操作成功！']);
		
		error:
		$do->rollback();
		$this->ajaxReturn(['status' => 'warning','msg' => '操作失败']);
	}
	/**
	 * 获取规则的扣分
	 * @author liangfeng 
	 * 2017-05-31
	 */
	public function ajax_get_point(){
		$res = M('shop_rules')->field('point1,point2,point3')->find(I('post.id'));
		$this->ajaxReturn(['status' => 'success','data'=>$res,'msg' => '操作成功']);
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
        //商品参数
        $goods_param=$this->_param_post();
        //商品库存

        //店铺ID
		$shop=M('shop')->where(['id' => I('post.shop_id')])->field('uid,type_id')->find();
		$_POST['seller_id']=$shop['uid'];
		if($shop['type_id']==1)	$_POST['is_self']=1;	//自营

        $_POST['uptime']    = date('Y-m-d H:i:s');

        $do=M();
        $do->startTrans();

        $do=D('Admin/Goods86');
        if(!$do->create()) {
            $msg=$do->getError();
            goto error;
        }
        $attr=$this->_attr_post();
        
        if(!$do->add()) goto error;
        $insid=$do->getLastInsID();



        $attr=$this->_attr_add_goods_id($attr,$insid,$_POST['seller_id']);
        

        //商品属性值
        //var_dump($attr);
        $attr_value_id=array();
        foreach($attr['attr'] as $val){        	
            if(!$tmp=D('Admin/Goodsattrvalue96')->create($val)){
                $msg=D('Admin/Goodsattrvalue96')->getError();
                goto error;
            }

            if(!D('Admin/Goodsattrvalue96')->add()) goto error;            
            
        }


        //库存
        $attr_list_id=array();
        foreach($attr['attr_list'] as $val){
            if(!D('Admin/Goodsattrlist97')->create($val)){
                $msg=D('Admin/Goodsattrlist97')->getError();
                goto error;
            }
            if(!D('Admin/Goodsattrlist97')->add()) goto error;
        }

        if(!M('goods')->where('id='.$insid)->save(array('price'=>$attr['price']['min'],'price_max'=>$attr['price']['max'],'num'=>$attr['num']))) goto error;

        //商品参数
        if(!empty($goods_param)){
            $goods_param=$this->_param_item_add($goods_param['data'],array('goods_id'=>$insid));
            if(!M('goods_param')->addAll($goods_param)) goto error;
        }

        //商品详情
        $do=D('Admin/Goodscontent90');
        $data=array();
        $data['goods_id']=$insid;
        $data['content']=I('post.content');

        if(!$do->create($data)){
            $msg=$do->getError();
            goto error;
        }

        if(!$do->add()) goto error;

		if(false===M('shop')->where(['id' => I('post.shop_id')])->setInc('goods_num')) goto error;

        $do->commit();

        goods_pr($insid); //更新商品PR

        $this->ajaxReturn(['status' => 'success','msg' => '发布成功！','id' => $insid]);

        error:
            $do->rollback();
            $this->ajaxReturn(['status' => 'warning','msg' => '发布失败！'.$msg]);



	}
	

	/**
	* 修改记录
	*/
	public function edit($param=null){
		//dump($this->get_goods_attr(100842045));
		//商品详情
		$a=M('goods_content')->where('goods_id='.I('get.id'))->field('content')->find();
		$data['content']=$a['content'];

		/*
		//商品包装
		$a=M('goods_package')->where('goods_id='.I('get.id'))->field('content')->find();
		$data['package']=$a['content'];

		//售后保障
		$a=M('goods_protection')->where('goods_id='.I('get.id'))->field('content')->find();
		$data['protection']=$a['content'];
		*/


		$this->_edit(array('data'=>$data));		
		$this->display();
	}

	/**
	* 保存修改记录
	*/
	public function edit_save($param=null){
		if(I('post.status_old')==4 && I('post.status_old')!=I('post.status')) $this->ajaxReturn(['status' => 'warning','msg' => '违规商品不充许在此次修改状态，可到“违规管理”中将违规关闭']);

		elseif(I('post.status_old')!=4 && I('post.status')==4) $this->ajaxReturn(['status' => 'warning','msg' => '设置违规商品请通过批量操作进行处理！']);

        //商品参数
        $goods_param=$this->_param_post();

		$shop=M('shop')->where(['id' => I('post.shop_id')])->field('uid,type_id')->find();
		$_POST['seller_id']=$shop['uid'];
		if($shop['type_id']==1)	$_POST['is_self']=1;	//自营

        //商品库存
        $attr=$this->_attr_post(I('post.id'));
        $attr=$this->_attr_add_goods_id($attr,I('post.id'),$_POST['seller_id']);

        //店铺ID
        
        //$data=I('post.');
        $_POST['num']        =$attr['num'];
        $_POST['price_max']  =$attr['price']['max'];
        $_POST['price']      =$attr['price']['min'];
        //$_POST['shop_category_id']  = implode(',',I('post.shop_category_id'));
        //$data['content']    =array('content'=>I('post.content'));
        //$data['package']  =array('content'=>I('post.package'));
        //$data['protection']   =array('content'=>I('post.protection'));
        $do=M();
        $do->startTrans();
        $do=D('Admin/Goods86');
        if(!$do->create()) {
            $msg=$do->getError();
            goto error;
        }
        if(false===$do->save()) goto error;

        if(false===M('goods_content')->where(['goods_id' => I('post.id')])->save(['content' => I('post.content')])) goto error;

        /**
        *------------------------
        * 商品库存
        *-----------------------
        */
        //商品属性值
        //var_dump($attr);
        $attr_value_id=array();
        foreach($attr['attr'] as $val){
            if(!D('Admin/Goodsattrvalue96')->create($val)){
                $msg=D('Admin/Goodsattrvalue96')->getError();
                goto error;
            }

            if($val['id']){
                $attr_value_id[]=$val['id'];
                if(false===D('Admin/Goodsattrvalue96')->save()) goto error;
            }else{
                if(!D('Admin/Goodsattrvalue96')->add()) goto error; 
                $attr_value_id[]=D('Admin/Goodsattrvalue96')->getLastInsID();
            }

            //echo D('Goodsattrvalue96')->getLastSQL().'<br>';
        }
        //print_r(I('post.'));
        //清除不相关的旧属性值
        //print_r($attr_value_id);
        if(!empty($attr_value_id) && false===M('goods_attr_value')->where(array('goods_id'=>I('post.id'),'id'=>array('not in',$attr_value_id)))->delete()) goto error;

        //库存
        $attr_list_id=array();
        foreach($attr['attr_list'] as $val){
            if(!D('Admin/Goodsattrlist97')->create($val)){
                $msg=D('Admin/Goodsattrlist97')->getError();
                goto error;
            }
            if($val['id']){
                $attr_list_id[]=$val['id'];
                if(false===D('Admin/Goodsattrlist97')->save()) goto error;
            }else{
                if(!D('Admin/Goodsattrlist97')->add()) goto error;
                $attr_list_id[]=D('Admin/Goodsattrlist97')->getLastInsID();
            }

            //echo D('Goodsattrlist97')->getLastSQL().'<br>';
        }
        //清除不相关的库存记录
        if(!empty($attr_list_id) && false===M('goods_attr_list')->where(array('goods_id'=>I('post.id'),'id'=>array('not in',$attr_list_id)))->delete()) goto error;


        //商品参数
        if(!empty($goods_param)){
            $param_item=M('goods_param')->where(array('goods_id'=>I('post.id'),'option_id'=>array('in',$goods_param['key'])))->getField('option_id',true);
            foreach($goods_param['data'] as $val){
                if(in_array($val['option_id'],$param_item)){
                    if(false===M('goods_param')->where(array('goods_id'=>I('post.id'),'option_id'=>$val['option_id']))->save($val)) goto error;
                }else{
                    $val['goods_id']=I('post.id');
                    if(!M('goods_param')->add($val)) goto error;
                }
            }

            if(false===M('goods_param')->where(array('goods_id'=>I('post.id'),'option_id'=>array('not in',$goods_param['key'])))->delete()) goto error;
        }else{
            if(false===M('goods_param')->where('goods_id='.I('post.id'))->delete()) goto error;
        }

        if(I('post.status_old')==1 && I('post.status')!=1){
        	if(false===M('shop')->where(['id' => I('post.shop_id')])->setDec('goods_num')) goto error;
        }

        $do->commit();
        
        goods_pr(I('post.id')); //更新商品PR
		
		if(I('post.status') == 3 || I('post.status') == 4 || I('post.status') == 5){
			//发送消息
			$msg_data = ['tpl_tag'=>'goods_down','uid'=>$shop['uid']];
			tag('send_msg',$msg_data);
		}

        $this->ajaxReturn(['status' => 'success','msg' => '修改成功！']);

        error:
            $do->rollback();
            $this->ajaxReturn(['status' => 'warning','msg' => '修改失败！'.$msg]);
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
		$result=$this->_active_change_select(['map'=>['status'=>['neq',4]]]);

		$this->ajaxReturn($result);		
	}


	/**
	** 设置商品参数
	*/
	public function param_set(){
		//$do=D('GoodsParamOptionRelation');
		//$list=$do->relation(true)->where(array('status'=>1,'category_id'=>I('get.cid')))->field('id,group_name')->select();
		$list=$this->get_goods_param(I('get.cid'));

		if($list && I('get.goods_id')){
			$param_item=M('goods_param')->where('goods_id='.I('get.goods_id'))->getField('option_id,param_value',true);
			foreach($param_item as $key=>$val){
				$param_value['param_'.$key]=$val;
			}
			$this->assign('param_value',$param_value);
		}


		//dump($list);
		//dump($param_value);

		$this->assign('list',$list);
		$this->assign('count',count($list));
		$this->display();
	}

	/**
	* 新增商品时处理$_POST数据中的商品参数
	*/
	public function _param_post(){
		//必填项验证
		
		$param=array();
		foreach($_POST as $key=>$val){
			if(substr($key,0,6)=='param_'){
				if(!empty($val)){
					$param['data'][]=array(				
							'param_value'	=>is_array($val)?implode(',', $val):$val,
							'option_id'		=>substr($key,6)
						);
					$param['key'][]=substr($key,6);
				}
				unset($_POST[$key]);
			}
			
		}
		return $param;
	}

	/**
	* 商品参数添加元素
	*/
	public function _param_item_add($param,$arr){
		foreach($param as $key=>$val){
			$param[$key]=array_merge($val,$arr);
		}
		return $param;
	}

	/**
	* 添加商品库存
	*/
	public function attr_set(){
		if(I('get.goods_id')){
			$attr_value_tmp=M('goods_attr_value')->where('goods_id='.I('get.goods_id'))->field('id,attr_id,option_id,attr_value,attr_album,concat(attr_id,":",option_id) as attr')->select();
			foreach($attr_value_tmp as $val){
				$attr_value['attr'][]=$val['attr'];
				$attr_value[$val['option_id']]['attr_value']=$val['attr_value'];
				$attr_value[$val['attr']]=$val['id'];
				
				if($val['attr_album']) $attr_value[$val['option_id']]['attr_album']=@explode(',',$val['attr_album']);
			}
			$this->assign('attr_value',$attr_value);
		}

		//dump($attr_value);

		$do=M('goods_attr');
		//$list=$do->where(array('status'=>1,'category_id'=>I('get.cid'),'sid'=>0))->field('id,attr_name')->order('sort asc')->select();
		$list=$this->get_goods_attr(I('get.cid'));
		//dump($list);

		foreach($list as $key=>$val){
			$list[$key]['attr_options']=$do->where(array('status'=>1,'sid'=>$val['id']))->order('sort asc')->getField('id,attr_name',true);
			$list[$key]['count']=count($list[$key]['attr_options']);
		}



		$this->assign('list',$list);
		$this->display();
	}

	/**
	* 创建商品库存表单
	* 多种属性组合，相当于多维数组
	*/
	public function attr_create_form(){
		//var_dump(I('post.'));exit;
		if(I('get.goods_id')){
			$list=M('goods_attr_list')->where('goods_id='.I('get.goods_id'))->getField('attr_id,id,price,price_market,price_purchase,num,code,barcode,weight',true);
		}
		$data=array();
		foreach(I('post.data') as $key=>$val){
			$v=explode(':',$val);
			$data[$v[0]][]=$val;
		}

		$ndata=array();
		$n=0;
		foreach($data as $val){
			$ndata[$n]=$val;
			$n++;
		}

		//分解多维数组组合成一维数组
		$attr_list=$ndata[0];
		$n=0;
		while ($n<count($ndata)-1) {
			$n++;
			$attr_list=$this->array_2($attr_list,$ndata[$n],'<-->');

		}

		//dump($attr_list);

		//格式化数据
		foreach($attr_list as $key=>$val){
			$val=explode('<-->',$val);
			//foreach($val as $vkey=>$v){
				//$val[$vkey]=explode(':',$v);
			//}

			$tmp=array();
			foreach($val as $v){
				$tmp['attr'][]=$v;
				$v=explode(':',$v);
				$tmp['attr_name'][]=$v[2];
				array_pop($v);
				$tmp['attr_id'][]=implode(':',$v);
			}
			$attr_list[$key]=array('attr'=>@implode(',',$tmp['attr']),'attr_name'=>@implode(',', $tmp['attr_name']),'attr_id'=>@implode(',', $tmp['attr_id']));
			//var_dump(implode(',',$tmp['attr_value_id']));

			if($list[$attr_list[$key]['attr_id']]){
				//dump($list[$attr_list[$key]['attr_id']]);
				$attr_list[$key]=array_merge($attr_list[$key],$list[$attr_list[$key]['attr_id']]);
			}

		}

		if(!empty($attr_list)){
			$this->assign('attr_set_list',$attr_list);
			$result['html']=$this->fetch('attr_set_list');
			$result['status']='success';
		}else{
			$result['status']='warning';
		}

		//dump($attr_list);
		$this->ajaxReturn($result);

	}

	/**
	* 拼二维数组
	* @param array $arr1,$arr2 两个要进行拼值的数组
	* @param string $icon 分隔符
	*/
	public function array_2($arr1,$arr2,$icon=','){
		foreach($arr1 as $key=>$val){
			foreach($arr2 as $v){
				$result[]=$val.$icon.$v;
			}
		}
		return $result;
	}

	/**
	* 库存属性 $_POST数据处理
	*/
	public function _attr_post(){
	    if (I('post.is_daigou')==1  && I('post.attr_sku_attr_id') == ""){
	        $this->ajaxReturn(array('status'=>'warning','msg'=>'请填写商品库存和商品参数！'));
	    }
		$attr_list=array();
		//$price=array();
        $price_min = 0;
        $price_max = 0;
		$num=0;
		foreach(I('post.attr_sku_attr_id') as $key=>$val){
			//属性名称
			$attr_name=array();
			$attr_name_tmp=explode(',',I('post.attr_sku_attr')[$key]);
			foreach($attr_name_tmp as $v){
				$v=explode(':',$v);
				$attr_name[]=end($v);
			}
			$tmp=array(
					'attr_id'			=>$val,
					'attr'				=>I('post.attr_sku_attr')[$key],
					'attr_name'			=>implode(',',$attr_name),
					'images'			=>I('post.images'),
					'id'				=>I('post.attr_sku_id')[$key],
					'seller_id'			=>I('post.seller_id'),
					'price'				=>I('post.attr_sku_price')[$key],
					'price_purchase'	=>I('post.attr_sku_price_purchase')[$key],
					'price_market'		=>I('post.attr_sku_price_market')[$key],
					'num'				=>I('post.attr_sku_num')[$key],
					'code'				=>I('post.attr_sku_code')[$key],
					'barcode'			=>I('post.attr_sku_barcode')[$key],
					'weight'			=>I('post.attr_sku_weight')[$key],
				);

			//验证数据合法性
			if(checkform($tmp['price'],array('egt',0.1))==false) {
				$this->ajaxReturn(array('status'=>'warning','msg'=>'【库存第'.($key+1).'条记录】<br>销售价格格式错误或价格低于0.1!'));
			}
			if(checkform($tmp['price_purchase'],array('egt',0.1))==false && $tmp['price_purchase']>0) {
				$this->ajaxReturn(array('status'=>'warning','msg'=>'【库存第'.($key+1).'条记录】<br>成本价格格式错误或价格低于0.1!'));
			}
			if(checkform($tmp['price_market'],array('egt',0.1))==false && $tmp['price_market']>0) {
				$this->ajaxReturn(array('status'=>'warning','msg'=>'【库存第'.($key+1).'条记录】<br>市场价格格式错误或价格低于0.1!'));
			}
			if(checkform($tmp['num'],array('is_positive_number'))==false) {
				$this->ajaxReturn(array('status'=>'warning','msg'=>'【库存第'.($key+1).'条记录】<br>库存数量格式错误，必须为正整数，如果缺货请填0'));
			}

			if($tmp['price_purchase']>0 && $tmp['price_purchase']>$tmp['price']) {
				$this->ajaxReturn(array('status'=>'warning','msg'=>'【库存第'.($key+1).'条记录】<br>成本价不得大于销售价！'));
			}

			if($tmp['price_market']>0 && $tmp['price_market']<$tmp['price']) {
				$this->ajaxReturn(array('status'=>'warning','msg'=>'【库存第'.($key+1).'条记录】<br>市场价不得小于销售价！'));
			}

            //$price[]=$tmp['price'];
            $price_min = $price_min == 0 ? $tmp['price'] : min($price_min,$tmp['price']);
            $price_max = $price_max == 0 ? $tmp['price'] : max($price_max,$tmp['price']);
			$num+=$tmp['num'];

			$attr_list[]=$tmp;
		}

        $result['price']['min']=$price_min;
        $result['price']['max']=$price_max;

		$price_dif=$result['price']['max']-$result['price']['min'];
		# if(count($attr_list)>1 && ($result['price']['min']*1.3 < $result['price']['max'] || $result['price']['max']*0.7>$result['price']['min'])){
		# 	$this->ajaxReturn(array('status'=>'warning','msg'=>'最低价和最高价格区间差价不得超过彼此的30%！'));
		# }

		$result['num']=$num;

		$attr=array();
		foreach(I('post.') as $key=>$val){
			if(substr($key,0,5)=='attr_'){
				if(substr($key,0,8)=='attr_id_'){
					$tkey=substr($key,8);
					$tval=explode(':',$val);
					$attr[]=array(
							'id'			=>I('post.attr_aid_'.$tkey),
							'attr_id'		=>$tval[0],
							'option_id'		=>$tval[1],
							'attr_value'	=>I('post.attr_value_'.$tkey),
							'attr_images'	=>I('post.attr_images_'.$tkey)[0],
							'attr_album'	=>@implode(',',I('post.attr_images_'.$tkey))
						);

					//库存主图
					if(I('post.attr_images_'.$tkey)[0]){
						foreach($attr_list as $vkey=>$v){
							$v['attr_id']=explode(',',$v['attr_id']);
							if(in_array($val,$v['attr_id'])){
								$attr_list[$vkey]['images']=I('post.attr_images_'.$tkey)[0];
							}
						}
					}

				}

				unset($_POST[$key]);  //清除
			}
		}

		$result['attr']=$attr;
		$result['attr_list']=$attr_list;
		return $result;
	}

	/**
	* 给库存数组添加商品ID
	* @param int $goods_id 添加商品ID
	* @param int $seller_id 卖家ID
	* 配合 _attr_post方法使用
	*/
	public function _attr_add_goods_id($attr,$goods_id,$seller_id){
		if(!empty($attr['attr'])){
			foreach($attr['attr'] as $key=>$val){
				$attr['attr'][$key]['goods_id']=$goods_id;
			}
		}
		if(!empty($attr['attr_list'])){
			foreach($attr['attr_list'] as $key=>$val){
				$attr['attr_list'][$key]['goods_id']=$goods_id;
				$attr['attr_list'][$key]['seller_id']   =   $seller_id;
			}
		}

		return $attr;
	}

	/**
	* 标记违规商品
	*/
	public function illegl_add_save(){
		if(empty($_POST['reason'])) $this->ajaxReturn(['status' => 'warning','msg' => '请输入违规原因！']);
		if(empty($_POST['id'])) $this->ajaxReturn(['status' => 'warning','msg' => '请选择违规商品！']);

		$goods=M('goods')->where(['id' => ['in',I('post.id')],'status'=>1])->field('id,seller_id,shop_id')->select();

		$n=0;
		foreach($goods as $val){
			if(!$rs=M('goods_illegl')->where(['goods_id'=>$val['id'],'status'=>['in','1,2,3']])->find()){

				$data=[
					'a_uid'			=>session('admin.id'),
					'uid'			=>$val['seller_id'],
					'shop_id'		=>$val['shop_id'],
					'goods_id'		=>$val['id'],
					'status'		=>1,
					'reason'		=>I('post.reason'),
					'illegl_point'	=>I('post.illegl_point')
				];

				$do=M();
				$do->startTrans();
				if(!D('Common/GoodsIllegl')->create($data)) goto error;
				if(!D('Common/GoodsIllegl')->add()) goto error;

				if(!M('goods')->where(['id'=>$val['id']])->save(['status' => 4])) goto error;

				if(I('post.illegl_point')>0){
					$illegl_point=M('goods_illegl')->where(['uid'=>$val['seller_id'],'status'=>['gt',0],'_string'=>'date_format(atime,"%Y")="'.date('Y').'"'])->sum('illegl_point');
					if(!M('shop')->where(['id'=>$val['shop_id']])->save(['illegl_point'=>$illegl_point])) goto error;
				}
				$user = M('user')->where(['id' => $val['seller_id']])->field('nick,mobile,name,email')->cache(true)->find();
				//系统通知
                (new System($val['seller_id'], 20, ['nick' => $user['nick'], 'mobile' => $user['mobile'], 'name' => $user['name'], 'email' => $user['email']]))->send();
				//客户端推送
                (new Pushs($val['seller_id'], 20, ['nick' => $user['nick'], 'mobile' => $user['mobile'], 'name' => $user['name'], 'email' => $user['email']]))->send();
				$do->commit();
				$n++;
				error:
					$do->rollback();
			}
		}

		if($n>0) $result=['status' => 'success','msg' => '添加了'.$n.'条违规记录！'];
		else $result=['status' => 'warning','msg' => '操作失败！'];

		$this->ajaxReturn($result);

	}

	/**
	* 根据类目取属性
	* @param int 	$_POST['cid']	类目ID
	*/
	public function get_goods_attr($cid){
		$do=M('goods_attr');
		$list=$do->where(array('status'=>1,'category_id'=>$cid,'sid'=>0))->field('id,attr_name')->order('sort asc')->select();


		if(empty($list)){
			$rs=M('goods_category')->where(['id' => $cid])->field('id,sid')->find();
			//dump($rs);
			if($rs['sid']>0) $list=$this->get_goods_attr($rs['sid']);
			else return false;
		}

		return $list;
	}

	/**
	* 根据类目取参数
	* @param int 	$_POST['cid']	类目ID
	*/
	public function get_goods_param($cid){
		$do=D('GoodsParamOptionRelation');
		$list=$do->relation(true)->where(array('status'=>1,'category_id'=>$cid))->field('id,group_name')->select();		
		if(empty($list)){
			$rs=M('goods_category')->where(['id' => $cid])->field('id,sid')->find();
			//dump($rs);
			if($rs['sid']>0) $list=$this->get_goods_param($rs['sid']);
			else return false;
		}
		return $list;		
	}

	/**
	* 设置为首页猜您喜欢
	*/
	public function love_change_select(){
		$do=M('goods');
		if($do->where(['id' => ['in',I('post.id')]])->setField('is_love',I('get.tolove'))){
			$this->ajaxReturn(['status' =>'success','msg' =>'操作成功！']);
		}else{
			$this->ajaxReturn(['status' =>'warning','msg' =>'操作失败！']);
		}
	}

	/**
	 * 屏蔽商品在搜索结果中显示
	 */
	public function set_display_select(){
		$do=M('goods');
		if($do->where(['id' => ['in',I('post.id')]])->setField('is_display',I('get.is_display'))){
			$this->ajaxReturn(['status' =>'success','msg' =>'操作成功！']);
		}else{
			$this->ajaxReturn(['status' =>'warning','msg' =>'操作失败！']);
		}
	}


    /**
     * 奖惩权重
     * Create by Lazycat
     * 2017-03-07
     */
    public function pr_extra_save(){
        if(empty($_POST['id'])) $this->ajaxReturn(['status' => 'warning','msg' => '请选择商品！']);

        $n = M('goods')->where(['id' => ['in',I('post.id')]])->save(['pr_extra' => I('post.pr_extra')]);

        if($n>0) $result=['status' => 'success','msg' => '更新了'.$n.'条记录！'];
        else $result=['status' => 'warning','msg' => '操作失败！'];

        $this->ajaxReturn($result);

    }
}