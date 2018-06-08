<?php
namespace Seller\Controller;
use Home\Controller\CommonController;
class SupplierToolController extends CommonController {
    public function _initialize() {
        //parent::_initialize();
    }

    public function express() {
        $express_company_id    =   I('get.company');
        $express_code          =   I('get.code');
        $res                   =  $this->curl('/Express/query_express_aliyun', ['company_id' => $express_company_id, 'express_code' => $express_code], 1);
        $this->assign('rs', $res['data']);
        $this->display();
    }


    /**
     *
     * 快递公司
     *
     */
    public function expressCompany() {
        $data['q'] = I('get.q') ? : 'all';
        $this->api('/Express/search', $data)->with();
        //dump($this->_data['data']['list']);
        $this->display();
    }

    /**
     * subject: 个人店铺无权限
     * api: notAccess
     * author: Mercury
     * day: 2017-05-08 9:46
     * [字段名,类型,是否必传,说明]
     */
    public function notAccess()
    {
        $this->display();
    }
}