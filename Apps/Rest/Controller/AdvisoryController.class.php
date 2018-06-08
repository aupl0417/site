<?php
/**
 * 商品咨询-用户
 */
namespace Rest\Controller;
class AdvisoryController extends CommonController {
    
    protected $_typeArr = [1 => '待回复', 2 => '已回复'];
    
    /**
     * 列表
     */
    public function index() {
        $this->need_param=array('sign', 'openid');
        $this->_need_param();
        $this->_check_sign();
    
        $map = [
            'uid' => $this->uid,
        ];
    
        $cates = M('goods_advisory_category')->cache(true)->where(['status' => ['in', '1,2']])->order('id asc')->getField('id,name', true);
    
        //是否查询分类
        if (isset($_POST['type']) && array_key_exists(I('post.type'), $cates)) {
            $map['sid'] = I('post.type');
        }
    
        //状态
        $map['status'] = ['in', '1,2'];
        if (isset($_POST['status']) && !empty(I('post.status'))) {
            $map['status'] = I('post.status') == 1 ? 1 : 2;
        }
    
        //用户
        if (isset($_POST['goods']) && !empty(I('post.goods'))) {
            $map['goods_name'] = ['like', '%' . (string)I('post.goods') . '%'];
        }
        
        //商家
        if (isset($_POST['shop']) && !empty(I('post.shop'))) {
            $map['shop_name'] = ['like', '%' . (string)I('post.shop') . '%'];
        }
        
        //时间区间筛选
        $timeArr = ['advisory' => 'atime', 'reply' => 'reply_time'];
        if (isset($_POST['time_area']) && !empty(I('post.time_area'))) {
            //咨询时间
            if (!empty($_POST['sday']) || !empty($_POST['eday'])) {
                if (empty(I('post.sday'))) {
                    $map[$timeArr[I('post.time_area')]] = ['lt', I('post.eday')];
                } elseif (empty(I('post.eday'))) {
                    $map[$timeArr[I('post.time_area')]] = ['gt', I('post.sday')];
                } else {
                    $map[$timeArr[I('post.time_area')]] = ['between', I('post.sday') . ',' . I('post.eday')];
                }
            }
        }
        
    
        //回复时间
//         if (!empty($_POST['rsday']) || !empty($_POST['reday'])) {
//             if (empty(I('post.rsday'))) {
//                 $map['reply_time'] = ['lt', I('post.reday')];
//             } elseif (empty(I('post.reday'))) {
//                 $map['reply_time'] = ['gt', I('post.rsday')];
//             } else {
//                 $map['reply_time'] = ['between', I('post.rsday') . ',' . I('post.reday')];
//             }
//         }
    
    
        $list = pagelist([
            'do'    => 'D',
            'table' => 'GoodsAdvisoryView',
            'map'   => $map,
            'pagesize'      => I('post.pagesize') > 0 ? I('post.pagesize') : 10,
            //'cache_name'    => md5(implode(',', I('post.')) . 'GoodsAdvisoryView'),
            'group' => 'id',
            'orders'=> 'id desc',
            'p'     =>I('post.p'),
        ]);
        
        foreach ($list['list'] as &$v) {
            $v['status_name'] = $this->_typeArr[$v['status']];
            $v['type_name'] = M('goods_advisory_category')->cache(true)->where(['id' => $v['sid']])->getField('name');
        }
        unset($v);
        
        if($list) {
            $this->apiReturn(1, ['data' => $list, 'cates' => $cates]);
        }
        $this->apiReturn(3);
    }
    
    /**
     * 删除
     */
    public function del() {
        $this->need_param=array('sign', 'openid', 'id');
        $this->_need_param();
        $this->_check_sign();
        $id = I('post.id', 0, 'int');
        if ($id > 0) {
            $map = [
                'uid' => $this->uid,
                'id'  => $id,
                'status' => 1,
            ];
            
            $model = M('goods_advisory');
            if ($model->where($map)->setDec('status')) {
                $this->apiReturn(1);
            }
        }
        $this->apiReturn(0);
    }
    
    /**
     * 详情
     */
    public function view() {
        $this->need_param=array('sign', 'openid', 'id');
        $this->_need_param();
        $this->_check_sign();
        $id = I('post.id', 1, 'int');
        $model = D('GoodsAdvisoryView');
        $map = [
            'uid' => $this->uid,
            'status'  => ['in', '1,2'],
            'id'      => $id,
        ];
        $data = $model->where($map)->find();
        if ($data) {
            $data['status_name'] = $this->_typeArr[$data['status']];
            $data['type_name'] = M('goods_advisory_category')->cache(true)->where(['id' => $data['sid']])->getField('name');
            $this->apiReturn(1, ['data' => $data]);
        }
        $this->apiReturn(3);
    }
}