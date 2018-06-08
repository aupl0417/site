<?php
/**
+----------------------------------------------------------------------
| RestFull API 
+----------------------------------------------------------------------
| 卖家商品管理
+----------------------------------------------------------------------
| Author: lazycat <673090083@qq.com>
+----------------------------------------------------------------------
*/

namespace Rest\Controller;
use Think\Controller\RestController;
class SellerGoodsController extends CommonController {
    protected $action_logs = array('set_goods_offline','set_goods_online','goods_delete','goods_illegl_add','category_add','category_edit','category_delete','category_more_delete','goods_name_edit','goods_sku_edit','set_best','cancel_best');
	public function index(){
    	redirect(C('sub_domain.www'));
    }

    /**
    * 出售中的商品
    * @param string $_POST['openid']    用户openid
    */
    public function goods_online(){
        //频繁请求限制,间隔300毫秒
        $this->_request_check();

        //必传参数检查
        $this->need_param=array('openid','sign');
        $this->_need_param();
        $this->_check_sign();


        $map['seller_id']   =$this->uid;
        $map['status']      =1;
      
		
		if(I('post.num')!=''){$map['num']=0;}else{  $map['num']         =['gt',0];}
			
		
		
        //if(I('post.shop_category_id')!='')    $map['shop_category_id']=['in',sortid(['table'=>'shop_goods_category','sid'=>I('post.shop_category_id')])]; //店铺内分类
        //if(I('post.shop_category_id')!='')    $map['shop_category_id']=I('post.shop_category_id');
        if(I('post.shop_category_id')!='') $map['_string'] =   'FIND_IN_SET('.I('post.shop_category_id').', shop_category_id)';
        if(I('post.category_id')!='')   $map['category_id']=['in',sortid(['table'=>'goods_category','sid'=>I('post.cid')])];    //商品类目
        if(I('post.q')!='') $map['goods_name']=['like','%'.I('post.q').'%'];    //搜索关键词
        if(I('post.code')!='') $map['code']=['like','%'.I('post.code').'%'];    //搜索商品编号
        //价格区间
        if(I('post.s_price')!='' && I('post.e_price')!='') $map['price']=['between',[I('post.s_price'),I('post.e_price')]];
        if(I('post.s_price')!='' && I('post.e_price')=='') $map['price']=['gt',I('post.s_price')];
        if(I('post.s_price')=='' && I('post.e_price')!='') $map['price']=['lt',I('post.e_price')];

        //销量区间
        if(I('post.s_sale')!='' && I('post.e_sale')!='') $map['sale_num']=['between',[I('post.s_sale'),I('post.e_sale')]];
        if(I('post.s_sale')!='' && I('post.e_sale')=='') $map['sale_num']=['gt',I('post.e_sale')];
        if(I('post.s_sale')=='' && I('post.e_sale')!='') $map['sale_num']=['lt',I('post.e_sale')];

        if(I('post.is_best')!='') $map['is_best']=1;    //推荐商品

        $pagesize=I('post.pagesize')?I('post.pagesize'):20;

        $pagelist=pagelist(array(
                'table'     =>'GoodsRelation',
                'do'        =>'D',
                'map'       =>$map,
                'order'     =>'id desc',
                'fields'    =>'id,atime,uptime,category_id,shop_category_id,goods_name,images,price,num,sale_num,score_ratio,code,is_best',
                'pagesize'  =>$pagesize,
                'relation'  =>'attr_list',
                'relationLimit'=>array('goods_attr_list',1),
                'relationField'=>array('goods_attr_list','id,concat("/Goods/view/id/",id,".html") as detail_url'),
                'action'    =>I('post.action'),
                'query'     =>I('post.query'),
                'p'         =>I('post.p')?I('post.p'):1,
            ));

        foreach($pagelist['list'] as $key=>$val){
            $pagelist['list'][$key]['images']           = myurl($val['images'],(I('post.imgsize')?I('post.imgsize'):160));
            $pagelist['list'][$key]['_images']          = $val ['images'];
            $pagelist['list'][$key]['category_name']    = nav_sort(['table' => 'goods_category','field'=>'id,sid,category_name','id'=>$val['category_id'],'key'=>'category_name']);

            if($val['shop_category_id'])  $pagelist['list'][$key]['my_category_name']    = nav_sort(['table' => 'shop_goods_category','field'=>'id,sid,category_name','id'=>$val['shop_category_id'],'key'=>'category_name']);
        }

        if($pagelist['listnum']>0) $this->apiReturn(1,['data' => $pagelist]);
        else $this->apiReturn(3);
       
    }

