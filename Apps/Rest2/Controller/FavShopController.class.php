<?php
/**
 * ----------------------------------------------------------
 * RestFull API V2.0
 * ----------------------------------------------------------
 * 关注的店铺
 * ----------------------------------------------------------
 * Author:Lazycat <673090083@qq.com>
 * ----------------------------------------------------------
 * 2017-01-18
 * ----------------------------------------------------------
 */
namespace Rest2\Controller;
use Think\Controller\RestController;
class FavShopController extends ApiController {
    protected $action_logs = array('delete','toggle');

    /**
     * subject: 关注的商品
     * api: /FavShop/shop
     * author: Lazycat
     * day: 2017-01-18
     *
     * [字段名,类型,是否必传,说明]
     * param: openid,string,1,用户openid
     * param: pagesize,int,0,每页显示数量，默认为12
	 * param: num,int,0,商品显示的数量，默认为4件商品
     */
    public function shop(){
        $this->check('openid',false);

        $res = $this->_shop($this->post);
        $this->apiReturn($res);
    }

    public function _shop($param){
        $map['uid'] = $this->user['id'];
        $pagesize   = $param['pagesize'] ? $param['pagesize'] : 15;
        $num        = $param['num'] ? $param['num'] : 4;
		
        $list = pagelist(array(
            'table'     => 'Common/ShopFavRelation',
            'do'        => 'D',
            'map'       => $map,
            'order'     => 'atime desc',
            'fields'    => 'id,shop_id',
            'pagesize'  => $pagesize,
            'relation'  => true,
            'p'			=> $param['p'],
        ));

        if($list['list']){
            //数据格式
            foreach($list['list'] as $key => $val){
                $list['list'][$key]['shop']['shop_logo']  = myurl($val['shop']['shop_logo'],150);
                $list['list'][$key]['shop']['shop_url']   = shop_url($val['shop_id'],$val['shop']['domain']);
				
				$data['shop_id'] = $list['list'][$key]['shop_id'];
				$data['status'] = 1;
				$result = D('Common/GoodsRelation')->relation(true)->cache(true,C('CACHE_LEVEL.M'))->relationWhere('goods_attr_list','num>0')->relationOrder('goods_attr_list','price asc')->relationField('goods_attr_list','id,attr_name,price,price_market,sale_num,view')->relationLimit('goods_attr_list',1)->where($data)->field('id,goods_name,images,price,sale_num,shop_id,seller_id,score_ratio')->limit($num)->select();
				$goods_num   = M('goods')->cache(true,10)->where(['shop_id' => $data['shop_id'],'num' => ['gt',0],'status' => 1])->count();
				$list['list'][$key]['shop_goods']         = $result;
				$list['list'][$key]['shop']['sale_num']   = $goods_num;
            }
            return ['code' => 1,'data' => $list];
        }

        return ['code' => 3];
    }

    /**
     * subject: 店铺取消关注
     * api: /FavShop/delete
     * author: Lazycat
     * day: 2017-01-18
     *
     * [字段名,类型,是否必传,说明]
     * param: openid,string,1,用户openid
     * param: id,int,1,关注ID
     */
    public function delete(){
        $this->check('openid,id');

        $res = $this->_delete($this->post);
        $this->apiReturn($res);
    }

    public function _delete($param){
        $do = M('shop_fav');
        $map['uid'] = $this->user['id'];
        $map['id']  = $param['id'];

        $shop_id   = $do->where($map)->getField('shop_id');
        if(empty($shop_id)) goto error;

        $do->startTrans();
        if(!$do->where($map)->delete()) goto error;
        if(!M('shop')->where(['id' => $shop_id])->setDec('fav_num')) goto error;

        $do->commit();
        return ['code' => 1];

        error:
        $do->rollback();
        return ['code' => 0];

    }

    /**
     * subject: 店铺关注或取消
     * api: /FavShop/toggle
     * author: Lazycat
     * day: 2017-02-10
     * content: 由于缓存原因，店铺关注数量不会立即+1或-1
     *
     * [字段名,类型,是否必传,说明]
     * param: openid,string,1,用户openid
     * param: shop_id,int,1,要关注的店铺ID
     */
    public function toggle(){
        $this->check('openid,shop_id');

        $res = $this->_toggle($this->post);
        $this->apiReturn($res);
    }

    public function _toggle($param){
        $do = D('Common/ShopFav');

        $id = $do->where(['uid' => $this->user['id'],'shop_id' => $param['shop_id']])->getField('id');
        if($id){    //已存在则取消关注
            $do->startTrans();

            if(!$this->sw[] = $do->where(['id' => $id])->delete()) goto error;
            if(!$this->sw[] = M('shop')->where(['id' => $param['shop_id']])->setDec('fav_num')) goto error;

            $do->commit();
            return ['code' => 2,'msg' => '已取消关注！'];

            error:
            $do->rollback();
            return ['code' => 0,'msg' => '取消关注失败！'];

        }else{  //添加关注
            $data = [
                'uid'       => $this->user['id'],
                'shop_id'   => $param['shop_id'],
            ];

            $do->startTrans();
            if(!$this->sw[] = $do->create($data)) goto error2;
            if(!$this->sw[] = $do->add()) goto error2;
            if(!$this->sw[] = M('shop')->where(['id' => $param['shop_id']])->setInc('fav_num')) goto error2;

            $do->commit();
            return ['code' => 1,'msg' => '添加关注成功！'];

            error2:
            $do->rollback();
            return ['code' => 0,'msg' => '添加关注失败！'];
        }

    }

}