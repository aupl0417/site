<?php
/**
+----------------------------------------------------------------------
| RestFull API 
+----------------------------------------------------------------------
| 卖家店铺
+----------------------------------------------------------------------
| Author: lazycat <673090083@qq.com>
+----------------------------------------------------------------------
*/

namespace Rest\Controller;
use Think\Controller\RestController;
class ShopController extends CommonController {
    protected $action_logs = array('shop_status');
    
    public function _initialize() {
        parent::_initialize();
        if (isset($_POST['shop_id']) && !empty(I('post.shop_id'))) {
            $shopId = I('post.shop_id', null);
            if (!is_null($shopId)) {
                if (is_numeric($shopId) == false) $_POST['shop_id'] = M('shop')->cache(true)->where(['domain' => $shopId])->getField('id');
                if ($_POST['shop_id'] <= 0) {
                    $this->apiReturn(301, ['data' => ['domain' => DM('wap')]], 1, '域名不存在');
                }
                unset($_POST['sign']);
                ksort($_POST);
                $sign = '';
                $signArr = ['access_key','appid','random','secret_key','sign_code','uid','shop_id',];
                foreach ($_POST as $k => $v) {
                    if (!in_array($k, $signArr)) {
                        $sign .= $k . ',';
                    }
                }
                $_POST['sign'] = _sign($_POST, trim($sign, ','));
            }
        }
    }
    
    // 采集url
    private $Url = array(
        1 => array(
            'bUrl'      => 'http://s.m.taobao.com/search',
            'eUrl'      => '&search=%E6%8F%90%E4%BA%A4%E6%9F%A5%E8%AF%A2&sst=1&n=40&buying=buyitnow&m=api4h5&abtest=27&wlsort=27',
            'search'    => 'q',
        ),
        2 => array(
            'bUrl'      => 'https://s.taobao.com/search?ie=utf8&search_type=tmall',
            'eUrl'      => '&tab=mall',
            'search'    => 'q',
        ),
    );

    public function index(){
    	redirect(C('sub_domain.www'));
    }

    /**
    * 店铺资料
    * @param int $_POST['shop_id']  店铺ID
    */
    public function shop_info(){
        //频繁请求限制,间隔2秒
        $this->_request_check();

        //必传参数检查
        $this->need_param=array('shop_id','sign');
        $this->_need_param();
        $this->_check_sign();   

        $do=D('Common/ShopRelation');
        $rs=$do->relation(true)->cache(true)->where(['id'=>I('post.shop_id')])->field('etime,ip',true)->find();
        if($rs) {
			$area 	=	$this->cache_table('area');
            $rs['province']    =$area[$rs['province']];
            $rs['city']        =$area[$rs['city']];
            $rs['district']    =$area[$rs['district']];
            $rs['town']        =$area[$rs['town']];

            if($rs['banner']) $rs['banner'] = html_entity_decode($rs['banner']);

            //判断是否有店铺促销
            $list = M('activity')->cache(true,C('CACHE_LEVEL.S'))->where(['shop_id' => $rs['id'],'start_time' => ['lt',date('Y-m-d H:i:s')],'end_time' => ['gt',date('Y-m-d H:i:s')]])->getField('type_id,id,full_money,full_value',true);
            if($list[3]){   //满减
                $img = '/Apps/Wap/View/default/Public/Images/'.$list[3]['full_money'].'_'.$list[3]['full_value'].'.png';
                if(file_exists('.'.$img)){
                    $rs['full_dec_img'] = $img;
                }
            }
            $rs['activity'] = $list;

            $this->apiReturn(1,['data' => $rs]);
        }else $this->apiReturn(3);
    }


