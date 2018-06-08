<?php
/*
+----------------------------
+ 我的团队
+-----------------------------
*/
namespace Rest\Controller;
use Think\Controller\RestController;
class TeamController extends CommonController {
    public function index(){
    	redirect(C('sub_domain.www'));
    }

    /**
    * 推荐人
    */
    public function team_total(){
        //频繁请求限制
        //$this->_request_check();

        //必传参数检查
        $this->need_param=array('openid','sign');
        $this->_need_param();
        $this->_check_sign();
        
        //直接推荐
        $do=M('user');
        $result['duser_num']=$do->where(array('up_uid'=>$this->uid))->count();

        //$rs=$do->where(array('id'=>$this->uid))->field('team_num')->find();
        //$result['team_num']=$rs['team_num'];
        //$result['jianjie_num']=$result['team_num']-$result['duser_num']-1;
        $result['team_num']=M('user_relation')->where(array('_string'=>'find_in_set ('.$this->uid.',upuid_list)'))->count();
        $result['jianjie_num']=$result['team_num']-$result['duser_num']-1;

        //$level=M('user_level')->cache(true,60)->order('sort asc')->getField('id,level_name,icon',true);
        $level=M('user_level')->cache(true,C('CACHE_LEVEL.M'))->order('sort asc')->Field('id,level_name,icon')->select();
        foreach($level as $key=>$val){
            $level[$key]['user_num']=$do->where(array('up_uid'=>$this->uid,'level_id'=>$val['id']))->count();
        }

        $result['level_total']=$level;

        $this->apiReturn(1,array('data'=>$result));
    }

    /**
    * 下线用户
    */
    public function downline_user(){
        //频繁请求限制
        //$this->_request_check();

        //必传参数检查
        $this->need_param = array('openid','level','sign');
        $this->_need_param();
        $this->_check_sign();

        $do = D('Common/LevelUserRelation');
        $level = I('post.level', 0, 'int');
        if( ! $level ){
            $level = "3,4,5,6";// 代理
        }else{
            $level = "$level";// 会员或者获取单个
        }
        $list = $do->relation(true)->cache(true,C('CACHE_LEVEL.XXS'))->relationWhere('User','up_uid='.$this->uid.' and level_id in ('.$level.')')->relationField('User','atime,nick,team_num-1 as team_num')->where(array('id'=>['in',$level]))->field('id,level_name')->find();
        //var_dump($do->getLastSQL());
        // $this->apiReturn(1,array('msg'=>$do->_sql(),'data'=>$list));
        if($list){
            $this->apiReturn(1,array('data'=>$list));
        }else{
            $this->apiReturn(3);
        }

    }



    
}