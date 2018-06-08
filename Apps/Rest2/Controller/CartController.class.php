<?php
/**
 * ----------------------------------------------------------
 * RestFull API V2.0
 * ----------------------------------------------------------
 * 购物车接口
 * ----------------------------------------------------------
 * Author:Lazycat <673090083@qq.com>
 * ----------------------------------------------------------
 * 2017-02-11
 * ----------------------------------------------------------
 */
namespace Rest2\Controller;
use Think\Controller\RestController;
class CartController extends ApiController {
    protected $action_logs = array('add','create_orders','delete','batch_edit_num','orders_multi_view','selected_goods','set_selected');

    /**
     * subject: 购车商品数量
     * api: /Cart/total
     * author: Lazycat
     * day: 2017-02-11
     *
     * [字段名,类型,是否必传,说明]
     * param: openid,int,1,用户openid
     */
    public function total(){
        $this->check('openid',false);

        $res = $this->_total($this->user['id']);
        $this->apiReturn($res);
    }

    public function _total($uid){
        //初始化数据
        $data = [
            'style_num' => 0,   //款式数量
            'goods_num' => 0,   //商品数量
            'weight'    => 0,   //合计重量
            'score'     => 0,   //奖励积分合计
            'money'     => 0,   //合计金额
            'selected'  => 0,   //已选择款式
        ];

        $res = M()->query('select count(*) as style_num,sum(num) as num,sum(total_weight) as weight,sum(score) as score,sum(is_select) as selected,sum(total_price) as money from '.C('DB_PREFIX').'cart where uid='.$uid.' AND is_display = 0');
        foreach($res[0] as $key => $val){
            $res[0][$key]   = is_null($val) ? 0 :$val;
        }

        if($res) return ['code' => 1,'data' => $res[0]];

        return ['code' => 1,'data' => $data];
    }

    /**
     * subject: 根据库存属性获取商品库存ID
     * api: /Cart/attr_list_id
     * author: Lazycat
     * day: 2017-02-18
     *
     * [字段名,类型,是否必传,说明]
     * param: goods_id,int,1,商品ID
     * param: attr_id,string,1,库存属性组合ID
     */
    public function attr_list_id(){
        $this->check('goods_id,attr_id',false);

        $res = $this->_attr_list_id($this->post);
        $this->apiReturn($res);
    }

    public function _attr_list_id($param){
        $res = M('goods_attr_list')->where(['goods_id' => $param['goods_id'],'attr_id' => $param['attr_id']])->field('id,num')->find();
        if($res) return ['code' => 1,'data' => $res];

        return ['code' => 3,'msg' => '找不到库存记录！'];
    }


    /**
     * subject: 加入购物车
     * api: /Cart/add
     * author: Lazycat
     * day: 2017-02-14
     *
     * [字段名,类型,是否必传,说明]
     * param: openid,int,1,用户openid
     * param: attr_list_id,int,1,商品库存ID
     * param: num,int,1,商品数量
     * param: type,int,1,类型，默认为1，1为增加数量，2为减少数量，3设定数量
     * param: atonce,int,0,是否为立即购买
     */
    public function add(){
        $this->check($this->_field('atonce','type,openid,attr_list_id,num'));

        $res = $this->_add($this->post);
        $this->apiReturn($res);
    }

    public function _add($param){
        $attr = M('goods_attr_list')->where(['id' => $param['attr_list_id']])->field('id,seller_id,attr,attr_id,attr_name,goods_id,images,price,weight,num,code,barcode')->find();
        if(empty($attr)) return ['code' => 0,'msg' => '找不到库存记录，该商品属性可能已变更！'];

        if($attr['seller_id'] == $this->user['id']) return ['code' => 0,'msg' => '不能选购自己的商品！'];

        $goods = M('goods')->where(['id' => $attr['goods_id']])->field('status,seller_id,shop_id,score_ratio,express_tpl_id,category_id,officialactivity_join_id,officialactivity_price,is_daigou,daigou_ratio,score_type')->find();
        if($goods['status'] != 1) return ['code' => 0,'msg' => '商品不存在或已下架！'];

        if($attr['num'] < 1) return ['code' => 0,'msg' => '库存不足！'];

        //if($goods['score_ratio'] < 0.25 || $goods['score_ratio'] > 4) return['code' => 0,'msg' => '商品奖励积分比例不符合要求！'];   //商品积分比较为0.25~4倍

        $shop = M('shop')->where(['uid' => $attr['seller_id']])->field('status')->find();
        if($shop['status'] != 1) return['code' => 0,'msg' => '店铺已停止营业！'];

        //取当前购物车是否已添加商品
        $rs = M('cart')->where(array('uid' => $this->user['id'],'attr_list_id' => $param['attr_list_id']))->field('id,num')->find();

        $data = array();
        //检查商品是否参与官方秒杀活动
        if($goods['officialactivity_join_id'] > 0) {
            $officialactivity = D('Common/OfficialactivityJoinUpRelation')->relation(true)->where(['id' => $goods['officialactivity_join_id']])->field('id,activity_id,day,time,price,num')->find();
            $time_dif = strtotime($officialactivity['day'].' '.$officialactivity['time']) - time();
            if($time_dif > 0) return ['code' => 0,'msg' => '秒杀活动还未开始！'];
            if($time_dif < -86400) return ['code' => 0,'msg' => '秒杀活动已结束！'];

            //是否超过限购数量
            $buy_num = M('orders_goods')->where(['uid' => $this->uid,'officialactivity_join_id' => $goods['officialactivity_join_id'],'_string' => 's_id in (select id from '.C('DB_PREFIX').'orders_shop where status in (2,3,4,5,6,11))'])->sum('num');
            if($officialactivity['officialactivity']['max_buy'] < ($buy_num + I('post.num'))) return ['code' => 0,'msg' => '活动商品每个ID限购'.$officialactivity['officialactivity']['max_buy'].'件'];


            $attr['price']                      = $officialactivity['price'];
            $data['is_display']                 = 1;
            $data['officialactivity_id']        = $officialactivity['activity_id'];
            $data['officialactivity_join_id']   = $officialactivity['id'];
        }

        //订购数量
        switch($param['type']){
            case 2:
                $num = $rs['num'] - $param['num'];
                break;
            case 3:
                $num = $param['num'];
                break;
            default:
                $num = $rs['num'] + $param['num'];
                break;
        }
        if($num < 1) return ['code' => 0,'msg' => '订购商品数量有误，请调整订购数量！'];
        if($num > $attr['num']) return ['code' => 0,'msg' => '商品库存不足！'];

        $data['uid']                = $this->user['id'];
        $data['goods_id']           = $attr['goods_id'];
        $data['seller_id']          = $goods['seller_id'];
        $data['shop_id']            = $goods['shop_id'];
        $data['category_id']        = $goods['category_id'];
        $data['attr_list_id']       = $attr['id'];
        $data['attr_id']            = $attr['attr_id'];
        $data['attr_name']          = $attr['attr_name'];
        $data['price']              = $attr['price'];
        $data['num']                = $num;
        $data['weight']             = $attr['weight'];
        $data['total_weight']       = $data['num'] * $data['weight'];
        $data['total_price']        = $data['num'] * $data['price'];
        $data['total_price_edit']   = $data['total_price'];
        $data['score_ratio']        = $goods['score_ratio'];
        //$data['score']              = $data['score_ratio'] * $data['total_price_edit'] * 100;
        $data['score']              = ($data['score_ratio']*$data['total_price_edit']) < 0.01 ? 0 : sprintf("%.2f",($data['score_ratio']*$data['total_price_edit']));
        $data['is_display']         = $data['is_display'] ? $data['is_display'] : 0;
        $data['express_tpl_id']     = $goods['express_tpl_id'];

        //不是现金支付，不赠送乐兑宝
        if($goods['score_type'] != 2){
            $data['score'] = 0;
        }

        $do=D('Common/Cart');
        //已存在购物车
        if($rs){
            $data['id'] = $rs['id'];
            if(!$do->create($data)) return ['code' => 0,'msg' => $do->getError()];

            if(false !== $do->save()){
                if($param['atonce'] == 1 || $data['officialactivity_join_id'] > 0) $this->_atonce_goods($data['id']); //设定立即购买

                $res = $this->_total($this->user['id']);
                return ['code' => 1,'msg' => '操作成功！','data' => $res['data']];
            }else return ['code' => 0,'msg' => '操作失败！'];

        }else{ //未加入购物车-新增
            if(!$do->create($data)) return ['code' => 0,'msg' => $do->getError()];
            if($do->add()){
                $data['id'] = $do->getLastInsID();
                if($param['atonce'] == 1 || $data['officialactivity_join_id'] > 0) $this->_atonce_goods($data['id']); //设定立即购买
                $res = $this->_total($this->user['id']);
                return ['code' => 1,'msg' => '操作成功！','data' => $res['data']];
            }else return ['code' => 0,'msg' => '操作失败！'];
        }
    }

