<?php
/**
 * ----------------------------------------------------------
 * RestFull API V2.0
 * ----------------------------------------------------------
 * 广告读取
 * ----------------------------------------------------------
 * Author:Lazycat <673090083@qq.com>
 * ----------------------------------------------------------
 * 2017-01-07
 * ----------------------------------------------------------
 */
namespace Rest2\Controller;
use Think\Controller\RestController;
class AdController extends ApiController {
    //protected $action_logs = array('ad');

    /**
     * subject: 读取多个广告位数据
     * api: /Ad/ads
     * author: Lazycat
     * day: 2017-01-07
     *
     * [字段名,类型,是否必传,说明]
     * param: position_id,int,1,广告位ID，多个请用逗号隔开
     * param: return_type,int,0,=1是返回的下标为默认而不是广告位ID为下标，方便App调用
     */
    public function ads(){
        $this->check($this->_field('return_type','position_id'),false);

        $ads = [];
        $ids = explode(',',$this->post['position_id']);
        foreach($ids as $val){
            if($this->post['return_type'] == 1) $ads[] = $this->_ad($val);
            else $ads[$val] = $this->_ad($val);
        }
        $res = ['code' => 1,'data' => $ads];
        $this->apiReturn($res);
    }

    /**
     * subject: 获取某一广告位
     * api: /Ad/ad
     * author: Lazycat
     * day: 2017-01-07
     *
     * [字段名,类型,是否必传,说明]
     * param: position_id,int,1,广告位ID
     */
    public function ad(){
        $this->check('position_id',false);
        $res = $this->_ad($this->post['position_id']);
        $this->apiReturn($res);
    }

    /**
     * 读取广告
     * @param int $position_id 广告位ID
     */
    public function _ad($position_id){
        $do=D('Common/PositionRelation');
        $prs=$do->cache(false)->relation(true)->relationWhere('ad','status=1 and (is_default=1 or FIND_IN_SET("'.date('Y-m-d').'",days))')->relationField('ad','id,name,sort,images,is_default,goods_id,shop_id,type,url')->where(array('id'=>$position_id))->field('id,position_name,type,num,default_images,url,width,height,is_seat,device')->find();

        //dump($prs);

        $default    = array();  //默认广告
        $ads        = array();
        foreach($prs['ads'] as $key=>$val){
            if($val['is_default']==1) $default[$val['sort']]=$val;
            else $adlist[$val['sort']]=$val;
        }
        for($i=0;$i<$prs['num'];$i++){
            $tmp = [
                'url'       => $prs['url'],
                'images'    => $prs['default_images'],
                'name'      => $prs['content'],
                'background_images' => $prs['background_images'],
            ];
            $ads[$i] = isset($adlist[$i]) ? $adlist[$i] : ($default[$i] ? $default[$i] : $tmp);
        }

        foreach ($ads as &$v) {
            if ($v['type'] == 0 && !empty($v['goods_id'])) {    //商品
                $tmp = M('goods_attr_list')->where(['goods_id' => $v['goods_id']])->field('id,price')->order('num desc,price desc')->find();
                if($tmp){
                    $v['attr_list_id']  = $tmp['id'];
                    $v['goods_pirce']   = $tmp['price'];
                    $v['url']           = C('sub_domain.m').'/Goods/view/id/'.$tmp['id'];
                }
            }elseif($v['type'] ==1 && !empty($v['shop_id'])){
                $v['url']   = C('sub_domain.m').'/Shop/index/shop_id/'.$v['shop_id'];
            }
        }
        unset($v);
        $prs['ads']=$ads;
        if($ads){
            return array('code'=> 1,'data'=> $prs);
        }else{
            return array('code'=> 0);
        }
    }
}