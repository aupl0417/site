<?php
// 本类由系统自动生成，仅供测试用途
namespace Zhaoshang\Widget;
use Think\Controller;
class WidgetWidget extends Controller {
    public function brand_category($ids){
        if(empty($ids)) return;
        $ids = explode(',',$ids);
        foreach($ids as $val){
            $rs = M('goods_category')->cache(true)->where(['id' => $val])->field('id,sid,category_name')->find();
            $us = M('goods_category')->cache(true)->where(['id' => $rs['sid']])->field('id,sid,category_name')->find();
            $html.='<li data-id="'.$rs['id'].'" data-first_id="'.$us['id'].'"><div class="action"><i class="fa fa-times text-danger" onclick="delete_select($(this))"></i></div>'.$us['category_name'].' > '.$rs['category_name'].'</li>';
        }

        return $html;
    }

    public function cred_images($images){
        if(empty($images)) return;
        $images = explode(',',$images);
        foreach($images as $val){
            $html.='<li data-url="'.$val.'">';
            $html.='<div class="action" onclick="delete_img($(this))"><i class="fa fa-times text-danger"></i></div>';
            $html.='<img src="'.myurl($val,80).'" alt="资质图片"></li>';
        }

        return $html;
    }
}