    /**
     * 设置为立即购买商品
     * @param int|array $cart_id 购物车商品ID
     */
    public function _atonce_goods($cart_id){
        $do=M('cart');
        $do->where(array('uid' => $this->user['id']))->setField('is_select',0);

        if($do->where(array('uid' => $this->user['id'],'id'=>array('in',$cart_id)))->setField('is_select',1)){
            $data = $do->where(array('uid' => $this->user['id'],'is_select'=>1))->field('sum(total_price) as total_price,sum(total_weight) as total_weight,count(*) as num')->find();
            return ['code' => 1,'data' => $data];
        }

        return ['code' => 0,'msg' => '设置为立即购买失败！'];
    }

    /**
     * subject: 批量更改购物车商品数量
     * api: /Cart/batch_edit_num
     * author: Lazycat
     * day: 2017-03-16
     *
     * [字段名,类型,是否必传,说明]
     * param: openid,string,1,用户openid
     * param: data,string,1,要修改的购物车商品数据，序列化或json
     */

    /**
     * data 未序列化前格式如 array(array('attr_list_id' => 123,'num' => 5),array('attr_list_id' => 124,'num' => 5));
     */
    public function batch_edit_num(){
        $this->post['data'] = html_entity_decode($this->post['data']);
        $this->check('openid,data');

        $tmp = unserialize($this->post['data']);
        if(empty($tmp)) $tmp    = json_decode($this->post['data'],true);

        foreach($tmp as $key => $val){
            if(empty($val['attr_list_id']) || empty($val['num'])) $this->apiReturn(['code' => 0,'msg' => '第'.$key.'款商品参数不完整（缺少attr_list_id或num）！']);
        }

        $this->post['data']     = $tmp;
        $res = $this->_batch_edit_num($this->post);
        $this->apiReturn($res);
    }

    public function _batch_edit_num($param){
        $n   = 0; //更新失败的记录数量
        $msg = [];
        $k   = 0;
        foreach($param['data'] as $key => $val){
            $k++;
            $val['type'] = 3;
            $res = $this->_add($val);

            if($res['code'] != 1){
                $n++;
                $msg[] = '第'.$k.'款'.$res['msg'];
            }
        }

        $res = $this->_total($this->user['id']);

        if($n > 0){
            return ['code' => 0,'data' => $res['data'],'msg' => implode(';',$msg)];
        }

        return ['code' => 1,'data' => $res['data']];
    }

    /**
     * subject: 删除购物车中商品
     * api: /Cart/delete
     * author: Lazycat
     * day: 2017-02-20
     *
     * [字段名,类型,是否必传,说明]
     * param: openid,int,1,用户openid
     * param: ids,int/string,1,购物车商品，多个请用逗号隔开
     */

    public function delete(){
        $this->check('openid,ids',false);

        $res = $this->_delete($this->post);
        $this->apiReturn($res);
    }

    public function _delete($param){
        if(M('cart')->where(['uid' => $this->user['id'],'id' => ['in',$param['ids']]])->delete()){
            $res = $this->_total($this->user['id']);
            return ['code' => 1,'msg' => '删除成功！','data' => $res['data']];
        }

        return ['code' => 0,'msg' => '删除失败！'];
    }




    /**
     * subject: 购物车商品列表
     * api: /Cart/goods
     * author: Lazycat
     * day: 2017-02-14
     * content: 根据运费模板进行分组
     *
     * [字段名,类型,是否必传,说明]
     * param: openid,int,1,用户openid
     */

