<?php
/**
 * ----------------------------------------------------------
 * RestFull API V2.0
 * ----------------------------------------------------------
 * 卖家店铺接口
 * ----------------------------------------------------------
 * Author:Lazycat <673090083@qq.com>
 * ----------------------------------------------------------
 * 2017-02-08
 * ----------------------------------------------------------
 */
namespace Rest2\Controller;
use Think\Controller\RestController;
class ShopController extends ApiController {
    protected $action_logs = array();

    /**
     * subject: 店铺基本资料
     * api: /Shop/info
     * author: Lazycat
     * day: 2017-02-08
     *
     * [字段名,类型,是否必传,说明]
     * param: shop_id,int,1,店铺ID
     * param: openid,string,0,用户openid，传入该值时可以判断该用户是否有关注店铺
     * param: is_make,int,0,是否获取店铺装内容
     */
    public function info(){
        $this->check('shop_id',false);

        $res = $this->_info($this->post);
        $this->apiReturn($res);
    }

    public function _info($param){
        $do = D('Common/ShopRelation');
        $rs = $do->relation(true)->cache(true,1)->where(['id' => $param['shop_id'],'_logic' => 'or','domain' => $param['shop_id']])->field('etime,ip',true)->find();
        if($rs) {
            $param['shop_id']   = $rs['id'];
			$shop_type 	=	$this->cache_table('shop_type');
            $area 	=	$this->cache_table('area');
            $rs['province_id'] =$rs['province'];
            $rs['city_id']     =$rs['city'];
            $rs['district_id'] =$rs['district'];
            $rs['town_id']     =$rs['town'];
            $rs['province']    =$area[$rs['province']];
            $rs['city']        =$area[$rs['city']];
            $rs['district']    =$area[$rs['district']];
            $rs['town']        =$area[$rs['town']];
			$rs['type_name']		=$shop_type[$rs['type_id']];
			
            $rs['goods_num']    = M('goods')->cache(true,10)->where(['shop_id' => $param['shop_id'],'num' => ['gt',0],'status' => 1])->count();
            $rs['best_num']     = M('goods')->cache(true,10)->where(['shop_id' => $param['shop_id'],'num' => ['gt',0],'status' => 1,'is_best' => 1])->count();

            if($rs['banner']) $rs['banner'] = html_entity_decode($rs['banner']);
			
            //经营类目
            if($rs['category_id']){
				$goods_category 	=	$this->cache_table('goods_category');
				$category_id=explode(',',$rs['category_second']);
				foreach($category_id as $val){
					$rs['category_name'][]	=	$goods_category[$val];
				}
			}
			
            //是否有收藏
            if($this->user['id']){
                $rs['is_fav'] = M('shop_fav')->where(['uid' => $this->user['id'],'shop_id' => $param['shop_id']])->count();
            }

            //获了店铺装修内容
            if($param['is_make']){
                $tmp        = $this->_shop_make($rs['id']);
                $rs['make'] = $tmp['data'];
            }
			
			$shop_news = M('shop_news')->field('remark,status')->where(['shop_id'=>$param['shop_id']])->find();
			$rs['shop_news_remark'] = $shop_news['remark'];
			$rs['shop_news_status'] = $shop_news['status'];

            return ['code' => 1,'data' => $rs];
        }

        return ['code' => 3];
    }

    /**
     * subject: 店铺装修内容
     * api: /Shop/shop_make
     * author: Lazycat
     * day: 2017-03-08
     *
     * [字段名,类型,是否必传,说明]
     * param: shop_id,int,1,店铺ID
     */
    public function shop_make(){
        $this->check('shop_id',false);

        $res = $this->_shop_make($this->post['shop_id']);
        $this->apiReturn($res);
    }

    //获取店铺装修
    public function _shop_make($shop_id){
        $list = M('shop_make_m_publish')->cache(true)->where(['shop_id' => $shop_id])->field('id,type,type_name,data')->order('sort asc,id asc')->select();
        if(empty($list)) return ['code' => 3,'msg' => '暂无装修数据！'];

        foreach($list as $key => $val){
            $val['data']    = unserialize(html_entity_decode($val['data']));
            $list[$key]     = $val;
        }

        return ['code' => 1,'data' => $list];
    }


