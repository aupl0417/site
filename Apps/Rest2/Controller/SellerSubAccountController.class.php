<?php
/**
 * Created by PhpStorm.
 * User: dttx
 * Date: 2017/3/23
 * Time: 9:53
 */

namespace Rest2\Controller;
use Think\Exception;

/**
 * 商家子账号
 *
 * Class SellerSubUserController
 * @package Rest2\Controller
 */

class SellerSubAccountController extends ApiController
{


    public function _initialize()
    {
        parent::_initialize();
        if ($this->user['shop_type'] == 6) $this->apiReturn(['code' => 0, 'msg' => '个人店不可进行此操作！']);
    }

    protected $action_logs      = array('createGroup','editGroup','changeGroup','delGroup','editAccount','apply','changeApply','delApply');
    /**
     * subject: 子账号列表
     * api: index
     * author: Mercury
     * day: 2017-03-23 11:45
     * [字段名,类型,是否必传,说明]
     */
    public function index() {
        $this->check($this->_field('openid'));
        $map = [
            'parent_uid'    =>  $this->user['id'],
            'status'        =>  ['in', '1,3']
        ];

        $list = pagelist([
            'table' =>  'ShopAuthSubAccountView',
            'do'    =>  'D',
            'map'   =>  $map,
            'order' =>  'id asc',
        ]);
        if ($list) $this->apiReturn(['code' => 1, 'data' => $list]);
        $this->apiReturn(['cdoe' => 3]);
    }

    /**
     * subject: 用户组列表
     * api: group
     * author: Mercury
     * day: 2017-03-23 11:45
     * [字段名,类型,是否必传,说明]
     */
    public function group()
    {
        $map  = [
            'uid'   =>  $this->user['id'],
        ];
        $list = pagelist([
            'table' =>  'shop_auth_group',
            'do'    =>  'M',
            'order' =>  'id asc',
            'pagelist' =>   $this->post['pagesize'] ? : 20,
            'map'   =>  $map,
        ]);
        $model= M('shop_auth_function');
        if ($list['list']) {
            foreach ($list['list'] as &$v) {
                $v['auths'] = join(',', $model->where(['id' => ['in', $v['fun_ids']]])->order('id')->getField('id,page_name', true));
            }
            unset($v);
            $this->apiReturn(['code' => 1, 'data' => $list]);
        }
        $this->apiReturn(['code' => 3]);
    }

    /**
     * subject: 创建用户组
     * api: createGroup
     * author: Mercury
     * day: 2017-03-23 11:45
     * [字段名,类型,是否必传,说明]
     */
    public function createGroup()
    {
        try {
            $map   = ['uid' => $this->user['id'], 'id' => $this->user['shop_id']];
            $max   = M('shop')->cache(true)->where($map)->getField('max_sub_group');
            $cnt   = M('shop_auth_group')->where($map)->count();
            if ($cnt >= $max) throw new Exception("您当前最多可创建{$max}个分组");
            $model = D('ShopAuthGroup');
            $this->post['uid']      = $this->user['id'];
            $this->post['shop_id']  = $this->user['shop_id'];
            //找出内联方法
            if ($model->create($this->post) == false) throw new Exception($model->getError());
            if ($model->add() == false) throw new Exception('创建组失败');
            $this->apiReturn(['code' => 1]);
        } catch (Exception $e) {
            $this->apiReturn(['code' => 0, 'msg' => $e->getMessage()]);
        }
    }

    /**
     * subject: 修改组
     * api: editGroup
     * author: Mercury
     * day: 2017-03-23 15:14
     * [字段名,类型,是否必传,说明]
     */
    public function editGroup()
    {
        try {
            $model = D('ShopAuthGroup');
            $this->post['uid']  = $this->user['id'];
            $this->post['shop_id']  = $this->user['shop_id'];
            if ($model->create($this->post) == false) throw new Exception($model->getError());
            if ($model->save() === false) throw new Exception('修改组失败');
            $this->apiReturn(['code' => 1]);
        } catch (Exception $e) {
            $this->apiReturn(['code' => 0, 'msg' => $e->getMessage()]);
        }
    }

    /**
     * subject: 修改用户所属分组
     * api: changeGroup
     * author: Mercury
     * day: 2017-03-25 16:50
     * [字段名,类型,是否必传,说明]
     */
    public function changeGroup()
    {
        try {
            $gMap = [
                'uid'   =>  $this->user['id'],
                'id'    =>  $this->post['shop_auth_group_id'],
            ];
            if (M('shop_auth_group')->where($gMap)->find() == false) throw new Exception('非法操作');
            $map = [
                'id'            =>  $this->post['id'],
                'parent_uid'    =>  $this->user['id'],
            ];
            if (M('user')->where($map)->save(['shop_auth_group_id' => $this->post['shop_auth_group_id']]) === false) throw new Exception('操作失败');
            $this->ajaxReturn(['code' => 1, 'msg' => '操作成功']);
        } catch (Exception $e) {
            $this->apiReturn(['code' => 0, 'msg' => $e->getMessage()]);
        }
    }

