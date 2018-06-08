<?php
/**
 * Created by PhpStorm.
 * User: dttx
 * Date: 2017/3/21
 * Time: 10:49
 */

namespace Rest\Controller;


class GoodsComplaintsController extends CommonController
{

    protected $type = [
        1 => '虚假宣传',
        2 => '商品信息有误',
        3 => '滥发信息',
        4 => '商品更换宝贝',
        5 => '商标/品牌侵权',
        6 => '价格违规',
    ];

    protected $status = [
        1 => '未回复',
        2 => '已回复',
    ];

    /**
     * 举报列表
     *
     * @return mixed
     */
    public function index() {
        $this->need_param = array('openid', 'sign');
        $this->_need_param();
        $this->_check_sign();

        $map  = [
            'uid'   =>  $this->uid,
        ];

        if (isset($_POST['status'])) {
            $map['status'] = I('post.status');
        } else {
            $map['status'] = ['gt', 0];
        }

        //是否查询分类
        if (isset($_POST['type'])) {
            $map['type'] = I('post.type');
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
        $timeArr = ['complaints' => 'atime', 'reply' => 'reply_time'];
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
            'table'     =>  'GoodsComplaintsView',
            'do'        =>  'D',
            'pagelist'  =>  I('post.pagelist') ? : 20,
            'order'     =>  'id desc',
            'map'       =>  $map,
        ]);
        if ($list) {
            foreach ($list['list'] as &$v) {
                $v['statusName']= $this->status[$v['status']];
                $v['typeName']  = $this->type[$v['type']];
            }
            unset($v);
            $this->apiReturn(1, ['data' => $list]);
        }
        $this->apiReturn(3);
    }

    /**
     * 删除举报
     */
    public function del() {
        $this->need_param = array('id','openid', 'sign');
        $this->_need_param();
        $this->_check_sign();
        $data = ['status' => 0];
        if (M('goods_complaints')->where(['uid' => $this->uid, 'id' => I('post.id')])->save($data) == false) $this->apiReturn(0);
        $this->apiReturn(1);
    }

    /**
     * 举报详情
     */
    public function detail() {
        $this->need_param = array('id','openid', 'sign');
        $this->_need_param();
        $this->_check_sign();
        $map = [
            'uid'   =>  $this->uid,
            'id'    =>  I('post.id'),
            'status'=>  ['gt', 0]
        ];
        $data = D('GoodsComplaintsView')->where($map)->find();
        if ($data) {
            $data['typeName']   = $this->type[$data['type']];
            $data['statusName'] = $this->status[$data['status']];
            $this->apiReturn(1, ['data' => $data]);
        }
        $this->apiReturn(3);
    }
}