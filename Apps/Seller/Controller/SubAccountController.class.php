<?php
/**
 * Created by PhpStorm.
 * User: dttx
 * Date: 2017/3/22
 * Time: 15:02
 */

namespace Seller\Controller;


use Common\Builder\R;
use Common\Form\Form;

class SubAccountController extends InitController
{

    protected function _initialize()
    {
        parent::_initialize();
        if (session('user.shop_type') == 6) {   //如果为个人店的话则执行跳转
            redirect(DM('zhaoshang', '/shopup'));
        }
    }
    
    /**
     * subject: 子账号管理
     * api: index
     * author: Mercury
     * day: 2017-03-23 11:19
     * [字段名,类型,是否必传,说明]
     */
    public function index() {
        $config = [
            'url'   =>  '/SellerSubAccount/index',
            'isAjax'=>  false,
        ];
        $res = R::getInstance($config)->auth();
        $this->assign('data', $res['data']);
        C('seo', ['title' => '子账号管理']);
        $this->assign('function', __FUNCTION__);
        $this->display();
    }

    /**
     * subject: 创建子账号
     * api: create
     * author: Mercury
     * day: 2017-03-23 11:22
     * [字段名,类型,是否必传,说明]
     */
    public function create() {
        $config = [
            'action'    =>  U('/subAccount').'?ret=/Erp/sub_user',
            'gourl'     =>  '"' . U('/subAccount') . '"',
        ];
        $form = Form::getInstance($config)
            ->text(['name' => 'nick', 'title' => '账号昵称', 'require' => 1, 'validate' => ['required', 'rangelength' => '[1,6]']])
            ->password(['name' => 'password', 'title' => '账号密码', 'require' => 1, 'validate' => ['required', 'rangelength' => '[6,16]']])
            ->password(['name' => 'repassword', 'title' => '确认密码', 'require' => 1, 'validate' => ['required', 'equalTo' => '#password']])
            ->singleImages(['name' => 'photo', 'title' => '账户头像', 'require' => 1, 'validate' => ['required']])
            ->select(['name' => 'shop_auth_group_id', 'title' => '账号组', 'require' => 1, 'validate' => ['required'], 'options' => $this->getGroups()])
            ->submit(['title' => '创建账号'])
            ->create();
        $this->assign('form', $form);
        C('seo', ['title' => '创建子账号']);
        $this->display();
    }

    /**
     * subject: 修改所属用户组
     * api: changeGroup
     * author: Mercury
     * day: 2017-03-23 14:38
     * [字段名,类型,是否必传,说明]
     */
    public function changeGroup()
    {
        $config = [
            'action'    =>  U('/subAccount').'?ret=/SellerSubAccount/changeGroup',
        ];
        $form = Form::getInstance($config)
            ->hidden(['value' => I('get.id'), 'name' => 'id'])
            ->select(['name' => 'shop_auth_group_id', 'title' => '账号组', 'require' => 1, 'validate' => ['required'], 'options' => $this->getGroups()])
            ->submit(['title' => '立即修改'])
            ->create();
        C('seo', ['title' => '修改子账号所属账号组']);
        $this->assign('form', $form);
        $this->display();
    }

    /**
     * subject: 修改头像
     * api: changeAvatar
     * author: Mercury
     * day: 2017-03-25 16:16
     * [字段名,类型,是否必传,说明]
     */
    public function changeAvatar() {
        $config = [
            'action'    =>  U('/subAccount').'?ret=/Erp/changeAvatar',
        ];
        $map    = [
            'parent_uid'=>  session('user.id'),
            'id'        =>  I('get.id'),
            'status'    =>  1,
        ];
        $avatar = M('user')->where($map)->getField('face');
        $form = Form::getInstance($config)
            ->hidden(['value' => I('get.id'), 'name' => 'id'])
            ->singleImages(['name' => 'photo', 'title' => '账户头像', 'value' => $avatar, 'require' => 1, 'validate' => ['required']])
            ->submit(['title' => '立即修改'])
            ->create();
        C('seo', ['title' => '修改子账号头像']);
        $this->assign('form', $form);
        $this->display();
    }

    /**
     * subject: 修改密码
     * api: changePass
     * author: Mercury
     * day: 2017-03-25 16:16
     * [字段名,类型,是否必传,说明]
     */
    public function changePass() {
        $config = [
            'action'    =>  U('/subAccount').'?ret=/Erp/changePass',
        ];
        $form = Form::getInstance($config)
            ->hidden(['value' => I('get.id'), 'name' => 'id'])
            ->password(['name' => 'password', 'title' => '账号密码', 'require' => 1, 'validate' => ['required', 'rangelength' => '[6,16]']])
            ->password(['name' => 'repassword', 'title' => '确认密码', 'require' => 1, 'validate' => ['required', 'equalTo' => '#password']])
            ->submit(['title' => '立即修改'])
            ->create();
        C('seo', ['title' => '修改子账号密码']);
        $this->assign('form', $form);
        $this->display();
    }

