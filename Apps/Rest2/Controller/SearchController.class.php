<?php
/**
 * ----------------------------------------------------------
 * RestFull API V2.0
 * ----------------------------------------------------------
 * 商品搜索、店铺搜索
 * ----------------------------------------------------------
 * Author:Lazycat <673090083@qq.com>
 * ----------------------------------------------------------
 * 2017-01-09
 * ----------------------------------------------------------
 */
namespace Rest2\Controller;
use Think\Controller\RestController;
use Xs\xs;
use Xs\XSDocument;
import('Vendor.Xs.xs');
class SearchController extends ApiController {
    //protected $action_logs = array('ad');

    protected $xs;  //迅搜句柄
    protected $search; //迅搜搜索句柄
    protected $project = 'goodscfgbyledui'; //商品搜索项目


    protected $project_keywords = 'keywords';   //关键词索引项目
    protected $q;
    protected $qs;

    protected $project_shop = 'shop';   //店铺索引项目
    protected $xsshop;
    protected $xsshopsearch;

    public function _initialize() {
        parent::_initialize();

        $this->xs       = new xs($this->project);
        $this->search   = $this->xs->search;

        $this->q  = new xs($this->project_keywords);
        $this->qs = $this->q->search;

        $this->xsshop       = new xs($this->project_shop);
        $this->xsshopsearch = $this->xsshop->search;
    }

    /**
     * subject: 商品分类
     * api: /Search/goods
     * author: Lazycat
     * day: 2017-01-09
     *
     * [字段名,类型,是否必传,说明]
     * param: p,int,0,页码
     * param: pagesize,int,0,每页记录数
     * param: q,string,0,搜索关键词
     * param: is_self,int,0,是否自营
     * param: free_express,int,0,包邮
     * param: is_daigou,int,0,是否代购
     * param: score_ratio,float,0,积分奖励倍数
     * param: cid,int,0,商品类目id
     * param: city_id,int,0,城市id
     * param: min_price,int,0,价格起始 与max_price使用表示价格区间
     * param: max_price,int,0,价格终止 与min_price使用表示价格区间
     * param: sort,string,0,排序 格式：排序参数-正反排序 如：sale_num-desc price-asc
     */
    public function goods(){
        $this->check(array_keys(I('post.')),false);

        $res = $this->_goods($this->post);
        $this->apiReturn($res);
    }


