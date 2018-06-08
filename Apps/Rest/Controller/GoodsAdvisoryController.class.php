<?php
/**
 * 商品咨询-商品
 */
namespace Rest\Controller;
class GoodsAdvisoryController extends CommonController {
    
    /**
     * 列表
     */
    public function index() {
        $this->need_param=array('sign', 'id');
        $this->_need_param();
        $this->_check_sign();
        
        $map = [
            'goods_id' => I('post.id'),
            'status' => 2, //只列出已回复的咨询
        ];
        
        $cates = M('goods_advisory_category')->cache(true)->where(['status' => 1])->order('id asc')->getField('id,name', true);
        
        //是否查询分类
        if (isset($_POST['type']) && array_key_exists(I('post.type'), $cates)) {
            $map['sid'] = I('post.type');
        }
        
        //取出当前用户未回复的咨询
        $curretUser = [];
        $cMaps      = [];
        if (isset($_POST['openid']) && !empty(I('post.openid'))) {
            $cMaps = ['status' => 1, 'uid' => $this->uid, 'goods_id' => I('post.id')];
            if ($map['sid'] > 0) $cMaps['sid'] = $map['sid'];
            $curretUser = D('GoodsAdvisoryView')->where($cMaps)->group('id')->field('nick,atime,content,reply_content')->order('id asc')->select();
        }
        
        $list = pagelist([
            'do'    => 'D',
            'table' => 'GoodsAdvisoryView',
            'map'   => $map,
            'pagesize'      => I('post.pagesize') > 0 ? I('post.pagesize') : 10,
            'cache_name'    => md5(implode(',', I('post.')) . 'GoodsAdvisoryView'),
            'fields'=> 'nick,atime,content,reply_time,reply_content',
            'group' => 'id',
            'orders'=> 'id desc',
            'p'     => I('post.p'),
            'ajax'  => 1,
            'page_js'  =>  'gotoPage($(this), \'advisory\');',
        ]);
        if (!empty($curretUser)) {
            foreach ($curretUser as $v) {
                array_unshift($list['list'], $v);  //往咨询列表前面加入当前用户未回复的咨询
            }
            unset($v, $curretUser);
        }
        
        if($list['list']) {
            $this->apiReturn(1, ['data' => $list, 'cates' => $cates]);
        }
        $this->apiReturn(3);
    }
    
    /**
     * 咨询分类
     */
    public function category() {
        $this->need_param=array('sign');
        $this->_need_param();
        $this->_check_sign();
        $data = M('goods_advisory_category')->where(['status' => 1])->getField('id,name', true);
        if ($data) {
            $this->apiReturn(1, ['data' => $data]);
        }
        $this->apiReturn(3);
    }
    
    /**
     * 分类介绍
     */
    public function categoryIntro() {
        $this->need_param=array('sign', 'id');
        $this->_need_param();
        $this->_check_sign();
        $id = I('post.id');
        if ($id > 0) {
            $content = M('goods_advisory_category')->where(['id' => $id, 'status' => 1])->getField('content');
            if ($content) {
                $this->apiReturn(1, ['data' => html_entity_decode($content)]);
            }
        }
        $this->apiReturn(3);
    }
    
    /**
     * 搜索
     */
    public function search() {
        $this->need_param=array('sign', 'id', 'q');
        $this->_need_param();
        $this->_check_sign();
        $attrListId = I('post.id', 0, 'int');
        $q = I('post.q');
        if ($attrListId > 0 && !empty($q)) {
            $map = [
                'attr_list_id' => $attrListId,
                'status' => 1,
                '_string' => 'content like %' . $q . '% OR reply_content like %' . $q . '%',
            ];
            $list = pagelist([
                'do'    => 'D',
                'table' => 'GoodsAdvisoryView',
                'map'   => $map,
                'pagesize'      => I('post.pagesize') > 0 ? I('post.pagesize') : 10,
                'cache_name'    => md5(implode(',', I('post.')) . 'GoodsAdvisoryView'),
                'orders'=> 'id desc',
                'p'     => I('post.p'),
            ]);
            if ($list) {
                $this->apiReturn(1, ['data' => $list]);
            }
            $this->apiReturn(3);
        }
        $this->apiReturn(0);
    }
    
    /**
     * 添加咨询
     */
    public function add() {
        $this->need_param=array('sign', 'id', 'openid', 'sid', 'goods_id', 'content');
        $this->_need_param();
        $this->_check_sign();
        $attrListId = I('post.id', 0, 'int');
        $sid = I('post.sid', 0, 'int');
        $data['attr_list_id']   = $attrListId;
        $data['uid']            = $this->uid;
        $data['content']        = I('post.content');
        $data['sid']            = $sid;
        $data['goods_id']       = M('goods_attr_list')->cache(true)->where(['id' => $attrListId])->getField('goods_id');
        if ($data['goods_id'] > 0) {
            $data['shop_id']        = M('goods')->cache(true)->where(['id' => $data['goods_id']])->getField('shop_id');
        }
        $model = D('GoodsAdvisory');
        if (!$aData = $model->create($data)) {
            $this->apiReturn(0, '', 1, $model->getError());
        }
        unset($data);
        if (!$model->add($aData)) {
            $this->apiReturn(0);
        }
        $this->apiReturn(1, '', 1, '提交成功，请静候佳音！');
    }
}