    /**
    * 店铺首页－按销量排序
    * @param int $_POST['shop_id']  店铺ID    
    * @param int $_POST['limit']    获取记录数量,选填
    * @param int $_POST['imgsize']  主图尺寸，选填
    */
    public function goods_sale(){
        //频繁请求限制,间隔2秒
        $this->need_param=array('shop_id','sign');
        $this->_need_param();
        $this->_check_sign();

        $limit=I('post.limit')?I('post.limit'):8;
        

        $do=D('GoodsRelation');
        $map['status']=1;
        $map['num']=array('gt',0);
        $map['shop_id'] = I('post.shop_id');

        //随机取商品
        //$count=M('goods')->where($map)->count();
        //if(($count-$num)>0) $limit=rand(0,$count-$limit).','.$limit;

        $list=$do->relation(true)->cache(true,C('CACHE_LEVEL.M'))->relationWhere('goods_attr_list','num>0')->relationOrder('goods_attr_list','price asc')->relationField('goods_attr_list','id,attr_name,price,price_market,sale_num,view,concat("/Goods/view/id/",id,".html") as detail_url')->relationLimit('goods_attr_list',1)->where($map)->field('id,goods_name,images,price,sale_num,shop_id,seller_id')->limit($limit)->order('sale_num desc')->select();
        
        if(I('post.imgsize')){
            foreach($list as $key=>$val){
                $list[$key]['images']=myurl($val['images'],I('post.imgsize'));
            }
        }

        if($list){
            $this->apiReturn(1,array('data'=>$list));
        }else{
            $this->apiReturn(3);
        }   
    }

    /**
    * 店铺-新品 - 按新增时间排序
    * @param int $_POST['shop_id']  店铺ID    
    * @param int $_POST['limit']    获取记录数量,选填
    * @param int $_POST['imgsize']  主图尺寸，选填
    */
    public function goods_new(){
        //频繁请求限制,间隔2秒
        $this->need_param=array('shop_id','sign');
        $this->_need_param();
        $this->_check_sign();

        $limit=I('post.limit')?I('post.limit'):20;
        

        $do=D('GoodsRelation');
        $map['status']=1;
        $map['num']=array('gt',0);
        $map['shop_id'] = I('post.shop_id');



        $list=$do->relation(true)->cache(true,C('CACHE_LEVEL.M'))->relationWhere('goods_attr_list','num>0')->relationOrder('goods_attr_list','price asc')->relationField('goods_attr_list','id,attr_name,price,price_market,sale_num,view,concat("/Goods/view/id/",id,".html") as detail_url')->relationLimit('goods_attr_list',1)->where($map)->field('id,goods_name,images,price,sale_num,shop_id,seller_id')->limit($limit)->order('id desc')->select();
        
        if(I('post.imgsize')){
            foreach($list as $key=>$val){
                $list[$key]['images']=myurl($val['images'],I('post.imgsize'));
            }
        }

        if($list){
            $this->apiReturn(1,array('data'=>$list));
        }else{
            $this->apiReturn(3);
        }   
    }    


    /**
    * 店铺-人气 - 按新人气排序
    * @param int $_POST['shop_id']  店铺ID    
    * @param int $_POST['limit']    获取记录数量,选填
    * @param int $_POST['imgsize']  主图尺寸，选填
    */
    public function goods_hot(){
        //频繁请求限制,间隔2秒
        $this->need_param=array('shop_id','sign');
        $this->_need_param();
        $this->_check_sign();

        $limit=I('post.limit')?I('post.limit'):20;
        

        $do=D('GoodsRelation');
        $map['status']=1;
        $map['num']=array('gt',0);
        $map['shop_id'] = I('post.shop_id');



        $list=$do->relation(true)->cache(true,C('CACHE_LEVEL.M'))->relationWhere('goods_attr_list','num>0')->relationOrder('goods_attr_list','price asc')->relationField('goods_attr_list','id,attr_name,price,price_market,sale_num,view,concat("/Goods/view/id/",id,".html") as detail_url')->relationLimit('goods_attr_list',1)->where($map)->field('id,goods_name,images,price,sale_num,shop_id,seller_id')->limit($limit)->order('view desc')->select();
        
        if(I('post.imgsize')){
            foreach($list as $key=>$val){
                $list[$key]['images']=myurl($val['images'],I('post.imgsize'));
            }
        }

        if($list){
            $this->apiReturn(1,array('data'=>$list));
        }else{
            $this->apiReturn(3);
        }   
    } 