    public function goods(){
        $this->check('openid',false);

        $res = $this->_goods($this->post);
        $this->apiReturn($res);
    }

    public function _goods($param){
        $cart_goods = M('cart')->where(['is_display' => 0,'uid' => $this->user['id']])->field('atime,etime,ip',true)->order('id desc')->select();

        if(empty($cart_goods)) goto error;

        $shop_ids   = array();
        $list       = array();
        foreach($cart_goods as $val){
            $list[$val['express_tpl_id']]['goods'][] = $val;
            if(!isset($list[$val['express_tpl_id']]['shop_id'])) $list[$val['express_tpl_id']]['shop_id']       = $val['shop_id'];
            if(!isset($list[$val['express_tpl_id']]['express_tpl_id'])) $list[$val['express_tpl_id']]['express_tpl_id'] = $val['express_tpl_id'];

            $shop_ids[] = $val['shop_id'];
        }

        //店铺资料
        $shop_ids = array_unique($shop_ids);
        $shop_tmp = D('Common/ShopUserRelation')->cache(true)->relation(true)->where(['id' => ['in',$shop_ids]])->field('id,uid,shop_name,shop_logo,mobile,domain,inventory_type')->select();
        $shop = array();
        foreach($shop_tmp as $val){
            $shop[$val['id']] = $val;
        }


        $res['goods_num']       = 0; //商品数量
        $res['style_num']       = 0; //商品款式
        $res['total_weight']    = 0; //合计重量
        $res['total_price']     = 0; //合计金额
        $res['total_score']     = 0; //合计积分
        $res['goods_status']    = 0; //是否存在不正常的商品

        foreach($list as $key => $val){
            //print_r($val['goods']);
            //检查购物车中的商品状态
            $tmp = $this->_check_goods($val['goods']);
            $tmp['express_tpl_id']  = $val['express_tpl_id'];
            $tmp['shop_id']         = $val['shop_id'];

            if(!isset($val['shop'])) {
                $tmp['shop'] = $shop[$val['shop_id']];
                $tmp['shop']['shop_url'] = shop_url($val['shop_id'],$tmp['shop']['domain']);
            }
            $val = $tmp;

            $res['goods_num']       += $val['goods_num'];
            $res['style_num']       += $val['style_num'];
            $res['total_price']     += $val['total_price'];
            $res['total_weight']    += $val['total_weight'];
            $res['total_score']     += $val['total_score'];
            //$res['goods_status']    += $val['goods_status'];

            $res['list'][] = $val;
        }

        $res['shop_num'] =  count($res['list']);

        if($res['shop_num'] > 0){
            return ['code' => 1,'data' => $res];
        }

        error:
        return ['code' => 3];

    }


    /**
     * 检查购物车中商品状态
     */
    public function _check_goods($cart_goods){
        $attr_ids   = arr_id(['plist' => $cart_goods,'field' => 'attr_list_id']);
        $goods_ids  = arr_id(['plist' => $cart_goods,'field' => 'goods_id']);
        $goods_ids  = array_unique($goods_ids);
        $attr_list  = M('goods_attr_list')->where(['id' => ['in',$attr_ids]])->getField('id,seller_id,attr,attr_id,attr_name,images,goods_id,price,weight,num,code,barcode',true);
        $goods_list = M('goods')->where(['id' => ['in',$goods_ids]])->getField('id,status,goods_name,images,score_ratio,express_tpl_id,service_days,category_id,officialactivity_join_id,score_type',true);

        $res['goods_num']       = 0; //商品数量
        $res['style_num']       = 0; //商品款式
        $res['total_weight']    = 0; //合计重量
        $res['total_price']     = 0; //合计金额
        $res['total_score']     = 0; //合计积分
        $res['goods_status']    = 0; //是否存在不正常的商品
        $res['is_miaosha']      = 0; //是否存在秒杀

        //以下字段主要用于优惠券匹配
        $res['goods_ids']       = [];   //所有商品的id集合
        $res['goods_group']     = [];   //某款商品所有属性统计
        $res['category_ids']    = [];   //所有商品的类目ID集全
        $res['category_group']  = [];   //类目商品属性统计

        $list = array();
        foreach($cart_goods as $key => $val){
            $val['status']      = 1;
            $val['status_name'] = '正常';
            if($goods_list[$val['goods_id']]['status'] != 1){
                $val['status']      = 4;
                $val['status_name'] = '商品已下架！';
                $res['goods_status']++;
            }elseif($goods_list[$val['goods_id']]['express_tpl_id'] != $val['express_tpl_id']){
                $val['status']      = 5;
                $val['status_name'] = '商家运费模板已变更！';
                $res['goods_status']++;
            }elseif(!isset($attr_list[$val['attr_list_id']])){
                $val['status']      = 2;
                $val['status_name'] = '库存属性已变更！';
                $res['goods_status']++;
            }elseif($val['num'] > $attr_list[$val['attr_list_id']]['num']){
                $val['status']      = 3;
                $val['status_name'] = '库存不足！';
                $res['goods_status']++;
            }

            //售后天数
            $val['goods_service_days']  = $goods_list[$val['goods_id']]['service_days'];
            $val['score_type']          = $goods_list[$val['goods_id']]['score_type'];

            //价格、重量、奖励积分比较、编码、条型码是否有变更
            if($val['status'] == 1 && $goods_list[$val['goods_id']]['score_ratio'] != $val['score_ratio'] || $goods_list[$val['goods_id']]['category_id'] != $val['category_id'] || $attr_list[$val['attr_list_id']]['price'] != $val['price'] || $attr_list[$val['attr_list_id']]['weight'] != $val['weight']){
                $is_edit    = true;
                //是否为官方秒杀商品
                if($val['officialactivity_join_id'] > 0){
                    $tmp = M('officialactivity_join')->cache(false)->where(['id' => $val['officialactivity_join_id']])->field('day,time')->find();
                    $tmp['time_dif'] = strtotime($tmp['day'].' '.$tmp['time']) - time();
                    if($tmp['time_dif'] > 0 || $tmp['time_dif'] < -86400) { //活动未开始或已过期
                        $val['officialactivity_id']         = 0;
                        $val['officialactivity_join_id']    = 0;
                        $val['is_display']                  = 0;
                    }else {
                        $is_edit = false;
                    }
                }

                if($is_edit == true) {
                    $val['price']       = $attr_list[$val['attr_list_id']]['price'];
                    $val['weight']      = $attr_list[$val['attr_list_id']]['weight'];
                    $val['total_price'] = $val['num'] * $val['price'];
                    $val['total_weight']= $val['num'] * $val['weight'];

                    $val['category_id'] = $goods_list[$val['goods_id']]['category_id'];
                    $val['score_ratio'] = $goods_list[$val['goods_id']]['score_ratio'];
                    $val['score'] = $val['total_price'] * $val['score_ratio'] * 100;

                    M('cart')->where(['id' => $val['id']])->save($val);
                }
            }

            if($val['officialactivity_join_id'] > 0) $res['is_miaosha'] = 1;

            if(!in_array($val['goods_id'],$res['goods_ids'])) $res['goods_ids'][] = $val['goods_id'];
            $res['goods_group'][$val['goods_id']]['total_price'] += $val['total_price'];
            $res['goods_group'][$val['goods_id']]['total_score'] += $val['score'];

            if(!in_array($val['category_id'],$res['category_ids'])) $res['category_ids'][] = $val['category_id'];
            $res['category_group'][$val['category_id']]['total_price'] += $val['total_price'];
            $res['category_group'][$val['category_id']]['total_score'] += $val['score'];

            $res['goods_num']       += $val['num'];
            $res['style_num']++;
            $res['total_price']     += $val['total_price'];
            $res['total_weight']    += $val['total_weight'];
            $res['total_score']     += $val['score'];

            $val['images']          = $attr_list[$val['attr_list_id']]['images'] ? $attr_list[$val['attr_list_id']]['images'] : $goods_list[$val['goods_id']]['images'];
            $val['goods_name']      = $goods_list[$val['goods_id']]['goods_name'];

            $res['goods'][] = $val;
        }

        return $res;
    }