    /**
     * 读取广告
     */
    public function _goods($param=null){
        /*
        //查询条件
        $map_string = 'status:1';
        if($param['q']) {
            $map_string .= ' AND ' . $param['q'];
            $title[] = trimall($param['q']);

            //记录搜索历史
            $this->_keywords_history($param['q']);
        }
        if($param['is_self'] == 1) $map_string .= ' AND is_self:1';
        if($param['free_express'] == 1) $map_string .= ' AND free_express:1';
        if($param['is_daigou'] == 1) $map_string .= ' AND is_daigou:1';
        if($param['score_ratio'] != '') $map_string .= ' AND score_ratio:' . $param['score_ratio'];
        if($param['cid']) {
            $map_string .= ' AND (category_id:'.$param['cid'].' OR first_category_id:'.$param['cid'].' OR second_category_id:' . $param['cid'].')';
            $nav_sort = nav_sort(['table' => 'goods_category','field' => 'id,sid,category_name','key' => 'category_name','id' => $param['cid'],'link' => U('/Index/index',['id' => '[id]']),'cache_name' => 'search_nav_sort_goods_category_'.$param['cid']]);
            $title[] = str_replace(array(',','，','/'),'_',strip_tags($nav_sort));
        }


        if($param['city_id']){
            $map_string .= ' AND city_id:'.$param['city_id'];
            $city = nav_sort(['table' => 'area','field' => 'id,sid,a_name','key' => 'a_name','id' => $param['city_id'],'cache_name' => 'search_city_id_'.$param['city_id']]);
            $title[] = strip_tags($city);
        }



        //参数条件
        $option = array();
        foreach($param as $key => $val){
            if(substr($key,0,7) == 'option_'){
                $option[] = ['key' => $key,'name' => $val];
                $map_string .= ' AND option:'.$val;
                $title[] = $val;
            }
        }

        //dump($map_string);

        //排序
        $order = ['pr'];
        if($param['sort']){
            $sort = explode('-',$param['sort']);
            $order = [$sort[0] => ($sort[1] == 'asc' ? true : false),'pr' => false];

        }

        //分页
        $pagesize = $param['pagesize'] ? $param['pagesize'] : 30;
        if($param['is_shop'] ==1) $pagesize = 15;
        if($param['is_merge'] == 1 || $param['is_shop'] == 1) {
            $count = $this->search->setFuzzy()->setAutoSynonyms()->setQuery($map_string)->addRange('price', $param['min_price'], $param['max_price'])->setCollapse('shop_id')->count();
        }else{
            $count = $this->search->setFuzzy()->setAutoSynonyms()->setQuery($map_string)->addRange('price', $param['min_price'], $param['max_price'])->count();
        }
        $page = ceil($count / $pagesize);
        $p = $param['p'] ? $param['p'] : 1;
        $p = $p > $page ? $page : $p;
        $p = $p > 100 ? 100 : $p;
        $offset = ($p - 1) * $pagesize;



        if($param['is_merge'] == 1 || $param['is_shop'] == 1) {   //店铺或合并卖家
            $list = $this->search->setFuzzy()->setAutoSynonyms()->setQuery($map_string)->addRange('price', $param['min_price'], $param['max_price'])->setCollapse('shop_id')->setLimit($pagesize, $offset)->setMultiSort($order)->search();
        }else{
            $list = $this->search->setFuzzy()->setAutoSynonyms()->setQuery($map_string)->addRange('price', $param['min_price'], $param['max_price'])->setLimit($pagesize, $offset)->setMultiSort($order)->search();
        }
        */
        $setQuery = '(status:1)';
        //$this->search->setQuery('status:1');
        $this->search->setAutoSynonyms();
        if($param['q']) {
            $param['q'] = html_entity_decode($param['q']);
            //$this->search->setQuery('goods_name:'.$param['q']);
            $setQuery .= " AND ({$param['q']})";
            $this->search->setQuery($param['q']);
            $this->search->addWeight('goods_name',$param['q']);
            $this->search->addWeight('category_name',$param['q']);

            //记录搜索历史
            $this->_keywords_history($param['q']);
        }
        if($param['city_id']) $setQuery .= " AND (city_id:{$param['city_id']})";
        //if($param['city_id']) $this->search->setQuery('city_id:'.$param['city_id']);
        if($param['is_self'] == 1) $setQuery .= ' AND (is_self:1)';
        //if($param['is_self'] == 1)      $this->search->setQuery('is_self:1');
        if($param['free_express'] == 1) $setQuery .= ' AND (free_express:1)';
        //if($param['free_express'] == 1) $this->search->setQuery('free_express:1');
        if($param['is_daigou'] == 1) $setQuery .= ' AND (is_daigou:1)';
        //if($param['is_daigou'] == 1)    $this->search->setQuery('is_daigou:1');
        if($param['score_ratio'] != '') $setQuery .= " AND (score_ratio:{$param['score_ratio']})";
        //if($param['score_ratio'] != '') $this->search->setQuery('score_ratio:'.$param['score_ratio']);
        if($param['cid']) {
            $setQuery .= " AND ((category_id:{$param['cid']}) OR (first_category_id:{$param['cid']}) OR (second_category_id:{$param['cid']}))";
            //$this->search->setQuery('((category_id:'.$param['cid'].') OR (first_category_id:'.$param['cid'].') OR (second_category_id:' . $param['cid'].'))');
        }
        //金积分银积分
        if(isset($param['score_type']) && !empty($param['score_type'])) $setQuery .= " AND (score_type:{$param['score_type']})";
        //if(isset($param['score_type']) && !empty($param['score_type'])) $this->search->setQuery('score_type:' . $param['score_type']);

        $this->search->setQuery($setQuery);

        if($param['min_price'] && $param['max_price']) $this->search->addRange('price', $param['min_price'], $param['max_price']);
        elseif($param['min_price'] && empty($param['max_price'])) $this->search->addRange('price', $param['min_price'],null);
        elseif(empty($param['min_price']) && $param['max_price']) $this->search->addRange('price', null, $param['max_price']);


        //排序
        $order = ['pr'];
        if($param['sort']){
            $sort = explode('-',$param['sort']);
            $order = [$sort[0] => ($sort[1] == 'asc' ? true : false),'pr' => false];
        }
        $this->search->setMultiSort($order);

        $pagesize = $param['pagesize'] ? $param['pagesize'] : ($param['is_shop'] ? 15 : 30);
        $p = $param['p'] ? $param['p'] : 1;
        $p = $p > 100 ? 100 : $p;
        $offset = ($p - 1) * $pagesize;
        $this->search->setLimit($pagesize, $offset);

        if($param['is_merge'] == 1 || $param['is_shop'] == 1) {   //店铺或合并卖家
            $this->search->setCollapse('shop_id');
        }
        $list   = $this->search->search();

        $count  = $this->search->count();
        $page   = ceil($count / $pagesize);


        foreach($list as $key => $val){
            //$goods_name = $this->search->highlight($val->goods_name,true);
            $goods_name = $val->goods_name;

            $tmp    = unserialize(html_entity_decode($val->attr_list));
            $tmp2   = array();
            $tmp2[0]= $tmp[0];
            //$list[$key]->setField('attr_list',unserialize(html_entity_decode($val->attr_list)));
            $list[$key]->setField('attr_list',$tmp2);

            $list[$key]->setField('goods_name',$goods_name);
            $list[$key]->setField('attr_count',count($list[$key]->attr_list));

            //店铺详情及相关商品
            if($param['is_shop'] == 1) $list[$key]->setField('shop',$this->shop_info($list[$key]->shop_id));
        }
        //dump($list);


        if($list){
            //格式化
            $list   = objectToArray($list);
            $nlist  = [];
            foreach($list as $i => $val){
                $item = [];
                foreach($val as $key => $v){
                    $item[substr($key,16)] = $v;
                }
                $nlist[$i] = $item;
            }
            $result['list']         = $nlist;
			
			foreach($result['list'] as $k => $v){
				if(check_ticket($v['data']['category_id'])) {
					$result['list'][$k]['data']['is_goupiao'] = 1;
				}else{
					$result['list'][$k]['data']['is_goupiao'] = 0;
				}
			}
			
			
            $result['pageinfo']     = ['count' => $count,'page' => $page,'p' => $p,'pagesize' => $pagesize, 'max' => 100, 'view' => true];
            $result['hot_keywords'] = $this->_hot_keywords($param['q']);

            $title[] = '商品搜索';
            $resutl['seo_title']    = @implode('_',$title);
            if($nav_sort)   $result['nav_sort']     = $nav_sort;
            if($city)       $result['city']         = $city;
            if($option)     $result['option']       = $option;
            if($param['cid']) $result['attr']       = $this->attr($param['cid']);

            return ['code' => 1,'data' => $result];
        }

        return ['code' => 3,'msg' => '找不到记录！'];
    }


