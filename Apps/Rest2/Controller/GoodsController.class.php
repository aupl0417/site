<?php
/**
 * ----------------------------------------------------------
 * RestFull API V2.0
 * ----------------------------------------------------------
 * 商品
 * ----------------------------------------------------------
 * Author:Lazycat <673090083@qq.com>
 * ----------------------------------------------------------
 * 2017-01-07
 * ----------------------------------------------------------
 */
namespace Rest2\Controller;
use Think\Controller\RestController;
class GoodsController extends ApiController {
    //protected $action_logs = array('ad');

    /**
     * subject: 商品分类
     * api: /Goods/category
     * author: Lazycat
     * day: 2017-01-09
     *
     * [字段名,类型,是否必传,说明]
     * param: cid,int,1,分类ID，选填，读取某个分类的子级
     * param: depth,int,0,读取层级，默认为3
     */
    public function category(){
        $this->check($this->_field('layer'),false);

        $res = $this->_category($this->post);
        $this->apiReturn($res);
    }


    /**
     * 读取广告
     * @param int $position_id 广告位ID
     */
    public function _category($param=null){
        $cid    = $param['cid'] ? $param['cid'] : 0;
        $depth  = $param['depth'] ? $param['depth'] : 3;
        $list=get_category(array('table'=>'goods_category','field'=>'id,sid,icon,images,category_name,sub_name,wap_banner','level'=> $depth,'map'=>[['status' =>1],['status' =>1],['status' =>1]],'sid'=>$cid,'cache_name'=>'goods_category_list_'.$cid,'cache_time'=>3600));

        if($list){
            return ['code' => 1,'data' => $list];
        }

        return ['code' => 3,'msg' => '找不到分类！'];
    }


    /**
     * subject: 商品信息
     * api: /Goods/view
     * author: Lazycat
     * day: 2017-01-20
     *
     * [字段名,类型,是否必传,说明]
     * param: id,int,1,商品库存ID
     * param: openid,string,0,用户openid，用于检测用户对该商品是否已关注
     */

    public function view(){
        $this->check('id',false);

        $res = $this->_view($this->post);
        $this->apiReturn($res);
    }