    /**
    * 店铺-收藏 - 按新人气排序
    * @param int $_POST['shop_id']  店铺ID    
    * @param int $_POST['limit']    获取记录数量,选填
    * @param int $_POST['imgsize']  主图尺寸，选填
    */
    public function goods_fav(){
        //频繁请求限制,间隔2秒
        $this->need_param=array('shop_id','sign');
        $this->_need_param();
        $this->_check_sign();

        $limit=I('post.limit')?I('post.limit'):20;
        

        $do=D('GoodsRelation');
        $map['status']=1;
        $map['num']=array('gt',0);
        $map['shop_id'] = I('post.shop_id');



        $list=$do->relation(true)->cache(true,C('CACHE_LEVEL.M'))->relationWhere('goods_attr_list','num>0')->relationOrder('goods_attr_list','price asc')->relationField('goods_attr_list','id,attr_name,price,price_market,sale_num,view,concat("/Goods/view/id/",id,".html") as detail_url')->relationLimit('goods_attr_list',1)->where($map)->field('id,goods_name,images,price,fav_num,shop_id,seller_id')->limit($limit)->order('fav_num desc')->select();
        
        if(I('post.imgsize')){
            foreach($list as $key=>$val){
                $list[$key]['images']=myurl($val['images'],I('post.imgsize'));
            }
        }

        if($list){
            $this->apiReturn(1,array('data'=>$list));
        }else{
            $this->apiReturn(3);
        }   
    }
    /**
    * 店铺-随机 
    * @param int $_POST['shop_id']  店铺ID    
    * @param int $_POST['limit']    获取记录数量,选填
    * @param int $_POST['imgsize']  主图尺寸，选填
    */
    public function goods_rand(){
        //频繁请求限制,间隔2秒
        $this->need_param=array('shop_id','sign');
        $this->_need_param();
        $this->_check_sign();

        $limit=I('post.limit')?I('post.limit'):20;
        

        $do=D('GoodsRelation');
        $map['status']=1;
        $map['num']=array('gt',0);
        $map['shop_id'] = I('post.shop_id');

        //随机取商品
        $count=M('goods')->where($map)->count();
        if(($count-$limit)>0) $limit=rand(0,$count-$limit).','.$limit;

        $list=$do->relation(true)->cache(true,C('CACHE_LEVEL.M'))->relationWhere('goods_attr_list','num>0')->relationOrder('goods_attr_list','price asc')->relationField('goods_attr_list','id,attr_name,price,price_market,sale_num,view,concat("/Goods/view/id/",id,".html") as detail_url')->relationLimit('goods_attr_list',1)->where($map)->field('id,goods_name,images,price,sale_num,shop_id,seller_id')->limit($limit)->select();
        
        if(I('post.imgsize')){
            foreach($list as $key=>$val){
                $list[$key]['images']=myurl($val['images'],I('post.imgsize'));
            }
        }

        if($list){
            $this->apiReturn(1,array('data'=>$list));
        }else{
            $this->apiReturn(3);
        }   
    } 