    /**
     * subject: 设置已选中的商品
     * api: /Cart/set_selected
     * author: Lazycat
     * day: 2017-02-14
     * content: 根据运费模板进行分组
     *
     * [字段名,类型,是否必传,说明]
     * param: openid,int,1,用户openid
     * param: ids,string,1,序列化过的购物车商品ID，如果不支持序列化，请格式化成json串
     */

    public function set_selected(){
        $this->post['ids'] = html_entity_decode($this->post['ids']);
        $this->check('openid,ids');

        $param['ids'] = unserialize($this->post['ids']);

        //兼容APP不支持序列化问题
        if($param['ids'] === false) $param['ids'] = json_decode($this->post['ids']);

        $res = $this->_set_selected($param);
        $this->apiReturn($res);
    }

    public function _set_selected($param){
        $do = M('cart');
        $do->where(array('uid' => $this->user['id']))->setField('is_select',0);

        if($do->where(array('uid' => $this->user['id'],'id' => array('in',$param['ids'])))->setField('is_select',1)){
            $data = $do->where(array('uid' => $this->user['id'],'is_select' => 1))->field('sum(total_price) as total_price,sum(total_weight) as total_weight,count(*) as num')->find();

            return ['code' => 1,'data' => $data];
        }

        return ['code' => 0];
    }



    /**
     * subject: 获取已选中要购买的商品
     * api: /Cart/selected_goods
     * author: Lazycat
     * day: 2017-02-14
     * content: 根据运费模板进行分组
     *
     * [字段名,类型,是否必传,说明]
     * param: openid,int,1,用户openid
     */

    public function selected_goods(){
        $this->check('openid',false);

        $res = $this->_selected_goods($this->post);
        $this->apiReturn($res);
    }

