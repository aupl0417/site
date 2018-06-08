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
class SupplierGoodsManageController extends CommonController {
    protected $action_logs = array('set_goods_offline','set_goods_online','goods_delete','goods_illegl_add','category_add','category_edit','category_delete','category_more_delete','goods_name_edit','goods_sku_edit','set_best','cancel_best','category_brand_adds','category_price_adds','category_sort');
	public function index(){
    	redirect(C('sub_domain.www'));
    }
	
	
	/**
     * 获取卖家信息
     * Create by liangfeng
     * 2017-09-07
     */
	private function get_seller(){
		$id = C('cfg.supplier')['seller_id'];
		$rs = M('user')->cache(true)->field('id,level_id,nick,password_pay,is_auth,shop_type,erp_uid,shop_id')->find($id);
		return $rs;
	}
	
	
    /**
    * 所有的商品
    * @param string $_POST['openid']    用户openid
    */
    public function goods_all(){
        //频繁请求限制,间隔300毫秒
        $this->_request_check();

        //必传参数检查
        $this->need_param=array('openid','sign');
        $this->_need_param();
        $this->_check_sign();


		$seller_info = $this->get_seller();
        $map['seller_id']   =$seller_info['id'];
        $map['supplier_id']   =$this->uid;
      
		if(I('post.status')!=''){
			$map['status'] = ['eq',I('post.status')];
		}else{
			$map['status'] = array('neq',0);
		}
        if(I('post.is_best')==1) {
            $map['is_best'] = 1;
        }

		if(I('post.brand_id')!='') $map['brand_id'] = ['eq',I('post.brand_id')]; 
        if(I('post.shop_category_id')!=''){
			if(I('post.shop_category_id') == 0){
				$map['shop_category_id'] = array('elt', 0);
			}else{
				$sql = M('shop_goods_category');
				$ids = $sql->where(['sid'=>I('post.shop_category_id')])->getField('id',true);
				$map['_string'] =   'FIND_IN_SET('.I('post.shop_category_id').', shop_category_id)';
				if($ids){
					foreach($ids as $val){
						$map['_string'] .= ' or FIND_IN_SET('.$val.', shop_category_id)';
					}
				}
				
			}
		}
        if(I('post.q')!='') $map['goods_name']=['like','%'.I('post.q').'%'];    //搜索关键词
        //价格区间
        if(I('post.s_price')!='' && I('post.e_price')!='') $map['price']=['between',[I('post.s_price'),I('post.e_price')]];
        if(I('post.s_price')!='' && I('post.e_price')=='') $map['price']=['gt',I('post.s_price')];
        if(I('post.s_price')=='' && I('post.e_price')!='') $map['price']=['lt',I('post.e_price')];
        if (!empty(I('post.score_type'))) $map['score_type'] = I('post.score_type');
        $pagesize=I('post.pagesize')?I('post.pagesize'):20;
        $pagelist=pagelist(array(
                'table'     =>'GoodsRelation',
                'do'        =>'D',
                'map'       =>$map,
                'order'     =>'id desc',
                'fields'    =>'id,atime,uptime,category_id,shop_category_id,goods_name,images,price,num,sale_num,score_ratio,code,is_best,status,express_tpl_id,score_ratio,is_collection,score_type,examine_status,examine_reason',
                'pagesize'  =>$pagesize,
                'relation'  =>'attr_list',
                'relationLimit'=>array('goods_attr_list',1),
                'relationField'=>array('goods_attr_list','id,concat("/Goods/view/id/",id,".html") as detail_url'),
                'action'    =>I('post.action'),
                'query'     =>I('post.query'),
                'p'         =>I('post.p')?I('post.p'):1,
            ));
//writeLog(M()->getlastsql());
		$do=M("shop_goods_category");
        foreach($pagelist['list'] as $key=>$val){
            $pagelist['list'][$key]['images']           =myurl($val['images'],(I('post.imgsize')?I('post.imgsize'):160));
            $pagelist['list'][$key]['category_name']    = nav_sort(['table' => 'goods_category','field'=>'id,sid,category_name','id'=>$val['category_id'],'key'=>'category_name']);
            if ($val['status'] == 4) $pagelist['list'][$key]['illegl_reason'] = M('goods_illegl')->where(['goods_id' => $val['id']])->cache(true)->getField('reason');
			if($val['shop_category_id']){
				$rs=$do->where(['id'=>array('in',$val['shop_category_id'])])->getField("id,category_name",true);	
				$pagelist['list'][$key]['my_category_name'] = $rs;
			}
        }

        if($pagelist['listnum']>0) $this->apiReturn(1,['data' => $pagelist]);
        else $this->apiReturn(3);
       
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


        $seller_info = $this->get_seller();
        $map['seller_id']   =$seller_info['id'];
        $map['supplier_id']   =$this->uid;
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
                'fields'    =>'id,atime,uptime,category_id,shop_category_id,goods_name,images,price,num,sale_num,score_ratio,code,is_best,status',
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
        
        
        $seller_info = $this->get_seller();
        $map['seller_id']   =$seller_info['id'];
        $map['supplier_id']   =$this->uid;
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
        $goodsInfo = $do->field('num,is_collection,category_id')->where(['id' => ['in',I('post.id')],'uid'=>$this->uid,'status'=>1])->find();
        if(!$goodsInfo){
            $this->apiReturn(0,array(),1,'商品不存在或已下架');
        }
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
        if(!$goodsInfo){
            $this->apiReturn(0,array(),1,'商品不存在或不符合上架条件');
        }
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
            goods_pr(I('post.id'));
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


        $seller_info = $this->get_seller();
        $map['seller_id']   =$seller_info['id'];
        $map['supplier_id']   =$this->uid;
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

		$seller_info = $this->get_seller();
     
		
        $list=get_category(['table' => 'shop_goods_category' , 'level' => 2,'field'=>'id,sid,category_name,icon,sort,atime,category_type' , 'map' => [0 => ['uid' => $seller_info['id']]]]);
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
     * 自动分类_查找（品牌）
     * author 梁丰
     * @param int $_POST['openid'] 用户openid
     */
    public function category_auto_brand(){
        //频繁请求限制,间隔2秒
        $this->_request_check(2);
        $this->need_param=array('openid','sign');
        $this->_need_param();
        $this->_check_sign();

        $where['seller_id'] = $this->uid;
        $where['status'] = ['gt',0];
        $limit = 1000;//每次读取的数据
        $sleep_time = 10000;//每次更新睡眠时间

        $count = D('GoodsRelation')->where($where)->count();
        $num = ceil($count/$limit);
        //循环读取所有商品的品牌
        for($i=0;$i<$num;$i++){
            $start = $i*$limit;
            $list = D('GoodsRelation')->where($where)->limit($start,$limit)->order('id asc')->select();
            foreach($list as $v){
                if($v['brand_id'] != 0){
                    $brands[$v['brand_id']]['num']++ ;
                }
            }
            usleep($sleep_time);
        }
        if(!empty($brands)){
            $str = '';
            foreach($brands as $k => $v){
                $str = $k.',';
            }
            $str = substr($str,0,strlen($str)-1);
            $where['id']  = array('in',$str);
            $cate_list = M('brand')->field('id,b_name')->where($where)->select();
            foreach($cate_list as $k => $v){
                $cate_list[$k]['goods_num'] = $brands[$v['id']]['num'];
            }
        }else{
            $cate_list = array();
        }
        $result['data'] = $cate_list;
        $this->apiReturn(1,['data'=>$cate_list]);
    }
    /**
     * 批量添加店内商品分类并归类(品牌)
     * author 梁丰
     * @param int $_POST['openid'] 用户openid
     * @param string     $_POST['category_names'] 分类名称
     */
    public function category_brand_adds(){
        //频繁请求限制,间隔1秒
        $this->_request_check(1);
        $this->need_param=array('openid','category_names','sign');
        $this->_need_param();
        $this->_check_sign();
        $limit = 100;//每次读取的数据
        $sleep_time = 10000;//每次更新睡眠时间
        $parent_cate_name = '品牌分类';

        $category_names=explode('|',I('post.category_names'));
        foreach($category_names as $v){
            $res = explode(',',$v);
            if(empty($res[1])){
                $this->apiReturn(0,['msg'=>'请填写分类名称']);
            }
            $re['brand_id'] = $res['0'];
            $re['cate_name'] = $res['1'];
            $brands[] = $re;
        }
        //$this->apiReturn(1,['data'=>$brands]);

        $shop_id   =   M('shop')->where(['uid' => $this->uid])->getField('id');

        //查找是否已存在一级分类
        $sid = M('shop_goods_category')->where(['uid'=>$this->uid,'category_name'=>$parent_cate_name,'sid'=>0])->getField('id');
        if(!$sid){
            $data['uid']=$this->uid;
            $data['category_name']=$parent_cate_name;
            $data['shop_id']=$shop_id;
            $data['sid']=0;
            $data['category_type']=2;
            $do=D('Common/ShopGoodsCategory');
            if(!$res=$do->create($data)){
                $this->apiReturn(4,'',1,$do->getError());
            }
            if(!$sid=$do->add()){
                $this->apiReturn(0,['msg'=>'添加分类失败！']);
            }
        }

        //添加分类
        foreach($brands as $v){
            //查找是否已存在此分类
            $id = M('shop_goods_category')->where(['uid'=>$this->uid,'category_name'=>$v['cate_name'],'sid'=>$sid])->getField('id');
            if(!$id){
                $data['uid']=$this->uid;
                $data['category_name']=$v['cate_name'];
                $data['shop_id']=$shop_id;
                $data['sid']=$sid;
                $data['category_type']=2;
                $do=D('Common/ShopGoodsCategory');
                if(!$res=$do->create($data)){
                    $this->apiReturn(4,'',1,$do->getError());
                }

                if(!$id=$do->add()){
                    $this->apiReturn(0,['msg'=>'添加分类失败！']);
                }
            }

            //根据价格条件范围归类商品
            $where['seller_id'] = $this->uid;
            $where['status'] = ['gt', 0];
            $where['shop_category_id'] = ['notlike', '%'.$id.'%'];
            $where['brand_id'] = $v['brand_id'];

            $count = D('GoodsRelation')->where($where)->count();
            $num = ceil($count / $limit);

            for ($i = 0; $i < $num; $i++) {
                $list = M('goods')->field('id,shop_category_id')->where($where)->order('id asc')->limit($start, $limit)->select();
                foreach ($list as $va) {
                    if($va['shop_category_id']){
                        $data2['shop_category_id'] = $va['shop_category_id'] . ',' . $id;
                    }else{
                        $data2['shop_category_id'] = $id;
                    }
                    $save_res = M('goods')->where(['id' => $va['id']])->data($data2)->save();
                    if(!$save_res){
                        $this->apiReturn(0);
                    }
                }
                usleep($sleep_time);
            }
        }
        $this->apiReturn(1);
    }
    /**
     * 批量添加店内商品分类并归类(价格)
     * author 梁丰
     * @param int $_POST['openid'] 用户openid
     * @param string     $_POST['min_prices'] 最小价格
     * @param string     $_POST['max_prices'] 最大价格
     * @param string     $_POST['category_names'] 分类名称
     */
    public function category_price_adds(){
        //频繁请求限制,间隔300毫秒
        $this->_request_check();
        $this->need_param=array('openid','price_cates','sign');
        $this->_need_param();
        $this->_check_sign();
        $limit = 100;//每次读取的数据
        $sleep_time = 10000;//每次更新睡眠时间
        $parent_cate_name = '价格分类';

        $price_cates = explode('|',I('post.price_cates'));

        if(!empty($price_cates)) {
            $shop_id   =   M('shop')->where(['uid' => $this->uid])->getField('id');

            //判断是否存在一级分类
            $sid = M('shop_goods_category')->where(['uid' => $this->uid, 'category_name' => $parent_cate_name, 'sid' => 0])->getField('id');
            if(!$sid){
                $data['uid'] = $this->uid;
                $data['category_name'] = $parent_cate_name;
                $data['shop_id'] = $shop_id;
                $data['sid'] = 0;
                $data['category_type'] = 2;
                $do = D('Common/ShopGoodsCategory');
                if (!$res = $do->create($data)) {
                    $this->apiReturn(4, '', 1, $do->getError());
                }
                if (!$sid = $do->add()) {
                    $this->apiReturn(0,['msg'=>'添加分类失败！']);
                }
            }

            foreach ($price_cates as $v) {
                $price_cate = explode(',', $v);
                $min_price = $price_cate[0];
                $max_price = $price_cate[1];
                if(empty($min_price) && empty($max_price)){
                    $this->apiReturn(0,['msg'=>'最小价格或最大价格必须填写一个,且必须为数字']);
                }
                if(!empty($min_price) && !ctype_digit($min_price)){
                    $this->apiReturn(0,['msg'=>'最小价格或最大价格必须填写一个,且必须为数字']);
                }
                if(!empty($max_price) && !ctype_digit($max_price)){
                    $this->apiReturn(0,['msg'=>'最小价格或最大价格必须填写一个,且必须为数字']);
                }
                $category_name = $price_cate[2];
                if(empty($category_name)){
                    $this->apiReturn(0,['msg'=>'请填写分类名称']);
                }

                //根据名称判断是否已经添加此分类
                $id = M('shop_goods_category')->where(['uid' => $this->uid, 'category_name' => $category_name, 'sid' => $sid])->getField('id');

                if (!$id) {
                    $data['uid'] = $this->uid;
                    $data['category_name'] = $category_name;
                    $data['shop_id'] = $shop_id;
                    $data['sid'] = $sid;
                    $data['category_type'] = 2;
                    $do = D('Common/ShopGoodsCategory');
                    if (!$res = $do->create($data)) {
                        $this->apiReturn(4, '', 1, $do->getError());
                    }
                    if (!$id = $do->add()) {
                        $this->apiReturn(0,['msg'=>'添加分类失败！']);
                    }
                }

                //根据价格条件范围归类商品
                if ($min_price != '' && $max_price != '') $where['price'] = ['between', [$min_price, $max_price]];
                if ($min_price != '' && $max_price == '') $where['price'] = ['egt', $min_price];
                if ($min_price == '' && $max_price != '') $where['price'] = ['elt', $max_price];
                $where['seller_id'] = $this->uid;
                $where['status'] = ['gt', 0];
                $where['shop_category_id'] = ['notlike', '%'.$id.'%'];

                $count = D('GoodsRelation')->where($where)->count();
                $num = ceil($count / $limit);
                for ($i = 0; $i < $num; $i++) {
                    $list = M('goods')->field('id,shop_category_id')->where($where)->order('id asc')->limit($start, $limit)->select();
                    foreach ($list as $va) {
                        if($va['shop_category_id']){
                            $data2['shop_category_id'] = $va['shop_category_id'] . ',' . $id;
                        }else{
                            $data2['shop_category_id'] = $id;
                        }
                        $save_res = M('goods')->where(['id' => $va['id']])->data($data2)->save();
                        if(!$save_res){
                            $this->apiReturn(0);
                        }
                    }
                    usleep($sleep_time);
                }
            }
            $this->apiReturn(1);
        }else{
            $this->apiReturn(0);
        }

    }
    /**
     * 修改店内商品分类排序
     * author 梁丰
     * @param int $_POST['openid'] 用户openid
     * @param ids     $_POST['ids']          icon图片
     */
    public function category_sort(){
        //频繁请求限制,间隔300毫秒
        $this->_request_check();
        $this->need_param=array('openid','ids','sign');
        $this->_need_param();
        $this->_check_sign();

        $data = explode(',',I('post.ids'));
        foreach($data as $key=>$val){
            M('shop_goods_category')->where(['id'=>$val])->setField('sort',$key);
        }
        $this->apiReturn(1);
    }
    /**
     * 批量保存商品分类
     * author 梁丰
     * @param int $_POST['openid'] 用户openid
     * @param string     $_POST['category_name'] 需要更改的分类信息
     */
    public function category_all_edit(){
        //频繁请求限制,间隔300毫秒
        $this->_request_check();
        $this->need_param=array('openid','str','sign');
        $this->_need_param();
        $this->_check_sign();

        $cate_info_str = explode('|',I('post.str'));

        //检测提交的数据
        foreach($cate_info_str as $v){
            if($v){
                $res = explode(',',$v);
                if(!preg_match('/^\d+$/i', $res[0]) || !preg_match('/^\d+$/i', $res[2])){
                    $this->apiReturn(0,['msg'=>'数据错误']);
                }
                if(empty($res[1])){
                    $this->apiReturn(0,['msg'=>'请填写分类名称']);
                }
                $re['id'] = $res[0];
                $re['category_name'] = $res[1];
                $re['sid'] = $res[2];
                $cate_infos[] = $re;
            }
        }
        //获取店铺id
        $shop_id = M('shop')->where(['uid' => $this->uid])->getField('id');
        //修改分类
           $do = M();

        foreach($cate_infos as $v){
            if($v['id'] == 0){
                $data['uid'] = $this->uid;
                $data['category_name'] = $v['category_name'];
                $data['shop_id'] = $shop_id;
                $data['sid'] = $v['sid'];
                $ShopGoodsCategory = D('Common/ShopGoodsCategory');
                $res = $ShopGoodsCategory->create($data);
                if($res){
                    $res = $ShopGoodsCategory->add();
                }
            }else{
                $res = M('shop_goods_category')->where(['uid'=>$this->uid,'id'=>$v['id']])->data(['category_name'=>$v['category_name'],'sid'=>$v['sid']])->save();
            }

            if($res === false){

                $this->apiReturn(0,['msg'=>'修改失败']);
            }
        }

        $this->apiReturn(1);
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
        //$check_ids = explode(',',$ids);

        foreach($ids as $v){
            $where['seller_id'] = $this->uid;
            $where['status'] = ['gt',0];
            $where['_string'] = 'FIND_IN_SET('.$v.', shop_category_id)';
            $check_res = M('goods')->field('id')->where($where)->find();
            if($check_res){
                $this->apiReturn(0,['msg'=>'此类目或下级类目下存在宝贝，无法删除']);
            }
        }
        if(M('shop_goods_category')->where(['uid' => $this->uid ,'id' => ['in',$ids]])->delete()){
            $this->apiReturn(1);
        }else $this->apiReturn(0);
    }

    /**
    * 批量删除分类
    * author 梁丰
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
        foreach($ids as $v){
            $where['seller_id'] = $this->uid;
            $where['status'] = ['gt',0];
            $where['_string'] = 'FIND_IN_SET('.$v.', shop_category_id)';
            $check_res = M('goods')->field('id')->where($where)->find();
            if($check_res){
                $this->apiReturn(0,['msg'=>'此类目或下级类目下存在宝贝，无法删除']);
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
        $flag = filterString(I('post.goods_name'));
        if($flag !== true) $this->apiReturn(0, '', 1, '商品名称不可出现' . $flag . '等字词');
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
	
	/**
    * 批量设置运费模板
	* @param string $_POST['ids']               商品的ids
	* @param string $_POST['express_tpl_id']    模板的id
    * @param string $_POST['openid']            用户openid
    */
    public function set_express_tpl(){
        //频繁请求限制,间隔300毫秒
        $this->_request_check();

        $this->need_param=array('openid','id','express_tpl_id','sign');
        $this->_need_param();
        $this->_check_sign();

        $do=M('goods');
        if($do->where(['id' => ['in',I('post.id')],'uid'=>$this->uid])->save(['express_tpl_id' => I('post.express_tpl_id')]) !== false){
			$this->apiReturn(1);
        }else $this->apiReturn(0,'',1,'操作失败！');
    }
	/**
    * 批量追加分类
	* @param string $_POST['ids']               商品的ids
	* @param string $_POST['shop_category_id']  分类的id
    * @param string $_POST['openid']            用户openid
    */
    public function set_shop_category(){
        //频繁请求限制,间隔300毫秒
        $this->_request_check();

        $this->need_param=array('openid','id','shop_category_id','sign');
        $this->_need_param();
        $this->_check_sign();

        $do=M('goods');
		$check = true;
		$res1 = $do->where(['id' => ['in',I('post.id')],'uid'=>$this->uid])->getField("id,shop_category_id",true);
		foreach($res1 as $key=>$val){
			if($val){
				$r = $val.','.I('post.shop_category_id');
			}else{
				$r = I('post.shop_category_id');
			}
			$result = explode(",",$r);
			$result = array_unique($result);
			$res = implode(',',$result);
			$re = $do->where(['id' => $key,'uid'=>$this->uid])->save(['shop_category_id' => $res]);	
			if($re === false){
				$check = false;
			}
		}
        if($check){
			$this->apiReturn(1);
        }else{
			$this->apiReturn(0,'',1,'操作失败！');
		}
    }
	/**
    * 批量修改分类
	* @param string $_POST['ids']               商品的ids
	* @param string $_POST['shop_category_id']  分类的id
    * @param string $_POST['openid']            用户openid
    */
    public function update_shop_category(){
        //频繁请求限制,间隔300毫秒
        $this->_request_check();

        $this->need_param=array('openid','id','shop_category_id','sign');
        $this->_need_param();
        $this->_check_sign();

        $do=M('goods');
		$result = $do->where(['id' => ['in',I('post.id')],'uid'=>$this->uid])->save(['shop_category_id' => I('post.shop_category_id')]);
        if($result){
			$this->apiReturn(1);
        }else{
			$this->apiReturn(0,'',1,'操作失败！');
		}
    }
	
	/**
    * 删除单个商品分类
	* @param string $_POST['id']                商品的id
	* @param string $_POST['shop_category_id']  分类的id
    * @param string $_POST['openid']            用户openid
    */
    public function delete_goods_category(){
        //频繁请求限制,间隔300毫秒
        $this->_request_check();

        $this->need_param=array('openid','id','shop_category_id','sign');
        $this->_need_param();
        $this->_check_sign();

        $do=M('goods');
		$res = $do->where(['id' => ['eq',I('post.id')],'uid'=>$this->uid])->getField("shop_category_id");
		$res = explode(",",$res);
		foreach($res as $key=>$val){
			if($val == I('post.shop_category_id')){
				unset($res[$key]);
			}
		}
		$res = implode(',',$res);
		$result = $do->where(['id' => I('post.id'),'uid'=>$this->uid])->save(['shop_category_id' => $res]);	

        if($result){
			$this->apiReturn(1);
        }else $this->apiReturn(0,'',1,'操作失败！');
    }
	
	/**
    * 批量删除商品分类
	* @param string $_POST['id']                商品的id
	* @param string $_POST['shop_category_id']  分类的id
    * @param string $_POST['openid']            用户openid
    */
    public function batch_delete_category(){
        //频繁请求限制,间隔300毫秒
        $this->_request_check();

        $this->need_param=array('openid','id','shop_category_id','sign');
        $this->_need_param();
        $this->_check_sign();

        $do=M('goods');
		$check = true;
		$res1 = $do->where(['id' => ['in',I('post.id')],'uid'=>$this->uid])->getField("id,shop_category_id",true);
		$res2 = explode(',',I('post.shop_category_id'));
		foreach($res1 as $key=>$val){
			if($val){
				$res = explode(",",$val);
				foreach($res as $ke=>$va){
					foreach($res2 as $k=>$v){
						if($va == $v){
							unset($res[$ke]);
						}
					}
				}
				$res = implode(",",$res);
				$result = $do->where(['id' => $key,'uid'=>$this->uid])->save(['shop_category_id' => $res]);	
				if($result === false){
					$check = false;
				}
			}
		}

        if($check){
			$this->apiReturn(1);
        }else{
			$this->apiReturn(0,'',1,'操作失败！');
		}
    }
}