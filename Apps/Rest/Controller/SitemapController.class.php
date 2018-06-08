<?php
/**
+----------------------------------------------------------------------
| RestFull API 
+----------------------------------------------------------------------
| 生成网站地图 Sitemap
+----------------------------------------------------------------------
| Author: lazycat <673090083@qq.com>
+----------------------------------------------------------------------
*/

namespace Rest\Controller;
use Think\Controller\RestController;
class SitemapController extends CommonController {

    private $_urlInfo = [];

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
            '_sitemap'              => array('require_check' => false),    //必填字段
        ];

        $result=$sign_field[$method];
        return $result;
    }


    public function index(){
    	redirect(C('sub_domain.www'));
    }

    
    /**
     * 更新url方法！
     */
    public function _sitemap(){
        $arr = ['home','brand','goods','faq','search','news'];

        $url = [];
        foreach ($arr as $val){
            $str = '_'.$val;
            $res = $this->$str();
            if(is_array($res)) $url = array_merge($url,$res);
        }
        
        $sitemapDetails='<?xml version="1.0" encoding="UTF-8"?><urlset>'.PHP_EOL;
        foreach ($url as $key=>$val){
            $sitemapDetails .= '    <url>'.PHP_EOL;
            $sitemapDetails .= '        <loc>'.$val.'</loc>'.PHP_EOL;//产品链接地址
            $sitemapDetails .= '        <lastmod>'.date('Y-m-d',time()).'</lastmod>'.PHP_EOL ;//修改时间
            $sitemapDetails .= '        <changefreq>always</changefreq>'.PHP_EOL;//always-代表经常更新
            $sitemapDetails .= '        <priority>1.0</priority>'.PHP_EOL;//权重
            $sitemapDetails .= '    </url>'.PHP_EOL;
        }
        
        $sitemapDetails .= '</urlset>';
         //dump($sitemapDetails);
        //file_put_contents("sitemap.xml", $sitemapDetails);

        //C('DEBUG_API',true);
        //dump($this->api_cfg);
        $this->doApi(C('sub_domain.web1').'/Sync/sync_file',['content' => $sitemapDetails,'save_path' => './sitemap.xml']);
        $this->doApi(C('sub_domain.web2').'/Sync/sync_file2',['content' => $sitemapDetails,'save_path' => './sitemap.xml']);
        $this->doApi(C('sub_domain.web3').'/Sync/sync_file3',['content' => $sitemapDetails,'save_path' => './sitemap.xml']);
        
        return ['code' => 1];
    }
	
	/**
	 * 生成url存到数据库
	 */
	public function _up_sitemap(){
		$arr = ['home','brand','goods','faq','search','news'];

        $url = [];
        foreach ($arr as $val){
            $str = '_'.$val;
            $res = $this->$str();
            if(is_array($res)) $url = array_merge($url,$res);
        }
		$model = M('seo');
		$history = $model->field('url')->where(['url'=>['in',$url]])->select();
		foreach($history as $value){
			$key = array_search($value['url'],$url);
			if(isset($url[$key])){
				unset($url[$key]);
			}
		}
		$insert = [];
		$ip = get_client_ip();
		foreach($url as $value){
			$insert[] = array(
				'url' => strtolower($value),
                'info' => (string) $this->_urlInfo[$value],
				'ip' => $ip,
			);
		}
		$model->addAll($insert);
		return ['code' => 1];
	}
    
    /**
     * Home
     */
    
    public function _home(){
        $url = [
            C('sub_domain.www'),
            C('sub_domain.www').U('/Service'),
        ];
        return $url;
    }
    
    /**
     * Brand
     */
    
    public function _brand(){
        $do = M('brand_tags');
        $list = $do->cache(true)->field('id')->order('id desc')->getField('id',true);

        $url[] = C('sub_domain.brand');
        foreach($list as $val){
            $url[] = C('sub_domain.brand').'/index/index/tag/'.$val;
        }
        return $url;
    }
    
    /**
     * Goods
     */
    public function _goods(){
        $attrs  = M('goods_attr_list')->cache(true,86400)->where(['num' => ['gt',0]])->field('id,goods_id,attr_name')->select();
        $goods  = M('goods')->cache(true,86400)->field('id,goods_name')->select();

        foreach($goods as $value){
            $goods_name[$value['id']] = $value['goods_name'];
        }
        foreach($attrs as $vo){
            $u      = C('sub_domain.item') . '/goods/' . $vo['id'] . '.html';
            $url[]  = $u;
            $this->_urlInfo[$u] = '商品：' . $goods_name[$vo['goods_id']] . '<br/>属性：' . $vo['attr_name'];
        }
        
        return $url;
    }
    
    /**
     * shop
     */
    
    public function _shop(){
        $do = M('shop');
        $list = $do->cache(true)->where(['status' => 1])->field('id,domain')->order('id desc')->select();
        
        foreach($list as $val){
            $url[] = shop_url($val['id'],$val['domain']);
        }
        
        return $url;
    }
    /**
     * faq
     */
    
    public function _faq(){
        $do = M('help_category');
        $list = $do->cache(true)->field('id')->order('id desc')->getField('id',true);

        $url1[] = C('sub_domain.faq');
        foreach($list as $val){
            $url1[] = C('sub_domain.faq').'/index.html?cid='.$val;
        }
    
        $do = M('help');
        $list = $do->field('id')->order('id desc')->getField('id',true);
    
        foreach($list as $val){
            $url2[] = C('sub_domain.faq').'/view.html?id='.$val;
        }
    
        return array_merge($url1,$url2);
    }
    

    /**
     * search
     */
    
    public function _search(){
        $do = M('goods_category');
        $list = $do->cache(true)->field('id')->order('id desc')->getField('id',true);

        $url[] = C('sub_domain.s');
        foreach($list as $val){
            $url[] = C('sub_domain.s').'/cat/'.$val.'.html';
        }
    
        return $url;
    }
    
    /**
     * new
     */
    
    public function _news(){
        $do = M('news');
        $list = $do->cache(true)->field('id')->order('id desc')->getField('id',true);

        $url1[] = C('sub_domain.news');
        foreach($list as $val){
            $url1[] = C('sub_domain.news').'/view.html?id='.$val;
        }
        
        $do = M('news_category');
        $list = $do->cache(true)->field('id')->order('id desc')->getField('id',true);

        foreach($list as $val){
            $url2[] = C('sub_domain.news').'/index/index/cid/'.$val.'.html';
        }
        return array_merge($url1,$url2);
    }
}