    /**
    * 商品列表
    * @param int $_POST['shop_id']  店铺ID
    */
    public function goods_list(){
        //频繁请求限制,间隔2秒
        $this->_request_check();

        //必传参数检查
        $this->need_param=array('shop_id','sign');
        $this->_need_param();
        $this->_check_sign();
		
        $map['status']      =1;
        $map['num']         =array('gt',0);
        $map['shop_id']     =I('post.shop_id');
        
        if(I('post.q')) $map['goods_name']=array('like','%'.trim(I('post.q')).'%');
        if (isset($_POST['min_price']) && !empty(I('post.min_price'))) $map['price']=['egt', intval(I('post.min_price'))];
        if (isset($_POST['max_price']) && !empty(I('post.max_price'))) $map['price']=['elt', intval(I('post.max_price'))];
		
		if(I('post.sid')) {
            $ids    = sortid(['table' => 'shop_goods_category','sid' => I('post.sid')]);
            $tmp    = array();
            foreach($ids as $val){
                $tmp[]  = 'find_in_set ('.$val.',shop_category_id)';
            }

            $map['_string'] = implode(' or ',$tmp);
			
        }
        $pagesize=I('post.pagesize')?I('post.pagesize'):20;
        $order=I('post.order')?I('post.order'):'sale_num desc,pr desc,id desc';
        $pagelist=pagelist(array(
                'table'     =>'Common/GoodsRelation',
                'do'        =>'D',
                'map'       =>$map,
                'order'     =>'atime desc',
                'fields'    =>'id,goods_name,images,price,sale_num,seller_id,shop_id,score_ratio,num',
                'order'     =>$order,
                'pagesize'  =>$pagesize,
                'relation'  =>true,
                'relationWhere'     =>array('goods_attr_list','num>0'),
                'relationOrder'     =>array('goods_attr_list','price asc'),
                'relationField'     =>array('goods_attr_list','id,attr_name,price,price_market,sale_num,view,concat("/Goods/view/id/",id,".html") as detail_url'),
                'relationLimit'     =>array('goods_attr_list',1),
                'action'            =>I('post.action'),
                'query'             =>I('post.query')?query_str_(I('post.query')):'',
                'p'                 =>I('post.p'),
                'ajax'              =>I('post.ajax'),
                'page_js'           =>I('post.page_js'),
                //'cache_name'        =>md5(implode(',',$_POST).__SELF__),
                //'cache_time'        =>C('CACHE_LEVEL.L'),                
            ));

        if(I('post.imgsize')){
            foreach($pagelist['list'] as $key=>$val){
                $pagelist['list'][$key]['images']=myurl($val['images'],I('post.imgsize'));
            }
        }

        if($pagelist['list']){
            $this->apiReturn(1,array('data'=>$pagelist));
        }else{
            $this->apiReturn(3);
        }
    }


    /**
    * 用户评价
    * @param int $_POST['shop_id']  店铺ID    
    * @param int $_POST['limit']    获取记录数量,选填
    */
    public function rate(){
        //频繁请求限制,间隔2秒
        $this->need_param=array('shop_id','sign');
        $this->_need_param();
        $this->_check_sign();

        $rate_name=[
            '-1'    =>'差评',
            '0'     =>'中评',
            '1'     =>'好评'
        ];

        $map['status']      =1;
        $map['shop_id']     =I('post.shop_id');

        if(I('post.rate')!='') $map['rate']=I('post.rate');

        $pagesize=I('post.pagesize')?I('post.pagesize'):20;
        $order=I('post.order')?I('post.order'):'id desc';
        $pagelist=pagelist(array(
                'table'     =>'Common/ShopGoodsCommentRelation',
                'do'        =>'D',
                'map'       =>$map,
                'order'     =>'id desc',
                'fields'    =>'id,atime,like_num,goods_id,orders_goods_id,attr_list_id,uid,seller_id,rate,reply_count,content,images,concat("/Goods/view/id/",attr_list_id,".html") as detail_url,is_anonymous',
                'order'     =>$order,
                'pagesize'  =>$pagesize,
                'relation'  =>true,
                'action'    =>I('post.action'),
                'query'     =>I('post.query')?query_str_(I('post.query')):'',
                'p'         =>I('post.p'),
                'cache_name'=>md5(implode(',',$_POST).__SELF__),
                'cache_time'=>C('CACHE_LEVEL.L'),                
            ));

        foreach($pagelist['list'] as $key=>$val){
            //$pagelist['list'][$key]['images']       =myurl($val['images'],I('post.imgsize'));
            $pagelist['list'][$key]['images']       =   imgsize_cmp($val['images'],50);
            $pagelist['list'][$key]['rate_name']    =   $rate_name[$val['rate']];
            $pagelist['list'][$key]['user']['face'] =   myurl($val['user']['face'],80);
            $pagelist['list'][$key]['user']['nick'] =   $val['is_anonymous'] == 1 ? '匿名' : $val['user']['nick'];
        }

        $count['rate_good']=M('orders_goods_comment')->where(['shop_id' => I('post.shop_id'),'rate' => 1])->count();
        $count['rate_middle']=M('orders_goods_comment')->where(['shop_id' => I('post.shop_id'),'rate' => 0])->count();
        $count['rate_bad']=M('orders_goods_comment')->where(['shop_id' => I('post.shop_id'),'rate' => -1])->count();

        $count['rate_num']=array_sum($count);

        if($pagelist['list']){
            $this->apiReturn(1,array('data'=>$pagelist,'count'=>$count));
        }else{
            $this->apiReturn(3);
        }
          
    } 