    /**
     * subject: 获取推荐商品
     * api: /Shop/goods_topN
     * author: Lazycat
     * day: 2017-02-08
     *
     * [字段名,类型,是否必传,说明]
     * param: shop_id,int,1,店铺ID
     * param: num,int,0,返回记录数量
     * param: is_best,0,是否厨窗商品
     * param: order,0,排序，默认按销量降序
     */
    public function goods_topN(){
        $this->check($this->_field('num,is_best,order','shop_id'),false);

        $res = $this->_goods_topN($this->post);
        $this->apiReturn($res);
    }

    public function _goods_topN($param){
        $limit = $param['num'] ? $param['num'] : 12;
        $order = $param['order'] ? $param['order'] : 'sale_num desc';

        $do = D('Common/GoodsRelation');
        $map['status']      = 1;
        $map['num']         = array('gt',0);
        $map['shop_id']     = $param['shop_id'];
        if($param['is_best']) $map['is_best']     = 1;

        $list = $do->relation(true)->cache(true,C('CACHE_LEVEL.M'))->relationWhere('goods_attr_list','num>0')->relationOrder('goods_attr_list','price asc')->relationField('goods_attr_list','id,attr_name,price,price_market,sale_num,view')->relationLimit('goods_attr_list',1)->where($map)->field('id,goods_name,images,price,sale_num,shop_id,seller_id,score_ratio')->limit($limit)->order($order)->select();

        if($list){
            return ['code' => 1,'data' => $list];
        }

        return ['code' => 3];
    }

    /**
     * subject: 厨窗商品
     * api: /Shop/best
     * author: Lazycat
     * day: 2017-02-09
     *
     * [字段名,类型,是否必传,说明]
     * param: shop_id,int,1,店铺ID
     * param: pagesize,int,0,分页数量
     * param: p,int,0,当前页码
     * param: order,string,0,排序，默认按权重降序
     * param: is_best,int,0,是否厨窗商品
     * param: q,string,0,关键词
     * param: sid,int,0,店内商品分类
     */

    public function goods(){
        $this->check($this->_field('pagesize,p,order,is_best,q,sid','shop_id'),false);

        $res = $this->_goods($this->post);
        $this->apiReturn($res);
    }

    public function _goods($param){
        $map['status']      = 1;
        $map['num']         = array('gt',0);
        $map['shop_id']     = $param['shop_id'];
        if($param['is_best']) $map['is_best']     = 1;

        //店铺分类
        if($param['sid']) {
            $ids    = sortid(['table' => 'shop_goods_category','sid' => $param['sid']]);
            $tmp    = array();
            foreach($ids as $val){
                $tmp[]  = 'find_in_set ('.$val.',shop_category_id)';
            }
            $map['_string'] = implode(' OR ',$tmp);
        }

        //关键词
        if($param['q']){
            $q = explode(' ',urldecode($param['q']));
            $tmp = array();
            foreach($q as $val){
                $tmp[] = 'goods_name like "%'.$val.'%"';
            }
            $sqlstr = implode(' AND ',$tmp);
            $map['_string'] = $map['_string'] ? $map['_string'] . ' AND '.$sqlstr : $sqlstr;
        }

        $order      = $param['order'] ? $param['order'] : 'pr desc';
        $pagesize   = $param['pagesize'] ? $param['pagesize'] : 18;

        $pagelist = pagelist(array(
            'table'         => 'Common/GoodsRelation',
            'do'            => 'D',
            'map'           => $map,
            'fields'        => 'id,goods_name,images,price,sale_num,shop_id,seller_id,score_ratio',
            'order'         => $order,
            'pagesize'      => $pagesize,
            'relation'      => true,
            'relationWhere' => array('goods_attr_list','num>0'),
            'relationOrder' => array('goods_attr_list','price asc'),
            'relationField' => array('goods_attr_list','id,attr_name,price,price_market,sale_num,view'),
            'relationLimit' => array('goods_attr_list',1),
            'p'             => $param['p'],
            'cache_name'    => md5(implode(',',$param).__SELF__),
            'cache_time'    => C('CACHE_LEVEL.L'),
        ));
        if($pagelist['list']){
            return ['code' => 1,'data' => $pagelist];
        }

        return ['code' => 3];
    }


