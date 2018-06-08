<?php
/**
+----------------------------------------------------------------------
| RestFull API 
+----------------------------------------------------------------------
| 正式发布后的店铺装修
+----------------------------------------------------------------------
| Author: lazycat <673090083@qq.com>
+----------------------------------------------------------------------
*/

namespace Rest\Controller;
use Think\Controller\RestController;
class ShopPublishController extends CommonController {
    protected $shop_id          ='';        //店铺ID
    protected $templates_id     ='';        //API接口配置
    protected $shop_info        =array();   //店铺信息
    protected $templates        =array();   //当前正在使用的模板

    public function _initialize() {
        parent::_initialize();



        //只充许从api方法入进入
        if(ACTION_NAME!='api') $this->apiReturn(1501);

        C('API_LOG',fasle);	//关闭日志记录

        if(I('post.make_templates_id')){
            $this->templates_id = I('post.make_templates_id');
            $do = M('shop_publish_templates');
            $this->uid = $do->where(['id' => I('post.make_templates_id')])->getField('uid');

            //店铺信息
            $shop_info = S('shop_publish_info_'.$this->uid);
            if(empty($shop_info)){
                $res        = $this->_shop_info();
                $shop_info  = $res['data'];
                S('shop_publish_info_'.$this->uid,$shop_info,600);
            }

            $this->shop_info = $shop_info;
            $this->shop_id   = $this->shop_info['id'];

            //模板信息
            $templates = S('shop_publish_templates'.$this->templates_id);
            if(empty($templates)){
                $res = $this->_templates_active();
                $templates = $res['data'];
                S('shop_publish_templates'.$this->templates_id,$templates,600);
            }

            $this->templates = $templates;

        }


    }

    /**
    * 请求入口
    */
    public function api(){
        //缺少要执行的方法
        if(empty($_GET['method'])) $this->apiReturn(1500);         
        $this->_api(['method' => I('get.method')]);
    }

    /**
    * 各方法的必签字段
    * @param string $method     方法
    */
    public function _sign_field($method){

        $sign_field = [
            '_templates_active'                 => 'openid',                    //当前正在使用的装修模板
            '_layout'                           => 'make_templates_id,page',                    //装修页面布局
            '_shop_init'                        => 'domain',    //店铺初始化资料
        ];
        $result=$sign_field[$method];

        return $result;
    }

    /**
    * 店铺初始化资料
    */
    public function _shop_init(){
        $do = M('shop');
        $domain = I('post.domain');
        $field = is_numeric($domain) ? 'id' : 'domain';
        $map[$field] = $domain;
        $uid = $do->where($map)->getField('uid');
        //$uid = $do->where(['_string' => 'id="'.I('post.domain').'" or domain="'.I('post.domain').'"'])->getField('uid');

        if(!$uid) return ['code' => 1515]; //店铺不存在

        $this->uid = $uid;
        $result['shop'] = $this->_shop_info();
        $result['templates'] = $this->_templates_active();

        return ['code' =>1,'data' => $result];
    }

    /**
    * 店铺资料
    * @param int $_POST['openid']  用户openid
    */
    public function _shop_info(){
        $status_name=['暂停营业','营业中','强制关闭'];

        $do=D('Common/ShopRelation');
        $rs=$do->relation(true)->where(['uid' => $this->uid])->field('id,atime,status,uid,shop_name,shop_logo,shop_level,about,type_id,category_id,province,city,district,city,town,street,domain,qq,mobile,tel,email,fav_num,wang,goods_num,sale_num,fraction_speed,fraction_service,fraction_desc,fraction')->find();

        if($rs) {
            $shop_type  =   $this->cache_table('shop_type');
            $area       =   $this->cache_table('area');
            $rs['province_name']    =$area[$rs['province']];
            $rs['city_name']        =$area[$rs['city']];
            $rs['district_name']    =$area[$rs['district']];
            $rs['town_name']        =$area[$rs['town']];  
    
            $rs['status_name']      =$status_name[$rs['status']];
            $rs['shop_url']         =shop_url($rs['id'],$rs['domain']);
            $rs['type_name']        =$shop_type[$rs['type_id']];
            $rs['shop_logo']        =myurl($rs['shop_logo'],100);
            
            if($rs['category_id']){
                $goods_category     =   $this->cache_table('goods_category');
                $category_id=explode(',',$rs['category_id']);
                foreach($category_id as $val){
                    $rs['category_name'][]  =   $goods_category[$val];
                }
            }

            return ['code' =>1,'data' => $rs];
        }else return ['code' => 3];
    }