    /**
    * 商品分类
    * @param int $_POST['shop_id'] 店铺ID
    */
    public function category(){
        //频繁请求限制,间隔2秒
        $this->need_param=array('shop_id','sign');
        $this->_need_param();
        $this->_check_sign();

        $list=get_category(['table' => 'shop_goods_category' , 'level' => 2,'field'=>'id,sid,category_name,icon,sort' , 'map' => ['shop_id' => I('post.shop_id')],'cache_name'=>md5(implode(',',$_POST)),'cache_time'=>C('CACHE_LEVEL.M')]);
        if($list){
            $this->apiReturn(1,['data' => $list]);
        }else{
            $this->apiReturn(3);
        }
    }


    /**
     * 获取采集列表
     * @param int       $_POST['name']          搜索商品名字
     * @param string    $_POST['type']          类型(1->天猫)
     */
    public function get_list(){

        $this->need_param = array('name','type');
        $this->_need_param();
        $this->_check_sign();

        set_time_limit(0);

        $t      = I('post.type', 0, 'int');
        $name   = trim(I('post.name', ''));
        $data   = array();

        switch($t){
            // 天猫搜索商品list
            case 1:
                $cache_key = 'tm_' . md5($name);
                // S($cache_key, null);
                $data = S($cache_key);
                
                if(empty($data)){
                    $Url  = $this->Url[$t];
                    $sUrl = $Url['bUrl'] . '?' . $Url['search'] . '=' . $name . $Url['eUrl'];
                    $data = $this->get_list_tm($sUrl);
                    if(! empty($data)){
                        S($cache_key, $data, 3600);//缓存
                    }
                }
                break;
            // 获取天猫店铺宝贝list
            case 2:
                $cache_key = 'tm_dp_' . md5($name);
                // S($cache_key, null);
                $data = S($cache_key);
                
                if(empty($data)){
                    $Url  = $this->Url[$t];
                    $sUrl = $Url['bUrl'] . '&' . $Url['search'] . '=' . urlencode($name) . $Url['eUrl'];
                    $data = $this->get_list_tm_dp($sUrl);
                    if(! empty($data)){
                        S($cache_key, $data, 3600);//缓存
                    }
                }
                break;
            default:
                break;
        }
        if(empty($data)){
            $this->apiReturn(0, [ 'data'=>$data , 'msg' => '没有获取到商品' ]);
        }else{
            $this->apiReturn(1, [ 'data'=>$data ]);
        }
        
    }