    /**
     * subject: 删除用户组
     * api: delGroup
     * author: Mercury
     * day: 2017-03-23 11:46
     * [字段名,类型,是否必传,说明]
     */
    public function delGroup()
    {
        try {
            $map = [
                'parent_uid'            =>  $this->user['id'],
                'shop_auth_group_id'    =>  $this->post['id'],
            ];
            if (M('user')->where($map)->find()) throw new Exception('当前组不能删，因为还有子账号属于当前组');
            if (M('shop_auth_group')->where(['id' => $this->post['id'], 'uid' => $this->user['id']])->delete() == false) throw new Exception('删除失败');
            $this->apiReturn(['code' => 1]);
        } catch (Exception $e) {
            $this->apiReturn(['code' => 0, 'msg' => $e->getMessage()]);
        }
    }

    /**
     * subject: 修改用户所属组
     * api: editAccount
     * author: Mercury
     * day: 2017-03-23 15:20
     * [字段名,类型,是否必传,说明]
     */
    public function editAccount()
    {
        try {
            $map = [
                'parent_uid'    =>  $this->user['id'],
                'id'            =>  $this->post['id'],
            ];
            if (M('shop_auth_group')->cache(true)->where(['uid' => $this->user['id'], 'id' => $this->post['group_id']]) ->find() == false) throw new Exception('你没有权限设置当前组');
            if (M('user')->where($map)->save(['shop_auth_group_id' => $this->post['group_id']]) === false) throw new Exception('修改失败');
        } catch (Exception $e) {
            $this->apiReturn(['code' => 0, 'msg' => $e->getMessage()]);
        }
    }

    /**
     * subject: 子账号申请增额
     * api: apply
     * author: Mercury
     * day: 2017-03-25 11:39
     * [字段名,类型,是否必传,说明]
     */
    public function apply()
    {
        try {
            $map = [
                'status'    =>  1,
                'uid'       =>  $this->user['id'],
            ];
            if (M('shop_auth_account_num_reply')->where($map)->find()) throw new Exception('您还有未审核的申请，请静候佳音');
            $model = D('ShopAuthAccountApplyNum');
            $this->post['uid']      =   $this->user['id'];
            $this->post['shop_id']  =   $this->user['shop_id'];
            if ($model->create($this->post) == false) throw new Exception($model->getError());
            if ($model->add() == false) throw new Exception('申请失败');
            $this->apiReturn(['code' => 1, 'msg' => '提交成功，请静候佳音']);
        } catch (Exception $e) {
            $this->apiReturn(['code' => 0, 'msg' => $e->getMessage()]);
        }
    }

    /**
     * subject: 修改申请
     * api: changeApply
     * author: Mercury
     * day: 2017-03-27 10:24
     * [字段名,类型,是否必传,说明]
     */
    public function changeApply()
    {
        try {
            $model = D('ShopAuthAccountApplyNum');
            $this->post['status']   =   1;
            $map   = [
                'uid'       =>  $this->user['id'],
                'shop_id'   =>  $this->user['shop_id'],
                'id'        =>  $this->post['id'],
                'status'    =>  ['in', '1,3']
            ];
            if ($model->create($this->post) == false) throw new Exception($model->getError());
            if ($model->where($map)->save() == false) throw new Exception('修改申请失败');
            $this->apiReturn(['code' => 1, 'msg' => '提交成功，请静候佳音']);
        } catch (Exception $e) {
            $this->apiReturn(['code' => 0, 'msg' => $e->getMessage()]);
        }
    }

    /**
     * subject: 申请列表
     * api: applyList
     * author: Mercury
     * day: 2017-03-27 9:31
     * [字段名,类型,是否必传,说明]
     */
    public function applyList()
    {
        $map  = [
            'uid'   =>  $this->user['id'],
            'status'=>  ['gt', 0],
        ];
        $list = pagelist([
            'table' =>  'shop_auth_account_num_reply',
            'do'    =>  'M',
            'pagelist'  =>  $this->post['pagelist'] ? : 20,
            'order' =>  'id desc',
            'map'   =>  $map,
            //'cache' =>  md5(join(',', $this->post) . $this->_user(['id']) . 'applyList'),
        ]);
        if ($list['list']) {
            $status = [1 => '待审核', 2 => '审核已通过', 3 => '审核不通过'];
            foreach ($list['list'] as &$v) {
                $v['statusName'] = $status[$v['status']];
            }
            unset($v);
            $this->apiReturn(['code' => 1, 'data' => $list]);
        }
        $this->apiReturn(['code' => 3]);
    }

    /**
     * subject: 删除申请
     * api: delApply
     * author: Mercury
     * day: 2017-03-27 9:39
     * [字段名,类型,是否必传,说明]
     */
    public function delApply()
    {
        $map = [
            'uid'   =>  $this->user['id'],
            'status'=>  ['gt', 0],
            'id'    =>  $this->post['id'],
        ];
        if (M('shop_auth_account_num_reply')->where($map)->save(['status' => 0]) == false) throw new Exception('删除失败');
        $this->apiReturn(['code' => 1]);
    }
}