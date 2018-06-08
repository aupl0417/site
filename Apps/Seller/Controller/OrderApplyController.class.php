<?php
namespace Seller\Controller;
use Common\Form\Form;

class OrderApplyController extends AuthController {
    public function _initialize() {
        parent::_initialize();
    }
    
    public function index() {
        $this->authApi('/SellerRate/order_apply_list',['cid' => I('get.id')],"cid")->with();
		
        C('seo', ['title' => '第' . I('p', 1, 'int') . '页_刷单申诉']);
		//print_r($this->_data);
        $this->display();
    }

    /**
     *
     * 刷单申诉
     *
     */
    public function appeal() {
		$this->authApi('/SellerRate/view', ['id' => I('get.id')])->with();
		//dump($this->_data);
        $config = [
            'action' => U('/run/authrun'),
            'gourl'  => '"' . U('/OrderApply/index') . '"',
        ];

        $form = Form::getInstance($config)
			->hidden(['name' => 'c_id', 'value' =>$this->_data['data']['id']])
			->hidden(['name' => 'attr_list_id', 'value' =>$this->_data['data']['attr_list_id']])
            ->hidden(['name' => 's_no', 'value' => $this->_data['data']['s_no']])
            ->textarea(['name' => 'remark', 'title' => '申诉原因', 'require' => 1, 'validate' => ['required', 'rangelength' => '[5,300]']])
            ->mutilImages(['name' => 'images', 'title' => '图片'])
            ->submit(['title' => '提交申诉'])
            ->create();
        $this->assign('form', $form);
        $this->display();
    }
    /**
     * 评价详情
     */
    public function detail() {
        $this->authApi('/SellerRate/apply_view', ['id' => I('get.id')])->with();
		//print_r($this->_data);
        C('seo', ['title' => '刷单申诉详情']);
        $this->display();
    }
}