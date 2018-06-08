<?php
/**
 * ----------------------------------------------------------
 * RestFull API V2.0
 * ----------------------------------------------------------
 * 优惠券接口
 * ----------------------------------------------------------
 * Author:Lazycat <673090083@qq.com>
 * ----------------------------------------------------------
 * 2017-03-13
 * ----------------------------------------------------------
 */
namespace Rest2\Controller;
use Think\Controller\RestController;
class CouponController extends ApiController {
    protected $action_logs      = array('get_coupon');
    protected $coupon_type      = [1 => '店铺优惠券',2 => '乐兑优惠券'];
    protected $coupon_use_type  = [1 => '全网通用',2 => '指定店铺使用',3 => '指定商品使用',4 => '指定类目使用'];
    protected $coupon_use_type2 = [1 => '全店通用',3 => '指定商品使用'];
    protected $coupon_use       = ['未使用','已使用'];

    /**
     * subject: 我的优惠券
     * api: /Coupon/my_coupon
     * author: Lazycat
     * day: 2017-03-14
     * content: is_use和is_expire两个只能传一个
     *
     * [字段名,类型,是否必传,说明]
     * param: openid,string,1,用户openid
     * param: pagesize,int,0,分页数量
     * param: p,int,0,获取第p页数据
     * param: is_expire,int,0,1为过期优惠券
     * param: is_use,int,0,1为可用优惠券
     */
    public function my_coupon(){
        $this->check($this->_field('pagesize,p,is_expire,is_use','openid'),false);

        $res = $this->_my_coupon($this->post);
        $this->apiReturn($res);
    }

    public function _my_coupon($param){
        $pagesize = $param['pagesize'] ? $param['pagesize'] : 15;

        $map['uid']             = $this->user['id'];
        $map['status']          = 1;
        $map['is_use']          = 0;
        if($param['is_use'] == 1){
            //$map['sday']        = ['elt',date('Y-m-d')];
            $map['eday']        = ['egt',date('Y-m-d')];
        }elseif($param['is_expire'] ==1){
            $map['eday']        = ['lt',date('Y-m-d')];
        }

        $list = pagelist(array(
            'table'     		=> 'Common/CouponRelation',
            'do'        		=> 'D',
            'map'       		=> $map,
            'fields'            => 'count(*) as num,id,price,min_price,sday,eday,shop_id,type,use_type',
            'order'     		=> 'id desc',
            'group'             => 'shop_id,price,use_type',
            'pagesize'  		=> $pagesize,
            'relation'  		=> true,
            'p'                 => $param['p'],
        ));

        if($list){
            foreach($list['list'] as &$val){
                if($val['shop_id']) $val['shop_url'] = shop_url($val['shop_id'],$val['domain']);
                $val['type_name']   = $this->coupon_type[$val['type']];
                if($val['type'] == 1) $val['use_type_name'] = $this->coupon_use_type2[$val['use_type']];
                else $val['use_type_name'] = $this->coupon_use_type[$val['use_type']];
            }
            return ['code' => 1,'data' => $list];
        }

        return ['code' => 3];
    }


    /**
     * subject: 取某店铺可领取优惠券批次
     * api: /Coupon/batch
     * author: Lazycat
     * day: 2017-03-13
     *
     * [字段名,类型,是否必传,说明]
     * param: shop_id,int,1,店铺ID
     */
    public function batch(){
        $this->check('shop_id',false);

        $res = $this->_batch($this->post['shop_id']);
        $this->apiReturn($res);
    }

    public function _batch($shop_id){
        $list = M('coupon_batch')->where(['status' => 1,'shop_id' => $shop_id,'sday' => ['elt',date('Y-m-d')],'eday' => ['egt',date('Y-m-d')],'_string' => 'num>=get_num'])->field('id,b_no,shop_id,uid,price,num,max_num,get_num,use_num,sday,eday,min_price,status,(num-get_num) as can_num')->order('price asc')->select();

        if($list){
            return ['code' => 1,'data' => $list];
        }

        return ['code' => 3];
    }


    /**
     * subject: 领取优惠券
     * api: /Coupon/get_coupon
     * author: Lazycat
     * day: 2017-03-13
     *
     * [字段名,类型,是否必传,说明]
     * param: openid,string,1,用户openid
     * param: batch_id,int,1,优惠券批次ID
     */
    public function get_coupon(){
        $this->check('openid,batch_id');

        $res = $this->_get_coupon($this->post);
        $this->apiReturn($res);
    }

