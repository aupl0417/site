<?php
/**
 * 
 */
namespace Common\Widget;
use Think\Controller;
class AdWidget extends Controller {


    public function index($position_id, $style = "left:50%; margin-left:-960px") {
        $data   =   $this->_ad($position_id);
        $this->assign('data', $data);
        $this->assign('style', $style);
        $this->display(T('Common@Widget:ad'));
    }
    
    public function index_new($position_id, $style = "left:50%; margin-left:-960px") {
        $data   =   $this->_ad($position_id);
        $this->assign('data', $data);
        $this->assign('style', $style);
        $this->display(T('Common@Widget:ad_new'));
    }


    /**
     * 获取广告
     * @param unknown $position_id
     * @return multitype:unknown multitype:unknown
     */
    private function _ad($position_id) {
        $do=D('Common/PositionRelation');
        if (strpos($position_id, ',') !== false) {
            $map['id']  =   ['in', $position_id];
            $prs=$do->relation(true)->cache(true,C('CACHE_LEVEL.S'))->relationWhere('ad','status=1')->where($map)->field('id,position_name,type,num,default_images,url,width,height,is_seat')->select();
        } else {
            $map['id']  =   $position_id;
            $prs=$do->relation(true)->cache(true,C('CACHE_LEVEL.S'))->relationWhere('ad','status=1')->where($map)->field('id,position_name,type,num,default_images,url,width,height,is_seat')->find();
        }
        
        
        //用户投放 $adlist
        //默认广告 $default
        foreach($prs['ads'] as $key=>$val){
            if($val['is_default']==1) $default[$val['sort']]=$val;
            else $adlist[$val['sort']]=$val;
        }
        $ad=array();
        if(empty($adlist)) $adlist=$default;
        else{
            if($prs['is_seat']==1){    //无广告时启用占位图
                for($i=0;$i<$prs['num'];$i++){
                    if($adlist[$i]) $ad[]=$adlist[$i];
                    elseif($default[$i]) $ad[]=$default[$i];
                    else $ad[]=array('images'=>$prs['default_images'],'url'=>$prs['url']);
                }
            }else{
                for($i=0;$i<$prs['num'];$i++){
                    if($adlist[$i]) $ad[]=$adlist[$i];
                    elseif($default[$i]) $ad[]=$default[$i];
        
                }
            }
        }
        $prs['ads']=$ad;
        return $prs;
    }
}