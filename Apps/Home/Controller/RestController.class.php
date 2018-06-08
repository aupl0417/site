<?php
namespace Home\Controller;
use Think\Controller;
class RestController extends CommonController {
    public function top(){
    	$ac=I('get.do')?I('get.do'):'M';
    	$table=I('get.table');

    	if(empty($table)) exit;
    	$table_sort=I('get.table_sort')?I('get.table_sort'):$table.'_sort';

    	$do=$ac($table);

    	$limit=I('get.limit')?I('get.limit'):5;
    	$order=I('get.order')?str_replace('-',' ',I('get.order')):'atime desc';

    	$map['active']=1;
		if(I('get.sid')) {
			$sid=explode(',',I('get.sid'));
			$ids=array();
			foreach($sid as $val){
				$ids=array_merge($ids,sortid(array('table'=>$table_sort,'sid'=>$val)));
			}
			$map['sid']=array('in',$ids);
		}
		if(I('get.q')) $map['name']=array('like','%'.trim(urldecode(I('get.q'))).'%');

    	$field=I('get.field')?I('get.field'):'id,active,atime,sid,name';
    	$list=$do->where($map)->field($field)->order($order)->limit($limit)->select();

		
		//缩略图
		if(I('get.images_cfg')){
			$images_cfg=explode(',',I('get.images_cfg'));
			$w=$images_cfg[0];
			$h=$images_cfg[1]?$images_cfg[1]:$w;
			$t=$images_cfg[2]?$images_cfg[2]:2;
			
			foreach($list as $key=>$val){
				$list[$key]['images']=myurl($val['images'],$w,$h,$t);
			}
		}

    	$this->ajaxReturn($list);
    }
    
    
    public function news() {
        $this->ajaxReturn($this->erp_get_news());
    }
    
    //首页底部品牌汇
    public function brand(){
    	$do=D('BrandView');

    	$map['active']=1;
    	$map['logo']=array('neq','');

    	$limit=29;
        $count=$do->where($map)->count();
        $limit=rand(0,$count-$limit).','.$limit;

    	$field='id,active,sid,name,logo,memberid,domain';
    	$list=$do->where($map)->field($field)->order($order)->limit($limit)->select();

    	foreach($list as $key=>$val){
    		$list[$key]['shop_url']=user_domain($val['memberid'],$val['domain']);
    		$list[$key]['logo']=myurl($val['logo'],100,50);
    	}
		

    	$this->ajaxReturn($list);
    }

    //首页左侧类目图下面推荐分类
    public function floor_sort(){
		$list=sortds(array(
			'table'	=>'products_sort',
			'sid'	=>I('get.sid'),
			'map'	=>array('ishome'=>1),
			'cache_name'=>md5('floor_sort_'.I('get.sid'))
		));

		/*
		$list=array();
		foreach($tmp as $val){
			if($val['ishome']==1) $list[]=$val;
		}
		*/
		$this->ajaxReturn($list);		    	
    }
	
    //第一屏数据
    public function category_screen_first(){
		$do=M('products_sort');
		$list=$do->where(array('active'=>1,'sid'=>0))->order('sort asc,id asc')->field('id,sid,name,icon')->limit(16)->select();
		foreach($list as $key=>$val){
			$list[$key]['icon']=html_entity_decode($val['icon']);
			$list[$key]['dlist']=$do->where(array('active'=>1,'sid'=>$val['id']))->order('sort asc,id asc')->field('id,sid,name')->limit(24)->select();
		}
		$this->ajaxReturn($list);
    }	

    //商品分类
    public function products_category(){
    	$list=get_category(array('table'=>'products_sort','level'=>3,'field'=>'id,sid,name,icon,ishome','map'=>array('active'=>1),'limit'=>12,'cache_name'=>'home_products_sort','cache_time'=>60*30));
		foreach($list as $key=>$val){
			$list[$key]['icon']=html_entity_decode($val['icon']);
			
		}
    	$this->ajaxReturn($list);
    }
	
	//双倍积分商品
	public function double_score_products(){
		$count = M('products')->where(array('active'=>1,'integral_level'=>1))->count();
		$list = array();
		for($i=1;$i<=6;$i++){
			$rand = rand(0,$count-1);
			$array = M('products')->field('id,name,images,price')->where(array('active'=>1,'integral_level'=>1))->limit($rand,1)->select();
			
			if(isset($list[$array[0]['id']])){
				$i--;
			}else{
				$list[$array[0]['id']] = $array[0];
			}
		}
		//处理图片
		$error_img = myurl($v['image'],185,185);
		foreach($list as $k=>$v){
			$list[$k]['images'] = myurl($v['images'],185,185);
			$list[$k]['error_img'] = $error_img;
		}
		
		$this->ajaxReturn($list);
	}
	//为你推荐商品
	public function recommend_products(){
		$count = M('products')->where(array('active'=>1))->count();
		$list = array();
		for($i=1;$i<=5;$i++){
			$rand = rand(0,$count-1);
			$array = M('products')->field('id,name,images,price,sale_num')->where(array('active'=>1))->limit($rand,1)->select();
			
			if(isset($list[$array[0]['id']])){
				$i--;
			}else{
				$list[$array[0]['id']] = $array[0];
			}
		}
		
		//处理图片
		$error_img = myurl($v['image'],225,222);
		foreach($list as $k=>$v){
			$list[$k]['images'] = myurl($v['images'],225,222);
			$list[$k]['error_img'] = $error_img;
		}
		
		$this->ajaxReturn($list);
	}
}