    /**
     * subject: 店铺用户评价
     * api: /Shop/rate
     * author: Lazycat
     * day: 2017-02-10
     *
     * [字段名,类型,是否必传,说明]
     * param: shop_id,int,1,店铺ID
     * param: pagesize,int,0,分页记录数
     * param: p,int,0,要获取的页码
     * param: level,number,0,评价级别
     */
    public function rate(){
        $this->check($this->_field('pagesize,p,level','shop_id'),false);

        $res = $this->_rate($this->post);
        $this->apiReturn($res);
    }

    public function _rate($param){
        $rate_name = [
            '-1'    =>'差评',
            '0'     =>'中评',
            '1'     =>'好评'
        ];

        $pagesize   = $param['pagesize'] ? $param['pagesize'] : 12;
        $order      = 'atime desc';

        $map['shop_id']     = $param['shop_id'];
        $map['status']      = 1;
        $map['is_shuadan']  = 0;
        if($param['level'] != '') $map['rate'] = $param['level'];
        $pagelist = pagelist(array(
            'table'         => 'Common/OrdersGoodsCommentRelation',
            'do'            => 'D',
            'map'           => $map,
            'fields'        => 'id,atime,like_num,uid,orders_goods_id,rate,content,images,is_anonymous',
            'order'         => $order,
            'pagesize'      => $pagesize,
            'relation'      => true,
            'p'             => $param['p'],
            'cache_name'    => md5(implode(',',$param).__SELF__),
            'cache_time'    => C('CACHE_LEVEL.L'),
        ));

        if($pagelist['list']){
            //数据格式化
            $user_level	= $this->cache_table('user_level');
            foreach($pagelist['list'] as $key=>$val){
                //晒图
                if($val['images']){
                    $val['images'] = explode(',',$val['images']);
                    //清除空值
                    $pagelist['list'][$key]['images'] = array_diff($val['images'],array(''));
                }else $pagelist['list'][$key]['images'] = null;

                $pagelist['list'][$key]['user']['level_name']			=	$user_level[$val['user']['level_id']];
                $pagelist['list'][$key]['user']['nick']                 =   $val['is_anonymous'] == 1 ? '匿名' : hiddenChineseStr($val['user']['nick']);
                $pagelist['list'][$key]['rate_name']					=	$rate_name[$val['rate']];
            }

            $count['rate_good']		= M('orders_goods_comment')->where(['shop_id' => $param['shop_id'],'status' => 1,'rate' => 1])->count();
            $count['rate_middle']	= M('orders_goods_comment')->where(['shop_id' => $param['shop_id'],'status' => 1,'rate' => 0])->count();
            $count['rate_bad']		= M('orders_goods_comment')->where(['shop_id' => $param['shop_id'],'status' => 1,'rate' => -1])->count();
            $count['rate_num']      = array_sum($count);

            $pagelist['count']      = $count;

            return ['code' => 1,'data' => $pagelist];
        }

        return ['code' => 3];   //无评价

    }


    /**
     * subject: 店铺商品分类
     * api: /Shop/category
     * author: Lazycat
     * day: 2017-02-10
     *
     * [字段名,类型,是否必传,说明]
     * param: shop_id,int,1,店铺ID
     */
    public function category(){
        $this->check('shop_id',false);

        $res = $this->_category($this->post);
        $this->apiReturn($res);
    }

    public function _category($param){
        $list=get_category(['table' => 'shop_goods_category' , 'level' => 2,'field'=>'id,sid,category_name,icon,sort' , 'map' => ['shop_id' => $param['shop_id']],'cache_name'=>md5(implode(',',$param)),'cache_time'=>C('CACHE_LEVEL.M')]);

        if($list){
            return ['code' => 1,'data' => $list];
        }

        return ['code' => 3];
    }

}