    public function _get_coupon($param){
        $rs = M('coupon_batch')->lock(true)->where(['id' => $param['batch_id']])->field('id,shop_id,price,sday,eday,num,max_num,get_num,use_num,status,min_price,(num-get_num) as can_num,channel,type,use_type,goods_ids,shop_ids,category_ids')->find();

        if($rs['status'] != 1) return ['code' => 0,'msg' => '已暂停领取！'];
        if($rs['sday'] > date('Y-m-d') || $rs['eday'] < date('Y-m-d')) return ['code' =>0,'msg' => '优惠券不在有效领取日期范围，无法领取！'];
        if($rs['can_num'] < 1 && $rs['num'] > 0) return ['code' => 0,'msg' => '优惠券已被领取完！'];

        $count = M('coupon')->where(['uid' => $this->user['id'],'b_id' => $rs['id']])->count();
        if($count >= $rs['max_num']) return ['code' => 0,'msg' => '每人最多只能领取'.$rs['max_num'].'张！'];

        $do = M();
        $do->startTrans();

        $data = [
            'ip'            => get_client_ip(),
            'b_id'          => $rs['id'],
            'price'         => $rs['price'],
            'code'          => md5($this->create_orderno() .'-'. $this->user['id'] .'-'. $rs['id']),
            'shop_id'       => $rs['shop_id'],
            'sday'          => $rs['sday'],
            'eday'          => $rs['eday'],
            'min_price'     => $rs['min_price'],
            'uid'           => $this->user['id'],
            'channel'       => $rs['channel'],
            'type'          => $rs['type'],
            'use_type'      => $rs['use_type'],
            'goods_ids'     => $rs['goods_ids'],
            'shop_ids'      => $rs['shop_ids'],
            'category_ids'  => $rs['category_ids'],
        ];
        $data['short_code']   = shortUrl($data['code']);


        if(!$this->sw[] = $data['id'] = M('coupon')->add($data)){
            $msg = '创建优惠券失败！';
            goto error;
        }

        if(!$this->sw[] = M('coupon_batch')->where(['id' => $param['batch_id']])->setInc('get_num',1)) {
            $msg = '更新优惠券领取数量失败！';
            goto error;
        }

        $do->commit();
        return ['code' => 1,'data' => $data,'msg' => '领取成功！'];

        error:
        $do->rollback();
        return ['code' => 0,'msg' => '领取优惠券失败！'];
    }

    /**
     * subject: 某用户可用优惠券
     * api: /Coupon/user_coupon
     * author: Lazycat
     * day: 2017-03-13
     *
     * [字段名,类型,是否必传,说明]
     * param: openid,string,1,用户openid
     * param: min_price,float,0,使用最低限额
     * param: shop_id,int,1,店铺ID
     */
    public function user_coupon(){
        $this->check($this->_field('min_price','openid,shop_id'),false);

        $res = $this->_user_coupon($this->post);
        $this->apiReturn($res);
    }

    public function _user_coupon($param){
        $map['uid']     = $this->user['id'];
        $map['shop_id'] = $param['shop_id'];
        if($param['min_price']) $map['min_price']       = ['elt',$param['min_price']];

        $map['is_use']  = 0;
        $map['sday']    = ['elt',date('Y-m-d')];
        $map['eday']    = ['egt',date('Y-m-d')];

        $list = M('coupon')->where($map)->field('count(*) as num,price,min_price,code,short_code,shop_id,sday,eday,uid')->group('price')->order('price asc')->select();

        if($list){
            return ['code' => 1,'data' => $list];
        }

        return ['code' => 3];
    }

    /**
     * 分配一张符合使用条件的优惠券
     * Create by lazycat
     * 2017-03-13
     * @param $param['uid']         int     用户ID
     * @param $param['shop_id']     int     店铺ID
     * @param $param['min_price']   float   最低限额
     * @param $param['not_ids']     string|array 不包含的优惠券ID
     */
    public function _get_use_coupon($param){
        $map['uid']         = $param['uid'];
        $map['status']      = 1;
        $map['shop_id']     = $param['shop_id'];
        $map['min_price']   = ['elt',$param['min_price']];
        $map['is_use']      = 0;
        $map['sday']        = ['elt',date('Y-m-d')];
        $map['eday']        = ['egt',date('Y-m-d')];

        if(!empty($param['not_ids'])) $map['id'] = ['not in',$param['not_ids']];

        $rs = M('coupon')->where($map)->field('id,code,short_code,price')->order('price desc,min_price desc')->find();

        if($rs){
            return ['code' => 1,'data' => $rs];
        }

        return ['code' => 3];
    }

