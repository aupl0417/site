<?php
namespace Seller\Controller;
use Common\Form\Form;

class ProtectionController extends AuthController {
    public function _initialize() {
        parent::_initialize();
    }
    
    public function index() {
        $data   =   M('goods_protection')->where(['uid' => getUid()])->field('protection_name,atime,id')->order('id desc')->select();
        $this->assign('data', $data);
        C('seo', ['title' => '售后模板管理']);
        $this->display();
    }
    
    public function add($id = null) {
        $title  =   '添加';
        if (intval($id) > 0) {
            $title  =   '编辑';
        }
        if (IS_POST) {
            $data   =   I('post.');
            $model  =   D('Common/Protection');
            if (!$model->token(false)->create($data)) {
                $this->ajaxReturn(['code' => 0, 'msg' => $model->getError()]);
            }
            $flag   =   $id > 0 ? $model->save() : $model->add();
            $flag ? $this->ajaxReturn(['code' => 1, 'msg' => $title . '成功']) : $this->ajaxReturn(['code' => 0, 'msg' => $id > 0 ? '无任何修改' : $title . '失败']);
        } else {
            $data   =   [];
            if ($id > 0) {
                $data   =   M('goods_protection')->where(['id' => $id, 'uid' => getUid()])->find();
                $data['content']    =   html_entity_decode($data['content']);
            }
            $this->builderForm()
            ->keyId()
            ->keyText('protection_name', '售后模板名称', 1)
            ->keyUeditor('content', '售后模板详情', '', 1, '没写过？<a data-type="vmodal" data-title="售后模板" data-url="'.U('/protection/template').'" class="text_yellow" href="javascript:;">查看模板</a>')
            ->data($data)
            ->view();
            $this->assign('title', $title);
            C('seo', ['title' => $title . '售后模板']);
            $this->display();
        }
    }

    /**
     *
     * 创建售后模板
     *
     */
    public function create()
    {
        $id = I('get.id', 0, 'int');
        $data   =   [];
        $title  =   '创建售后模板';
        if ($id > 0) {
            $data   =   M('goods_protection')->where(['id' => $id, 'uid' => getUid()])->find();
            $data['content']    =   html_entity_decode($data['content']);
            $title = '修改售后模板';
        }

        $config = [
            'action' => U('/protection/save'),
            'gourl'  => '"' . U('/protection') . '"',
        ];
        $form = Form::getInstance($config)
            ->hidden(['name' => 'id', 'value' => $data['id']])
            ->text(['name' => 'protection_name', 'title' => '模板名称', 'value' => $data['protection_name'], 'require' => 1, 'validate' => ['required', 'rangelength' => '[1,20]']])
            ->ueditor(['name' => 'content', 'title' => '模板详情', 'value' => $data['content'], 'require' => 1, 'validate' => ['required', 'ranglength' => '[5,1000]']])
            ->submit(['title' => $title])
            ->create();

        $this->assign('form', $form);
        C('seo', ['title' => '售后模板']);
        $this->assign('title', $title);
        $this->display();
    }


    /**
     *
     * 保存
     *
     */
    public function save() {
        if (IS_POST) {
            $data   =   I('post.');
            $id     =   $data['id'];
            $model  =   D('Common/Protection');
            if (!$model->token(false)->create($data)) {
                $this->ajaxReturn(['code' => 0, 'msg' => $model->getError()]);
            }
            $flag   =   $id > 0 ? $model->save() : $model->add();
            $flag ? $this->ajaxReturn(['code' => 1, 'msg' => '操作成功']) : $this->ajaxReturn(['code' => 0, 'msg' => $id > 0 ? '无任何修改' : '操作失败']);
        }
    }

    public function del() {
        $id =   I('post.id');
        if ($id) {
            $flag   =   M('goods_protection')->where(['id' => ['in', $id], 'uid' => getUid()])->delete();
            $flag   ?   $this->ajaxReturn(['code' => 1, 'msg' => '删除成功']) : $this->ajaxReturn(['code' => 0, 'msg' => '删除失败']);
        }
    }
    
    /**
     * 模板
     */
    public function template() {
        $this->display();
    }
}