    /**
     * subject: 申请子账号数量
     * api: apply
     * author: Mercury
     * day: 2017-03-25 11:24
     * [字段名,类型,是否必传,说明]
     */
    public function apply()
    {
        $config = [
            'action'    =>  U('/subAccount').'?ret=/SellerSubAccount/apply',
            'gourl'     =>  '"' . U('/subAccount/applyList') . '"',
        ];
        $form = Form::getInstance($config)
            ->number(['name' => 'num', 'title' => '申请增额数量', 'require' => 1, 'validate' => ['required', 'number', 'min' => 1, 'max' => 10]])
            ->textarea(['name' => 'reason', 'title' => '申请理由', 'require' => 1, 'validate' => ['required', 'rangelength' => '[30,200]']])
            ->submit(['title' => '立即申请'])
            ->create();
        $this->assign('form', $form);
        C('seo', ['title' => '申请子账号数量']);
        $this->display();
    }

    /**
     * subject: 修改申请
     * api: changeApply
     * author: Mercury
     * day: 2017-03-27 10:23
     * [字段名,类型,是否必传,说明]
     */
    public function changeApply()
    {
        $id = I('get.id', 0, 'int');
        if ($id > 0) {
            $map    = [
                'uid'   =>  getUid(),
                'id'    =>  $id,
            ];

            $data   = M('shop_auth_account_num_reply')->where($map)->find();
            if ($data) {
                $config = [
                    'action'    =>  U('/subAccount/changeApply').'?ret=/SellerSubAccount/changeApply',
                    'gourl'     =>  '"' . U('/subAccount/applyList') . '"',
                ];
                $form = Form::getInstance($config)
                    ->hidden(['name' => 'id', 'value' => $id])
                    ->number(['name' => 'num', 'title' => '申请增额数量', 'value' => $data['num'], 'require' => 1, 'validate' => ['required', 'number', 'min' => 1, 'max' => 10]])
                    ->textarea(['name' => 'reason', 'title' => '申请理由', 'value' => $data['reason'], 'require' => 1, 'validate' => ['required', 'rangelength' => '[30,200]']])
                    ->submit(['title' => '立即修改'])
                    ->create();
                $this->assign('form', $form);
                C('seo', ['title' => '申请子账号数量修改']);
                $this->display();
            }
        }
    }
    
    /**
     * subject: 申请列表
     * api: applyList
     * author: Mercury
     * day: 2017-03-27 9:29
     * [字段名,类型,是否必传,说明]
     */
    public function applyList()
    {
        $config = [
            'url'   =>  '/SellerSubAccount/applyList',
            'isAjax'=>  false,
        ];
        $res = R::getInstance($config)->auth();
        $this->assign('data', $res['data']);
        C('seo', ['title' => '子账号管理']);
        $this->assign('function', __FUNCTION__);
        $this->display();
    }

    /**
     * subject: 删除或冻结账户
     * api: delAccount
     * author: Mercury
     * day: 2017-03-27 9:14
     * [字段名,类型,是否必传,说明]
     */
    public function delAccount()
    {
        $config = [
            'action'    =>  U('/subAccount').'?ret=/Erp/delAccount',
        ];
        $form = Form::getInstance($config)
            ->hidden(['value' => I('get.id'), 'name' => 'id'])
            ->radio(['name' => 'status', 'title' => '类型', 'require' => 1, 'validate' => ['required'], 'options' => [-1 => '冻结', 2 => '删除']])
            ->password(['name' => 'password', 'title' => '账号密码', 'require' => 1, 'validate' => ['required', 'rangelength' => '[6,16]']])
            ->submit(['title' => '立即提交'])
            ->create();
        C('seo', ['title' => '删除子账号']);
        $this->assign('form', $form);
        $this->display();
    }
    
    /**
     * subject: 获取组
     * api: getGroups
     * author: Mercury
     * day: 2017-03-25 16:23
     * [字段名,类型,是否必传,说明]
     * @return mixed
     */
    private function getGroups()
    {
        $groups = M('shop_auth_group')->where(['uid' => getUid(), 'shop_id' => getShopId()])->getField('id,group_name');
        return $groups;
    }
}