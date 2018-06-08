<?php
/**
// +----------------------------------------------------------------------
// | YiDaoNet [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) www.yunlianhui.com  All rights reserved.
// +----------------------------------------------------------------------
// | Author: Mercury <mercury@jozhi.com.cn>
// +----------------------------------------------------------------------
// | Create Time: 2016 下午4:48:25
// +----------------------------------------------------------------------
 */
namespace Faq\Controller;
use Home\Controller\CommonController;
class ViewController extends CommonController {
    public function index() {
        $id     =   I('get.id');
        $this->api('/help/view', array('id' => $id))->with();
        if ($this->_data['data']) {
            $this->seo(['title' => $this->_data['data']['name'] . ' - 帮助中心']);
            C('seo', ['title' => $this->_data['data']['name'] . '_帮助中心']);
            $res_category_buyer = $this->api('/Help/category_buyer')->with('category_buyer');
            $res_category_seller = $this->api('/Help/category_seller')->with('category_seller');
            $this->display();
        } else {
            C('seo', ['title' => 'sorry你访问的页面不存在']);
            $this->display(T('Home@Empty:404'));
        }
    }
}