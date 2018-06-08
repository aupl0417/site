<?php
/**
* 列表选择
*/
namespace Admin\Controller;
use Think\Controller;
class SelectItemController extends CommonController {
    public function goods(){
        $pagesize = 12;
        $map['status']  = 1;
        if(I('get.status')!='') $map['status']  = I('get.status');
        if(I('get.is_self')) $map['is_self']   = 1;
        if(I('get.is_love')) $map['is_love']   = 1;
        if(I('get.goods_name')) $map['goods_name'] = ['like','%'.I('get.goods_name').'%'];
        if(I('get.shop'))  $map['_string'] = 'shop_id in (select id from '.C('DB_PREFIX').'shop where shop_name like "%'.I('get.shop').'%")';
        if(I('get.seller'))  {
            $sql = 'seller_id in (select id from '.C('DB_PREFIX').'user where nick like "%'.I('get.seller').'%")';
            $map['_string'] = $map['_string'] ? $map['_string'] . ' and '.$sql : $sql;
        }

        $list = pagelist(array(
            'table'     => 'Goods86Relation',
            'do'        => 'D',
            'pagesize'  => $pagesize,
            'map'       => $map,
            'relation'  => true,
            'fields'    => 'id,status,images,goods_name,price,num,sale_num,shop_id,seller_id',
        ));

        $this->assign('pagelist',$list);

        $tpl = I('get.tpl') ? I('get.tpl') : '';
        $this->display($tpl);
    }

    public function goods_item(){
        $do = M('goods');
        $rs = $do->where(['id' => I('get.id')])->field('id,images,goods_name,price,num')->find();
        $rs['images'] = myurl($rs['images'],60);
        $this->ajaxReturn(['status' => 'success','data' => $rs]);
    }

    public function user(){
        $pagesize = 12;
        $map['status']  = 1;
        if(I('get.status')!='') $map['status']  = I('get.status');
        if(I('level_id'))   $map['level_id']  = I('get.level_id');
        if(I('type') != '')       $map['type']    = I('get.type');
        if(I('is_auth'))    $map['is_auth' ]    = I('get.is_auth');
        if(I('nick'))   $sql[] = 'nick like "%'.I('get.nick').'%"';
        if(I('name'))   $sql[] = 'name like "%'.I('get.name').'%"';
        if(I('mobile')) $sql[] = 'mobile like "%'.I('get.mobile').'%"';
        if($sql) $map['_string'] = implode(' and ',$sql);

        $list = pagelist(array(
            'table'     => 'User72Relation',
            'do'        => 'D',
            'pagesize'  => $pagesize,
            'map'       => $map,
            'relation'  => true,
            'fields'    => 'id,status,level_id,face,nick,name,mobile,type,is_auth',
        ));

        $this->assign('pagelist',$list);
        $tpl = I('get.tpl') ? I('get.tpl') : '';
        $this->display($tpl);
    }

    public function user_item(){
        $do = M('user');
        $rs = $do->where(['id' => I('get.id')])->field('id,face,nick,name,mobile')->find();
        $rs['face'] = myurl($rs['face'],60);
        $this->ajaxReturn(['status' => 'success','data' => $rs]);
    }

    public function shop(){
        $pagesize = 12;
        $map['status']  = 1;
        if(I('get.status')!='') $map['status']  = I('get.status');
        if(I('get.shop_name')) $map['shop_name'] = ['like','%'.I('get.shop_name').'%'];
        if(I('get.tel')) $map['tel'] = ['like','%'.I('get.tel').'%'];
        if(I('get.mobile')) $map['mobile'] = ['like','%'.I('get.mobile').'%'];
        if(I('get.wang')) $map['wang'] = ['like','%'.I('get.wang').'%'];
        if(I('get.qq')) $map['qq'] = ['like','%'.I('get.qq').'%'];
        if(I('get.nick'))  {
            $map['_string'] = 'uid in (select id from '.C('DB_PREFIX').'user where nick like "%'.I('get.nick').'%")';

        }

        $list = pagelist(array(
            'table'     => 'Shop116Relation',
            'do'        => 'D',
            'pagesize'  => $pagesize,
            'map'       => $map,
            'relation'  => true,
            'fields'    => 'id,status,uid,shop_name,shop_logo,mobile,qq,pr,goods_num,province,city,shop_level,inventory_type,total_money_pay',
        ));

        //dump($list);

        $this->assign('pagelist',$list);
        $tpl = I('get.tpl') ? I('get.tpl') : '';
        $this->display($tpl);
    }

    public function shop_item(){
        $do = D('Shop116Relation');
        $rs = $do->relation(true)->where(['id' => I('get.id')])->field('id,shop_logo,shop_name,mobile,uid')->find();
        $rs['shop_logo'] = myurl($rs['shop_logo'],60);
        $rs['nick'] = $rs['user']['nick'];
        $this->ajaxReturn(['status' => 'success','data' => $rs]);
    }


    public function category(){
        $list = get_category([
            'table'         => 'goods_category',
            'level'         => 3,
            'field'         => 'id,sid,category_name,sub_name',
            'map'           => [['status' => 1],['status' => 1],['status' => 1]],
            'cache_name'    => 'select_category_checkbox',
        ]);

        $this->assign('list',$list);

        $tpl = I('get.tpl') ? I('get.tpl') : '';
        $this->display($tpl);
    }


    /**
     * 根据商品ID获取商品数据
     */
    public function goods_ids(){
        $do = D('Goods86Relation');
        $list = $do->relation(true)->where(['id' => ['in',I('post.ids')]])->field('id,status,images,goods_name,price,num,sale_num,shop_id,seller_id')->select();
        $this->assign('list',$list);

        $tpl = I('post.tpl') ? I('post.tpl') : 'goods_ids';
        $html = $this->fetch($tpl);
        $this->ajaxReturn(['code' => 1,'html' => $html]);
    }

    /**
     * 根据店铺ID获取店铺数据
     */
    public function shop_ids(){
        $do = D('Shop116Relation');
        $list = $do->relation(true)->where(['id' => ['in',I('post.ids')]])->field('id,shop_logo,shop_name,mobile,uid')->select();
        $this->assign('list',$list);

        $tpl = I('post.tpl') ? I('post.tpl') : 'shop_ids';
        $html = $this->fetch($tpl);
        $this->ajaxReturn(['code' => 1,'html' => $html]);
    }

    /**
     * 根据类目ID获取类目数据
     */
    public function category_ids(){
        $do = M('goods_category');
        $list = $do->where(['id' => ['in',I('post.ids')]])->field('id,category_name')->select();
        $this->assign('list',$list);

        $tpl = I('post.tpl') ? I('post.tpl') : 'category_ids';
        $html = $this->fetch($tpl);
        $this->ajaxReturn(['code' => 1,'html' => $html]);
    }
}