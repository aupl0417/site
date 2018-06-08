<?php
/*
+--------------------------
+ 辅助内容
+--------------------------
*/
namespace Rest\Controller;
use Think\Controller\RestController;
class ToolsController extends CommonController {
    public function index(){
    	redirect(C('sub_domain.www'));
    }
    /*
    * 获取企业类型
    */
    public function companytype(){
        $this->_check_sign();   
         
        $do=M('company_type');
        $list=$do->cache(true,C('CACHE_LEVEL.L'))->field('id,type_name')->order('sort asc')->select();
        if($list){
            $this->apiReturn(1,array('data'=>$list));
        }else{
            $this->apiReturn(3);
        }

    }

    /**
    * 获取城市
    */
    public function city(){
        $this->_check_sign();

        $map['sid']=I('post.sid')?I('post.sid'):0;

        $cache_name='area_'.$map['sid'];
        $do=M('area');
        $list=$do->cache(true,C('CACHE_LEVEL.L'))->where($map)->order('sort asc')->field('id,a_name,a_postcode')->select();

        if(empty($list)) $this->apiReturn(3); //找不到记录
        $this->apiReturn(1,array('data'=>$list));
    }


    /**
    * 检查七牛图片尺寸
    * @param string $_POST['img'] 图片url
    * @param int    $_POST['width'] 宽度
    * @param int    $_POST['height'] 高度
    */
    public function qn_imgsize($img,$width,$height){
        $res=$this->curl_get($img.'?imageInfo');
        $res=json_decode($res);

        if($width==$res->width && $height==$res->height) return true;
        else return false;
    }
    
    /**
     * 获取一二级城市
     */
    public function one_two_city(){

        $this->_check_sign();
        $model = M('area');
        $list  = $model->cache(true,C('CACHE_LEVEL.L'))->order('sort asc')->where(['sid'=>0])->field('id,a_name,sid')->select();
        $ids = array();
        foreach($list as $ko => $vo){
            $ids[] = $vo['id'];
            $listnew[$vo['id']] = $vo;
            unset($list[$ko]);
        }
        $list = $listnew;
        $list2  = $model->cache(true,C('CACHE_LEVEL.L'))->order('sort asc')->where(array('sid'=>['in',$ids]))->field('id,a_name,sid')->select();
        foreach ($list2 as $k => $v) {
            $list[$v['sid']]['child'][] = $v;
        }
        $this->apiReturn(1,array('data'=>$list));
    }


    /**
    * 中文分词，只支持UTF8
    */
    public function scws(){
        $this->_check_sign();

        $word = $this->_scws(I('post.word'));
        $this->apiReturn(1,['data' => $word]);
    }


    /**
    * @param string $string 要进行分词的字符串,建议不要太长，以免影响效率
    */
    public function _scws($string){
        Vendor('phpanalysis.phpanalysis#class');
        $pa=new \PhpAnalysis();
        $pa->SetSource($string);
        $pa->resultType=2;
        $pa->differMax=true;
        $pa->StartAnalysis();
        $string=$pa->GetFinallyKeywords();

        return $string;     
    }
}