<?php
/**
+----------------------------------------------------------------------
| RestFull API 
+----------------------------------------------------------------------
| 卖家 - 品牌管理
+----------------------------------------------------------------------
| Author: lazycat <673090083@qq.com>、梁丰
+----------------------------------------------------------------------
*/

namespace Rest\Controller;
use Think\Controller\RestController;
class SellerBrandController extends CommonController {
	protected $action_logs = array('my_brand_add','my_brand_edit','brand_edit');
    public function index(){
    	redirect(C('sub_domain.www'));
    }

    /**
    * 品牌详情
    * @param string $_POST['openid']    用户openid  
    * @param int    $_POST['brand_id']  品牌ID
    */
    public function view(){
        //频繁请求限制,间隔2秒
        $this->_request_check();

        //必传参数检查
        $this->need_param=array('openid','brand_id','sign');
        $this->_need_param();
        $this->_check_sign();

        $status_name=array('待审核','审核通过','审核未通过');

        $brs=M('brand')->where(['uid' => $this->uid,'id' => I('post.brand_id')])->field('etime,ip',true)->find();
        if(!$brs) $this->apiReturn(3);

        $rs=D('Common/BrandExtRetion')->relation(true)->where(['brand_id' => I('post.brand_id')])->field('etime,ip',true)->find();
        $rs['brand_name']    =  $brs['b_name']; //多赋一个值
        if(!$rs){
            $rs=[
                'uid'           =>$this->uid,
                'brand_id'      =>I('post.brand_id'),
                'name'          =>$brs['b_name'],
                'ename'         =>$brs['b_ename'],
                'logo'          =>$brs['b_logo'],
                'about'         =>$brs['scope'],
                'status_name'   =>$status_name[0],
            ];
        }else{
            $rs['status_name']  =$status_name[$rs['status']];
            $category_id=explode(',',$rs['category_id']);
            $goods_category 	=	$this->cache_table('goods_category');
			foreach($category_id as $val){
                $rs['category_name'][]  =	$goods_category[$val];
            }
        }

        $this->apiReturn(1,['data' => $rs]);
    }


    /**
     * 品牌推广详情
     */
    public function promotionView() {
        $this->_request_check();

        //必传参数检查
        $this->need_param=array('openid','id','sign');
        $this->_need_param();
        $this->_check_sign();

        $brs=M('brand_ext')->where(['uid' => $this->uid,'id' => I('post.id')])->find();
        if(!$brs) $this->apiReturn(3);
        $this->apiReturn(1,['data' => $brs]);
    }


    /**
     * 修改推广品牌
     */
    public function promotionEdit() {
        //频繁请求限制,间隔2秒
        $this->_request_check();

        //必传参数检查
        $this->need_param=array('openid','brand_id','name','logo','images','about','category_id','tag','sign');
        $this->_need_param();
        $this->_check_sign();

        //同一品牌不能经营超过10个类目的商品
        $category_id=explode(',',I('post.category_id'));
        if(count($category_id)>10) $this->apiReturn(556);

        $brs=M('brand')->where(['uid' => $this->uid,'id' => I('post.brand_id')])->field('id,shop_id')->find();
        if(!$brs) $this->apiReturn(3);

        $rs=M('brand_ext')->where(['brand_id' => I('post.brand_id')])->field('id')->find();

        $data=[
            'uid'           =>$this->uid,
            'brand_id'      =>I('post.brand_id'),
            'status'        =>0,
            'name'          =>I('post.name'),
            'ename'         =>I('post.ename'),
            'logo'          =>I('post.logo'),
            'images'        =>I('post.images'),
            'about'         =>I('post.about'),
            'category_id'   =>I('post.category_id'),
            'tag'           =>I('post.tag'),
            //'shop_id'       =>$brs['shop_id'],
        ];


        if($rs) {
            $data['id']     =$rs['id'];
            if(!D('Common/BrandExt')->create($data)) $this->apiReturn(4,'',1,D('Common/BrandExt')->getError());
            if(D('Common/BrandExt')->save() === false) $this->apiReturn(0);
        }else{
            if(!D('Common/BrandExt')->create($data)) $this->apiReturn(4,'',1,D('Common/BrandExt')->getError());
            if(!D('Common/BrandExt')->add()) $this->apiReturn(0);
        }

        $this->apiReturn(1);
    }


