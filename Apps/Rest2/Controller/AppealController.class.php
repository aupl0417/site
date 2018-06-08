<?php
/**
 * ----------------------------------------------------------
 * RestFull API V2.0
 * ----------------------------------------------------------
 * 买家退款申诉
 * ----------------------------------------------------------
 * Author:梁丰
 * ----------------------------------------------------------
 * 2017-03-03
 * ----------------------------------------------------------
 */
namespace Rest2\Controller;
use Think\Controller\RestController;
class AppealController extends CommonController {
    protected $action_logs = array('refund_appeal');
    /**
     * subject: 提交申诉
     * api: /Appeal/refund_appeal
     * author: 梁丰
     * day: 2017-03-03
     *
     * [字段名,类型,是否必传,说明]
     * param: openid,string,1,用户openid
     * param: r_no,string,1,退款单号
     * param: s_no,string,1,订单单号
     * param: remark,string,1,申诉原因
     * param: images,string,0,图片链接 用,隔开
     */
    public function refund_appeal(){		
		$this->check($this->_field('images','openid,r_no,s_no,remark'));
		$data   =   ['uid' => $this->user['id'], 'check_type' => 2];		
		$res    =   (new \Common\Controller\AppealController(array_merge($data, I('post.'))))->run();
		$this->apiReturn($res);
    }
}