    /**
     * subject: 热搜词
     * api: /Search/hot_keywords
     * author: Lazycat
     * day: 2017-01-20
     *
     * [字段名,类型,是否必传,说明]
     * param: q,string,0,搜索关键词
     */

    public function hot_keywords(){
        $field = '';
        if(in_array('q',array_keys($this->post))) $field = 'q';
        $this->check($field,false);

        $res = $this->_hot_keywords($this->post['q']);
        $this->apiReturn(['code' => 1,'data' => $res]);
    }

    /**
     * 相关搜索关键词
     */
    public function _hot_keywords($q=''){
        $keyword = array();
        if($q !='') {
            $res = $this->qs->setQuery($q)->setLimit(10)->search();
            foreach($res as $val){
                $keyword[] = $val->keyword;
            }
        }

        if(empty($keyword)){
            $res = $this->search->getHotQuery(10, 'lastnum');
            foreach($res as $key => $val){
                $keyword[] = $key;
            }
        }

        $words = M('search_keyword')->cache(true)->where(['status' => 1])->limit(10)->getField('keyword',true);

        //$words = ['Iphone','美的','小米','三只松鼠','楼兰密语','安利','百草味','西域美农','批发街','求是数码','东北小妹'];
        $keyword = array_merge($keyword,$words);
        shuffle($keyword);  //打乱数组排序

        //搜索历史
        $cache_name = md5('keywords_history_'.$this->token['data']['device_id']);
        $history    = S($cache_name);

        return ['keywords' => $keyword,'history' => $history];
    }