    /**
     * 获取数据列表和处理
     */
    private function get_list_tm($url){
        
        $html   = $this->get_oburl($url);
        $html   = json_decode($html, true);
        $data   = $this->handle_tm_list($html['listItem']);
        
        $tp     = ($html['totalPage'] >= 5 ? 5 : $html['totalPage']);
        for($i = 2;$i <= $tp;$i++){
            sleep(1);
            $html       = $this->get_oburl($url . '&page='. $i);
            $html       = json_decode($html, true);
            $data = array_merge($data ,$this->handle_tm_list($html['listItem']));
            
        }

        return $data;

    }

    private function handle_tm_list($list){
        
        foreach($list as $val){
            if(strstr($val['nick'], '专卖店') || strstr($val['nick'], '专营店') || strstr($val['nick'], '旗舰店')){
                $t = 2;
            }else{
                $t = 1;
            }
            $data[] = array(
                'name'          => $val["title"],
                'images'        => 'http:' . $val["img2"],
                'nid'           => $val["item_id"],
                'price'         => $val["price"],
                'nick'          => $val["nick"],
                'price_market'  => $val["priceWap"],
                'category'      => $val['category'],
                'shop_type'     => $t ,
                'detail_url'    => 'https://'.($t == 1 ? 'item.taobao.com' : 'detail.tmall.com') . '/item.htm?id=' . $val["item_id"],
            );
        }

        return $data;
    }


    /**
     * 获取天猫店铺宝贝list  此方法在本地可以，好像上了服务器，IP被屏就无法使用
     */
    private function get_list_tm_dp($url){
        
        $html   = $this->get_oburl($url);
        $tp     = MidStr($html,'"totalPage":',',') >= 5 ? 5 : MidStr($html,'"totalPage":',',');
        $html   = MidStr($html, 'g_page_config = ', 'g_srp_loadCss();');
        $html   = json_decode(substr(trim($html),0,-1), true);
        // return $html['mods']['itemlist']['data']['auctions'];
        $data   = $this->handle_tm_dp_list($html['mods']['itemlist']['data']['auctions']);
        
        for($i = 2;$i <= $tp;$i++){
            sleep(1);
            $html       = $this->get_oburl($url . '&s='. 44 * ($i - 1));
            $html       = MidStr($html, 'g_page_config = ', 'g_srp_loadCss();');
            $html       = json_decode(substr(trim($html),0,-1), true);
            $data = array_merge($data, $this->handle_tm_dp_list($html['mods']['itemlist']['data']['auctions']));
        }
        return $data;
    }

    private function handle_tm_dp_list($list){
        $data = array();
        foreach($list as $val){
            $data[] = array(
                'name'          => $val["raw_title"],
                'images'        => 'http:' . $val["pic_url"],
                'nid'           => $val["nid"],
                'price'         => $val["view_price"],
                'nick'          => $val["nick"],
                'price_market'  => $val["reserve_price"],
                'category'      => $val['category'],
                'detail_url'    => 'https:' . urldecode($val["detail_url"])
            );
        }
        return $data;
    }

    /**
     * 导入商品
     * @param json $_POST['goods_json'] 传入一条记录 json格式
     */
    public function import(){
        
        $json = json_decode(I('post.goods_json', '', false), true);
        if( ! $json ){
            $this->apiReturn(0, [ 'msg' => '数据错误' ]);
        }
        
        $model = M('goods');    
        if( ! $model->where(array('goods_name'=>$json['name'],'shop_id'=>$json['shop_id'], 'seller_id' => $json['seller_id']))->find() ){
            
            $model->goods_name  = $json['name'];
            $model->images      = $json['images'];
            $model->status      = 2;
            $model->category_id = 100845542;//全部放到其他分类
            $model->shop_id     = (int)$json['shop_id'];
            $model->price       = (float) $json['price'] * 2;
            $model->price_max   = $model->price * 2;
            $model->num         = mt_rand(200, 300);
            $model->seller_id   = (int)$json['seller_id'];
            $model->ip          = get_client_ip();

            M()->startTrans();
            
            $r1 = $model->add();
            $model2 = M('goods_attr_list');
            $model2->seller_id      = (int)$json['seller_id'];
            $model2->goods_id       = $r1;
            $model2->images         = $json['images'];
            $model2->price          = (float) $json['price'] * 2;
            $model2->price_market   = (float)$json['price_market'];
            $model2->price_purchase = $model2->price * 2;
            $model2->num            = 100;
            $model2->ip             = get_client_ip();
            $r2 = $model2->add();

            if( $r1 && $r2 ){
                M()->commit();
                $this->apiReturn(0, [ 'msg' => '添加成功' ]);
            }else{
                M()->rollback();
                $this->apiReturn(0, [ 'msg' => '添加失败' ]);
            }
        }else{
            $this->apiReturn(0, [ 'msg' => '已存在记录' ]);
        }
    }



