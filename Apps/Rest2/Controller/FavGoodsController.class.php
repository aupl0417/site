<?php
/**
 * ----------------------------------------------------------
 * RestFull API V2.0
 * ----------------------------------------------------------
 * 关注的商品
 * ----------------------------------------------------------
 * Author:Lazycat <673090083@qq.com>
 * ----------------------------------------------------------
 * 2017-01-17
 * ----------------------------------------------------------
 */
namespace Rest2\Controller;
use Think\Controller\RestController;
class FavGoodsController extends ApiController {
    protected $action_logs = array('delete','toggle');

    /**
     * subject: 已关注的商品
     * api: /FavGoods/goods
     * author: Lazycat
     * day: 2017-01-17
     *
     * [字段名,类型,是否必传,说明]
     * param: openid,string,1,用户openid
     * param: pagesize,int,0,每页显示数量，默认为12
     */
    public function goods(){
        $this->check('openid',false);

        $res = $this->_goods($this->post);
        $this->apiReturn($res);
    }

    public function _goods($param){
        $map['uid'] = $this->user['id'];
        $pagesize   = $param['pagesize'] ? $param['pagesize'] : 12;

        $list = pagelist(array(
            'table'         => 'Common/GoodsFavRelation',
            'do'            => 'D',
            'map'           => $map,
            'order'         => 'atime desc',
            'fields'        => 'id,goods_id',
            'pagesize'      => $pagesize,
            'relation'      => true,
            'relationLimit' => array('goods_attr_list',1),
            'p'			    => $param['p'],
        ));

        if($list['list']){
            return ['code' => 1,'data' => $list];
        }

        return ['code' => 3];
    }

    /**
     * subject: 商品取消关注
     * api: /FavGoods/delete
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
        $do = M('goods_fav');
        $map['uid'] = $this->user['id'];
        $map['id']  = $param['id'];

        $goods_id   = $do->where($map)->getField('goods_id');
        if(empty($goods_id)) goto error;

        $do->startTrans();
        if(!$do->where($map)->delete()) goto error;
        if(!M('goods')->where(['id' => $goods_id])->setDec('fav_num')) goto error;

        $do->commit();
        return ['code' => 1];

        error:
        $do->rollback();
        return ['code' => 0];

    }

    /**
     * subject: 商品关注或取消
     * api: /FavGoods/toggle
     * author: Lazycat
     * day: 2017-02-06
     * content: 由于缓存原因，商品关注数量不会立即+1或-1
     *
     * [字段名,类型,是否必传,说明]
     * param: openid,string,1,用户openid
     * param: goods_id,int,1,要关注的商品ID
     */
    public function toggle(){
        $this->check('openid,goods_id');

        $res = $this->_toggle($this->post);
        $this->apiReturn($res);
    }

    public function _toggle($param){
        $do = D('Common/GoodsFav');

        $id = $do->where(['uid' => $this->user['id'],'goods_id' => $param['goods_id']])->getField('id');
        if($id){    //已存在则取消关注
            $do->startTrans();

            if(!$this->sw[] = $do->where(['id' => $id])->delete()) goto error;
            if(!$this->sw[] = M('goods')->where(['id' => $param['goods_id']])->setDec('fav_num')) goto error;

            $do->commit();
            return ['code' => 2,'msg' => '已取消关注！'];

            error:
            $do->rollback();
            return ['code' => 0,'msg' => '取消关注失败！'];

        }else{  //添加关注
            $data = [
                'uid'       => $this->user['id'],
                'goods_id'   => $param['goods_id'],
            ];

            $do->startTrans();
            if(!$this->sw[] = $do->create($data)) goto error2;
            if(!$this->sw[] = $do->add()) goto error2;
            if(!$this->sw[] = M('goods')->where(['id' => $param['goods_id']])->setInc('fav_num')) goto error2;

            $do->commit();
            return ['code' => 1,'msg' => '添加关注成功！'];

            error2:
            $do->rollback();
            return ['code' => 0,'msg' => '添加关注失败！'];
        }
    }

}