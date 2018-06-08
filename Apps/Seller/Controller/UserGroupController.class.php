<?php
/**
 * Created by PhpStorm.
 * User: dttx
 * Date: 2017/3/23
 * Time: 11:22
 */

namespace Seller\Controller;
use Common\Builder\R;
use Common\Form\Form;

/**
 * 用户组
 *
 * Class UserGroupController
 * @package Seller\Controller
 */

class UserGroupController extends InitController
{

    protected function _initialize()
    {
        parent::_initialize();
        if (session('user.shop_type') == 6) {   //如果为个人店的话则执行跳转
            redirect(DM('zhaoshang', '/shopup'));
        }
    }

    /**
     * subject: 用户组管理
     * api: index
     * author: Mercury
     * day: 2017-03-23 11:23 
     * [字段名,类型,是否必传,说明]
     */
    public function index()
    {

        $config = [
            'url'   =>  '/SellerSubAccount/group',
            'isAjax'=>  false,
        ];
        $res = R::getInstance($config)->auth();
        $this->assign('data', $res['data']);
        //dump($res['data']);
        C('seo', ['title' => '用户组管理']);
        $this->display();
    }

    /**
     * subject: 创建用户组
     * api: create
     * author: Mercury
     * day: 2017-03-23 11:23
     * [字段名,类型,是否必传,说明]
     */
    public function create()
    {
        $config = [
            'action'    =>  __SELF__.'?ret=/SellerSubAccount/createGroup',
            'gourl'     =>  '"' . U('/userGroup') . '"',
        ];
        $model      = M('shop_auth_module');
        $map        = ['status' => 1];
        $functions  = $model->where($map)->field('id,title,sid')->order('sort asc')->select();
        $do         = M('shop_auth_function');
        $field      = 'id,page_name,inline';
        foreach ($functions as $k => &$v) {
            if ($v['sid'] > 0) {
                $v['child'] =   $do->field($field)->where(['cid' => $v['id'], 'status' => 1])->select();
                if ($v['child'] == false) unset($functions[$k]);    //如果没有可选项则剔除。
            }
        }
        $functions  = listToTree($functions);
        $form = Form::getInstance($config)
            ->text(['name' => 'group_name', 'title' => '账号组名称', 'require' => 1, 'validate' => ['required', 'rangelength' => '[1,10]']])
            ->shopAuthFunctions(['name' => 'fun_ids', 'title' => '选择', 'options' => $functions, 'require' => 1, 'validate' => ['required']])
            ->submit(['title' => '创建账号组'])
            ->create();
        $this->assign('form', $form);
        C('seo', ['title' => '创建账号组']);
        $this->display();
    }

    /**
     * subject: 修改
     * api: edit
     * author: Mercury
     * day: 2017-03-25 15:45
     * [字段名,类型,是否必传,说明]
     */
    public function edit()
    {
        $id     = I('get.id', 0, 'int');
        if ($id > 0) {
            $map    = [
                'uid'       => getUid(),
                'shop_id'   => getShopId(),
                'id'        => $id,
            ];
            $data   = M('shop_auth_group')->where($map)->find();
            if ($data == false) $this->redirect('/userGroup');
            $config = [
                'action'    =>  __SELF__.'?ret=/SellerSubAccount/editGroup',
                'gourl'     =>  '"' . U('/userGroup') . '"',
            ];
            $model      = M('shop_auth_module');
            $map        = ['status' => 1];
            $functions  = $model->where($map)->field('id,title,sid')->order('sort asc')->select();
            $do         = M('shop_auth_function');
            foreach ($functions as $k => &$v) {
                if ($v['sid'] > 0) {
                    $v['child'] =   $do->where(['cid' => $v['id'], 'status' => 1])->select();
                    if ($v['child'] == false) unset($functions[$k]);    //如果没有可选项则剔除。
                }
            }
            $functions  = listToTree($functions);
            $form = Form::getInstance($config)
                ->hidden(['name' => 'id', 'value' => $id])
                ->text(['name' => 'group_name', 'title' => '账号组名称', 'value' => $data['group_name'], 'require' => 1, 'validate' => ['required', 'rangelength' => '[1,10]']])
                ->shopAuthFunctions(['name' => 'fun_ids', 'title' => '选择', 'value' => $data['fun_ids'], 'options' => $functions, 'require' => 1, 'validate' => ['required']])
                ->submit(['title' => '修改账号组'])
                ->create();
            C('seo', ['title' => '修改账号组']);
            $this->assign('form', $form);
            $this->display();
        }
    }
}