    /**
    * 橱窗中的商品
    * @param string $_POST['openid']    用户openid
    */
    public function recommend() {
        //频繁请求限制,间隔300毫秒
        $this->_request_check();
        
        //必传参数检查
        $this->need_param=array('openid','sign');
        $this->_need_param();
        $this->_check_sign();
        
        
        $map['seller_id']   =$this->uid;
        $map['status']      =1;
        $map['num']         =['gt',0];
        $map['is_best']     =1;
        
        if(I('post.shop_category_id')!='')    $map['shop_category_id']=['in',sortid(['table'=>'shop_goods_category','sid'=>I('post.my_cid')])]; //店铺内分类
        if(I('post.category_id')!='')   $map['category_id']=['in',sortid(['table'=>'goods_category','sid'=>I('post.cid')])];    //商品类目
        if(I('post.q')!='') $map['goods_name']=['like','%'.I('post.q').'%'];    //搜索关键词
        if(I('post.code')!='') $map['code']=['like','%'.I('post.code').'%'];    //搜索商品编号
        //价格区间
        if(I('post.s_price')!='' && I('post.e_price')!='') $map['price']=['between',[I('post.s_price'),I('post.e_price')]];
        if(I('post.s_price')!='' && I('post.e_price')=='') $map['price']=['gt',I('post.s_price')];
        if(I('post.s_price')=='' && I('post.e_price')!='') $map['price']=['lt',I('post.e_price')];
        
        //销量区间
        if(I('post.s_sale')!='' && I('post.e_sale')!='') $map['sale_num']=['between',[I('post.s_sale'),I('post.e_sale')]];
        if(I('post.s_sale')!='' && I('post.e_sale')=='') $map['sale_num']=['gt',I('post.e_sale')];
        if(I('post.s_sale')=='' && I('post.e_sale')!='') $map['sale_num']=['lt',I('post.e_sale')];
        
        //if(I('post.is_best')!='') $map['is_best']=1;    //推荐商品
        
        $pagesize=I('post.pagesize')?I('post.pagesize'):20;
        
        $pagelist=pagelist(array(
            'table'     =>'GoodsRelation',
            'do'        =>'D',
            'map'       =>$map,
            'order'     =>'id desc',
            'fields'    =>'id,atime,uptime,category_id,shop_category_id,goods_name,images,price,num,sale_num,score_ratio,code,is_best',
            'pagesize'  =>$pagesize,
            'relation'  =>'attr_list',
            'relationLimit'=>array('goods_attr_list',1),
            'relationField'=>array('goods_attr_list','id,concat("/Goods/view/id/",id,".html") as detail_url'),
            'action'    =>I('post.action'),
            'query'     =>I('post.query'),
            'p'         =>I('post.p')?I('post.p'):1,
        ));
        
        foreach($pagelist['list'] as $key=>$val){
            $pagelist['list'][$key]['images']           =myurl($val['images'],(I('post.imgsize')?I('post.imgsize'):160));
            $pagelist['list'][$key]['category_name']    = nav_sort(['table' => 'goods_category','field'=>'id,sid,category_name','id'=>$val['category_id'],'key'=>'category_name']);
        
            if($val['shop_category_id'])  $pagelist['list'][$key]['my_category_name']    = nav_sort(['table' => 'shop_goods_category','field'=>'id,sid,category_name','id'=>$val['shop_category_id'],'key'=>'category_name']);
        }
        
        if($pagelist['listnum']>0) $this->apiReturn(1,['data' => $pagelist]);
        else $this->apiReturn(3);
    }
    