    /**
     * 获取店铺
     */
    public function shop_info($id){
        $this->xs       = new xs($this->project);
        $this->search   = $this->xs->search;

        $list = $this->xsshopsearch->setQuery('id:'.$id)->search();
        foreach($list as $key => $val){
            $goods = $this->search->setQuery('shop_id:'.$id.' AND status:1')->setMultiSort(['pr','sale_num'])->setLimit(6)->search();
            //dump($goods);
            $list[$key]->setField('goods',$goods);
        }
        //dump($list);

        return $list;
    }


    /**
     * 商品类目及属性
     */
    public function attr($cid){
        if(empty($cid)) return false;

        //同级分类目录
        $result['category'] = M('goods_category')->cache(true)->where(['status' => 1,'sid' => $cid])->field('id,category_name')->order('sort asc,id asc')->select();
        if(empty($result['category'])){
            $sid = M('goods_category')->cache(true)->where(['id' => I('get.id')])->getField('sid');
            $result['category'] = M('goods_category')->cache(true)->where(['status' => 1,'sid' => $sid])->field('id,category_name')->order('sort asc,id asc')->select();

        }

        //$attr_id = $this->get_goods_attr($get['id']);
        //dump($attr_id);

        //类目参数
        $option = $this->get_goods_param($cid);
        $option = $this->_goods_param_cmp($option);
        //dump($option);
        $result['option'] = $option;


        //dump($result);
        return $result;
    }


    /**
     * 根据类目取属性
     * @param int    $_POST['cid']   类目ID
     */
    public function get_goods_attr($cid){
        $do=M('goods_attr');
        $list=$do->cache(true)->where(array('status'=>1,'category_id'=>$cid,'sid'=>0))->field('id,attr_name')->order('sort asc')->select();

        if(empty($list)){
            $rs=M('goods_category')->cache(true)->where(['id' => $cid])->field('id,sid')->find();
            if($rs['sid']>0) $list=$this->get_goods_attr($rs['sid']);
            else return false;
        }

        return $list;
    }

    /**
     * 根据类目取参数
     * @param int    $_POST['cid']   类目ID
     */
    public function get_goods_param($cid){
        $do=D('Admin/GoodsParamOptionRelation');
        $list=$do->relation(true)->cache(true)->where(array('status'=>1,'category_id'=>$cid))->field('id,group_name')->select();
        if(empty($list)){
            $rs=M('goods_category')->cache(true)->where(['id' => $cid])->field('id,sid')->find();
            //dump($rs);
            if($rs['sid']>0) $list=$this->get_goods_param($rs['sid']);
            else return false;
        }

        return $list;
    }