    public function _selected_goods($param){
        $cart_goods = M('cart')->where(['uid' => $this->user['id'],'is_select' => 1])->field('atime,etime,ip',true)->order('id desc')->select();
        if(empty($cart_goods)) return ['code' => 3];
        $shop_ids   = array();
        $list       = array();
        foreach($cart_goods as $val){
            $list[$val['express_tpl_id']]['goods'][] = $val;
            if(!isset($list[$val['express_tpl_id']]['shop_id'])) $list[$val['express_tpl_id']]['shop_id']       = $val['shop_id'];
            if(!isset($list[$val['express_tpl_id']]['express_tpl_id'])) $list[$val['express_tpl_id']]['express_tpl_id'] = $val['express_tpl_id'];

            $shop_ids[] = $val['shop_id'];
        }

        //店铺资料
        $shop_ids = array_unique($shop_ids);
        $shop_tmp = D('Common/ShopUserRelation')->cache(true)->relation(true)->where(['id' => ['in',$shop_ids]])->field('id,uid,shop_name,shop_logo,mobile,domain,inventory_type')->select();
        $shop = array();
        foreach($shop_tmp as $val){
            $shop[$val['id']] = $val;
        }

        //默认收货地址
        if (isset($param['address_id']) && $param['address_id'] > 0) {
            $aMap = [
                'id'    =>  $param['address_id'],
                'uid'   =>  $this->user['id'],
                'status'=>  1,
            ];
            $res['address'] = M('shopping_address')->where($aMap)->field('atime,etime,ip',true)->order('is_default desc,id desc')->find();
        }
        if (!$res['address']) $res['address'] = M('shopping_address')->where(['uid' => $this->user['id']])->field('atime,etime,ip',true)->order('is_default desc,id desc')->find();
        if($res['address']){
            $area = $this->cache_table('area');
            $res['address']['province_name']    = $area[$res['address']['province']];
            $res['address']['city_name']        = $area[$res['address']['city']];
            $res['address']['district_name']    = $area[$res['address']['district']];
            $res['address']['town_name']        = $area[$res['address']['town']];
        }


        $res['goods_num']       = 0; //商品数量
        $res['style_num']       = 0; //商品款式
        $res['total_weight']    = 0; //合计重量
        $res['total_price']     = 0; //合计金额
        $res['total_score']     = 0; //合计积分
        $res['pay_price']       = 0; //合计金额，含运费
        $res['goods_status']    = 0; //是否存在不正常的商品
        $res['price_error']     = 0; //是否存在错误金额的订单，单个订单商品最低金额不得小于0.1元

        $coupon_ids = array(); //已获取过的优惠券ID;
        foreach($list as $key => $val){
            //print_r($val['goods']);
            //检查购物车中的商品状态
            $tmp = $this->_check_goods($val['goods']);
            $tmp['express_tpl_id']  = $val['express_tpl_id'];
            $tmp['shop_id']         = $val['shop_id'];

            if(!isset($val['shop'])) {
                $tmp['shop'] = $shop[$val['shop_id']];
                $tmp['shop']['shop_url'] = shop_url($val['shop_id'],$tmp['shop']['domain']);
            }
            $val = $tmp;

            $val['express_type']    = $this->_express_type($val['express_tpl_id']);
            $val['express']         = $val['express_type'][0];
            $val['express_price']   = $res['address'] ? $this->_express_price(['address_id' => $res['address']['id'],'express_tpl_id' => $val['express_tpl_id'],'express_type' => $val['express']['value']])['data']['express_price'] : 0;
            $val['pay_price']       = $val['total_price'] + $val['express_price'];

            $res['goods_num']       += $val['goods_num'];
            $res['style_num']       += $val['style_num'];
            $res['total_price']     += $val['total_price'];
            $res['total_weight']    += $val['total_weight'];
            $res['total_score']     += $val['total_score'];
            $res['goods_status']    += $val['goods_status'];
            $res['price_error']     += $val['total_price'] < 0.1 ? 1 : 0;

            $res['pay_price']       += $val['total_price'] + $val['express_price'];

            //是否有可用优惠券，如有的话分配一张，秒杀时不支持优惠券
            if($val['is_miaosha'] != 1) {
                $tmp = A('Rest2/Coupon')->_get_use_coupon2($val,$coupon_ids);
                $val['coupon'] = null;
                if ($tmp['code'] == 1) {
                    $coupon_ids         = array_merge($tmp['ids'],$coupon_ids ? $coupon_ids : array());
                    $val['coupon']      = $tmp['data'];
                    $val['is_sale']     = 1;    //是否有优惠开关
                }
            }
            $res['list'][] = $val;
        }
        $res['shop_num'] =  count($res['list']);
        if($res['shop_num'] > 0){
            return ['code' => 1,'data' => $res];
        }

        return ['code' => 3];

    }

    /**
     * subject: 计算运费
     * api: /Cart/express_price
     * author: Lazycat
     * day: 2017-02-16
     *
     * [字段名,类型,是否必传,说明]
     * param: openid,int,1,用户openid
     * param: express_tpl_id,int,1,运费模板ID
     * param: address_id,int,1,收货地址ID
     * param: express_type,int,1,配送方式，1快递，2EMS
     */
    public function express_price(){
        $this->check('openid,express_tpl_id,express_type,address_id',false);

        $res = $this->_express_price($this->post);
        $this->apiReturn($res);
    }

    public function _express_price($param){
        //统计
        $do = M('cart');
        $rs = $do->where(array('uid' => $this->user['id'],'is_select' => 1,'express_tpl_id' => $param['express_tpl_id']))->field('sum(num) as num,sum(total_weight) as total_weight')->find();
        if($rs['num']<1) goto free;


        $do = M('shopping_address');
        if(!$city = $do->where(array('id' => $param['address_id'],'uid' => $this->user['id']))->getField('city')) {
            //收货地址不存在
            goto free;
        }

        //取运费模板
        $tpl = M('express_tpl')->where(['id' => $param['express_tpl_id']])->field('atime,etime,ip',true)->find();
        $express_price = 0;
        if($tpl['is_free'] == 1) goto free;  //包邮



        $total = $tpl['unit'] == 1 ? $rs['num'] : $rs['total_weight'];
        //print_r($tpl);echo '<br>';
        //print_r($total);

        if($param['express_type'] == 2){ //EMS默认运费
            $result['first'] = $tpl['ems_default_first_price'];
            if ($total > $tpl['ems_default_first_unit']) {
                $result['next'] = ceil(($total - $tpl['ems_default_first_unit']) / $tpl['ems_default_next_unit']) * $tpl['ems_default_next_price'];
            }

        }else { //快递默认运费
            $result['first'] = $tpl['express_default_first_price'];
            if ($total > $tpl['express_default_first_unit']) {
                $result['next'] = ceil(($total - $tpl['express_default_first_unit']) / $tpl['express_default_next_unit']) * $tpl['express_default_next_price'];
            }
        }

        //print_r($result);

        //取地区自定义模板
        $area = M('express_area')->where(['tpl_id' => $param['express_tpl_id'],'type' => $param['express_type']])->field('atime,etime,ip',true)->select();

        //自定义地区运费
        if($area) {
            foreach ($area as $val) {
                $city_ids = explode(',', $val['city_ids']);
                if (in_array($city, $city_ids)) {
                    //print_r($val);
                    //print_r($total);
                    $result = array();
                    $result['first'] = $val['first_price'];
                    if ($total > $val['first_unit']) {
                        $result['next'] = ceil(($total - $val['first_unit']) / $val['next_unit']) * $val['next_price'];
                    }
                    //print_r($result);
                    break;
                }
            }
        }


        $express_price = $result['first'] + $result['next'];

        //dump($express_price);

        return ['code' => 1,'data' => ['express_price' => $express_price]];

        free:   //包邮或其它情况
        return ['code' => 1,'data' => ['express_price' => 0]];
    }

    /**
     * 根据商品取某商家支持的快递方式
     * @param int $express_tpl_id 运费模板ID
     */
    public function _express_type($express_tpl_id){
        //取所有运费模板
        $do=M('express_tpl');
        $rs=$do->where(['id' => $express_tpl_id])->field('is_express,is_ems')->find();

        if($rs['is_express']==1) {
            $express_type[]=array(
                'name'	=>'快递',
                'value'	=>1
            );
        }
        if($rs['is_ems']==1) {
            $express_type[]=array(
                'name'	=>'EMS',
                'value'	=>2
            );
        }

        //当全部为包邮模板时
        if(empty($express_type)){
            $express_type[]=array(
                'name'	=>'快递',
                'value'	=>1
            );
        }

        return $express_type;
    }