	/**
    * 读取品牌详情
    * @param string $_POST['openid']    	用户openid    
    * @param string $_POST['brand_id']    	品牌id    
    */
	public function my_brand_view(){
		 //频繁请求限制,间隔2秒
        $this->_request_check();

        //必传参数检查
        $this->need_param=array('openid','brand_id','sign');
        $this->_need_param();
        $this->_check_sign();
		$brs=M('brand')->where(['uid' => $this->uid,'id' => I('post.brand_id')])->find();
		if(!$brs) $this->apiReturn(3);
		$this->apiReturn(1,['data' => $brs]);
	}
	/**
    * 新增品牌
    * @param string $_POST['openid']    	用户openid    
    * @param string $_POST['b_name']    	品牌名称    
    * @param string $_POST['b_logo']    	品牌logo    
    * @param string $_POST['b_master']    	品牌所有者    
    * @param string $_POST['shop_id']    	商家id 
    */
	public function my_brand_add(){
		//频繁请求限制,间隔2秒
        $this->_request_check();

        //必传参数检查
        $this->need_param=array('openid','b_name','b_logo','b_master','shop_id','sign');
        $this->_need_param();
		$this->_check_sign();
		$data['uid'] 		= $this->uid;
		$data['status'] 	= 0;
		$data['b_name'] 	= I('post.b_name');
		$data['b_ename'] 	= I('post.b_ename');
		$data['b_logo'] 	= I('post.b_logo');
		$data['b_code'] 	= I('post.b_code');
		$data['b_images'] 	= I('post.b_images');
		$data['b_images2'] 	= I('post.b_images2');
		$data['b_master'] 	= I('post.b_master');
		$data['shop_id'] 	= I('post.shop_id');
		
		if(!D('Common/Brand')->create($data)) $this->apiReturn(4,'',1,D('Common/Brand')->getError());
		
        if(D('Common/Brand')->add() === false) $this->apiReturn(0);
		$this->apiReturn(1);
	}
	/**
    * 修改品牌
    * @param string $_POST['openid']    	用户openid    
    * @param string $_POST['id']    		品牌id    
    * @param string $_POST['b_name']    	品牌名称    
    * @param string $_POST['b_logo']    	品牌logo    
    * @param string $_POST['b_master']    	品牌所有者    
    * @param string $_POST['shop_id']    	商家id 
    */
	public function my_brand_edit(){
		//频繁请求限制,间隔2秒
        $this->_request_check();

        //必传参数检查
        $this->need_param=array('openid','id','b_name','b_logo','b_master','shop_id','sign');
        $this->_need_param();
		$this->_check_sign();

		$brs = M('brand')->field('status')->find(I('post.id'));
		if(!$brs){$this->apiReturn(3);}
		if($brs['status'] != 0){$this->apiReturn(4,'',1,'只有未通过审核的品牌才可以修改！');}
		
		$data['id']			= I('post.id');
		$data['uid'] 		= $this->uid;
		$data['status'] 	= 0;
		$data['b_name'] 	= I('post.b_name');
		$data['b_ename'] 	= I('post.b_ename');
		$data['b_logo'] 	= I('post.b_logo');
		$data['b_images'] 	= I('post.b_images');
		$data['b_images2'] 	= I('post.b_images2');
		$data['b_master'] 	= I('post.b_master');
		$data['shop_id'] 	= I('post.shop_id');
		$data['b_code']     = I('post.b_code');
		$data['b_master']   = I('post.b_master');
		//$this->apiReturn(3,['data'=>$data]);
		if(!D('Common/Brand')->create($data)) $this->apiReturn(4,'',1,D('Common/Brand')->getError());
		
        if(D('Common/Brand')->save() === false) $this->apiReturn(0);
		$this->apiReturn(1);
	}
    /**
    * 品牌推广修改
    * @param string $_POST['openid']    用户openid
    * @param int    $_POST['brand_id']  品牌ID
    * @param string $_POST['name']      品牌中文名
    * @param string $_POST['ename']     品牌英文名
    * @param string $_POST['logo']      品牌logo
    * @param string $_POST['images']    品牌形象图
    * @param string $_POST['about']     品牌介绍
    * @param string $_POST['category_id'] 类目ID
    * @param string $_POST['tag']       标签
    */
    public function brand_edit(){
        //频繁请求限制,间隔2秒
        $this->_request_check();

        //必传参数检查
        $this->need_param=array('openid','brand_id','name','logo','images','about','category_id','tag','sign');
        $this->_need_param();
        $this->_check_sign();

        //同一品牌不能经营超过10个类目的商品
        $category_id=explode(',',I('post.category_id'));
        if(count($category_id)>10) $this->apiReturn(556);

        $brs=M('brand')->where(['uid' => $this->uid,'id' => I('post.brand_id')])->field('id,shop_id')->find();
        if(!$brs) $this->apiReturn(3);

        $rs=M('brand_ext')->where(['brand_id' => I('post.brand_id')])->field('id')->find();     

        $data=[
            'uid'           =>$this->uid,
            'brand_id'      =>I('post.brand_id'),
            'status'        =>0,
            'name'          =>I('post.name'),
            'ename'         =>I('post.ename'),
            'logo'          =>I('post.logo'),
            'images'        =>I('post.images'),
            'about'         =>I('post.about'),
            'category_id'   =>I('post.category_id'),
            'tag'           =>I('post.tag'),
            'shop_id'       =>$brs['shop_id']
        ];


        if($rs) {
            $data['id']     =$rs['id'];
            if(!D('Common/BrandExt')->create($data)) $this->apiReturn(4,'',1,D('Common/BrandExt')->getError());
            if(D('Common/BrandExt')->save() === false) $this->apiReturn(0);
        }else{
            if(!D('Common/BrandExt')->create($data)) $this->apiReturn(4,'',1,D('Common/BrandExt')->getError());
            if(!D('Common/BrandExt')->add()) $this->apiReturn(0);            
        }

        $this->apiReturn(1);
    }

    /**
    * 已更新品牌资料的品牌列表
    * @param string $_POST['openid']    用户openid
    */
    public function brand_list(){
        //频繁请求限制,间隔2秒
        $this->_request_check();

        //必传参数检查
        $this->need_param=array('openid','sign');
        $this->_need_param();
        $this->_check_sign();

        $status_name=array('待审核','审核通过','审核未通过');

        $do=D('Common/BrandExtRetion');
        $list=$do->where(['uid' => $this->uid])->field('etime,ip',true)->select();

        if($list){
			$goods_category 	=	$this->cache_table('goods_category');
            foreach($list as $i => $val){
                $list[$i]['status_name']    =$status_name[$val['status']];
                $category_id=explode(',',$val['category_id']);
                foreach($category_id as $v){
                    $list[$i]['category_name'][]  =	$goods_category[$v];
                }                
            }
            $this->apiReturn(1,['data' => $list]);
        }else $this->apiReturn(3);
    }
}