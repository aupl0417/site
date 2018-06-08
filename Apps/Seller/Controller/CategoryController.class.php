<?php
namespace Seller\Controller;
class CategoryController extends AuthController {
    public function _initialize() {
        parent::_initialize();
    }
    
    public function index() {
        $this->authApi('/SellerGoods/category')->with();
        //$this->authApi('/SellerGoods/category_first');
        C('seo', ['title' => '分类管理']);
        $this->display();
    }
    
    /**
     * 添加或修改分类
     * @param string $id
     */
    public function add($id = null) {
        $header =   enCryptRestUri('/Category/add');
        if ($id > 0) {
            $this->authApi('/SellerGoods/category_view', ['id' => I('get.id')]);
            $data   =   $this->_data['data'];
            $header =   enCryptRestUri('/Category/edit');
        }
        $this->authApi('/SellerGoods/category_first');
        $options    =   [];
        foreach ($this->_data['data'] as $v) {
			if($v['id'] != $id){
				$options[$v['id']]  =   $v['category_name'];
			}
        }
        $this->builderForm()
        ->keyId()
        ->keySelect('sid', '选择上级分类', $options)
        ->keyText('category_name', '分类名称', 1)
        ->data($data)
        ->view();
        $this->assign('header', $header);
        C('seo', ['title' => '添加分类']);
        $this->display();
    }
}