    /**
     * subject: 创建合并订单
     * api: /Cart/create_orders
     * author: Lazycat
     * day: 2017-02-16
     *
     * [字段名,类型,是否必传,说明]
     * param: openid,int,1,用户openid
     * param: terminal,int,1,终端类型，0=PC，1=WAP，2=IOS，3=ANDROID
     * param: address_id,int,1,收货地址ID
     * param: data,string,1,序列化后的订单数据，不支持序列化请转成json，(未序列化前数据格式)：array(array('express_tpl_id'=>1,'express_type'=>1,'remark'=>'发顺丰'),array('express_tpl_id'=>2,'express_type'=>1))
     */
    public function create_orders(){
        $this->post['data'] = html_entity_decode($this->post['data']);
        $this->check('openid,address_id,data,terminal',5);

        //数据格式检查
        $tmp = unserialize($this->post['data']);
        if($tmp === false) $tmp = json_decode($this->post['data'], true);

        $this->post['data'] = $tmp;

        $tmp = [];
        foreach($this->post['data'] as $val){
            if(!in_array('express_tpl_id',array_keys($val)) || !in_array('express_type',array_keys($val)) || empty($val['express_tpl_id']) || empty($val['express_type'])) {
                $this->apiReturn(['code' => 0,'msg' => '提交的数据格式错误！']);
                break;
            }

            $tmp[$val['express_tpl_id']]['express_type']    = $val['express_type'];
            $tmp[$val['express_tpl_id']]['remark']          = $val['remark'];
            $tmp[$val['express_tpl_id']]['coupon_id']       = $val['coupon_id'];
        }

        $this->post['data'] = $tmp;

        $res = $this->_create_orders($this->post);
        $this->apiReturn($res);
    }