    public function _view($param){
        //当前属性数据
        $do = M('goods_attr_list');
        $rs = $do->cache(true,C('CACHE_LEVEL.XS'))->where(array('id' => $param['id']))->field('id,seller_id,goods_id,attr_id,attr_name,price,price_market,num,sale_num,rate_good,rate_middle,rate_bad,weight')->find();
        if(empty($rs)) return ['code' => 3,'msg' => '找不到商品记录！'];

        //if($rs['num'] < 1){
        //    $rs=$do->cache(true,C('CACHE_LEVEL.XS'))->where(array('goods_id' => $rs['goods_id'],'num' => ['gt',0]))->field('id,seller_id,goods_id,attr_id,attr_name,price,price_market,num,sale_num,rate_good,rate_middle,rate_bad,weight')->find();
        //}

        //店铺已停止营业或关闭！
        if(!M('shop')->where(['uid' => $rs['seller_id'],'status' => 1])->field('id')->find()) return ['code' => 0,'msg' => '店铺已停止营业！'];

        //库存(SKU)
        $attr_list = $do->cache(true,C('CACHE_LEVEL.XS'))->where(array('goods_id' => $rs['goods_id']))->getField('attr_id,id,goods_id,attr_name,price,price_market,num,sale_num,rate_good,rate_middle,rate_bad');
        $do     = D('Common/GoodsViewRelation');
        $goods  = $do->where(array('id' => $rs['goods_id']))->field('atime,etime,ip',true)->find();
        //$goods['keywords']  = A('Tools')->_scws($goods['goods_name']);

        //0=删除,1=上架,2=仓库,3=主图缺失,4=违夫,5=异常,6=禁止上架
        switch ($goods['status']) {
            case 0:
                return ['code' => 3,'msg' => '商品不存在！'];
                break;
            case 2:
                return ['code' => 3,'msg' => '商品未上架！'];
                break;
            case 3:
                return ['code' => 3,'msg' => '商品主图不全！'];
                break;
            case 4:
                return ['code' => 3,'msg' => '商品违规！'];
                break;
            case 5:
                return ['code' => 3,'msg' => '商品存在异常！'];
                break;
            case 6:
                return ['code' => 3,'msg' => '商品被强制下架！'];
                break;
        }

        //促销标签
        $rs['label'] = []; //商品促销标签
        if($goods['is_self'] == 1)        $rs['label'][] = '自营';
        if($goods['is_diagou'] == 1)      $rs['label'][] = '代购';
        if($goods['free_express'] == 1)   $rs['label'][] = '包邮';
        //if($goods['score_ratio'] == 2)    $rs['label'][] = '双倍积分';
        //if($goods['score_ratio'] == 4)    $rs['label'][] = '四倍积分';


        $rs['score']    = $rs['price'] * $goods['score_ratio'];
        $rs['score'] = $rs['score'] < 0.01 ? 0 : sprintf("%.2f",$rs['score']);

        //不是现金支付，不赠送乐兑宝
        if($goods['score_type'] != 2){
            $rs['score'] = 0;
        }
        //如果属于购票，将销量去除
        if(check_ticket($goods['category_id'])) {
            $goods['sale_num'] = 0;
            $rs['is_goupiao'] = 1;
        }



        //是否参与了官方活动
        if($goods['officialactivity_join_id'] > 0){
            $rs['label'][] = '秒杀';
            $goods['activity'] = M('officialactivity_join')->cache(false)->where(['id' => $goods['officialactivity_join_id']])->field('id,day,time,price')->find();
            $goods['activity']['time_dif']	= strtotime($goods['activity']['day'].' '.$goods['activity']['time']) - time();
            //距离活动开始的前12小时开始倒计时
            //if(time() > strtotime($goods['activity']['day'].' '.$goods['activity']['time']) - (3600 * 12)) $goods['is_officialactivity'] = 1;
            $rs['score']    = intval($goods['officialactivity_price'] * $goods['score_ratio'] * 100);

            //秒杀状态
            $rs['is_miaosha']       = 1;    //秒杀商品
            $rs['miaosha_status']   = 1;    //秒杀中
            $rs['miaosha_time_dif'] = $goods['activity']['time_dif'];
            if($goods['activity']['time_dif'] > 0)          $rs['miaosha_status']    = 2;    //倒计时
            elseif($goods['activity']['time_dif'] < -86400) $rs['miaosha_status']    = 3;    //秒杀结束

            //测试
            //$rs['miaosha_status']   = 2;
            //$goods['activity']['time_dif'] = 5;
        }


        //代购
        if ($goods['is_daigou'] == 1 && $goods['daigou_ratio'] == 0) {
            $cfg = C('cfg.daigou');
            $goods['daigou_ratio'] = $cfg['daigou_cost_ratio'];
        }


        $rs['images']   = $goods['images'];
        $rs['attr_ids'] = explode(',',$rs['attr_id']);

        //属性
        $do     = M('goods_attr_value');
        $attr   = $do->cache(true,C('CACHE_LEVEL.XS'))->where(array('goods_id'=>$rs['goods_id']))->group('attr_id')->getField('attr_id',true);
        $do     = D('Common/GoodsAttrValueRelation');
        $attr_value = $do->relation(true)->relationWhere('goods_attr_value','goods_id='.$rs['goods_id'])->cache(false,C('CACHE_LEVEL.XS'))->where(array('id'=>array('in',$attr)))->field('id,attr_name')->order('sort asc')->select();
        //echo $do->getLastSQL();
        //print_r($attr_value);
        foreach($attr_value as $key => $val){
            foreach($val['option'] as $vkey => $v){
                $attr_value[$key]['option'][$vkey]['attr'] = $val['id'].':'.$v['option_id'];
                if($v['attr_album']){
                    $attr_value[$key]['option'][$vkey]['attr_album'] = explode(',',$v['attr_album']);
                }
            }
        }

        //当前库存商品主图
        $attr_id = explode(',',$rs['attr_id']);
        //dump($attr_id);
        foreach($attr_value as $Key => $val){
            foreach($val['option'] as $vkey => $v){
                if(in_array($v['attr'], $attr_id) && $v['attr_album']){
                    if(is_array($rs['images_album'])) $rs['images_album'] = array_merge($rs['images_album'],$v['attr_album']);
                    else $rs['images_album'] = $v['attr_album'];
                }
            }
        }

        //商品相册
        if($rs['images_album']) {
            $rs['images_album'][]   = $rs['images'];
            $rs['images']           = $rs['images_album'][0];
        }else $rs['images_album']   = array($rs['images']);
        $rs['images_album']	        = array_values(array_unique($rs['images_album']));

        $rs['attr_list']        = array_values($attr_list);
        $rs['attr']             = $attr_value;
        $rs['goods']            = $goods;
        $rs['seller']           = M('user')->cache(true,C('CACHE_LEVEL.XXL'))->where(array('id' => $rs['seller_id']))->field('id,nick,face,erp_uid')->find();
        $rs['shop']             = M('shop')->cache(true,C('CACHE_LEVEL.XXL'))->where(array('uid' => $rs['seller_id']))->field('id,shop_name,shop_logo,domain,qq,mobile,wang,type_id,fraction_desc,fraction,fraction_speed,fraction_service')->find();

        $shop_type                      = $this->cache_table('shop_type');
        $rs['shop']['type_name']        = $shop_type[$rs['shop']['type_id']];
        $rs['shop']['shop_url']         = shop_url($rs['shop']['id'],$rs['shop']['domain']);

        if($param['isget_content'])     $rs['content']      = $this->_goods_content($rs['goods_id']);
        if($param['isget_protection'])  $rs['protection']   = $this->_goods_protection($goods['protection_id']);
        if($param['isget_package'])     $rs['package']      = $this->_goods_package($goods['package_id']);
        if($param['isget_param'])       $rs['param']        = $this->_goods_param($rs['goods_id'],$goods['category_id']);
        if($param['isget_rate'])        $rs['rate']         = $this->_rate_topN($rs['goods_id'],($param['rate_num'] ? $param['rate_num'] : 5));

        M('goods_attr_list')->where(['id' => $param['id']])->setInc('view',1,60);   //浏览次数，60延迟更新
        M('goods')->where(['id' => $rs['goods_id']])->setInc('view',1,60);          //浏览次数，60延迟更新

        //所在地
        $express_tpl    = M('express_tpl')->where(['id' => $goods['express_tpl_id']])->field('province,city')->find();
        $area           = $this->cache_table('area');
        $rs['city']     = $area[$express_tpl['province']].' '.$area[$express_tpl['city']];

        //是否已关注商品
        $rs['is_fav']   = 0;
        if($this->user['id']){
            if(M('goods_fav')->where(['uid' => $this->user['id'],'goods_id' => $rs['goods_id']])->count() > 0) $rs['is_fav'] = 1;
        }

        //优惠券
        $tmp            = A('Rest2/Coupon')->_get_batch(['shop_id' => $goods['shop_id'],'goods_id' => $rs['goods_id'],'category_id' => $goods['category_id']]);
        $rs['coupon']   = $tmp['data'];

        $this->_goods_history($rs['goods_id']);             //记录浏览历史
        $this->_category_history($goods['category_id'],$this->user['id']);    //记录类目记录

        return ['code' => 1,'data' => $rs];
    }


