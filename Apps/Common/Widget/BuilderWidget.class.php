<?php
/**
// +----------------------------------------------------------------------
// | YiDaoNet [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) www.yunlianhui.com  All rights reserved.
// +----------------------------------------------------------------------
// | Author: Mercury <mercury@jozhi.com.cn>
// +----------------------------------------------------------------------
// | Create Time: 2016 下午4:01:06
// +----------------------------------------------------------------------
 */
namespace Common\Widget;
use Think\Controller;
class BuilderWidget extends Controller {
    /**
     * 生成form表单
     * @param array $data       表单数据        必填
     * @param string $run       提交地址        默认为     home/run/index
     * @param string $resturl   action  默认为当前action
     * @param string $name      表单名称      默认为formadd
     * @param string $clickClass点击事件class名称     默认无
     * @param string $nosign    不需要签名的字段,多个请使用逗号隔开
     */
    public function form($param) {
        if ($param['self'] == 'yes') {
            $run    =   __SELF__;
        } else {
            $run    =   $param['run'] ? $param['run'] : '/run';
        }
        $name   =   $param['name'] ? $param['name'] : 'formadd';
        //$nosign =   !empty($param['nosign']);
        if ($param['nosign']) {
            $nosign =   enCryptRestUri($param['nosign']);
            $this->assign('nosign', $nosign);
        }
        $this->assign('type', $param['type']);
        $this->assign('clickClass', $param['clickClass']);
        $this->assign('keyList', $param['data']);
        $this->assign('name', $name);
        $this->assign('run', $run);
        $this->display(T('Common@Widget/form'));
    }
    
    /**
     * 搜索
     */
    public function search($shop = null) {
        $from   =   enCryptRestUri(MODULE_NAME . __ACTION__);
        //$keywords   =   array('纯露', '按摩器', '个人护理', '远红外', 'iPhone');
        $pData['nosign']=   'limit,nosign';
        $pData['limit'] =   12;
        $data   =   $this->curl('/Goods/hot_keywords',$pData,1);
        $this->assign('hot_keywords', $data['data']);
        $this->assign('from', $from);
        $this->assign('action', __ACTION__);
        $s  =   'search';   //全局搜索
        if (MODULE_NAME == 'Item' || MODULE_NAME == 'Shop') $s  =   'storeSearch';  //搜当前店铺
        $this->assign('shop', $shop);
        $this->display(T('Common@Widget/' . $s));
    }
    
    /**
     * 百度编辑器
     * @param unknown $param
     * @param string  $name 变量名必须
     * @param string  $value 值可选
     */
    public function ueditor($param, $isFetch = false) {
        $this->assign('param', $param);
        if ($isFetch) return $this->fetch(T('Common@Widget/ueditor'));
        $this->display(T('Common@Widget/ueditor'));
    }
    
    /**
     * 城市下拉菜单
     * @param array $param
     * @param integer $province
     * @param integer $city
     * @param integer $district
     * @param integer $twon
     */
    public function chinaCity($param = array(), $isFetch = false) {
        $data   =   getApiCfg();
        ksort($data);
        $data['sign']   =   _sign($data);
        $datas  =   $this->curl('/Tools/city', $data, 1);
        $this->assign('data', $datas['data']);
        if ($param['value']) {
            $data['sid']    =   $param['value']['province'];
            $datas  =   $this->curl('/Tools/city', $data, 1);
            $this->assign('city', $datas['data']);
            $data['sid']    =   $param['value']['city'];
            $datas  =   $this->curl('/Tools/city', $data, 1);
            $this->assign('district', $datas['data']);
            $data['sid']    =   $param['value']['district'];
            $datas  =   $this->curl('/Tools/city', $data, 1);
            $this->assign('town', $datas['data']);
        }
        $action =   enCryptRestUri('/Tools/city');
        $this->assign('val', $param['value']);
        $this->assign('name', $param['name']);
        $this->assign('action', $action);
        if ($isFetch) return $this->fetch(T('Common@Widget/chinaCity'));
        $this->display(T('Common@Widget/chinaCity'));
    }
    
    /**
     * 提示
     * @param string $param
     */
    public function nors($param = null) {
        $text   =   !empty($param['text']) ? $param['text'] : '暂无记录';
        $this->assign('text', $text);
        $this->display(T('Common@Widget/nors'));
    }

    /**
    * 表单生成器 - by enhong
    * @param array $param 字段选项
    * @param array $data 值
    */
    public function buildform($param,$data){
        $html=array();
        $form=new \Org\Util\BuildForm();
        $form->value=$data;
        foreach($param as $key=>$val){
            if(substr($key,0,5)=='field'){
                foreach($val as $vkey=>$val){
                    $html[]=$form->$val['formtype']($val)->create();
                }
            }
        }
        $html=@implode('',$html);
        echo $html;
    }    
}