    public function _create_orders($param){
        $res = $this->_selected_goods('');
        if($res['code'] != 1) return $res;
        if($res['data']['goods_status'] > 0) return ['code' => 11,'msg' => '订单中有部分商品存在异常！'];
        if($res['data']['price_error'] > 0) return ['code' => 11,'msg' => '单个订单中商品合计金额（不含运费）最低不得少于0.1元！'];

        $do = M('shopping_address');
        if(!$address = $do->where(array('id' => $param['address_id'],'uid' => $this->user['id']))->field('atime,etime,ip',true)->find()) {
            //收货地址不存在
            return ['code' => 0,'msg' => '收货地址不存在！'];
        }

        //重计运费
        $cart = $res['data'];
        $cart['address']    = $address;
        $cart['pay_price']  = 0;
        foreach($cart['list'] as $key => $val){
            $val['express_price']       = $this->_express_price(['address_id' => $address['id'],'express_tpl_id' => $val['express_tpl_id'],'express_type' => $param['data'][$val['express_tpl_id']]['express_type']])['data']['express_price'];
            $val['pay_price']           = $val['total_price'] + $val['express_price'];
            $cart['list'][$key]         = $val;

            $cart['pay_price']  += $val['pay_price'];
        }



        $do->startTrans();

        //创建合并订单
        $o_no                   = $this->create_orderno('OG',$this->user['id']);

        $a_data=array();
        $a_data['uid']          = $this->user['id'];
        $a_data['o_no']         = $o_no;
        $a_data['status']	    = 1;
        $a_data['province']     = $address['province'];
        $a_data['city']         = $address['city'];
        $a_data['district']     = $address['district'];
        $a_data['town']         = $address['town'] ? $address['town'] : 0;
        $a_data['street']       = $address['street'];
        $a_data['linkname']     = $address['linkname'];
        $a_data['mobile']       = $address['mobile'];
        $a_data['tel']          = $address['tel'];
        $a_data['postcode']     = $address['postcode'];
        $a_data['shop_num']     = $cart['shop_num'];
        $a_data['terminal']     = $param['terminal'];  //0=PC,1=WAP,2=IOS,3=ANDROID
        $a_data['style_num']    = $cart['style_num'];
        $a_data['goods_num']    = $cart['goods_num'];
        $a_data['pay_price']    = $cart['pay_price'];
        $a_data['goods_price']  = $cart['total_price'];
        $a_data['score']        = $cart['total_score'];

        if(!$this->sw[] = D('Common/Orders')->create($a_data)){
            $msg = D('Common/Orders')->getError();
            goto error;
        }

        if(!$this->sw[] = $oid = D('Common/Orders')->add()){
            $msg = '创建合并订单失败！';
            goto error;
        }


        $multi_price    = 0;        //合并付款金额
        $multi_score    = 0;        //合并奖励积分
        $discount_price = 0;        //优惠金额

        //创建商家订单
        foreach($cart['list'] as $key => $val){
            $tmp = $param['data'][$val['express_tpl_id']];
            //print_r($val);

            $data=array();
            $data['o_no']           	= $o_no;
            $data['o_id']           	= $oid;
            $data['s_no']           	= $this->create_orderno('DD',$this->user['id']);
            $data['status']				= 1;
            $data['inventory_type'] 	= $val['shop']['inventory_type'];
            $data['shop_id']        	= $val['shop_id'];
            $data['uid']            	= $this->user['id'];
            $data['seller_id']      	= $val['shop']['uid'];
            $data['goods_price']        = $val['total_price'];
            $data['goods_price_edit']   = $data['goods_price'];
            $data['express_type']		= $tmp['express_type'];
            $data['express_price']  	= $val['express_price'];
            $data['express_price_edit'] = $data['express_price'];
            $data['remark']             = $tmp['remark'];
            $data['goods_num']          = $val['goods_num'];
            $data['style_num']          = $val['style_num'];
            $data['weight']             = $val['total_weight'];
            $data['score']              = $val['total_score']; //运费不赠送积分
            $data['terminal']           = $param['terminal'];
            $data['next_time']          = date('Y-m-d H:i:s',time() + C('cfg.orders')['add']);   //过了这个时间未付款将关闭订单
            $data['coupon_price']       = 0;

            //有使用优惠券,支持同时使用多张
            $coupon_batch_ids   = []; //批次ID
            $coupon_code        = [];
            $coupon_type        = 0;    //是否有使用官方优惠券
            if(!empty($tmp['coupon_id'])){
                $coupon_arr = explode(',',$tmp['coupon_id']);
                foreach($coupon_arr as $c){
                    $c = explode('|',$c);   //优惠券格式为: id|price|use_type|goods_id or category_id
                    $data['coupon_id'][] = $c[0];
                }
                $data['coupon_id'] = implode(',',$data['coupon_id']);

                $coupon = M('coupon')->lock(true)->where(['id' => ['in',$data['coupon_id']],'uid' => $this->user['id']])->order('price desc')->getField('id,b_id,uid,code,short_code,shop_id,sday,eday,is_use,price,min_price,type,use_type,goods_ids,category_ids',true);

                //print_r($coupon);
                if(empty($coupon)) {
                    $msg = '第'.($key+1).'个子订单优惠券不存在！';
                    goto error;
                }
                if(count($coupon_arr) != count($coupon)){
                    $msg = '第'.($key+1).'个子订单优惠券有部分优惠券不存在！';
                    goto error;
                }

                foreach($coupon as $c){
                    $coupon_batch_ids[] = $c['b_id'];
                    $coupon_code[]      = $c['short_code'];
                    if($c['type'] == 2) $coupon_type++;

                    if($c['type'] == 1 && $c['shop_id'] != $val['shop_id']) {   //店铺优惠券校验
                        $msg = '第'.($key+1).'个子订单存编号为#'.$c['short_code'].'的优惠券为非法优惠券！';
                        goto error;
                    }
                    if($c['is_use'] != 0){
                        $msg = '第'.($key+1).'个子订单存编号为#'.$c['short_code'].'的优惠券已被使用！';
                        goto error;
                    }
                    if($c['sday'] > date('Y-m-d') || $c['eday'] < date('Y-m-d')){
                        $msg = '第'.($key+1).'个子订单存编号为#'.$c['short_code'].'的优惠券不在使用期限内！';
                        goto error;
                    }

                    if($c['type'] ==1 && $c['use_type'] == 1 && $c['min_price'] > $data['goods_price']){    //店铺优惠券校验
                        $msg = '第'.($key+1).'个子订单存编号为#'.$c['short_code'].'的优惠券不符合使用要求，须满￥'.$c['min_price'].'才可使用！！';
                        goto error;
                    }

                    if($c['type'] == 2 && ($c['use_type'] == 1 || $c['use_type'] == 2) && $c['min_price'] > $data['goods_price']){  //官方优惠券校验
                        $msg = '第'.($key+1).'个子订单存编号为#'.$c['short_code'].'的优惠券不符合使用要求，须满￥'.$c['min_price'].'才可使用！！';
                        goto error;
                    }

                    //当优惠券为指定商品使用时
                    if($c['use_type'] == 3){
                        $is_status = 0;
                        $c['goods_ids'] = explode(',',$c['goods_ids']);

                        foreach($val['goods_ids'] as $g){
                            if(in_array($g,$c['goods_ids'])){
                                if($val['goods_group'][$g]['total_price'] >= $c['min_price']){
                                    $is_status++;
                                }
                            }
                        }

                        if($is_status == 0){
                            $msg = '第'.($key+1).'个子订单存编号为#'.$c['short_code'].'的优惠券不符合使用要求！';
                            goto error;
                        }
                    }

                    //当优惠券为指定类目使用时
                    if($c['use_type'] == 4){
                        $is_status = 0;
                        $c['category_ids'] = explode(',',$c['category_ids']);
                        foreach($val['category_ids'] as $g){
                            if(in_array($g,$c['category_ids'])){
                                if($val['category_group'][$g]['total_price'] >= $c['min_price']){
                                    $is_status++;
                                }
                            }
                        }

                        if($is_status == 0){
                            $msg = '第'.($key+1).'个子订单存编号为#'.$c['short_code'].'的优惠券不符合使用要求！';
                            goto error;
                        }
                    }


                    $data['coupon_price'] += $c['price'];
                }

                $data['coupon_code'] = implode(',',$coupon_code);
            }

            $data['discount_price']         = $data['coupon_price'];    //优惠金额
            $data['goods_price_edit']       -= $data['discount_price'];
            $data['total_price']            = $data['goods_price_edit'] + $data['express_price_edit'];
            $data['pay_price']              = $data['total_price'];
            $data['money']                  = $data['pay_price'];

            $multi_price                += $data['pay_price'];
            $discount_price             += $data['discount_price'];

            if($data['pay_price'] < 0.1) {  //最小金额验证
                $msg = '实付金额有异常！';
                goto error;
            }
            //单笔订单最大金额验证
            if(C('cfg.orders')['orders_max_price'] > 0 && $data['pay_price'] > C('cfg.orders')['orders_max_price']){
                $msg = '单笔订单最大金额不得超过￥'.C('cfg.orders')['orders_max_price'];
                goto error;
            }

            if(!$this->sw[] = D('Common/OrdersShop')->create($data)){
                $msg=D('Common/OrdersShop')->getError();
                goto error;
            }

            if(!$this->sw[] = D('Common/OrdersShop')->add()){
                $msg = '创建订单失败！';
                goto error;
            }
            $s_id=D('Common/OrdersShop')->getLastInsID();

            if(!empty($tmp['coupon_id'])){
                if(!$this->sw[] = M('coupon')->where(['id' => ['in',$tmp['coupon_id']]])->save(['is_use' => 1,'use_time' => date('Y-m-d H:i:s'),'orders_id' => $s_id,'orders_no' => $data['s_no']])){
                    $msg = '更新优惠券状态失败！';
                    goto error;
                }

                if(!$this->sw[] = M('coupon_batch')->where(['id' => ['in',$coupon_batch_ids]])->setInc('use_num',1)){
                    $msg = '更新优惠券使用数量！';
                    goto error;
                }
            }

            //订单logs
            $logs_data=array(
                'o_id'		=> $oid,
                'o_no'		=> $o_no,
                's_id'		=> $s_id,
                's_no'		=> $data['s_no'],
                'status'	=> 1,
                'remark'	=> '创建订单'
            );

            if(!$this->sw[] = D('Common/OrdersLogs')->create($logs_data)){
                $msg=D('Common/OrdersLogs')->getError();
                goto error;
            }
            if(!$this->sw[] = D('Common/OrdersLogs')->add()){
                $msg = '写入订单日志失败！';
                goto error;
            }


            //商品移至已订购的表中
            $score          = 0;
            $items_price    = 0;
            $score_type     = '';
            $goods_type     = 1;
            $supplier_id     = 0;

            foreach($val['goods'] as $k => $v){

                unset($v['id']);
                $v['o_no']              = $o_no;
                $v['o_id']              = $oid;
                $v['s_id']              = $s_id;
                $v['s_no']              = $data['s_no'];
                $v['total_price_edit']  = $v['total_price'];
                $v['is_can_refund']     = $coupon_type > 0 ? 0 : 1; //使用官方优惠券时订单不允许退款
				
				//获取商品供货商id
				$v['supplier_id'] = M('goods')->cache(true)->where(['id'=>$v['goods_id']])->getField('supplier_id');
				$supplier_id             = $v['supplier_id'];
				
				//商品成本价
				$v['price_purchase'] = M('goods_attr_list')->where(['id'=>$v['attr_list_id']])->getField('price_purchase');
				
				//商品加价倍数（金积分、银积分）
				if($v['score_type'] == 1 || $v['score_type'] == 4){
					$v['multiply'] = $v['total_price']/$v['price_purchase'];
				}else{
					$v['multiply'] = 0;
				}
				
				
                $score_type             = $v['score_type'];
                $goods_type             = $v['goods_type'] = 1;
				

                //属于购票，不能退款
                if(check_ticket($v['category_id'])) {
                    $v['is_can_refund'] = 0;
                    $goods_type = $v['goods_type'] = 2;
                }

                //存在优惠时，需要将优惠的金额分摊到订购的商品中去，因为商品退款需要退相应的积分
                if($data['discount_price'] > 0){
                    $item_point             = $v['total_price'] / $data['goods_price'];             //在订单中占比
                    $item_price             = intval($data['discount_price'] * $item_point * 100) / 100;    //不进行四舍五入，最后多出的分给最后一个

                    if($k == (count($val['goods'])-1) && ($data['discount_price'] - $items_price) != $item_price){  //由于没有四舍五入，最后一个有可能多出
                        $item_price         = $data['discount_price'] - $items_price;
                    }
                    if($item_price < 0)     $item_price = 0;
                    $items_price            += $item_price;

                    $v['total_price_edit']  -= $item_price;
                    $v['score']             = $v['total_price_edit'] * $v['score_ratio'] * 100;
                    $score += $v['score'];
                }
                //writelog($v);
                $val['goods'][$k] = $v;
            }

            /**
             * 2017-03-23 ERP小组通知要求订单赠送积分不得小于50积分
             */
            if($score > 0 && $score < C('cfg.orders')['min_score']){
                $msg = '创建订单失败，第'.$key.'个商家订单奖励积分小于'.C('cfg.orders')['min_score'].'分！';
                goto error;
            }

            if(!$this->sw[] = M('orders_goods')->addAll($val['goods'])){
                $msg = '购物车商品移入订单失败！';
                goto error;
            }

			//单个购买商品，商品供货商id直接等于订单供货商id
			$sData['supplier_id'] = $supplier_id;
			
			
            $sData['score_type']    =   $score_type;
            if($data['discount_price'] > 0) $data['score'] = $sData['score_type'] = $score;
            $sData['goods_type'] = $goods_type;
			
            if(!$this->sw[] = M('orders_shop')->where(['id' => $s_id])->save($sData)){
                $msg = '更新订单积分失败！';
                goto error;
            }
            $multi_score += $data['score'];
        }

        //如果存在优惠金额须更新合并付款订单
        if($discount_price > 0){
            if(!$this->sw[] = M('orders')->where(['id' => $oid])->save(['discount_price' => $discount_price,'pay_price' => $multi_price,'score' => $multi_score])){
                $msg = '更新合并订单金额失败！';
                goto error;
            }
        }


        //删除购物车中商品
        if(!$this->sw[] = M('cart')->where(array('is_select' => 1,'uid'=> $this->user['id']))->delete()){
            //清除购物车中商品失败！
            $msg = '无法清除购物车已被选购的商品！';
            goto error;
        }

        //print_r($cart);

        //提交事务
        $do->commit();
        //$do->rollback();
        return ['code' => 1,'data' => ['o_id' => $oid,'o_no' => $o_no]];

        error:
        $do->rollback();
        return ['code' => 0,'msg' => $msg ? $msg :'创建订单失败！'];
    }


