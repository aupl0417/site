<?php
namespace Rest\Controller;

class BaiduUrlPushController extends CommonController
{

    private $limit = 500;
    private $lists = array(
        array(
            'title'	=> '手动推送',
            'action' => 'zhijie',
        ),
        array(
            'title'	=> '商品详情',
            'action' => 'goods',
        ),
        array(
            'title'	=> '商品分类',
            'action' => 'goodsCategory',
        ),
        // array(
        //     'title'	=> '商品搜索',
        //     'action' => 'goodsSeach',
        // ),
        array(
            'title'	=> '品牌',
            'action' => 'brand',
        ),
        array(
            'title'	=> '代购',
            'action' => 'daigou',
        ),
    );

    public function lists(){
        $this->_check_sign();
        $this->apiReturn(1,['data' => $this->lists]);
	}

    public function pushUrl($urls){
        $apiUrl     = 'http://data.zz.baidu.com/urls?site=www.trj.cc&token=qk3MIT5yvZ8TLad8';
        $ch         = curl_init();
        $options    = array(
            CURLOPT_URL             => $apiUrl,
            CURLOPT_POST            => true,
            CURLOPT_RETURNTRANSFER  => true,
            CURLOPT_POSTFIELDS      => implode("\n", $urls),
            CURLOPT_HTTPHEADER      => array('Content-Type: text/plain'),
        );

        curl_setopt_array($ch, $options);
        $result = curl_exec($ch);
        return $result ? json_decode($result, true) : [];
    }

    public function zhijie(){
        $this->need_param = ['urls'];
        $this->_need_param();
        $this->_check_sign();

        $urls = explode(chr(10),trim(I('urls','')));
        $result = $this->pushUrl($urls);
        $this->apiReturn(1,['msg' => '更新完成', 'result' => $result]);
    }

    public function push(){
        $this->need_param = ['type','p'];
        $this->_need_param();
        $this->_check_sign();

        $action = $this->lists[I('type','','int')]['action'];
        $data   = $this->$action(I('p',0,'int'));
        $result = $this->pushUrl($data['urls']);


        if(isset($data['p'])){
            $this->apiReturn(1,['p' => $data['p'],'result'=>$result,'data' => $data]);
        }else{
            $this->apiReturn(1,['msg' => '更新完成','result'=>$result,]);
        }

    }


    /**
     * 商品
     */
    public function goods($p){
        $count  = M('goods_attr_list')->field('id')->count();
        $ps     = ceil($count/$this->limit);
        $list   = M('goods_attr_list')->limit($p * $this->limit,$this->limit)->field('id')->select();
        $data   = [];
        foreach($list as $key => $value){
            $data['urls'][] = 'https://item.trj.cc' . '/goods/' . $value['id'] . '.html';
        }
        if($ps > $p){
            $data['p'] = ++$p;
        }
        return $data;
    }

    /**
     * 商品分类
     */
    public function goodsCategory($p){
        $count  = M('goods_category')->field('id')->count();
        $ps     = ceil($count/$this->limit);
        $list   = M('goods_category')->limit($p * $this->limit,$this->limit)->where(['status' => 1])->field('id')->select();
        $data   = [];
        foreach($list as $key => $value){
            $data['urls'][] = 'https://s.trj.cc/index/index/id/' . $value['id'] . '.html';
        }
        if($ps > $p){
            $data['p'] = ++$p;
        }
        return $data;
    }

    /**
     * 品牌
     */
    public function brand($p){
        $data = [];
        $data['urls'][] = 'https://brand.trj.cc';

        $tags = M('brand_tags')->field('id')->where(['status' => 1])->select();
        foreach($tags as $key => $value){
            $data['urls'][] = 'https://brand.trj.cc/index/index/tag/' . $value['id'] . '.html';
        }

        $exts = M('brand_ext')->where(['status' => 1])->field('brand_id,shop_id')->select();
        foreach($exts as $key => $value){
            $data['urls'][] = 'https://' . $value['shop_id'] . '.trj.cc/index/goods/brand_id/' . $value['brand_id'] . '.html';
        }

        return $data;
    }

    /**
     * 代购
     */
    public function daigou($p){
        $data = [];
        $data['urls'][] = 'https://s.trj.cc/Index/index/is_daigou/1.html';

        $goods = M('goods')->where(['is_daigou' => 1,'status' => 1])->field('id')->select();
        foreach($goods as $key => $value){
            $gids[] = $value['id'];
        }
        $daigouPagesize = 30;
        if($gids){
            $attr   = M('goods_attr_list')->where(['goods_id' =>['in',$gids]])->count();
            $ps     = ceil($attr / $daigouPagesize);
            $b      = $p * $this->limit + 1;
            $e      = ($p + 1) * $this->limit;
            $e      = $ps < $e ? $ps : $e;
            for($i = $b;$i <= $e;$i++){
                $data['urls'][] = 'https://s.trj.cc/Index/index/is_daigou/1/p/' . $i . '.html';
            }
            if($ps > $p){
                $data['p'] = ++$p;
            }
        }

        return $data;
    }













}