    /**
     * 利用缓存取数据，不使用CURL，因为CURL可能会因为DNS关系而获取不到数据
     */
    public function get_oburl($url){
        
        // ob_start();
        // readfile($url);
        // $html=ob_get_contents();
        // ob_clean();
        $html = curl_file($url);
        return $html;
    }

    /**
     * 判断用户是否开店 - 供ERP使用
     * @param int $_POST['uid'] ERP用户的UID
     */
    public function shop_status(){
        //频繁请求限制,间隔2秒
        $this->_request_check();

        //必传参数检查
        $this->need_param=array('uid','sign');
        $this->_need_param();
        $this->_check_sign();

        $rs = M('user')->where(['erp_uid' => I('post.uid')])->field('id,shop_type')->find();
        if(!$rs) $this->apiReturn(1600); //找不到用户，即还未开店


        if($rs['shop_type'] > 0){   //已开店
            $status_name=['暂停营业','营业中','强制关闭'];
            $do=D('Common/ShopRelation');
            $rs=$do->relation(true)->where(['uid' => $rs['id']])->field('id,uid,status,shop_name,shop_logo,type_id,domain,province,city,district,town')->find();
            if($rs) {
                $shop_type 	=	$this->cache_table('shop_type');
                $area 		=	$this->cache_table('area');
                $rs['province']         =$area[$rs['province']];
                $rs['city']             =$area[$rs['city']];
                $rs['district']         =$area[$rs['district']];
                $rs['town']             =$area[$rs['town']];

                $rs['status_name']		=$status_name[$rs['status']];
                $rs['shop_url']			=shop_url($rs['id'],$rs['domain']);
                $rs['type_name']		=$shop_type[$rs['type_id']];
                $rs['shop_logo']		=myurl($rs['shop_logo'],100);

                unset($rs['type_id']);
                unset($rs['uid']);
                unset($rs['status']);
                unset($rs['domain']);


                $this->apiReturn(1,['data' => $rs],1,'已开店');
            }else $this->apiReturn(0,'',1,'店铺信息出现错误！');

        }else{  //判断是否处于开店申请流程中
            if($join = M('shop_join_step')->where(['uid' => $rs['id']])->find()){
                $this->apiReturn(2,'',1,'店铺审核中');
            }else{
                $this->apiReturn(0,'',1,'未开店');
            }
        }
    }
    
    
    /**
     * 关闭店铺
     */
    public function close() {
        //频繁请求限制,间隔2秒
        $this->_request_check();
        
        //必传参数检查
        $this->need_param=array('uid','sign');
        $this->_need_param();
        $this->_check_sign();
        $rs = M('user')->where(['erp_uid' => I('post.uid')])->field('id,shop_type,shop_id')->find();
        if(!$rs) $this->apiReturn(1600); //找不到用户，即还未开店
        if ($rs['shop_type'] > 0) {
            if (M('shop')->where(['id' => $rs['shop_id']])->save(['status' => 3])) {
                $this->apiReturn(1);
            }
            $this->apiReturn(0);
        }
        $this->apiReturn(1600); //找不到用户，即还未开店
    }
}