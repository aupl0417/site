<?php
/**
 * -------------------------------------------------
 * 店铺
 * -------------------------------------------------
 * Create by Lazycat <673090083@qq.com>
 * -------------------------------------------------
 * 2017-02-08
 * -------------------------------------------------
 */
namespace Mobile\Controller;
use Think\Controller;
class ShopController extends CommonController {
    protected $info;    //店铺基本资料
    public function _initialize() {
        parent::_initialize();

        if(I('get.shop_id')){
            $data['shop_id'] = I('get.shop_id');
            if(session('user.openid')) $data['openid'] = session('user.openid');
            $res = $this->doApi2('/Shop/info',$data);
            $this->info = $res['data'];
            $this->assign('info',$this->info);
            //var_dump($this->info);
            $_GET['shop_id'] = $res['data']['id'];
        }
    }
    /**
     * 店铺首页
     * Create by Lazycat
     * 2017-02-08
     */
    public function index(){
        //厨窗商品
        $best = $this->doApi2('/Shop/goods_topN',['is_best' => 1,'shop_id' => I('get.shop_id')]);
        $this->assign('best',$best['data']);

        //综合
        $res = $this->doApi2('/Shop/goods',['shop_id' => I('get.shop_id')]);
        $this->assign('pagelist',$res['data']);

        //销量
        $res = $this->doApi2('/Shop/goods',['shop_id' => I('get.shop_id'),'order' => 'sale_num desc']);
        $this->assign('sale',$res['data']);

        //人气
        $res = $this->doApi2('/Shop/goods',['shop_id' => I('get.shop_id'),'order' => 'view desc']);
        $this->assign('view',$res['data']);

        //价格
        $res = $this->doApi2('/Shop/goods',['shop_id' => I('get.shop_id'),'order' => 'price asc']);
        $this->assign('price',$res['data']);

        //店铺装修
        //C('DEBUG_API',true);
        $res = $this->doApi2('/Shop/shop_make',['shop_id' => I('get.shop_id')]);
        $this->assign('shop_make',$res['data']);
        //print_r($res);


        //var_dump($best);
        $this->display();
    }

    public function goods_page(){
        //C('DEBUG_API',true);
        $res = $this->doApi2('/Shop/goods',I('get.'));
        $this->ajaxReturn($res);
    }

    /**
     * 促销/厨窗
     * Create by Lazycat
     * 2017-02-09
     */
    public function best(){
        //C('DEBUG_API',true);
        $res = $this->doApi2('/Shop/goods',['is_best' => 1,'shop_id' => I('get.shop_id')]);
        $this->assign('pagelist',$res['data']);

        //var_dump($res);
        $this->display();
    }

    public function best_page(){
        //C('DEBUG_API',true);
        $res = $this->doApi2('/Shop/goods',['is_best' => 1,'shop_id' => I('get.shop_id'),'p' => I('get.p')]);
        $this->ajaxReturn($res);
    }


    /**
     * 所有商品
     * Create by Lazycat
     * 2017-02-09
     */
    public function goods(){
        //综合
        $res = $this->doApi2('/Shop/goods',I('get.'));
        $this->assign('pagelist',$res['data']);

        //销量
        $res = $this->doApi2('/Shop/goods',array_merge(I('get.'),array('order' => 'sale_num desc')));
        $this->assign('sale',$res['data']);

        //人气
        $res = $this->doApi2('/Shop/goods',array_merge(I('get.'),array('order' => 'view desc')));
        $this->assign('view',$res['data']);

        //价格
        $res = $this->doApi2('/Shop/goods',array_merge(I('get.'),array('order' => 'price asc')));
        $this->assign('price',$res['data']);


        //var_dump($best);
        $this->display();
    }

    /**
     * 店铺关注
     * Create by Lazycat
     * 2017-02-10
     */
    public function fav_toggle(){
        $this->ajax_check_logined();


        $res = $this->doApi2('/FavShop/toggle',I('post.'));
        $this->ajaxReturn($res);
    }


    /**
     * 联系卖家
     * Create by Lazycat
     * 2017-02-10
     */
    public function contact(){
        $this->display();
    }


    /**
     * 用户评价
     * Create by Lazycat
     * 2017-02-10
     */
    public function rate(){
        $res = $this->doApi2('/Shop/rate',['shop_id' => I('get.shop_id')]);
        $this->assign('pagelist',$res['data']);
        $this->assign('count',$res['data']['count']);

        //var_dump($res['data']['count']);

        //var_dump($res);

        //好评
        if($res['data']['count']['rate_good'] > 0){
            $res = $this->doApi2('/Shop/rate',['shop_id' => I('get.shop_id'),'level' => 1]);
            $this->assign('rate_good',$res['data']);
        }

        //中评
        if($res['data']['count']['rate_good'] > 0){
            $res = $this->doApi2('/Shop/rate',['shop_id' => I('get.shop_id'),'level' => 0]);
            $this->assign('rate_middle',$res['data']);
        }

        //差评
        if($res['data']['count']['rate_good'] > 0){
            $res = $this->doApi2('/Shop/rate',['shop_id' => I('get.shop_id'),'level' => -1]);
            $this->assign('rate_bad',$res['data']);
        }

        $this->display();
    }


    /**
     * 用户评价分页
     */
    public function rate_page(){
        $res = $this->doApi2('/Shop/rate',['shop_id' => I('get.shop_id'),'p' => I('get.p'),'level' => I('get.level')]);
        $this->ajaxReturn($res);
    }


    /**
     * 店铺商品分类
     * Create by Lazycat
     * 2017-02-10
     */
    public function category(){
        $res = $this->doApi2('/Shop/category',['shop_id' => I('get.shop_id')]);
        $this->assign('category',$res['data']);

        $this->display();
    }

}