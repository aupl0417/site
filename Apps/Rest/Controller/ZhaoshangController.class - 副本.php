<?php
/**
+----------------------------------------------------------------------
| RestFull API 
+----------------------------------------------------------------------
| 招商
+----------------------------------------------------------------------
| Author: lazycat <673090083@qq.com>
+----------------------------------------------------------------------
*/

namespace Rest\Controller;
use Think\Controller\RestController;
class ZhaoshangController extends CommonController {
    public function _initialize() {
        parent::_initialize();

        $action = ACTION_NAME;
        if(!in_array('_'.$action,get_class_methods($this))) $this->apiReturn(1501);  //请求的方法不存在

        $this->_api(['method' => $action]);
    }


    /**
     * 各方法的必签字段
     * @param string $method     方法
     */
    public function _sign_field($method){
        $sign_field = [
            '_about'                => array('require_check' => false),    //招商介绍
            '_category'             => array('require_check' => false),    //招商类目
            '_brand'                => array('require_check' => false,'not_sign' => 'category_id'),    //入驻品牌
            '_brand_lib'            => array('require_check' => false,'not_sign' => 'category_id'),    //招商品牌
            '_shop_type'            => array('require_check' => false),    //店铺类型
            '_cred_view'             => 'category_id',    //类目资质要求
        ];

        $result=$sign_field[$method];
        return $result;
    }


    public function index(){
    	redirect(C('sub_domain.www'));
    }

    /**
     * 招商介绍
     */
    public function _about(){
        $rs = M('zhaoshang')->cache(true)->field('atime,etime,ip',true)->find();
        if($rs) return array('code' => 1,'data' => $rs);

        return array('code' => 3);
    }

    /**
     * 招商类目
     */
    public function _category(){
        $param = [
            'table'     => 'zhaoshang_category',
            'sql'       => 'status=1',
            'level'     => 2,
            'field'     => 'id,sid,category_name',
            'cache_name'=> 'zhaoshang_category_level2'
        ];
        $list = get_category($param);

        if($list) return array('code' => 1,'data' => $list);
        else return array('code' => 3);
    }

    /**
     * 已入驻品牌
     * @param int $_POST['category_id'] 招商类目ID，选填
     */
    public function _brand(){
        $map['status']  = 1;
        if(I('post.category_id')) $map['category_id']   = I('post.category_id');

        $list = M('brand')->distinct(true)->where($map)->field('id,b_name,b_logo,shop_id')->limit(1000)->select();

        if($list) return array('code' => 1,'data' => $list);
        else return array('code' => 3);
    }

    /**
     * 招商品牌库
     * @param int $_POST['category_id'] 招商类目ID，选填
     */
    public function _brand_lib(){
        $map['status']  = 1;
        if(I('post.category_id')) $map['_string']   = 'find_in_set ('.I('post.category_id').',category_id)';

        $list = M('zhaoshang_brand')->where($map)->field('atime,etime,ip',true)->select();

        if($list) return array('code' => 1,'data' => $list);
        else return array('code' => 3);
    }

    /**
     * 店铺类型
     */
    public function _shop_type(){
        $list = M('shop_type')->cache(false)->where(['status' => 1])->field('atime,etime',true)->order('id asc')->select();
        if($list) return array('code' => 1,'data' => $list);
        else return array('code' => 3);
    }

    /**
     * 类目资质详情
     */
    public function _cred_view(){
        //店铺类别
        $shop_type = ['cred_flagship','cred_franchised','cred_exclusive','cred_personal'];

        //资质类别
        $category['cred'] = array(
            //array('id' => 1,'name' => '公司资质'),
            array('id' => 4,'name' => '会员资质'),
            array('id' => 2,'name' => '品牌资质'),
            array('id' => 3,'name' => '行业资质'),
        );

        $rs = M('zhaoshang_category')->cache(true)->where(['id' => I('post.category_id')])->field('atime,etime,ip',true)->find();
        if($rs){
            $rs['nav'] = nav_sort(array('table' => 'zhaoshang_category','field' => 'id,sid,category_name','id' => I('post.category_id'),'key' => 'category_name','cache_name' => 'zhaoshang_category_nav_'.I('post.category_id')));

            foreach($shop_type as $val){
                if($rs[$val]){
                    $tmp = unserialize(html_entity_decode($rs[$val]));
                    $item = $category;
                    $item['options']    = $tmp;
                    foreach ($category['cred'] as $k => $v){
                        switch($v['id']){
                            case 4:
                                $item['cred'][$k]['dlist'] = M('zhaoshang_cred')->where(['status' => 1,'type' => 4,'id' => ['in',$tmp['is_select']]])->field('atime,etime,ip',true)->select();
                                break;
                            case 2:
                                $item['cred'][$k]['dlist'] = M('zhaoshang_cred')->where(['status' => 1,'type' => 2,'id' => ['in',$tmp['is_select']]])->field('atime,etime,ip',true)->select();
                                break;
                            case 3:
                                $item['cred'][$k]['dlist'] = M('zhaoshang_cred')->where(['status' => 1,'type' => 3,'id' => ['in',$tmp['is_select']]])->field('atime,etime,ip',true)->select();
                                break;
                        }
                    }

                    $rs[$val]   = $item;
                }
            }

            //$this->apiReturn(1,['data' => $rs]);
            return ['code' => 1,'data' => $rs];
        }else return['code' => 3];
    }
}