    /**
    * 商品下架
    * @param string $_POST['openid']    用户openid
    * @param int|string     $_POST['id']    商品ID
    */
    public function set_goods_offline(){
        //频繁请求限制,间隔300毫秒
        $this->_request_check();

        $this->need_param=array('openid','id','sign');
        $this->_need_param();
        $this->_check_sign();

        $do=M('goods');
        if($do->where(['id' => ['in',I('post.id')],'uid'=>$this->uid,'status'=>1,'officialactivity_join_id' => 0])->save(['status' => 2, 'is_best' => 0])){
            A('Total')->seller_goods_online($this->uid);    //统计在售商品数量
            shop_pr('',$this->uid); //更新店铺pr

            $this->apiReturn(1);
        }else $this->apiReturn(0,'',1,'操作失败！<br /><br />提示：参与官方活动的商品不充许编辑、下架或删除等操作，请检查您要操作的商品是否处于活动中！');
    }

    /**
    * 商品上架
    * @param string $_POST['openid']    用户openid
    * @param int|string     $_POST['id']    商品ID
    */
    public function set_goods_online(){
        //频繁请求限制,间隔300毫秒
        $this->_request_check();

        $this->need_param=array('openid','id','sign');
        $this->_need_param();
        $this->_check_sign();

        $do=M('goods');
        $goodsInfo = $do->field('num,is_collection,category_id')->where(['id' => ['in',I('post.id')],'uid'=>$this->uid,'status'=>2])->find();
        if($goodsInfo['num'] == 0){
            $this->apiReturn(0,array(),1,'库存不能为0');
        }
        if($goodsInfo['is_collection']>0 && $goodsInfo['category_id'] == '100845550'){
             $this->apiReturn(0,array(),1,'导入商品请编辑商品类目');
        }
        # if($do->where(['id' => ['in',I('post.id')],'uid'=>$this->uid,'status'=>2])->where(' AND (is_collection > 0 AND cid("notin", 100845550))')->save(['status' => 1])){
        #     A('Total')->seller_goods_online($this->uid);    //统计在售商品数量

        #     $this->apiReturn(1);
        # }else $this->apiReturn(0);
        if($do->where(['id' => ['in',I('post.id')],'uid'=>$this->uid,'status'=>2])->save(['status' => 1])){
            A('Total')->seller_goods_online($this->uid);    //统计在售商品数量
            shop_pr('',$this->uid); //更新店铺pr

            $this->apiReturn(1);
        }else $this->apiReturn(0);
    }

    /**
    * 删除商品
    * @param string $_POST['openid']    用户openid
    * @param int|string     $_POST['id']    商品ID
    */
    public function goods_delete(){
        //频繁请求限制,间隔300毫秒
        $this->_request_check();

        $this->need_param=array('openid','id','sign');
        $this->_need_param();
        $this->_check_sign();

        $do=M('goods');
        if($do->where(['id' => ['in',I('post.id')],'uid'=>$this->uid,'status'=>['in','1,2,3,5'],'officialactivity_join_id' => 0])->save(['status'=>0])){
            A('Total')->seller_goods_online($this->uid);    //统计在售商品数量
            shop_pr('',$this->uid); //更新店铺pr

            $this->apiReturn(1);
        }else $this->apiReturn(0,'',1,'操作失败！<br /><br />提示：参与官方活动的商品不充许编辑、下架或删除等操作，请检查您要操作的商品是否处于活动中！');
    }