    /**
     * subject: 商品详情
     * api: /Goods/content
     * author: Lazycat
     * day: 2017-02-07
     *
     * [字段名,类型,是否必传,说明]
     * param: goods_id,int,1,商品ID
     */

    public function content(){
        $this->check('goods_id',false);

        $rs = M('goods')->cache(true)->where(['id' => $this->post['goods_id']])->field('package_id,protection_id')->find();

        $res['content']         = $this->_goods_content($this->post['goods_id']);
        $res['protection']      = $this->_goods_protection($rs['protection_id']);
        $res['package']         = $this->_goods_package($rs['package_id']);
        $res['param']           = $this->_goods_param($this->post['goods_id']);

        $this->apiReturn(['code' => 1,'data' => $res]);
    }



    /**
     * subject: 商品评价
     * api: /Goods/rate
     * author: Lazycat
     * day: 2017-02-07
     *
     * [字段名,类型,是否必传,说明]
     * param: goods_id,int,1,商品ID
     * param: pagesize,int,0,分页记录数
     * param: p,int,0,要获取的页码
     * param: level,number,0,评价级别
     */
    public function rate(){
        $this->check($this->_field('pagesize,p,level','goods_id'),false);

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

        $map['goods_id']    = $param['goods_id'];
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

            $count['rate_good']		= M('orders_goods_comment')->where(['goods_id' => $param['goods_id'],'rate' => 1])->count();
            $count['rate_middle']	= M('orders_goods_comment')->where(['goods_id' => $param['goods_id'],'rate' => 0])->count();
            $count['rate_bad']		= M('orders_goods_comment')->where(['goods_id' => $param['goods_id'],'rate' => -1])->count();
            $count['rate_num']      = array_sum($count);

            $pagelist['count']      = $count;

            return ['code' => 1,'data' => $pagelist];
        }

