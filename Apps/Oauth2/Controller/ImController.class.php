<?php
/**
 * ---------------------------------------
 * IM验证及跳转
 * ---------------------------------------
 * Author: Lazycat <673090083@qq.com>
 * ---------------------------------------
 * 2016-12-15
 * ---------------------------------------
 */
namespace Oauth2\Controller;
use Think\Controller;
class ImController extends InitController {
    public function check(){
        $this->check_login();

        $param = I('get.');
        $param['sendert'] = session('user.nick');
        if(empty($param['receiver']) && empty($param['seller_id']) && empty($param['goods_id'])){
            echo '<div>缺少参数！</div>';
            exit();
        }



        //http://121.9.240.110:18080/storeim/quanzi.html?sendert=ctestnum217&receiver=yinyan&commodity_name=%E5%95%86%E5%93%81%E5%90%8D%E7%A7%B0&commodity_price=138&commodity_pattern=1&commodity_print=1&integral=1000.00&commodity_image=https://img.trj.cc/Ftw7DanBlQYYc-Y3x30qh-7S5_Et?imageMogr2/thumbnail/!300x300r/gravity/Center/crop/300x300&shop_name=%E6%89%B9%E5%8F%91%E8%A1%97

        if($param['goods_id']){
            $do=D('Common/GoodsRelation');
            $rs=$do->relation(true)->cache(true)->relationWhere('goods_attr_list','num>0')->relationOrder('goods_attr_list','price asc')->relationField('goods_attr_list','id,attr_name,price,price_market,sale_num,view,concat("/Goods/view/id/",id,".html") as url')->relationLimit('goods_attr_list',1)->where(['id' => $param['goods_id']])->field('id,goods_name,images,price,sale_num,score_ratio,shop_id,seller_id')->find();

            $param['receiver']          = $rs['seller']['nick'];
            $param['commodity_name']    = $rs['goods_name'];
            $param['commodity_price']   = $rs['price'];
            $param['commodity_pattern'] = $rs['shop']['type_id'] == 1 ? 1: '';
            $param['commodity_print']   = $rs['sale_num'];
            $param['integral']          = $rs['price'] * $rs['score_ratio'] * 100;
            $param['commodity_image']   = $rs['images'];
            $param['shop_name']         = $rs['shop']['shop_name'];
            $param['shop_url']          = shop_url($rs['shop']['id'],$rs['shop']['domain']);
            $param['goods_url']         = C('sub_domain.item').'/goods/'.$rs['attr_list'][0]['id'].'.html';


        }elseif($param['seller_id']){
            $param['receiver'] = M('user')->where(['id' => $param['seller_id']])->getField('nick');
        }

        $imurl = im_url($param);
        //echo $imurl;
        redirect($imurl);
    }


}