    /**
    * 仓库中的商品
    * @param string $_POST['openid']    用户openid
    */
    public function goods_offline(){
         //频繁请求限制,间隔300毫秒
        $this->_request_check();

        //必传参数检查
        $this->need_param=array('openid','sign');
        $this->_need_param();
        $this->_check_sign();


        $map['seller_id']   =$this->uid;
        $map['status']      =2;

        if(I('post.shop_category_id')!='')    $map['shop_category_id']=['in',sortid(['table'=>'shop_goods_category','sid'=>I('post.my_cid')])]; //店铺内分类
        if(I('post.category_id')!='')   $map['category_id']=['in',sortid(['table'=>'goods_category','sid'=>I('post.cid')])];    //商品类目
        if(I('post.q')!='') $map['goods_name']=['like','%'.I('post.q').'%'];    //搜索关键词
        if(I('post.code')!='') $map['code']=['like','%'.I('post.code').'%'];    //搜索商品编号

        //价格区间
        if(I('post.s_price')!='' && I('post.e_price')!='') $map['price']=['between',[I('post.s_price'),I('post.e_price')]];
        if(I('post.s_price')!='' && I('post.e_price')=='') $map['price']=['gt',I('post.s_price')];
        if(I('post.s_price')=='' && I('post.e_price')!='') $map['price']=['lt',I('post.e_price')];

        //销量区间
        if(I('post.s_sale')!='' && I('post.e_sale')!='') $map['sale_num']=['between',[I('post.s_sale'),I('post.e_sale')]];
        if(I('post.s_sale')!='' && I('post.e_sale')=='') $map['sale_num']=['gt',I('post.e_sale')];
        if(I('post.s_sale')=='' && I('post.e_sale')!='') $map['sale_num']=['lt',I('post.e_sale')];

        if(I('post.is_best')!='') $map['is_best']=1;    //推荐商品

        $pagesize=I('post.pagesize')?I('post.pagesize'):20;

        $pagelist=pagelist(array(
                'table'     =>'GoodsRelation',
                'do'        =>'D',
                'map'       =>$map,
                'order'     =>'id desc',
                'fields'    =>'id,atime,uptime,category_id,shop_category_id,goods_name,images,price,num,sale_num,score_ratio,code',
                'pagesize'  =>$pagesize,
                'relation'  =>'attr_list',
                'relationLimit'=>array('goods_attr_list',1),
                'relationField'=>array('goods_attr_list','id,concat("/Goods/view/id/",id,".html") as detail_url'),
                'action'    =>I('post.action'),
                'query'     =>I('post.query'),
                'p'         =>I('post.p')?I('post.p'):1,
            ));

        foreach($pagelist['list'] as $key=>$val){
            $pagelist['list'][$key]['images']           =myurl($val['images'],(I('post.imgsize')?I('post.imgsize'):160));
            $pagelist['list'][$key]['category_name']    = nav_sort(['table' => 'goods_category','field'=>'id,sid,category_name','id'=>$val['category_id'],'key'=>'category_name']);

            if($val['shop_category_id'])  $pagelist['list'][$key]['my_category_name']    = nav_sort(['table' => 'shop_goods_category','field'=>'id,sid,category_name','id'=>$val['shop_category_id'],'key'=>'category_name']);
        }

        if($pagelist['listnum']>0) $this->apiReturn(1,['data' => $pagelist]);
        else $this->apiReturn(3);
       
    }


    /**
    * 主图有异常的商品
    * @param string $_POST['openid']    用户openid
    */
    public function goods_bad_images(){
         //频繁请求限制,间隔300毫秒
        $this->_request_check();

        //必传参数检查
        $this->need_param=array('openid','sign');
        $this->_need_param();
        $this->_check_sign();


        $map['seller_id']   =$this->uid;
        $map['status']      =3;

        if(I('post.shop_category_id')!='')    $map['shop_category_id']=['in',sortid(['table'=>'shop_goods_category','sid'=>I('post.my_cid')])]; //店铺内分类
        if(I('post.category_id')!='')   $map['category_id']=['in',sortid(['table'=>'goods_category','sid'=>I('post.cid')])];    //商品类目
        if(I('post.q')!='') $map['goods_name']=['like','%'.I('post.q').'%'];    //搜索关键词


        //价格区间
        if(I('post.s_price')!='' && I('post.e_price')!='') $map['price']=['between',[I('post.s_price'),I('post.e_price')]];
        if(I('post.s_price')!='' && I('post.e_price')=='') $map['price']=['gt',I('post.s_price')];
        if(I('post.s_price')=='' && I('post.e_price')!='') $map['price']=['lt',I('post.e_price')];

        //销量区间
        if(I('post.s_sale')!='' && I('post.e_sale')!='') $map['sale_num']=['between',[I('post.s_sale'),I('post.e_sale')]];
        if(I('post.s_sale')!='' && I('post.e_sale')=='') $map['sale_num']=['gt',I('post.e_sale')];
        if(I('post.s_sale')=='' && I('post.e_sale')!='') $map['sale_num']=['lt',I('post.e_sale')];

        if(I('post.is_best')!='') $map['is_best']=1;    //推荐商品

        $pagesize=I('post.pagesize')?I('post.pagesize'):20;

        $pagelist=pagelist(array(
                'table'     =>'GoodsRelation',
                'do'        =>'D',
                'map'       =>$map,
                'order'     =>'id desc',
                'fields'    =>'id,atime,uptime,category_id,shop_category_id,goods_name,images,price,num,sale_num,score_ratio',
                'pagesize'  =>$pagesize,
                'relation'  =>'attr_list',
                'relationLimit'=>array('goods_attr_list',1),
                'relationField'=>array('goods_attr_list','id,concat("/Goods/view/id/",id,".html") as detail_url'),
                'action'    =>I('post.action'),
                'query'     =>I('post.query'),
                'p'         =>I('post.p')?I('post.p'):1,
            ));

        foreach($pagelist['list'] as $key=>$val){
            $pagelist['list'][$key]['images']           =myurl($val['images'],(I('post.imgsize')?I('post.imgsize'):160));
            $pagelist['list'][$key]['category_name']    = nav_sort(['table' => 'goods_category','field'=>'id,sid,category_name','id'=>$val['category_id'],'key'=>'category_name']);

            if($val['shop_category_id'])  $pagelist['list'][$key]['my_category_name']    = nav_sort(['table' => 'shop_goods_category','field'=>'id,sid,category_name','id'=>$val['shop_category_id'],'key'=>'category_name']);
        }

        if($pagelist['listnum']>0) $this->apiReturn(1,['data' => $pagelist]);
        else $this->apiReturn(3);       
    }