    /**
     * subject: 获取优惠券批次(新，已支持官方优惠券)
     * api: /Coupon/get_batch
     * author: Lazycat
     * day: 2017-04-21
     *
     * [字段名,类型,是否必传,说明]
     * param: shop_id,int,0,店铺ID
     * param: goods_id,int,0,商品ID
     * param: category_id,int,0,类目ID
     */
    public function get_batch(){
        $this->check($this->_field('shop_id,goods_id,category_id'),false);

        $res = $this->_get_batch($this->post);
        $this->apiReturn($res);
    }

    public function _get_batch($param){
        //官方优惠券
        $map    = [
            'sday'      => ['elt',date('Y-m-d')],
            'eday'      => ['egt',date('Y-m-d')],
            'status'    => 1,
            'channel'   => 1,   //领取通道,1=详情页,2=抽奖
            'type'      => 2,   //类型,1=店铺优惠券,2=官方优惠券,3=第三方优惠券
            'face_type' => 1,   //1=优惠券,2=现金券
            'use_type'  => 1,   //使用场景1=通用型,2=指定店铺,3=指定商品,4=指定类目
            '_string'   => 'num >= get_num',
        ];

        $result['official'] = [];
        $result['shop']     = [];

        //通用型官方优惠券
        $map_c1 = $map;
        $c1 = M('coupon_batch')->where($map_c1)->field('id,b_no,shop_id,uid,price,num,max_num,get_num,use_num,sday,eday,min_price,status,type,use_type,(num-get_num) as can_num')->order('price asc')->select();

        if($c1){
            /*
            $result['official'][] = [
                'title'     => '全网通用',
                'use_type'  => 1,
                'list'      => $c1,
            ];
            */
            foreach($c1 as &$val){
                $val['type_name']       = $this->coupon_type[$val['type']];
                $val['use_type_name']   = $this->coupon_use_type[$val['use_type']];
            }
            $result['official'] = array_merge($result['official'],$c1);
        }

        //指定店铺官方优惠券
        if($param['shop_id']){
            $map_c2             = $map;
            $map_c2['use_type'] = 2;
            $map_c2['_string']  .= ' and find_in_set('. $param['shop_id'] .',shop_ids)';
            $c2 = M('coupon_batch')->where($map_c2)->field('id,b_no,shop_id,uid,price,num,max_num,get_num,use_num,sday,eday,min_price,status,type,use_type,(num-get_num) as can_num')->order('price asc')->select();

            if($c2){
                /*
                $result['official'][] = [
                    'title'     => '指定店铺使用',
                    'use_type'  => 2,
                    'list'      => $c2,
                ];
                */
                foreach($c2 as &$val){
                    $val['type_name']       = $this->coupon_type[$val['type']];
                    $val['use_type_name']   = $this->coupon_use_type[$val['use_type']];
                }
                $result['official'] = array_merge($result['official'],$c2);
            }

            //店铺优惠券
            $map_s1             = $map;
            $map_s1['type']     = 1;
            $map_s1['shop_id']  = $param['shop_id'];
            $s1 = M('coupon_batch')->where($map_s1)->field('id,b_no,shop_id,uid,price,num,max_num,get_num,use_num,sday,eday,min_price,status,type,use_type,(num-get_num) as can_num')->order('price asc')->select();
            if($s1){
                /*
                $result['shop'][] = [
                    'title'     => '全店通用',
                    'use_type'  => 1,
                    'list'      => $s1,
                ];
                */
                foreach($s1 as &$val){
                    $val['type_name']       = $this->coupon_type[$val['type']];
                    $val['use_type_name']   = $this->coupon_use_type2[$val['use_type']];
                }
                $result['shop'] = array_merge($result['shop'],$s1);
            }
        }

        //指定商品官方优惠券
        if($param['goods_id']){
            $map_c3             = $map;
            $map_c3['use_type'] = 3;
            $map_c3['_string']  .= ' and find_in_set('. $param['goods_id'] .',goods_ids)';
            $c3 = M('coupon_batch')->where($map_c3)->field('id,b_no,shop_id,uid,price,num,max_num,get_num,use_num,sday,eday,min_price,status,type,use_type,(num-get_num) as can_num')->order('price asc')->select();

            if($c3){
                /*
                $result['official'][] = [
                    'title'     => '指定商品使用',
                    'use_type'  => 3,
                    'list'      => $c3,
                ];
                */
                foreach($c3 as &$val){
                    $val['type_name']       = $this->coupon_type[$val['type']];
                    $val['use_type_name']   = $this->coupon_use_type[$val['use_type']];
                }
                $result['official'] = array_merge($result['official'],$c3);
            }

            //店铺优惠券
            $map_s2             = $map;
            $map_s2['type']     = 1;
            $map_s2['use_type'] = 3;
            $map_s2['shop_id']  = $param['shop_id'] ? $param['shop_id'] : M('goods')->where(['id' => $param['goods_id']])->getField('shop_id');
            $map_s2['_string']  .= ' and find_in_set('. $param['goods_id'] .',goods_ids)';
            $s2 = M('coupon_batch')->where($map_s2)->field('id,b_no,shop_id,uid,price,num,max_num,get_num,use_num,sday,eday,min_price,status,type,use_type,(num-get_num) as can_num')->order('price asc')->select();
            if($s2){
                /*
                $result['shop'][] = [
                    'title'     => '指定商品使用',
                    'use_type'  => 3,
                    'list'      => $s2,
                ];
                */
                foreach($s2 as &$val){
                    $val['type_name']       = $this->coupon_type[$val['type']];
                    $val['use_type_name']   = $this->coupon_use_type2[$val['use_type']];
                }
                $result['shop'] = array_merge($result['shop'],$s2);

            }
        }

        //指定类目官方优惠券
        if($param['category_id']){
            $map_c4             = $map;
            $map_c4['use_type'] = 4;
            $map_c4['_string']  .= ' and find_in_set('. $param['category_id'] .',category_ids)';
            $c4 = M('coupon_batch')->where($map_c4)->field('id,b_no,shop_id,uid,price,num,max_num,get_num,use_num,sday,eday,min_price,status,type,use_type,(num-get_num) as can_num')->order('price asc')->select();

            if($c4){
                /*
                $result['official'][] = [
                    'title'     => '指定类目使用',
                    'use_type'  => 4,
                    'list'      => $c4,
                ];
                */
                foreach($c4 as &$val){
                    $val['type_name']       = $this->coupon_type[$val['type']];
                    $val['use_type_name']   = $this->coupon_use_type[$val['use_type']];
                }
                $result['official'] = array_merge($result['official'],$c4);
            }
        }

        if(empty($result['official']) && empty($result['shop'])) return ['code' => 3,'msg' => '暂无优惠券！'];
        return ['code' => 1,'data' => $result];
    }