    /**
     * subject: 获取合并订单信息
     * api: /Cart/create_orders
     * author: Lazycat
     * day: 2017-02-17
     *
     * [字段名,类型,是否必传,说明]
     * param: openid,int,1,用户openid
     * param: o_no,string,1,合并订单号
     */
    public function orders_multi_view(){
        $this->check('openid,o_no',false);

        $res = $this->_orders_multi_view($this->post);

        $this->apiReturn($res);
    }

    public function _orders_multi_view($param){
        $rs = M('orders')->where(['o_no' => $param['o_no']])->field('atime,etime,ip',true)->find();
        if($rs){
            $area = $this->cache_table('area');
            $rs['province_name']    = $area[$rs['province']];
            $rs['city_name']        = $area[$rs['city']];
            $rs['district_name']    = $area[$rs['district']];
            $rs['town_name']        = $area[$rs['town']];
            $rs['is_tangbao_pay']   = 1;
            $rs['score_type']       = M('orders_goods')->cache(true)->where(['o_id' => $rs['id']])->getField('score_type');

            //判断订单中是否有使用官方优惠券，如果有的话就不支持唐宝支付
            $coupon = M('orders_shop')->where(['o_no' => $param['o_no']])->getField('coupon_id',true);
            if($coupon){
                $ids = implode(',',$coupon);
                $count = M('coupon')->where(['type' => 2,'id' => ['in',$ids]])->count();
                if($count > 0) $rs['is_tangbao_pay'] = 0;
            }

            return ['code' => 1,'data' => $rs];
        }

        return ['code' => 3];
    }

}