    /**
    * 违规商品
    * @param string $_POST['openid']    用户openid
    */
    public function goods_illegl(){
         //频繁请求限制,间隔300毫秒
        $this->_request_check();

        //必传参数检查
        $this->need_param=array('openid','sign');
        $this->_need_param();
        $this->_check_sign();

        $status_name=['取消违规','违规下架','等待审核','审核未通过','审核通过'];
        $map['uid']   =$this->uid;
        //$map['status']      =4;
        $pagesize=I('post.pagesize')?I('post.pagesize'):20;

        $pagelist=pagelist(array(
                'table'     =>'GoodsIlleglRelation',
                'do'        =>'D',
                'map'       =>$map,
                'order'     =>'id desc',
                'fields'    =>'id,atime,status,uid,shop_id,goods_id,status,reason,illegl_point',
                'pagesize'  =>$pagesize,
                'relation'  =>true,
                'action'    =>I('post.action'),
                'query'     =>I('post.query'),
                'p'         =>I('post.p')?I('post.p'):1,
            ));

        foreach($pagelist['list'] as $key=>$val){
            $pagelist['list'][$key]['status_name']=$status_name[$val['status']];
            $pagelist['list'][$key]['goods']['images']=myurl($val['goods']['images'],(I('post.imgsize')?I('post.imgsize'):160));

        }

        if($pagelist['listnum']>0) $this->apiReturn(1,['data' => $pagelist]);
        else $this->apiReturn(3);       
    }

    /**
    * 提交违规商品进行审核
    * @param string $_POST['openid']    用户openid
    * @param int|string $_POST['id']    违规记录ID,多个用逗号隔开
    */
    public function goods_illegl_add(){
        //频繁请求限制,间隔300毫秒
        $this->_request_check();

        //必传参数检查
        $this->need_param=array('openid','id','sign');
        $this->_need_param();
        $this->_check_sign();       

        if(M('goods_illegl')->where(['uid'=>$this->uid,'id'=>['in',I('post.id')]])->save(['status'=>2,'dotime'=>date('Y-m-d H:i:s')]) !== false){
            $this->apiReturn(1);
        }else{
            $this->apiReturn(0);
        }
    }


    /**
    * 店铺宝贝分类
    * @param int $_POST['openid'] 用户openid
    */
    public function category(){
        //频繁请求限制,间隔2秒
        $this->need_param=array('openid','sign');
        $this->_need_param();
        $this->_check_sign();

        $list=get_category(['table' => 'shop_goods_category' , 'level' => 2,'field'=>'id,sid,category_name,icon,sort' , 'map' => [0 => ['uid' => $this->uid]]]);
        if($list){
            $this->apiReturn(1,['data' => $list]);
        }else{
            $this->apiReturn(3);
        }
    }

