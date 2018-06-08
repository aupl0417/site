<?php
namespace Seller\Controller;
use Common\Form\Form;

class CommentsController extends AuthController {
    public function _initialize() {
        parent::_initialize();
    }
    
    public function index() {
        $this->authApi('/SellerRate/rate_goods_list',['is_shuadan' => I('get.is_shuadan')],'is_shuadan')->with();
		if (session('user.nick') == 'ceshi4') dump($this->_data);
        C('seo', ['title' => '第' . I('p', 1, 'int') . '页_我的评价']);
		//print_r($this->_data);
        $this->display();
    }

    /**
     * 回复
     */
    public function reply() {
        $id = I('get.id', 0, 'int');
        if ($id > 0) {
            $this->authApi('/SellerRate/view', ['id' => $id])->with();
            if ($this->_data['code'] == 1) {
                $headers = enCryptRestUri('/Comments/reply');
                if (C('DEFAULT_THEME') == 'default') {
                    $data['comment_id'] =   $this->_data['data']['id'];
                    $this->builderForm()
                        ->keyId('comment_id')
                        ->keyTextArea('content', '回复内容', 1)
                        ->data($data)
                        ->view();
                    $this->assign('header', $headers);
                } else {
                    $config['action'] = U('/run/authrun');
                    $config['gourl'] = '"' . U('/comments/detail', ['id' => $id]) . '"';
                    $form = Form::getInstance($config)
                        ->hidden(['name' => 'comment_id', 'value' => $this->_data['data']['id']])
                        ->textarea(['name' => 'content', 'title' => '回复内容', 'require' => 1, 'validate' => ['required', 'rangelength' => '[5,500]']])
                        ->submit(['title' => '立即回复'])
                        ->create();
                    $this->assign('form', $form);
                }

            }
        }

        C('seo', ['title' => '回复评论']);

        $this->display();
    }

    /**
     * 评价详情
     */
    public function detail() {
        $this->authApi('/SellerRate/view', ['id' => I('get.id')])->with();
        C('seo', ['title' => '评价详情']);
        $this->display();
    }
}