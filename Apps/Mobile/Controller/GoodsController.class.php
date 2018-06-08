<?php
namespace Mobile\Controller;
use Think\Controller;
class GoodsController extends CommonController {

    public function index(){



		$this->display();
    }

    /**
     * 商品分类
     */
    public function category(){
        $res = $this->doApi2('/Goods/category',[]);
        $this->assign('category',$res['data']);
		//print_r($res['data']);
        $this->display();
    }


    /**
     * 猜您喜欢
     */
    public function love(){
        $data = I('get.');
        $data['num'] = $data['num'] ? $data['num'] : 8;

        $res = $this->doApi2('/Search/love',$data);
        $this->ajaxReturn($res);
    }

    /**
     * 商品详情
     */
    public function view(){
        //('DEBUG_API',true);
        $data = ['id' => I('get.id'),'isget_rate' => 1,'rate_num' => 3];
        if($_SESSION['user']) $data['openid'] = session('user.openid');
        $res = $this->doApi2('/Goods/view',$data);
        $this->assign('rs',$res['data']);

        //var_dump($res['data']['attr_list']);
        //var_dump($res['data']['attr']);
        //var_dump($res['data']['coupon']);
		
		$rs=D('ExpressTplRelation')->relation(true)->where(['id' => $res['data']['goods']['express_tpl_id']])->find();
		if($rs['is_free'] == 0){
			$city = $this->doApi2('/City/city_level',['level'=>2]);
		}
		//print_r($city);
		//print_r($rs);
		
		$this->assign('city',$city['data']);
	    $this->assign('express_price',$rs);
        //猜您喜欢
        $love = $this->doApi2('/Search/love',['num' => 8,'cid' => $res['data']['goods']['category_id']]);
        $this->assign('love',$love['data']);

        $this->display();
    }


    /**
     * 商品添加或取消关注
     * Create by Lazycat
     * 2017-02-07
     */
    public function fav_toggle(){
        $this->ajax_check_logined();

        $res = $this->doApi2('/FavGoods/toggle',I('post.'));
        $this->ajaxReturn($res);
    }


    /**
     * 商品详情
     * Create by Lazycat
     * 2017-02-07
     */
    public function content(){
        $res = $this->doApi2('/Goods/content',['goods_id' => I('get.goods_id')]);
        $this->assign('rs',$res['data']);

        //dump($res);

        $tmp = 'kdfsd,sdfs,sdf,,sdf,';
        $tmp = explode(',',$tmp);
        //var_dump($tmp);

        $keys = array_diff($tmp,array(''));
        //var_dump($keys);

        $this->display();
    }

    /**
     * 商品评价
     * Create by Lazycat
     * 2017-02-08
     */
    public function rate(){
        $res = $this->doApi2('/Goods/rate',['goods_id' => I('get.goods_id')]);
        $this->assign('pagelist',$res['data']);
        $this->assign('count',$res['data']['count']);

        //var_dump($res['data']['count']);

        //var_dump($res);

        //好评
        if($res['data']['count']['rate_good'] > 0){
            $res = $this->doApi2('/Goods/rate',['goods_id' => I('get.goods_id'),'level' => 1]);
            $this->assign('rate_good',$res['data']);
        }

        //中评
        if($res['data']['count']['rate_good'] > 0){
            $res = $this->doApi2('/Goods/rate',['goods_id' => I('get.goods_id'),'level' => 0]);
            $this->assign('rate_middle',$res['data']);
        }

        //差评
        if($res['data']['count']['rate_good'] > 0){
            $res = $this->doApi2('/Goods/rate',['goods_id' => I('get.goods_id'),'level' => -1]);
            $this->assign('rate_bad',$res['data']);
        }

        $this->display();
    }


    /**
     * 商品评价分页
     * Create by Lazycat
     * 2017-02-08
     */
    public function rate_page(){
        $res = $this->doApi2('/Goods/rate',['goods_id' => I('get.goods_id'),'p' => I('get.p'),'level' => I('get.level')]);
        $this->ajaxReturn($res);
    }


    /**
     * 选择商品属性
     * Create by Lazycat
     * 2017-02-13
     */
    public function change_attr(){
        $res = $this->doApi2('/Goods/change_attr',I('post.'));

        $this->assign('rs',$res['data']);
        $html = $this->fetch('change_attr');
        //var_dump($res);
        $this->ajaxReturn(['code' => $res['code'],'msg' => $res['msg'],'html' => $html]);
    }
	
	/**
     * 根据模板获取运费
     * Create by lizhuheng
     * 2017-03-30
    */
    public function get_express_price(){
		$res = $this->doApi2('/Goods/get_express_price',I('post.'));
		//print_r($res);
        $this->ajaxReturn($res);
    }

    /**
     * 获取秒杀商品信息
     * Create by Lazycat
     * 2017-03-31
     */
    public function miaosha_goods_view(){
        $res = $this->doApi2('/Goods/miaosha_goods_view',['goods_id' => I('post.goods_id')]);
        $this->ajaxReturn($res);
    }
}