    /**
    * 取当前模板
    * @param string $_POST['openid']            用户openid
    */
    public function _templates_active(){
        $rs=D('Common/ShopPublishTemplates')->where(['uid' => $this->uid,'status' => 1])->field('atime,etime,ip',true)->find();

        if($rs) {
            //模板风格
            if($rs['styles']){
                $style_tmp  =explode(',', $rs['styles']);
                foreach($style_tmp as $val){
                    $val=explode('|',$val);
                    $styles[]=['name' => $val[0],'value' => $val[1]];
                }
                $rs['styles']=$styles;
            }

            $rs['css']  = $rs['bgcolor']?'body{background-color:'.$rs['bgcolor'].' !important;}':'';

            //模板CSS
            if($rs['is_css']==1){   //是否启用自定义样式
                $tmp  = $rs['cell_is_border']?'border: 1px solid '.$rs['cell_border_color'].';':'';
                $tmp  .= $rs['cell_bgcolor']?'background-color:'.$rs['cell_bgcolor'].';':'';
                $tmp  .= $rs['cell_bgimages']?'background-image:url('.$rs['cell_bgimages'].');':'';
                $tmp  .= $rs['cell_margin_top']?'margin-top:'.$rs['cell_margin_top'].'px;':'';
                $tmp  .= $rs['cell_margin_bottom']?'margin-bottom:'.$rs['cell_margin_bottom'].'px;':'';

                $tmp2  = $rs['cell_title_bgcolor']?'background-color:'.$rs['cell_title_bgcolor'].';':'';
                $tmp2  .= $rs['cell_title_bgimages']?'background-image:url('.$rs['cell_title_bgimages'].');':'';            
                $tmp2  = $rs['cell_title_color']?'color:'.$rs['cell_title_color'].';':'';

                $tmp3  = $rs['cell_text_color']?'color:'.$rs['cell_text_color'].';':'';
                $tmp3  .= $rs['cell_text_size']?'font-size:'.$rs['cell_text_size'].'px;line-height:'.($rs['cell_text_size']+10).'px;':'';

                $rs['css'] .= $rs['cell_style'];
                $rs['css'] .= $tmp ? '.layout .col .col-item{'.$tmp.'}':'';
                $rs['css'] .= $tmp2 ? '.layout .col .col-item .col-item-title{'.$tmp2.'}':'';
                $rs['css'] .= $tmp3 ? '.layout .col .col-item .col-item-content{'.$tmp3.'}':'';
            }

            //模板背景
            if($rs['bgimages']){
                $bgimages = explode(',',$rs['bgimages']);
                if(count($bgimages) == 1) {
                    $rs['css'] .= 'body{background-image:url('.$bgimages[0].');}';
                    $rs['css'] .= $rs['fixed']==1?'body{background-size:cover;background-attachment:fixed;}':'';
                }else{
                    $rs['js']   = 'var RandBG = function () {';
                    $rs['js']   .= 'var pic=new Array();';
                    foreach($bgimages as $key=>$val){
                        $rs['js']   .='pic['.$key.']="'.$val.'";';
                    }
                    $rs['js']   .='var rand_pic=randomSort(pic);
                                    return {
                                            init: function () {
                                                $.backstretch(rand_pic, {
                                                    fade: 1000,
                                                    duration: 10000
                                                });
                                            }
                                        };
                                    }();
                                    RandBG.init();';                        
                }
            }

            //插件CSS            
            $plugins = M('shop_plugins')->where('find_in_set ("'.$rs['templates_id'].'",templates_id)')->field('path')->select();

            
            foreach($plugins as $val){
                if(file_exists('./Templates/zh_cn/plugins'.$val['path'].'/css/css.css')){
                    $rs['css'] .= file_get_contents('./Templates/zh_cn/plugins'.$val['path'].'/css/css.css');
                }
                if(file_exists('./Templates/zh_cn/plugins'.$val['path'].'/js/js.js')){
                    $rs['js'] .= file_get_contents('./Templates/zh_cn/plugins'.$val['path'].'/js/js.js');
                }
            }

            $result=['code' => 1,'data' => $rs];
        }
        else $result=['code' =>3];