    /**
    * 店内商品一级分类
    */
    public function category_first(){
        //频繁请求限制,间隔2秒
        $this->need_param=array('openid','sign');
        $this->_need_param();
        $this->_check_sign();        

        $list=M('shop_goods_category')->where(['uid'=>$this->uid,'sid' => 0])->field('id,sid,category_name,icon')->order('sort asc')->select();

        if($list) $this->apiReturn(1,['data'=>$list]);
        else $this->apiReturn(3);
    }

    /**
    * 店内商品分类详情
    * @param int $_POST['openid']   用户openid
    * @param int $_POST['id']       分类ID
    */
    public function category_view(){
        //频繁请求限制,间隔300毫秒
        $this->_request_check();

        $this->need_param=array('openid','id','sign');
        $this->_need_param();
        $this->_check_sign();

        $rs=M('shop_goods_category')->where(['uid' => $this->uid,'id' => I('post.id')])->field('id,atime,sid,category_name,icon,sort')->find();

        if($rs){
            $this->apiReturn(1,['data' => $rs]);
        }else $this->apiReturn(3);

    }

    /**
    * 添加店内商品分类
    * @param int $_POST['openid'] 用户openid
    * @param string     $_POST['category_name'] 分类名称
    * @param int        $_POST['sid']           上级分类
    * @param string     $_POST['icon']          icon图片
    */
    public function category_add(){
        //频繁请求限制,间隔300毫秒
        $this->_request_check();

        $this->need_param=array('openid','category_name','sign');
        $this->_need_param();
        $this->_check_sign();

        if(empty($_POST['sid'])) $_POST['sid']=0;

        $_POST['uid']       =   $this->uid;
        $_POST['shop_id']   =   M('shop')->where(['uid' => $this->uid])->getField('id');
        $do=D('Common/ShopGoodsCategory');
        if(!$data=$do->create()) $this->apiReturn(4,'',1,$do->getError());

        if(!$data['id']=$do->add()) $this->apiReturn(0);
        $this->apiReturn(1,['data'=>$data]);
    }

    /**
    * 修改店内商品分类
    * @param int $_POST['openid'] 用户openid
    * @param string     $_POST['category_name'] 分类名称
    * @param int        $_POST['sid']           上级分类
    * @param string     $_POST['icon']          icon图片
    * @param int        $_POST['id']            分类ID
    */
    public function category_edit(){
        //频繁请求限制,间隔300毫秒
        $this->_request_check();

        $this->need_param=array('openid','category_name','id','sign');
        $this->_need_param();
        $this->_check_sign();

        if(empty($_POST['sid'])) $_POST['sid']=0;

        $_POST['uid']=$this->uid;
        $do=D('Common/ShopGoodsCategory');
        if(!$do->create()) $this->apiReturn(4,'',1,$do->getError());

        if(!$do->save()) $this->apiReturn(0);
        $this->apiReturn(1);
    }

    /**
    * 删除店内商品分类
    * @param int $_POST['openid']       用户openid    
    * @param int|string $_POST['id']    分类ID
    */
    public function category_delete(){
        //频繁请求限制,间隔300毫秒
        $this->_request_check();

        $this->need_param=array('openid','id','sign');
        $this->_need_param();
        $this->_check_sign();

        $ids=sortid(['table' => 'shop_goods_category','sid' => I('post.id')]);
        if(M('shop_goods_category')->where(['uid' => $this->uid ,'id' => ['in',$ids]])->delete()){
            $this->apiReturn(1);
        }else $this->apiReturn(0);
    }

    /**
    * 批量删除分类
    * @param int $_POST['openid']       用户openid    
    * @param int|string $_POST['id']    分类ID    
    */
    public function category_more_delete(){
        //频繁请求限制,间隔300毫秒
        $this->_request_check();

        $this->need_param=array('openid','id','sign');
        $this->_need_param();
        $this->_check_sign();

        $ids=array();
        $id=explode(',', I('post.id'));
        foreach($id as $val){
            if($val){
                $ids=array_merge($ids,sortid(['table' => 'shop_goods_category','sid' => $val]));
            }
        }
        
        if(M('shop_goods_category')->where(['uid' => $this->uid ,'id' => ['in',$ids]])->delete()){
            $this->apiReturn(1);
        }else $this->apiReturn(0);
    }    

