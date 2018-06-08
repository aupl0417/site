<?php
/**
 * ----------------------------------------------------------
 * RestFull API V2.0
 * ----------------------------------------------------------
 * 商家商品管理
 * ----------------------------------------------------------
 * Author:liangfeng 
 * ----------------------------------------------------------
 * 2017-03-14
 * ----------------------------------------------------------
 */
namespace Rest2\Controller;
use Think\Controller;
class SellerGoodsController extends ApiController {
	protected $action_logs = array('');

	/**
     * subject: 卖家商品列表
     * api: /SellerGoods/goods
     * author: liangfeng
     * day: 2017-03-14
     * content: 商品状态：1=>'出售中' 2=>'待上架'
     *
     * [字段名,类型,是否必传,说明]
     * param: openid,int,1,用户openid
     * param: pagesize,int,0,每页显示数量
     * param: p,int,0,第p页
     * param: status,int,0,商品状态
     * param: is_best,int,0,是否推荐 0.否 1.是
     * param: q,string,0,商品名称
     * param: code,string,0,商品编号
     * param: s_price,float,0,最低商品价格
     * param: e_price,float,0,最高商品价格
	 * param: s_sale_num,int,0,最低商品销量
     * param: e_sale_num,int,0,最高商品销量
     * param: goods_id,string,0,指定商品id
     */
	public function goods(){
        $this->check($this->_field('q,p,pagesize','openid'),false);
        $res = $this->_goods($this->post);
        $this->apiReturn($res);
    }
	public function _goods($param){
		$map['seller_id']     = $this->user['id'];
		$pagesize = $param['pagesize'] ? $param['pagesize'] : 12;
		
		//商品id
		if($param['goods_id'] != '') $map['id'] = ['in',$param['goods_id']];
		//商品状态
		if($param['status'] != '') $map['status'] = $param['status'];
		//是否推荐
		if($param['is_best'] == 1) $map['is_best'] = $param['is_best'];
		//商品编号
		if($param['code'] != '') $map['code'] = ['like','%'.$param['code'].'%'];
		//商品名称
		if($param['q']!='') $map['goods_name']=['like','%'.$param['q'].'%'];
		
		//价格区间
		if($param['s_price']!='' && $param['e_price']!='') $map['price']=['between',[$param['s_price'],$param['e_price']]];
        if($param['s_price']!='' && $param['e_price']=='') $map['price']=['gt',$param['s_price']];
        if($param['s_price']=='' && $param['e_price']!='') $map['price']=['lt',$param['e_price']];
		
		//销量区间
		if($param['s_sale_num']!='' && $param['e_sale_num']!='') $map['sale_num']=['between',[$param['s_sale_num'],$param['e_sale_num']]];
        if($param['s_sale_num']!='' && $param['e_sale_num']=='') $map['sale_num']=['gt',$param['s_sale_num']];
        if($param['s_sale_num']=='' && $param['e_sale_num']!='') $map['sale_num']=['lt',$param['e_sale_num']];
       
		//店铺分类
		if($param['shop_category_id']!=''){
			if($param['shop_category_id'] == 0){
				$map['shop_category_id'] = array('eq',"");
			}else{
				$ids = M('shop_goods_category')->where(['sid'=>$param['shop_category_id']])->getField('id',true);
				$map['_string'] =   'FIND_IN_SET('.$param['shop_category_id'].', shop_category_id)';
				if($ids){
					foreach($ids as $val){
						$map['_string'] .= ' or FIND_IN_SET('.$val.', shop_category_id)';
					}
				}
			}
		}
		$pagelist = pagelist(array(
            'table'     => 'Common/GoodsRelation',
            'do'        => 'D',
            'map'       => $map,
            'pagesize'  => $pagesize,
            'p'         => $param['p'],
            'order'     => $order,
            'relation'  => true,
        ));
		if($pagelist['list']){
			return ['code' => 1,'data'=>$pagelist];
		}
		return ['code' => 3];
	}
	/**
     * subject: 设置商品下架
     * api: /SellerGoods/set_goods_offline
     * author: liangfeng
     * day: 2017-03-16
     * [字段名,类型,是否必传,说明]
     * param: openid,int,1,用户openid
     * param: goods_id,int,1,商品id
     */
	public function set_goods_offline(){
		$this->check('openid,goods_id',false);
        $res = $this->_set_goods_offline($this->post);
        $this->apiReturn($res);
	}
	public function _set_goods_offline($param){
        if(M('goods')->where(['id' => ['in',$param['goods_id']],'uid'=>$this->user['id'],'status'=>1,'officialactivity_join_id' => 0])->save(['status' => 2, 'is_best' => 0])){
            A('Total')->seller_goods_online($this->user['id']);    //统计在售商品数量
            shop_pr('',$this->user['id']); //更新店铺pr

            return ['code'=>1];
        }else return ['code' => 0,'msg' => '操作失败！提示：参与官方活动的商品不充许编辑、下架或删除等操作，请检查您要操作的商品是否处于活动中！'];
	}
	/**
     * subject: 设置商品上架
     * api: /SellerGoods/set_goods_online
     * author: liangfeng
     * day: 2017-03-16
     * [字段名,类型,是否必传,说明]
     * param: openid,int,1,用户openid
     * param: goods_id,int,1,商品id
     */
	public function set_goods_online(){
		$this->check('openid,goods_id',false);
        $res = $this->_set_goods_online($this->post);
        $this->apiReturn($res);
	}
	public function _set_goods_online($param){
        $goodsInfo = M('goods')->field('num,is_collection,category_id')->where(['id' => ['in',$param['goods_id']],'uid'=>$this->user['id'],'status'=>2])->find();
        if($goodsInfo['num'] == 0){
			return ['code' => 0,'msg' => '库存不能为0'];
        }
        if($goodsInfo['is_collection']>0 && $goodsInfo['category_id'] == '100845550'){
			return ['code' => 0,'msg' => '导入商品请编辑商品类目'];
        }
        if(M('goods')->where(['id' => ['in',$param['goods_id']],'uid'=>$this->user['id'],'status'=>2])->save(['status' => 1])){
            A('Total')->seller_goods_online($this->user['id']);    //统计在售商品数量
            shop_pr('',$this->user['id']); //更新店铺pr

            return ['code' => 1];
        }else return ['code' => 0];
	}
	
	/**
     * subject: 店铺宝贝分类
     * api: /SellerGoods/category
     * author: liangfeng
     * day: 2017-03-29
     * [字段名,类型,是否必传,说明]
     * param: openid,int,1,用户openid
     */
	public function category(){
		$this->check('openid',false);
        $res = $this->_category($this->post);
        $this->apiReturn($res);
	}
	public function _category($param){
	
        $list=get_category(['table' => 'shop_goods_category' , 'level' => 2,'field'=>'id,sid,category_name,icon,sort,atime,category_type' , 'map' => [0 => ['uid' => $this->user['id']]]]);
		if($list){
			return ['code' => 1,'data'=>$list];
        }else{
			return ['code' => 3];
        }
	}
}