        return ['code' => 3];   //无评价

    }




    /**
     * 商品详情
     * @param int $goods_id 商品ID
     */
    public function _goods_content($goods_id){
        $do=M('goods_content');
        $rs=$do->cache(true,C('CACHE_LEVEL.XL'))->where(array('goods_id' => $goods_id))->field('content')->find();
        $rs['content']=html_entity_decode($rs['content']);
        preg_match_all("/<img([\s\S]*?)src=\"([\s\S]*?)\"/ies",$rs['content'],$images);
        $rs['images_album'] = $images[2] ? $images[2] : null;
        return $rs;
    }

    /**
     * 商品保障
     * @param int $id 商品保障模板ID
     */
    public function _goods_protection($id){
        $do=M('goods_protection');
        $rs=$do->cache(true,C('CACHE_LEVEL.XL'))->where(array('id' => $id))->field('content')->find();
        $rs['content'] = html_entity_decode($rs['content']);
        return $rs;
    }

    /**
     * 商品包装
     * @param int $id 商品包装模板ID
     */
    public function _goods_package($id){
        $do=M('goods_package');
        $rs=$do->cache(true,C('CACHE_LEVEL.XL'))->where(array('id' => $id))->field('content')->find();
        $rs['content'] = html_entity_decode($rs['content']);
        return $rs;
    }

    /**
     * 商品参数
     * @param int $goods_id 商品包装模板ID
     * @param int $cid  商品类目ID
     */
    public function _goods_param($goods_id,$cid=''){
        if(empty($cid)){
            $crs=M('goods')->cache(true,C('CACHE_LEVEL.XL'))->where(array('id' => $goods_id))->field('category_id')->find();
            $cid=$crs['category_id'];
        }

        $list=$this->_goods_param_group($cid);
        $do=D('Common/GoodsParamGroupOptionRelation');
        foreach($list as $key=>$val){
            $list[$key]['param']=$do->relation(true)->relationWhere('goods_param','goods_id='.$goods_id)->cache(true,C('CACHE_LEVEL.XS'))->where(array('group_id'=>$val['id']))->field('id,param_name')->select();
        }
        return $list ? $list : null;
    }

    /**
     * 根据类目取参数
     * @param int    $_POST['cid']   类目ID
     */
    public function _goods_param_group($cid){
        $do=D('Admin/GoodsParamOptionRelation');
        $list=$do->relation(true)->where(array('status' => 1,'category_id' => $cid))->field('id,group_name')->select();
        if(empty($list)){
            $rs=M('goods_category')->where(['id' => $cid])->field('id,sid')->find();
            //dump($rs);
            if($rs['sid']>0) $list=$this->get_goods_param($rs['sid']);
            else return false;
        }

        return $list;
    }

    /**
     * 商品topN条评价
     * @param int $goods_id 商品ID
     * @param int $cid 商品类目ID
     * @param int $limit 取n条评价
     */

    public function _rate_topN($goods_id,$limit=5){
        $rate_name=[
            '-1'    =>'差评',
            '0'     =>'中评',
            '1'     =>'好评'
        ];

        $do=D('Common/OrdersGoodsCommentRelation');
        $list=$do->cache(true,C('CACHE_LEVEL.L'))->relation(true)->where(array('goods_id' => $goods_id,'status' => 1,'is_shuadan' => 0))->field('atime,like_num,uid,orders_goods_id,rate,content,images,is_anonymous')->order('atime desc')->limit($limit)->select();

        $user_level	=	$this->cache_table('user_level');
        foreach($list as $key=>$val){
            //晒图
            if($val['images']){
                $val['images']          = explode(',',$val['images']);
                //清除空值
                $list[$key]['images']   = array_diff($val['images'],array(''));
            }else $list[$key]['images'] = null;

            $list[$key]['user']['level_name']			=	$user_level[$val['user']['level_id']];
            $list[$key]['user']['nick']                 =   $val['is_anonymous'] == 1 ? '匿名' : hiddenChineseStr($val['user']['nick']);
            $list[$key]['rate_name']					=	$rate_name[$val['rate']];
        }

        return $list;
    }

    /**
     * 根据类目取参数
     * @param int    $_POST['cid']   类目ID
     */
    public function get_goods_param($cid){
        $do=D('Admin/GoodsParamOptionRelation');
        $list=$do->relation(true)->cache(true,C('CACHE_LEVEL.OneDay'))->where(array('status' => 1,'category_id' => $cid))->field('id,group_name')->select();
        if(empty($list)){
            $rs=M('goods_category')->cache(true,C('CACHE_LEVEL.OneDay'))->where(['id' => $cid])->field('id,sid')->find();
            //dump($rs);
            if($rs['sid']>0) $list = $this->get_goods_param($rs['sid']);
            else return false;
        }

        return $list;
    }

    /**
     * subject: 商品信息
     * api: /Goods/view
     * author: Lazycat
     * day: 2017-02-13
     *
     * [字段名,类型,是否必传,说明]
     * param: goods_id,int,1,商品ID
     * param: attr_id,string,1,库存属性ID组合
     */

    public function change_attr(){
        $this->check('goods_id,attr_id',false);

        $data['id'] = M('goods_attr_list')->cache(true)->where(['goods_id' => $this->post['goods_id'],'attr_id' => $this->post['attr_id']])->getField('id');

        $res = $this->_view($data);
        $this->apiReturn($res);
    }


    /**
     * 记录商品浏览记录
     * Create by Lazycat
     * 2017-03-07
     * @param $goods_id int 商品ID
     */
    public function _goods_history($goods_id){
        if(empty($goods_id) || empty($this->token['data']['device_id'])) return;

        $max            = 50;   //最多保存50条浏览历史
        $cache_name     = md5('goods_history_'.$this->token['data']['device_id']);

        $data           = S($cache_name);
        if(empty($data)) $data = array();

        array_unshift($data,$goods_id);
        $data           = array_unique($data);

        if(count($data) > $max) $data = array_pop($data);

        S($cache_name,$data,86400 * 7); //保存7天，测试先保留10分钟
    }

    /**
     * 记录浏览商品的类目，用于分析
     * Create by Lazycat
     * 2017-03-07
     * @param $cid int 商品类目ID
     * @param $uid int 用户ID
     */
    public function _category_history($cid,$uid=0){
        if(empty($cid) || empty($this->token['data']['device_id'])) return;

        if($uid > 0){
            $id = M('user_love_category')->where(['uid' => $uid,'category_id' => $cid])->getField('id');
            if($id){
                M('user_love_category')->where(['id' => $id])->setInc('num',1,60);
                M('user_love_category')->where(['id' => $id])->setInc('pr',1,60);
            }else{
                $data = [
                    'uid'           => $uid,
                    'category_id'   => $cid,
                    'num'           => 1,
                    'pr'            => 1,
                ];
                M('user_love_category')->add($data);
            }
        }else {
            $max = 10;   //最多保存10条历史类目
            $cache_name = md5('goods_cid_history_' . $this->token['data']['device_id']);

            $data = S($cache_name);
            if (empty($data)) $data = array();

            array_unshift($data, $cid);
            $data = array_unique($data);

            if (count($data) > $max) $data = array_pop($data);

            S($cache_name, $data, 86400 * 7); //保存7天，测试先保留10分钟
        }
    }

    /**
     * subject: 秒杀商品资料
     * api: /Goods/miaosha_goods_view
     * author: Lazycat
     * day: 2017-03-31
     * content: goods_id 和 attr_list_id 两项必传一项，建议传goods_id
     *
     * [字段名,类型,是否必传,说明]
     * param: goods_id,int,0,商品ID
     * param: attr_list_id,string,0,商品库存ID
     */
    public function miaosha_goods_view(){
        $this->check($this->_field('goods_id,attr_list_id'));
        if(empty($this->post['goods_id']) && empty($this->post['attr_list_id'])) $this->apiReturn(['code' => 0,'msg' => 'goods_id 和 attr_list_id 两项必传一项！']);

        $res = $this->_miaosha_goods_view($this->post);
        $this->apiReturn($res);
    }

    public function _miaosha_goods_view($param){
        if(empty($param['goods_id'])){
            $param['goods_id'] = M('goods_attr_list')->where(['id' => $param['attr_list_id']])->getField('goods_id');
            if(empty($param['goods_id'])) return ['code' => 0,'msg' => '找不到库存记录！'];
        }

        $goods = M('goods')->where(['id' => $param['goods_id']])->field('officialactivity_join_id')->find();
        if(empty($goods)) return ['code' => 0,'msg' => '找不到商品记录！'];

        $rs = M('officialactivity_join')->cache(false)->where(['id' => $goods['officialactivity_join_id']])->field('id,day,time,price')->find();
        $rs['time_dif']	= strtotime($rs['day'].' '.$rs['time']) - time();

        //秒杀状态
        $rs['is_miaosha']       = 1;    //秒杀商品
        $rs['miaosha_status']   = 1;    //秒杀中
        if($rs['time_dif'] > 0)          $rs['miaosha_status']    = 2;    //倒计时
        elseif($rs['time_dif'] < -86400) $rs['miaosha_status']    = 3;    //秒杀结束
        return ['code' => 1,'data' => $rs];
    }


	/**
     * subject: 运费查看
     * api: /Goods/get_express_price
     * author: lizuheng
     * day: 2017-03-12
     *
     * [字段名,类型,是否必传,说明]
     * param: openid,string,1,用户openid
     * param: tid,string,1,运费模板id(express_tpl_id)
     * param: city,string,1,城市id
     */
    public function get_express_price(){
        $this->check($this->_field('openid,city,tid'));

        $res = $this->_get_express_price($this->post);
        $this->apiReturn($res);
    }

    public function _get_express_price($param){
        $rs=D('ExpressTplRelation')->relation(true)->where(['id' => $param['tid']])->find();

        if($rs['is_free']){
			return ['code' => 1,'data' => ['express'=>0,"ems"=>0]];
		}

        if($rs['is_express']==1){
            $express    =$rs['express_default_first_price'];
        }

        if($rs['is_ems']==1){
            $ems        =$rs['ems_default_first_price'];
        }
        foreach($rs['express_area'] as $val){
            $val['city_ids']    =explode(',', $val['city_ids']);

            if($val['type']==1){
                if(in_array($param['city'],$val['city_ids'])) $express =$val['first_price'];
            }
            elseif($val['type']==2){
                if(in_array($param['city'],$val['city_ids'])) $ems =$val['first_price'];
            }
                
        }
		$res['express'] = $express;
		$res['ems']     = $ems;

        return ['code' => 1,'data' => $res];
    }
}