    /**
     * 参数格式化
     */
    public function _goods_param_cmp($param){
        foreach($param as $i => $val){
            foreach($val['param_option'] as $k => $v){
                //1,2表示单选和多选项
                if(in_array($v['type'],[1,2])){
                    $tmp=[];
                    $options=explode(',',$v['options']);
                    foreach($options as $o){
                        $tmp[]  =['name' =>trim($o),'id' => $v['id']];
                    }
                    $search[]    =   [
                        'name'  =>  $v['param_name'],
                        'data'  =>  $tmp,
                    ];
                }
            }
        }
        return $search;
    }


    /**
     * subject: 猜您喜欢
     * api: /Goods/love
     * author: Lazycat
     * day: 2017-01-14
     *
     * [字段名,类型,是否必传,说明]
     * param: num,int,0,读取记录数
     */
    public function love(){
        $this->check(array_keys(I('post.')),false);

        $res = $this->_love($this->post);
        $this->apiReturn($res);
    }

    /**
     * 临时使用的方法，有空再根据用户行为分析取用户感觉兴趣的商品
     */
    public function _love($param=null){
        $do = M('goods');
        $map = [
            'status'    => 1,
            'num'       => ['gt',0],
            //'sale_num'  => ['gt',10],
            //'pr'        => ['gt',20],
            '_string'   => 'goods_name not like "%乐视%" and goods_name not like "%letv%" and goods_name not like "%vivo%" and goods_name not like "%步步高%"',
        ];
        if (isset($param['score_type'])) $map['score_type'] = $param['score_type'];
        if($param['cid']) {
            if(is_array($param['cid'])) $map['category_id'] = ['in',$param['cid']];
            else $map['category_id'] = $param['cid'];
        }
        if($param['not_ids']) $map['id'] = ['not in',$param['not_ids']];
        $count  = $do->where($map)->count();
        //dump($count);

        $num    = $param['num'] ? $param['num'] : 5;
        $ids    = [];
        $xsmap  = [];
        $i      = 0;    //最多循环50次
        $limit  = [];
        while (count($limit) < $num && $i < 100){
            $tmp = rand(0,$count-1).',1';
            if(!in_array($tmp,$limit)) $limit[] = $tmp;
            $i++;
        }

        //dump($limit);

        foreach($limit as $val){
            $id = $do->where($map)->limit($val)->getField('id',true);
            $ids[]  = $id[0];
            $xsmap[]= 'id:'.$id[0];
        }


        //dump($ids);
        if(empty($xsmap)) goto error;

        $map_string = implode(' OR ',$xsmap);
        $list = $this->search->setQuery($map_string)->search();

        if($list) {
            //格式化
            $list   = objectToArray($list);
            $nlist  = [];
            foreach($list as $i => $val){
                $item = [];
                foreach($val as $key => $v){
                    $v['attr_list'] = unserialize(html_entity_decode($v['attr_list']));
                    if(is_array($v['attr_list'])) {
                        $v['app_url']   = DM('m', '/goods/view/id/' . $v['attr_list'][0]['id']);
                        $tmp            = A('App')->_token(['erp_uid' => $param['erp_uid'], 'redirect_url' => $v['app_url']]);
                        $v['auth_url']  = $tmp['data']['url'];
                        $v['url']       = $v['auth_url'];
                    }
                    $item[substr($key,16)] = $v;
                }
                $nlist[$i] = $item;
            }

            return ['code' => 1,'data'=> $nlist];
        }

        error:
        return ['code' => 3,'msg' => '找不到记录！'];
    }

    /**
     * subject: WAP首页猜您喜欢
     * api: /Goods/love_list
     * author: Lazycat
     * day: 2017-01-21
     * content: 随机读取某页数据，须传入ps已读到的页面才可自动过滤获取新页面
     *
     * [字段名,类型,是否必传,说明]
     * param: pagesize,int,0,读取记录数
     * param: p,int,0,读取第p页
     */
    public function love_list(){
        $this->check($this->_field('openid'),false);

        $res = $this->_love_list($this->post);
        $this->apiReturn($res);
    }