    /**
     * 获取可用优惠券
     * Create by lazycat
     * 2017-04-22
     * @return array
     */
    public function _get_use_coupon2($param,$not_ids=array()){
        //print_r($param);

        $map['uid']         = $this->user['id'];
        $map['status']      = 1;
        $map['is_use']      = 0;
        $map['sday']        = ['elt',date('Y-m-d')];
        $map['eday']        = ['egt',date('Y-m-d')];
        if(!empty($not_ids)) $map['id'] = ['not in',$not_ids];

        $result     = [];
        $max_price  = 0;
        //获取通用型店铺优惠券及官方优惠
        $map1               = $map;
        $map1['min_price']  = ['elt',$param['total_price']];
        $map1['_string']    = '(type=1 and shop_id='.$param['shop_id'].') or (type=2 and (use_type=1 or (use_type=2 and find_in_set ('.$param['shop_id'].',shop_ids))))';
        $rs = M('coupon')->where($map1)->field('id,code,short_code,price,min_price,type,use_type')->order('price desc,min_price desc')->find();
        //print_r($rs);
        if($rs) {
            $rs['title']    = $this->coupon_type[$rs['type']];
            $result[]       = $rs;
            $max_price      = $rs['price'];
            $not_ids[]      = $rs['id'];
        }

        //获取指定商品优惠券，多个商品符合条件时可以同时使用多张优惠券
        $goods_price = 0;
        $tmp         = [];
        foreach($param['goods_ids'] as $val){
            $map2               = $map;
            $map2['min_price']  = ['elt',$param['goods_group'][$val]['total_price']];
            $map2['_string']    = '(type=1 and find_in_set ('.$val.',goods_ids)) or (type=2 and use_type=3 and find_in_set ('.$val.',goods_ids))';
            if(!empty($not_ids)) $map2['id'] = ['not in',$not_ids];
            //print_r($map2);
            $rs = M('coupon')->where($map2)->field('id,code,short_code,price,min_price,type,use_type')->order('price desc,min_price desc')->find();
            //print_r($rs);
            if($rs){
                $rs['goods_id'] = $val;
                $rs['total']    = $param['goods_group'][$val];
                $rs['title']    = $this->coupon_type[$rs['type']];
                $not_ids[]      = $rs['id'];
                $goods_price    += $rs['price'];
                $tmp[]          = $rs;
            }
        }

        if($max_price < $goods_price){
            $result     = $tmp;
            $max_price  = $goods_price;
        }


        //获取指定类目的优惠券，多个商品符合条件时可以同时使用多张优惠券
        $category_price = 0;
        $tmp            = [];
        foreach($param['category_ids'] as $val){
            $map3               = $map;
            $map3['min_price']  = ['elt',$param['category_group'][$val]['total_price']];
            $map3['_string']    = 'type=2 and use_type=4 and find_in_set ('.$val.',category_ids)';
            if(!empty($not_ids)) $map3['id'] = ['not in',$not_ids];
            //print_r($map3);
            $rs = M('coupon')->where($map3)->field('id,code,short_code,price,min_price,type,use_type')->order('price desc,min_price desc')->find();
            //print_r($rs);
            if($rs){
                $rs['category_id']  = $val;
                $rs['total']        = $param['category_group'][$val];
                $rs['title']        = $this->coupon_type[$rs['type']];
                $not_ids[]          = $rs['id'];
                $category_price     += $rs['price'];
                $tmp[]              = $rs;
            }
        }

        if($max_price < $category_price){
            $result     = $tmp;
        }

        //print_r($result);

        if($result){
            return ['code' => 1,'data' => $result,'ids' => arr_id(['plist' => $result])];
        }

        return ['code' => 3];
    }