        return $result;
    }


    /**
    * 取某页面布局
    * @param int    $_POST['make_templates_id'] 装修模板ID
    * @param string $_POST['page']  装修页面,如:/Index/index
    */
    public function _layout(){

        $count=M('shop_publish_templates')->cache(true)->where(['id' =>I('post.make_templates_id')])->count();
        if($count<1) return ['code' => 0];

        $page_id=M('shop_page')->cache(true)->where(['page' => I('post.page')])->getField('id');
        if(empty($page_id)) return ['code' => 0];

        $do=D('Common/ShopPublishLayoutModulesRelation');
        $list=$do->cache(true)->relation(true)->where(['make_templates_id' => I('post.make_templates_id'),'_string' => 'page_id = '.$page_id.' or layout_type = 9'])->field('atime,etime,ip',true)->order('sort asc')->select();


        foreach($list as $i => $val){
            foreach($val['modules'] as $j => $v){
                $list[$i]['item'][$v['col_index']] .= '<div class="col-sort" data-id="'.$v['id'].'">'.$this->_modules_item_view($v['id']).'</div>';
            }
            unset($list[$i]['modules']);
        }


        if($list) return ['code' =>1,'data' => $list];

        return ['code' =>3];
    }


    /**
    * 输出模块 ,$id和$data两项必传一项
    * @param int $id        模块id
    * @param array $data    模块数据
    */
    public function _modules_item_view($id='',$data=''){
        if($id=='' && $data=='') return ['code' =>0];

        if($data==''){
            $data = M('shop_publish_modules')->cache(true)->where(['id' => $id])->field('atime,etime,ip',true)->find();
        }
        if(empty($data)) return ['code' => 3];

        $tpl=M('shop_publish_templates')->cache(true)->where(['id' => $data['make_templates_id']])->field('tpl_url,cfg_box')->find();
        $tpl['cfg_box'] = unserialize(html_entity_decode($tpl['cfg_box']));

        $data['data'] = unserialize(html_entity_decode($data['data']));
        $cfg=$data['data'];      

        switch($data['type']){
            case 'slide':   //轮播图
                $ads = [];
                foreach($cfg['images'] as $i => $val){
                    $ads[]  = [
                        'title'     => $cfg['title'][$i],
                        'url'       => $cfg['url'][$i],
                        'images'    => $val
                    ];
                }
                $data['ads']    = $ads;
            break;
            case 'links':   //友情链接
                $links = [];
                foreach($cfg['title'] as $i => $val){
                    $links[]  = [
                        'title'     => $cfg['title'][$i],
                        'url'       => $cfg['url'][$i],
                        'images'    => $cfg['images'][$i]
                    ];
                }
                $data['links']    = $links;
            break;            
            case 'shop_info':   //店铺信息
                
            break;
            case 'sale_order':  //宝贝排行
                $do=D('Common/GoodsRelation');
                $map['seller_id']   =   $this->uid;
                $map['num']         =   ['gt',0];
                $map['status']      =   1;

                if($cfg['keyword']) $map['goods_name'] = ['like' , '%'.trim($cfg['keyword']).'%'];

                if($cfg['category_id']){
                    $ids = sortid(['table' => 'shop_goods_category','sid' => $cfg['category_id']]);
                    $tmp = [];
                    foreach($ids as $val){
                        $tmp[] = 'find_in_set ('.$val.',shop_category_id)';
                    }
                    $map['_string'] = implode(' or ',$tmp);
                }

                if(max($cfg['s_price'],$cfg['e_price'])>0){
                    if(empty($cfg['s_price'])) $cfg['s_price'] = 0;
                    if(empty($cfg['e_price'])) $cfg['e_price'] = 100000000;
                    $map['price']   =   ['between',[$cfg['s_price'],$cfg['e_price']]];
                }

                $limit              =   intval($cfg['row'])?intval($cfg['row']):5;
                $result['sale']     =   $do->cache(true,C('CACHE_LEVEL.S'))->relation('attr_list')->relationLimit('goods_attr_list',1)->relationField('goods_attr_list','id,images,price')->where($map)->field('id,shop_id,goods_name,price,sale_num,fav_num')->limit($limit)->order('sale_num desc')->select();

                
                $result['fav']     =   $do->cache(true,C('CACHE_LEVEL.S'))->relation('attr_list')->relationLimit('goods_attr_list',1)->relationField('goods_attr_list','id,images,price')->where($map)->field('id,shop_id,goods_name,price,sale_num,fav_num')->limit($limit)->order('fav_num desc')->select();

                $result=imgsize_list($result,'images',80);

                $this->assign('sale_order',$result);
            break;

            case 'search':  //搜索
            case 'category':    //分类
                $shop_category = get_category(['table' => 'shop_goods_category','field' => 'id,sid,category_name','level' => 2,'sql' => 'uid='.$this->uid,'cache_name' => 'shop_category_'.$this->uid,'cache_time' => C('CACHE_LEVEL.S')]);

                $this->assign('shop_category',$shop_category);            
            break;
            case 'hot': //推荐宝贝
                $col = [2 => 'ccol-50',3 => 'ccol-33',4 => 'ccol-25',5 => 'ccol-20'];
                $do=D('Common/GoodsRelation');

                if($cfg['goods_id'] && $cfg['set_goods']==1){   //自定义宝贝
                    $result['list'] = $do->cache(true,C('CACHE_LEVEL.S'))->relation('attr_list')->relationLimit('goods_attr_list',1)->relationField('goods_attr_list','id,images,price')->where(['id' => ['in',$cfg['goods_id']],'seller_id' => $this->uid])->field('id,shop_id,goods_name,price,score_ratio,(price*score_ratio*100) as score,sale_num,fav_num,rate_num')->order('find_in_set (id,"'.implode(',',$cfg['goods_id']).'")')->select();

                }else{
                    
                    $map['seller_id']   =   $this->uid;
                    $map['num']         =   ['gt',0];
                    $map['status']      =   1;

                    if($cfg['keyword']) $map['goods_name'] = ['like' , '%'.trim($cfg['keyword']).'%'];

                    if($cfg['category_id']){
                        $ids = sortid(['table' => 'shop_goods_category','sid' => $cfg['category_id']]);
                        $tmp = [];
                        foreach($ids as $val){
                            $tmp[] = 'find_in_set ('.$val.',shop_category_id)';
                        }
                        $map['_string'] = implode(' or ',$tmp);
                    }

                    if(max($cfg['s_price'],$cfg['e_price'])>0){
                        if(empty($cfg['s_price'])) $cfg['s_price'] = 0;
                        if(empty($cfg['e_price'])) $cfg['e_price'] = 100000000;
                        $map['price']   =   ['between',[$cfg['s_price'],$cfg['e_price']]];
                    }

                    $limit              =   $cfg['row'] * $cfg['col'];
                    $result['list']     =   $do->cache(true,C('CACHE_LEVEL.S'))->relation('attr_list')->relationLimit('goods_attr_list',1)->relationField('goods_attr_list','id,images,price')->where($map)->field('id,shop_id,goods_name,price,score_ratio,(price*score_ratio*100) as score,sale_num,fav_num,rate_num')->limit($limit)->order($cfg['order'])->select();

                }

                $result=imgsize_list($result,'images',$this->templates[$cfg['imgsize']]);

                $result['other']    = [
                    'imgsize'       => $this->templates[$cfg['imgsize']],
                    'count'         => count($result['list']),
                    'col'           => $col[$cfg['col']],
                ];


                $this->assign('list',$result);
            break;            

            case 'plist': //宝贝列表
                $col = [2 => 'ccol-50',3 => 'ccol-33',4 => 'ccol-25',5 => 'ccol-20'];

                $map['seller_id']   =   $this->uid;
                $map['num']         =   ['gt',0];
                $map['status']      =   1;

                if(I('post.keywords')) $map['goods_name'] = ['like' , '%'.trim(I('post.keywords')).'%'];
                if(I('post.brand_id')) $map['brand_id']   = I('post.brand_id');

                if(I('post.sid')){
                    $ids = sortid(['table' => 'shop_goods_category','sid' => I('post.sid')]);
                    $tmp = [];
                    foreach($ids as $val){
                        $tmp[] = 'find_in_set ('.$val.',shop_category_id)';
                    }
                    $map['_string'] = implode(' or ',$tmp);
                }

                if(max(I('post.s_price'),I('post.e_price'))>0){
                    if(empty(I('post.s_price'))) I('post.s_price',0);
                    if(empty(I('post.e_price'))) I('post.e_price',100000000);
                    $map['price']   =   ['between',[I('post.s_price'),I('post.e_price')]];
                }                

                $pagesize           =   $cfg['row'] * $cfg['col'];
                $result['list']     =  pagelist([
                        'table'             => 'Common/GoodsRelation',
                        'do'                => 'D',
                        'pagesize'          => $pagesize,
                        'map'               => $map,
                        'fields'            => 'id,shop_id,goods_name,price,score_ratio,(price*score_ratio*100) as score,sale_num,fav_num,rate_num',
                        'order'             => $cfg['order'],
                        'relation'          => 'attr_list',
                        'relationLimit'     => ['goods_attr_list',1],
                        'relationField'     => ['goods_attr_list','id,images,price'],
                        'action'            => '/index/goods',
                        'p'                 => I('post.p'),
                        'query'             => I('post.query') ?unserialize(html_entity_decode(I('post.query'))):'',
                    ]); 

                //print_r(I('post.'));
               
                $result=imgsize_list($result,'images',$this->templates[$cfg['imgsize']]);

                $result['other']    = [
                    'imgsize'       => $this->templates[$cfg['imgsize']],
                    'count'         => count($result['list']),
                    'col'           => $col[$cfg['col']],
                ];

                $this->assign('list',$result);
            break;  
            case 'rate':
                $rate_name=[0 => '中评',1 => '好评','-1' => '差评'];
                $status_name=['未生效','已生效'];

                $map['seller_id']       =   $this->uid;
                $map['status']          =   1;
                $map['is_shuadan']      =   0;

                $pagesize=20;
                $pagelist=pagelist(array(
                        'table'     => 'OrdersGoodsCommentRelation',
                        'do'        => 'D',
                        'map'       => $map,
                        'order'     => 'id desc',
                        'fields'    => 'id,atime,status,like_num,s_no,orders_goods_id,goods_id,shop_id,attr_list_id,uid,seller_id,rate,reply_count,content,images,is_anonymous',
                        'pagesize'  => $pagesize,
                        'relation'  => true,
                        'action'    => '/index/rate',
                        'p'         => I('post.p'),
                        'query'     => I('post.query') ?unserialize(html_entity_decode(I('post.query'))):'',

                    ));

                if($pagelist['list']){
                    foreach($pagelist['list'] as $key=>$val){
                        $pagelist['list'][$key]['orders_goods']   =   imgsize_list($val['orders_goods'],'images',80);
                        $pagelist['list'][$key]['user']           =   imgsize_list($val['user'],'face',60);

                        if($val['images']){
                            $pagelist['list'][$key]['images_']  =   imgsize_cmp($val['images'],50);
                            $pagelist['list'][$key]['images']   =   explode(',',$val['images']);
                        }
                    }                    
                }
                

                $this->assign('rate',$pagelist);
            break;
            case 'header':
                if($data['data']['banner']) $data['data']['banner'] = myurl($data['data']['banner'],1200,100);

                $data['data']['header_style'] = $cfg['bgimages']?'background-image:url('.$cfg['bgimages'].');':'';
                $data['data']['header_style'] .= $cfg['bgcolor']?'background-color:'.$cfg['bgcolor'].';':'';
            break;
            case 'menu':
                if($cfg['category_id']){
                    $category   = M('shop_goods_category')->where(['id' => ['in',$cfg['category_id']]])->field('id,category_name')->select();
                    $this->assign('category',$category);
                }

                $data['data']['header_style'] = $cfg['bgimages']?'background-image:url('.$cfg['bgimages'].');':'';
                $data['data']['header_style'] .= $cfg['bgcolor']?'background-color:'.$cfg['bgcolor'].';':'';
            break; 

            case 'plugins_lib':
                $goods_id=array_unique($cfg['goods_id']);
                if(false!== $key = array_search('', $goods_id)){
                    array_splice($goods_id, $key, 1);
                }

                if($goods_id){
                    $do=D('Common/GoodsRelation');
                    $goods_tmp = $do->relation('attr_list')->relationLimit('goods_attr_list',1)->relationField('goods_attr_list','id,images,price')->where(['id' => ['in',$goods_id]])->field('id,shop_id,goods_name,price,score_ratio,(price*score_ratio*100) as score,sale_num,fav_num,rate_num')->select();
                    foreach($goods_tmp as $val){
                        $goods[$val['id']]  = $val;
                    }
                }

                foreach($cfg['goods_id'] as $key => $val){
                    $item[$key]['target']       = '_blank';
                    $item[$key]['subject']      = $cfg['subject'][$key];
                    $item[$key]['sub_subject']  = $cfg['sub_subject'][$key];                    
                    $item[$key]['goods']        = $goods[$cfg['goods_id'][$key]];
                    $item[$key]['pic']          = $cfg['pic'][$key]?$cfg['pic'][$key]:$item[$key]['goods']['attr_list'][0]['images'];
                    if(empty($item[$key]['pic']))   $item[$key]['is_null']  =1;
                }

                //$data['plugins_items'] = $item;

                $plugins = M('shop_plugins')->cache(true)->where(['id' => $data['plugins_id']])->field('id,setting')->find();
                $plugins = unserialize(html_entity_decode($plugins['setting']));

                $n=0;
                foreach ($plugins as $key => $val) {                    
                    foreach ($val['item'] as $k => $v) {
                        for($i = 0; $i < $v['num'];$i++){
                            $item[$n]['pic']    = myurl($item[$n]['pic'],$v['width'],$v['height']);
                            if($item[$n]['goods']){
                                $item[$n]['url'] = DM('item','/goods/'.$item[$n]['goods']['attr_list'][0]['id']);
                            }else $item[$n]['url'] = '#';
                            $plugins[$key]['item'][$k]['item'][] = $item[$n];
                            $n++;
                        }
                    }
                }
                

                $data['plugins']=$plugins;


                $tpl_url = './Templates/zh_cn/plugins'.$cfg['tpl_path'].$cfg['tpl_url'];
            break;                        
        }

        //自定样式
        $data['css']['content']     =$cfg['content_padding']== -1?'':'padding:'.$cfg['content_padding'].'px;';

        $data['css']['box']         =$cfg['height']>50?'height:'.$cfg['height'].'px;overflow:hidden;':'';
        $data['css']['box']         .= $data['data']['header_style'];


        if($cfg['is_setting']==1){
            $data['css']['box']   .=$cfg['is_border']==0?'border:0;':'';
            $data['css']['box']   .=$cfg['bgcolor']?'background-color:'.$cfg['bgcolor'].';':'';
            $data['css']['box']   .=$cfg['transparent']?'background-color: transparent;':'';
            $data['css']['box']   .=$cfg['bgimages']?'background-image:url('.$cfg['bgimages'].');':'';
            $data['css']['box']   .=$cfg['text_color']?'color:'.$cfg['text_color'].';':'';
            $data['css']['box']   .=$cfg['margin_top']!=-1?'margin-top:'.$cfg['margin_top'].'px;':'';
            $data['css']['box']   .=$cfg['margin_bottom']!=-1?'margin-bottom:'.$cfg['margin_bottom'].'px;':'';

            $data['css']['box']   .=$cfg['show_title']?'border-bottom: 1px solid '.$cfg['border_color'].';':'';
            $data['css']['box']   .=$cfg['title_color']?'color:'.$cfg['title_color'].';':'';
            $data['css']['box']   .=$cfg['title_bgcolor']?'background-color:'.$cfg['title_bgcolor'].';':'';
            $data['css']['box']   .=$cfg['title_bgimages']?'background-image:url('.$cfg['title_bgimages'].');':'';

            $data['css']['box']   .=$cfg['style']?$cfg['style']:'';
        }
        


        $this->assign('rs',$data);
        $this->assign('seller_id',$this->uid);

        $this->assign('shop_info',$this->shop_info);

        $tpl_url=$tpl_url?$tpl_url:'.'.$tpl['tpl_url'].'/'.$cfg['tpl_url'];
        $html=$this->fetch($tpl_url);


        return $html;
    }

}