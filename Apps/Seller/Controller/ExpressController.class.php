<?php
/**
+--------------------------------
+ 商家运费模板管理
+ by enhong
+ 2016-07-30
+-------------------------------
*/
namespace Seller\Controller;
class ExpressController extends AuthController{
	/**
	* 模板列表
	*/
	public function index(){
		$do=D('Common/ExpressTplRelation');

		$list=$do->relation(true)->where(array('uid' => session('user.id')))->field('ip,etime',true)->order('id desc')->select();

		$area=$this->cache_table('area');
		foreach($list as $i => $val){
			foreach($val['express_area'] as $j =>$v){
				$ids=explode(',', $v['city_ids']);
				foreach($ids as $c){
					$v['city'][]=$area[$c];
				}

				$list[$i]['express_area'][$j]['city']=implode(',', $v['city']);
			}
		}
		C('seo', ['title' => '运费模板列表']);
		$this->assign('list',$list);

		$this->display();
	}

	/**
	* 删除运费模板
	*/
	public function delete(){
		$do=M('express_tpl');

		$count=M('goods')->where(array('uid' => session('user.id'),'express_tpl_id' => I('post.id'),'status' => array('gt',0)))->count();
		if($count>0) $this->ajaxReturn(array('code'=>0,'msg'=>'您有个'.$count.'商品应用了该模板(含仓库中、违规、及主图异常商品)，必须解除应用后才可以删除！'));

		if($do->where(array('id' => I('post.id'),'uid' => session('user.id')))->delete()){
			$this->ajaxReturn(array('code'=>1,'msg'=>'删除成功！'));
		}else{
			$this->ajaxReturn(array('code'=>0,'msg'=>'删除失败！'));
		}
	}

	/**
	* 修改运费模板
	*/
	public function edit(){
		$do=D('ExpressTplRelation');
		$rs=$do->relation(true)->where(array('uid' => session('user.id'),'id' => I('get.id')))->find();
		$area=$this->cache_table('area');

		foreach($rs['express_area'] as $i =>$v){
			$ids=explode(',', $v['city_ids']);
			foreach($ids as $c){
				$v['city'][]=$area[$c];
			}

			$rs['express_area'][$i]['city']=implode(',', $v['city']);
		}	

		if($rs['town']) $id=$rs['town'];
		elseif($rs['district']) $id=$rs['district'];
		else $id=$rs['city'];

		$rs['select_city']=nav_sort(array('table'=>'area','icon' => ' > ','field' =>'id,sid,a_name','key' => 'a_name','id' =>$id));
		C('seo', ['title' => '修改运费模板']);
		$this->assign('rs',$rs);

		$this->display();
	}

	/** 
	* 新增运费模板
	*/
	public function add(){
		C('seo', ['title' => '添加运费模板']);
		$this->display();
	}

	/**
	* 修改运费模板 - 保存
	*/
	public function edit_save(){
		C('TOKEN_ON',false);
		$do=D('Common/ExpressTpl');
        
		//只能创建一个包邮模板和一个不包邮模板
		# if($do->where(['uid' => session('user.id'),'is_free' => I('post.is_free')])->count()>0) $this->ajaxReturn(['code' => 0,'msg' => '该类型的运费模板已存在，不能重复创建！<br /><br />提示：只能创建一个包邮模板和一个自定义地区运费模板']);

		$_POST['uid']	=session('user.id');
		if(!isset($_POST['is_express']) && !isset($_POST['is_ems']) && I('post.is_free') < 1){
			$this->ajaxReturn(array('code'=>0,'msg' =>'快递和EMS必须启用一个！'));
		}
		if(!isset($_POST['is_express'])){
			$_POST['is_express'] = 0;
		}
		if(!isset($_POST['is_ems'])){
			$_POST['is_ems'] = 0;
		}
		$data =$do->create();
		if (!$data) {
			# code...
			$this->ajaxReturn(['code' => 0, 'msg' => $do->getError()]);
		}
		foreach($_POST['express_city_ids'] as $key=>$val){
			$tmp=array();
			$tmp = array(
					'type'			=>1,
					'uid'			=>session('user.id'),
					'city_ids'		=>$val,
					'first_unit'	=>$_POST['express_first_unit'][$key],
					'first_price'	=>$_POST['express_first_price'][$key],
					'next_unit'		=>$_POST['express_next_unit'][$key],
					'next_price'	=>$_POST['express_next_price'][$key],
				
				);
			if(!empty($_POST['express_id'][$key])) $tmp['id']=$_POST['express_id'][$key];

			$data['express_area'][]=$tmp;
		}

		foreach($_POST['ems_city_ids'] as $key=>$val){
			$tmp=array();
			$tmp = array(
					'type'			=>2,
					'uid'			=>session('user.id'),
					'city_ids'		=>$val,
					'first_unit'	=>$_POST['ems_first_unit'][$key],
					'first_price'	=>$_POST['ems_first_price'][$key],
					'next_unit'		=>$_POST['ems_next_unit'][$key],
					'next_price'	=>$_POST['ems_next_price'][$key],
				);
			if(!empty($_POST['ems_id'][$key])) $tmp['id']=$_POST['ems_id'][$key];

			$data['express_area'][]=$tmp;
		}

		$do=D('Common/ExpressTplRelation');
		$res=$do->relation(true)->save($data);

		if($res!==false) $this->ajaxReturn(array('code'=>1,'msg'=>'操作成功！'));
		else $this->ajaxReturn(array('code'=>0,'msg' =>'操作失败'));
	}