    /**
    * 修改商品标题
    * @param string $_POST['openid']    用户openid
    * @param string $_POST['godos_name']    商品标题
    * @param int    $_POST['goods_id']      商品ID
    */
    public function goods_name_edit(){
        //频繁请求限制,间隔300毫秒
        $this->_request_check();

        $this->need_param=array('openid','goods_name','goods_id','sign');
        $this->_need_param();
        $this->_check_sign();

        if(M('goods')->where(['id' => I('post.goods_id'),'seller_id' => $this->uid])->save(['goods_name' => I('post.goods_name')])){
            $this->apiReturn(1);
        }else{
            $this->apiReturn(0);
        }

    }


    /**
    * 获取商品SKU
    * @param string $_POST['openid']    用户openid
    * @param int    $_POST['goods_id']      商品ID    
    */
    public function goods_sku(){
        //频繁请求限制,间隔300毫秒
        $this->_request_check();

        $this->need_param=array('openid','goods_id','sign');
        $this->_need_param();
        $this->_check_sign();

        $do=M('goods_attr_list');
        $list=$do->where(['seller_id' => $this->uid,'goods_id' => I('post.goods_id')])->field('id,attr_name,goods_id,images,price,price_market,price_purchase,weight,num,code,barcode')->select();

        if($list){
            $list=imgsize_list($list,'images',160);

            $this->apiReturn(1,['data' => $list]);
        }else $this->apiReturn(3);

    }

    /**
    * 修改商品SKU
    * @param string $_POST['openid']    用户openid    
    * @param int $_POST['goods_id']  商品ID   
    * @param string $_POST['id']           库存ID
    * @param string $_POST['price']      售价   
    * @param string $_POST['num']          库存数量  
    * @param string $_POST['price_market']   市场价
    * @param string $_POST['price_purchase'] 成本价
    * @param string $_POST['weight']     重量
    * @param string $_POST['code']      编码
    * @param string $_POST['barcode']   条型码 
    */

    public function goods_sku_edit(){
        //频繁请求限制,间隔300毫秒
        $this->_request_check();

        $this->need_param=array('openid','goods_id','id','price','num','sign');
        $this->_need_param();
        $this->_check_sign();

        //验证记录合法性
        if(!$rs = M('goods')->where(['uid' => $this->uid,'id' => I('post.goods_id')])->field('id,officialactivity_join_id')->find()) $this->apiReturn(980);    //找不到对应的商品记录！

        //商品参与官方活动中，不充许编辑
        if($rs['officialactivity_join_id'] >0 ) $this->apiReturn(1700);


        $field=['id','price','num','price_market','price_purchase','weight','code','barcode'];
        foreach($field as $val){
            parse_str($_POST[$val],$_POST[$val]);
        }

        //数据验证
        $datas=array();
        foreach(I('post.id') as $i=>$val){
            $data=[
                'id'                =>$val,
                'price'             =>$_POST['price'][$i],
                'num'               =>$_POST['num'][$i],
                'price_market'      =>empty($_POST['price_market'])?0:$_POST['price_market'][$i],
                'price_purchase'    =>empty($_POST['price_purchase'])?0:$_POST['price_purchase'][$i],
                'weight'            =>empty($_POST['weight'])?0:$_POST['weight'][$i],
                'code'              =>empty($_POST['code'])?'':$_POST['code'][$i],
                'barcode'           =>empty($_POST['barcode'])?'':$_POST['barcode'][$i]
            ];

            if(!isset($min_price)) $min_price=$data['price'];
            else $min_price=min($min_price,$data['price']);

            if(!isset($max_price)) $max_price=$data['price'];
            else $max_price=max($max_price,$data['price']);

            //第{n}条库存记录市场价必须大于或等于销售价！
            if($data['price_market']<$data['price'] && $data['price_market']>0) $this->apiReturn(4,'',1,str_replace('{n}', ($i+1), C('error_code.981')));

            //第{n}条库存记录成本价必须小于或等于销售价！
            if($data['price_purchase']>$data['price'] && $data['price_purchase']>0) $this->apiReturn(4,'',1,str_replace('{n}', ($i+1), C('error_code.982')));

            if(!D('Common/GoodsAttrList')->create($data)) $this->apiReturn(4,'',1,D('Common/GoodsAttrList')->getError());

            $datas[]=$data;
        }


        //最高价和最低价相差不得超过50%;
        //if(($min_price*1.5) < $max_price || ($max_price*0.5) > $min_price) $this->apiReturn(983);

        $n=0;   //正常更新计数
        foreach($datas as $val){
            if(M('goods_attr_list')->save($val) !== false) $n++;
        }

        $num=M('goods_attr_list')->where(['goods_id' => I('post.goods_id')])->sum('num');
        M('goods')->where(['id' => I('post.goods_id')])->save(['num' => $num,'price' => $min_price]);
        
        if($n>0) $this->apiReturn(1);
        else $this->apiReturn(0);
        
    }