    /**
     * subject:优惠券详情
     * api: /Coupon/view
     * author: Lazycat
     * day: 2017-04-22
     *
     * [字段名,类型,是否必传,说明]
     * param: openid,int,1,用户openid
     * param: id,int,1,优惠券ID
     */
    public function view(){
        $this->check('openid,id',false);

        $res = $this->_view($this->post);
        $this->apiReturn($res);
    }

    public function _view($param){
        $rs = D('Common/CouponRelation')->relation(true)->where(['id' => $param['id'],'uid' => $this->user['id']])->field('etime,ip,get_time',true)->find();
        if($rs){
            $rs['type_name']    = $this->coupon_type[$rs['type']];
            $rs['use_name']     = $this->coupon_use[$rs['is_use']];
            if($rs['type'] == 1) $rs['use_type_name'] = $this->coupon_use_type2[$rs['use_type']];
            else $rs['use_type_name'] = $this->coupon_use_type[$rs['use_type']];

            if($rs['use_type'] == 2 && $rs['shop_ids']){   //指定店铺
                $rs['shop_list'] = D('Common/ShopRelation')->relation(true)->cache(true)->where(['id' => ['in',$rs['shop_ids']],'status' => 1])->field('id,uid,shop_name,shop_logo,domain,goods_num,fraction_speed,fraction_service,fraction_desc,fraction,about')->select();
                foreach($rs['shop_list'] as &$val){
                    $val['shop_url']    = shop_url($val['id'],$val['domain']);
                    $val['about']       = html_entity_decode($val['about']);
                }
            }

            if($rs['use_type'] == 3 && $rs['goods_ids']){   //指定商品
                $rs['goods_list'] = D('Common/GoodsRelation')->relation(true)->cache(true)->relationWhere('goods_attr_list','num>0')->relationOrder('goods_attr_list','price asc')->relationField('goods_attr_list','id,attr_name,price,price_market,sale_num,view')->relationLimit('goods_attr_list',1)->where(['id' => ['in',$rs['goods_ids']],'status' => 1,'num' => ['gt',0]])->field('id,goods_name,images,price,sale_num,shop_id,seller_id,score_ratio')->select();
            }

            if($rs['use_type'] == 4 && $rs['category_ids']){   //指定类目
                $rs['category'] = M('goods_category')->cache(true)->where(['id' => ['in',$rs['category_ids']],'status' => 1])->field('id,category_name')->select();
            }

            return ['code' => 1,'data' => $rs];
        }

        return ['code' => 3,'msg' => '优惠券不存在！'];
    }



}