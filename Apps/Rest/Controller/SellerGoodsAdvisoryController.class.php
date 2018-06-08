<?php
/**
 * 商家商品咨询管理
 */
namespace Rest\Controller;
class SellerGoodsAdvisoryController extends CommonController {
    protected $_typeArr = [1 => '待回复', 2 => '已回复'];
    
    /**
     * 列表
     */
    public function index() {
        $this->need_param=array('sign', 'openid');
        $this->_need_param();
        $this->_check_sign();
        
        $map = [
            'shop_id' => $this->user['shop_id'],
        ];
        
        $cates = M('goods_advisory_category')->cache(true)->where(['status' => 1])->order('id asc')->getField('id,name', true);
        
        //是否查询分类
        if (isset($_POST['type']) && array_key_exists(I('post.type'), $cates)) {
            $map['sid'] = I('post.type');
        }
        
        //状态
        $map['status'] = ['in', '1,2'];
        if (isset($_POST['status']) && !empty(I('post.status'))) {
            $map['status'] = I('post.status') == 1 ? 1 : 2;
        }
        
        //商品检索
        if (isset($_POST['goods']) && !empty(I('post.goods'))) {
            $map['goods_name'] = ['like', '%' . (string)I('post.goods') . '%'];
        }
        
        //用户
        if (isset($_POST['nick']) && !empty(I('post.nick'))) {
            $map['nick'] = ['like', '%' . (string)I('post.nick') . '%'];
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
        $list = pagelist([
            'do'    => 'D',
            'table' => 'GoodsAdvisoryView',
            'map'   => $map,
            'pagesize'      => I('post.pagesize') > 0 ? I('post.pagesize') : 10,
            'group' => 'id',
            //'cache_name'    => md5(implode(',', I('post.')) . 'GoodsAdvisoryView'),
            'orders'=> 'id desc',
            'action'=>I('post.action'),
            'p'     => I('post.p'),
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
     * 回复
     */
    public function reply() {
        $this->need_param=array('sign', 'openid', 'content', 'id');
        $this->_need_param();
        $this->_check_sign();
        $id = I('post.id', 1, 'int');
        if ($id > 0) {
            $model = D('GoodsAdvisoryReply');
            $data['reply_content']  = I('post.content');
            $data['reply_uid']      = $this->uid;
            $data['status']         = 2;
            $data['reply_ip']       = get_client_ip();
            $data['reply_time']     = date('Y-m-d H:i:s', NOW_TIME);
            $map = [
                'id'        => $id,
                'status'    => 1,
                'shop_id'   => $this->user['shop_id'],
            ];
            if (false == $model->create($data)) {
                $this->apiReturn(0, '', 1, $model->getError());
            }
            if ($model->where($map)->save()) {
                $this->apiReturn(1);
            }
        }
        $this->apiReturn(0);
    }
    
    /**
     * 修改
     */
    public function edit() {
        $this->need_param=array('sign', 'openid', 'content', 'id');
        $this->_need_param();
        $this->_check_sign();
        $id = I('post.id', 1, 'int');
        if ($id > 0) {
            $model = D('GoodsAdvisoryReply');
            $data['reply_content']  = I('post.content');
            $data['reply_uid']      = $this->uid;
            $data['status']         = 2;
            $map = [
                'id'        => $id,
                'status'    => 2,
                'shop_id'   => $this->user['shop_id'],
            ];
            if (!$aData = $model->create($data)) {
                $this->apiReturn(0, '', $model->getError());
            }
            unset($data);
            if ($model->where($map)->save($aData)) {
                $model->where($map)->setInc('edit_num', 1); //修改+1
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
            'shop_id' => $this->user['shop_id'],
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