    public function _love_list($param){
        $cache_name     = md5('goods_history_'.$this->token['data']['device_id']);
        $goods_history  = S($cache_name);

        if($this->user['id']){
            $cid_history = M('user_love_category')->cache(true)->where(['uid' => $this->user['id']])->order('pr desc,num desc')->limit(10)->getField('category_id',true);
        }else {
            $cache_name = md5('goods_cid_history_' . $this->token['data']['device_id']);
            $cid_history = S($cache_name);
        }
        //print_r($cid_history[0]);

        //查询条件
        //$map_string = 'status:1';
        $map_string = '';
        //$count = $this->search->setQuery($map_string)->addRange('sale_num', 10)->addRange('pr', 20)->count();
        $count = $this->search->setQuery($map_string)->count();
        $pagesize   = $param['pagesize'] ? $param['pagesize'] : 12;
        $page       = ceil($count / $pagesize);
        //已被读取的页码
        //$ps = $param['ps'] ? explode(',',$param['ps']) : array();

        /*
        $i = 0;
        while($i < 50){    //随机读取页码
            $p  = rand(1,$page-1);
            if(!in_array($p,$ps)){
                break;
            }
            $i++;
        }
        if(in_array($p,$ps)) return ['code' => 3];
        */

        $p = $param['p'] ? $param['p'] : 1;
        $p = $p > $page ? $page : $p;
        $p = $p > 100 ? 100 : $p;
        $offset = ($p - 1) * $pagesize;

        $order = ['pr' => false,'sale_num' => false];
        $this->search->setQuery($map_string);
//        foreach($goods_history as $key => $val){
//            $this->search->addWeight('goods_name',$val,1);
//        }
//        foreach($cid_history as $key => $val){
//            $this->search->addWeight('category_id',$val,1);
//        }
//        $this->search->addRange('sale_num', 10);
//        $this->search->addRange('pr', 20);
        $this->search->setLimit($pagesize, $offset);
        if(empty($goods_history) && empty($cid_history)) $this->search->setMultiSort($order);
        $list = $this->search->search();
        if($list){
            foreach($list as $key => $val){
                //$goods_name = $this->search->highlight($val->goods_name,true);
                $tmp    = unserialize(html_entity_decode($val->attr_list));
                $tmp2   = array();
                $tmp2[0]= $tmp[0];
                //$list[$key]->setField('attr_list',unserialize(html_entity_decode($val->attr_list)));
                $list[$key]->setField('attr_list',$tmp2);

                $goods_name = $val->goods_name;
                $list[$key]->setField('goods_name',$goods_name);
                $list[$key]->setField('attr_count',count($list[$key]->attr_list));
            }
            //格式化
            $list   = objectToArray($list);
            $nlist  = [];
            foreach($list as $i => $val){
                $item = [];
                foreach($val as $key => $v){
                    $item[substr($key,16)] = $v;
                }
                $nlist[$i] = $item;
            }
            $result['list']         = $nlist;
            $result['pageinfo']     = ['count' => $count,'page' => $page,'p' => $p,'pagesize' => $pagesize, 'max' => 100, 'view' => true];

            return ['code' => 1,'data' => $result];
        }

        return ['code' => 3,'msg' => '找不到记录！'];
    }


    /**
     * 记录用户的搜索历史
     * Create by Lazycat
     * 2017-30-07
     * @param $q    string 搜索关键词
     */
    public function _keywords_history($q){
        if(empty($q) || empty($this->token['data']['device_id'])) return;

        $max            = 20;   //最多保存20个历史关键词
        $cache_name     = md5('keywords_history_'.$this->token['data']['device_id']);

        $data           = S($cache_name);
        if(empty($data)) $data = array();

        array_unshift($data,$q);
        $data           = array_unique($data);

        if(count($data) > $max) $data = array_pop($data);

        S($cache_name,$data,86400 * 7); //保存7天，测试先保留10分钟
    }

}