	/**
	* 添加地区运费
	*/
	public function area_add(){

		$this->display();
	}
	/**
	* 修改地区运费
	*/
	public function area_edit(){

		$this->display();
	}	
	/**
	* 删除地区运费
	*/
	public function area_delete(){
		$do=M('express_area');
		if($do->where(array('id' => I('post.id'),'uid' => session('user.id')))->delete()){
			$this->ajaxReturn(array('code'=>1,'msg'=>'删除成功！'));
		}else{
			$this->ajaxReturn(array('code'=>0,'msg'=>'删除失败！'));
		}

	}

	/**
	* 保存运费模板
	*/

	public function add_save(){
		C('TOKEN_ON',false);
		$do=D('Common/ExpressTpl');
        $cacheName = md5($_POST['__hash__']);
        if (S($cacheName)) {
            $this->ajaxReturn(['code' => 0, 'msg' => '请不要重复提交']);
        } else {
            S($cacheName, 1, 10);
        }
//        $expresstpl = C('cfg.expresstpl')['shop_id'];
//        $is_more    = 0;
//        if($expresstpl){
//            $expresstpl = explode(',',$expresstpl);
//            if(in_array(session('user.shop_id'),$expresstpl)) $is_more = 1;
//        }

        //if($is_more ==1){
            //只能创建一个包邮模板
            if(I('post.is_free') == 1) {
                if ($do->where(['uid' => session('user.id'), 'is_free' => I('post.is_free')])->count() > 0) $this->ajaxReturn(['code' => 0, 'msg' => '已有包邮模板存在，不能重复创建！<br />提示：每个店铺只能创建一个包邮模板！']);
            }
        //}else{
            //只能创建一个包邮模板和一个不包邮模板
//            if($do->where(['uid' => session('user.id'),'is_free' => I('post.is_free')])->count()>0) $this->ajaxReturn(['code' => 0,'msg' => '该类型的运费模板已存在，不能重复创建！<br /><br />提示：只能创建一个包邮模板和一个自定义地区运费模板']);
//        }

		$_POST['uid']	=session('user.id');
		$data =$do->create();

		foreach($_POST['express_city_ids'] as $key=>$val){
			$tmp=array();
			$tmp = array(
					'type'			=>1,
					'uid'			=>session('user.id'),
					'city_ids'		=>$val,
					'first_unit'	=>$_POST['express_first_unit'][$key],
					'first_price'	=>$_POST['express_first_price'][$key],
					'next_unit'		=>$_POST['express_next_unit'][$key],
					'next_price'	=>$_POST['express_next_price'][$key],
				);

			$data['express_area'][]=$tmp;
		}

		foreach($_POST['ems_city_ids'] as $key=>$val){
			$tmp=array();
			$tmp = array(
					'type'			=>2,
					'uid'			=>session('user.id'),
					'city_ids'		=>$val,
					'first_unit'	=>$_POST['ems_first_unit'][$key],
					'first_price'	=>$_POST['ems_first_price'][$key],
					'next_unit'		=>$_POST['ems_next_unit'][$key],
					'next_price'	=>$_POST['ems_next_price'][$key],
				);

			$data['express_area'][]=$tmp;
		}



		$do=D('Common/ExpressTplRelation');
		$res=$do->relation(true)->add($data);

		if($res) {
		    $this->ajaxReturn(array('code'=>1,'msg'=>'操作成功！'));
        }
		else {
            S($cacheName, null);
		    $this->ajaxReturn(array('code'=>0,'msg' =>'操作失败'));
        }
	}



	public function city(){
		$this->Api('/Tools/city',['sid' => I('get.sid')],'sid')->with('city');  //地址列表
		$this->display();
	}

	public function get_city(){
		$this->Api('/Tools/city',['sid' => I('get.sid')],'sid')->with();  //地址列表

		$this->ajaxReturn($this->_data);
	}



}