    /**
    * 设置橱窗商品（推荐）
    * @param string $_POST['openid']    用户openid
    * @param int    $_POST['id']        商品ID
    */
    public function set_best(){
        //频繁请求限制,间隔300毫秒
        $this->_request_check();
        
        $this->need_param=array('openid','id','sign');
        $this->_need_param();
        $this->_check_sign();

        $max_best=M('shop')->where(['uid' => $this->uid])->getField('max_best');

        $count=M('goods')->where(['seller_id' => $this->uid,'status' => 1,'num' => ['gt',0],'is_best' =>1])->count();

        $goods_id=explode(',',trim(I('post.id'),','));
        
        //橱窗商品超过限制
        if(count($goods_id)+$count > $max_best) $this->apiReturn(4,'',1,str_replace('{max_best}', $max_best, C('error_code.984')));
        
        if(M('goods')->where(['id' => ['in',$goods_id],'status' => 1])->setField('is_best',1) === false) {
            goods_pr($goods_id); //更新商品PR
            $this->apiReturn(0);
        }else $this->apiReturn(1);

    }

    /**
    * 取消橱窗商品（推荐）
    * @param string $_POST['openid']    用户openid
    * @param int    $_POST['id']        商品ID
    */
    public function cancel_best(){
        //频繁请求限制,间隔300毫秒
        $this->_request_check();
        
        $this->need_param=array('openid','id','sign');
        $this->_need_param();
        $this->_check_sign();


        if(M('goods')->where(['id' => ['in',I('post.id')],'status' => 1])->setField('is_best',0)) {
            goods_pr(I('post.id')); //更新商品PR
            $this->apiReturn(1);
        }else $this->apiReturn(0);

    }


    /**
    * 品牌
    * @param string $_POST['openid']    用户openid
    */
    public function brand(){
        //必传参数检查
        $this->need_param=array('openid','sign');
        $this->_need_param();
        $this->_check_sign(); 
        
        $list=M('brand')->where(['uid' => $this->uid,'status' => 1])->field('id,b_name,b_ename,b_logo')->order('b_name asc')->select();
        if($list) $this->apiReturn(1,['data' => $list]);
        else $this->apiReturn(3);
    }

    /**
    * 取充许上传商品的一级类目
    * @param string $_POST['openid']    用户openid
    */
    public function first_category(){
        //必传参数检查
        $this->need_param=array('openid','sign');
        $this->_need_param();
        $this->_check_sign();   
        
        $shop=M('shop')->where(['uid' => $this->uid])->field('category_id')->find();
        if(!$shop) $this->apiReturn(651);   //还未开店，无权限操作

        if($shop['category_id']){
            $list=M('goods_category')->cache(true,C('CACHE_LEVEL.S'))->where(['id' => ['in',$shop['category_id']],'status' => 1])->field('id,sid,category_name')->order('sort asc')->select();
        }else{
            $list=M('goods_category')->cache(true,C('CACHE_LEVEL.S'))->where(['status' => 1])->field('id,category_name')->order('sort asc')->select();
        }

        if($list) $this->apiReturn(1,['data' => $list]);
        else $this->apiReturn(3);

    }

    /**
    * 商品统计
    * @param string $_POST['openid']    用户openid
    */
    public function total_goods(){
        //必传参数检查
        $this->need_param=array('openid','sign');
        $this->_need_param();
        $this->_check_sign();
                
        $res=A('Total')->seller_goods($this->uid);

        $this->apiReturn(1,['data' => $res]);
    }
}