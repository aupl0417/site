<?php

namespace Rest\Controller;
use Think\Controller\RestController;
use Rest\Controller\TjController as Tj;

/**
* RestFull API
* 广告管理
* Author: lazycat <673090083@qq.com>
*/

class AdController extends CommonController {

    private $_ads = [];

    public function index(){
    	redirect(C('sub_domain.www'));
    }

    /**
    * 获取某个广告位图片
    * @param int    $_POST['position_id']   广告位ID
    */

    public function ad(){
        //必传参数检查
        $this->need_param=array('position_id','sign');
        $this->_need_param();
        $this->_check_sign();
        
		$res=$this->_ad(I('post.position_id'));
        # $this->recordShow();
		$this->apiReturn($res['code'],$res['data']);
    }
	
	/**
	* 同时获取多个广告位
    * @param int    $_POST['position_id']   广告位ID
	*/
	public function ads(){
        //必传参数检查
        $this->need_param=array('position_id','sign');
        $this->_need_param();
        $this->_check_sign();

		$position_id=explode(',',I('post.position_id'));
		foreach($position_id as $val){
			$list[$val]=$this->_ad($val)['data'];
		}
		# $this->recordShow();
		$this->apiReturn(1,array('data'=>$list));
	}
	
    # private function recordShow(){
    #     if($this->_ads){
    #         Tj::ad_show_update($this->_ads);
    #         $this->_ads = [];
    #     }
    # }


	/**
	* 获取某个广告位图片
	* @param int $position_id 广告位ID
	*/
	public function _ad($position_id){
        $do=D('Common/PositionRelation');
        $prs=$do->relation(true)->relationWhere('ad','status=1 and (is_default=1 or FIND_IN_SET("'.date('Y-m-d').'",days))')->where(array('id'=>$position_id))->field('id,position_name,type,num,default_images,url,width,height,is_seat,device,content,background_width,background_height')->find();
        //dump($prs);

        //用户投放 $adlist
        //默认广告 $default
        foreach($prs['ads'] as $key=>$val){
            if($val['is_default']==1) $default[$val['sort']]=$val;
            else $adlist[$val['sort']]=$val;
        }
        for($i=0;$i<$prs['num'];$i++){
            $tmp = [
                'url'       => $prs['url'],
                'images'    => $prs['default_images'],
                'name'      => $prs['content'],
                'background_images' => $prs['background_images'],
            ];
            $ads[$i] = isset($adlist[$i]) ? $adlist[$i] : ($default[$i] ? $default[$i] : $tmp);
        }
        
        foreach ($ads as &$v) {
            if (!empty($v['goods_id'])) {
                $v['goods_price'] = M('goods')->where(['id' => $v['goods_id']])->getField('price');
            }
        }
        unset($v);
        $prs['ads']=$ads;
        if($ads){
            # # PC的已用脚本统计广告展现
            # if($prs['device'] == 2){
            #     $this->_ads[] = $position_id;
            # }
            return array('code'=>1,'data'=>array('data'=>$prs));
        }else{
            return array('code'=>3);
        }
	}

    /**
    * 广告资源
    * @param string $_POST['openid']    用户openid
    */
    public function position_list2(){
        //频繁请求限制,间隔2秒
        $this->_request_check();

        //必传参数检查
        $this->need_param=array('sign');
        $this->_need_param();
        $this->_check_sign();

        $typeArr = [1,2];   //只能头发图片广告
        //if(I('post.status')!='') $map['status']=I('post.status');
        $device_name    = [1 => 'PC端', 2 => '移动端'];
        $type_name      = ['','图片','焦点图'];
        $map['type']    = ['lt', 3];
        if(I('post.device')!='') $map['device']     =I('post.device');
        if(I('post.type')!='' && in_array(I('post.type'), $typeArr)) $map['type']         =I('post.type');
        if(I('post.channel')!='') $map['channel']   =I('post.channel');
        if(I('post.size')!=''){
            $size=explode('x',I('post.size'));
            $map['width']   =$size[0];
            $map['height']  =$size[1];
        }
        
        $map['status']  = 1;
        if(I('post.category_id')!='') $map['_string']='FIND_IN_SET ("'.I('post.category_id').'",category_id)';

        $pagesize=I('post.pagesize')?I('post.pagesize'):20;
        $pagelist=pagelist(array(
                'table'         =>'ad_position',
                'map'           =>$map,
                'order'         =>'id desc',
                'fields'        =>'etime,ip,default_images,url',
                'fields_type'   =>true,
                'pagesize'      =>$pagesize,
                'action'        =>I('post.action'),
                'query'         =>I('post.query'),
                'p'             =>I('post.p'),
                'cache_name'    =>md5(implode(',', $_POST).__SELF__),
            ));
        if($pagelist['list']){
			$goods_category	= $this->cache_table('goods_category');
            foreach($pagelist['list'] as $i => $val){
                $pagelist['list'][$i]['type_name']      =$type_name[$val['type']];
                $pagelist['list'][$i]['device_name']    =$device_name[$val['device']];

                $val['category_id']  =explode(',',$val['category_id']);
                foreach($val['category_id'] as $v){
                    $pagelist['list'][$i]['category_name'][]	=	$goods_category[$v];
                }
            }
            $this->apiReturn(1,array('data'=>$pagelist));
        }else{
            $this->apiReturn(3);
        }
    }

    /**
    * 广告位详情
    * @param int    $_POST['id']    广告位ID
    */
    public function position_view(){
        //频繁请求限制,间隔2秒
        $this->_request_check();

        //必传参数检查
        $this->need_param=array('id','sign');
        $this->_need_param();
        $this->_check_sign();

        $device_name    = [1 => 'PC端', 2 => '移动端'];
        $type_name      = ['','图片','焦点图'];

        $rs=M('ad_position')->cache(true,C('CACHE_LEVEL.OneDay'))->where(['id' => I('post.id')])->field('atime,etime,ip,default_images,url,is_seat',true)->find();

        if(!$rs) $this->apiReturn(3);   //广告位不存在
        $rs['device_name']  =$device_name[$rs['device']];
        $rs['type_name']    =$type_name[$rs['type']];

        $category_id  =explode(',',$rs['category_id']);
		$goods_category	= $this->cache_table('goods_category');
        foreach($category_id as $v){
            $rs['category_name'][]	=	$goods_category[$v];
        }
        //取最近一个月内不可购买的日期
        $sort = I('post.sort', 0, 'int');
        if($sort > $rs['num'] - 1 || $sort < 0){
            $sort = 0;
        }
        $result=ad_days_check('',$rs['id'],$sort,1);

        //生成最近一年日历
        $calendar=calendar(array('days'=>$result['days_use'],'isuse'=>1));


        $this->apiReturn(1,['data' => $rs,'calendar' => $calendar,'days_use' => $result['days_use']]);
    }

    /**
    * 广告搜索条件
    */
    public function condition(){
        //必传参数检查
        $this->need_param=array('sign');
        $this->_need_param();
        $this->_check_sign();

        $data['device']=[
            ['name' => 'PC端','value' => 1],
            ['name' => '移动端','value' => 2],
        ];

        $do=M('ad_position');
        $data['size']=$do->cache(true,C('CACHE_LEVEL.OneDay'))->where(['status' =>1])->distinct(true)->field('concat(width,"x",height) as name,concat(width,"x",height) as value')->order('width asc')->select();
        $data['bsize']=$do->cache(true,C('CACHE_LEVEL.OneDay'))->where(['status' =>1, 'background_width' => ['gt', 0], 'background_height' => ['gt', 0]])->distinct(true)->field('concat(background_width,"x",background_height) as b_name,concat(background_width,"x",background_height) as b_value')->order('width asc')->select();
        $this->apiReturn(1,['data' => $data]);
    }

    /**
    * 投放类目
    */
    public function category(){
        //必传参数检查
        $this->need_param=array('sign');
        $this->_need_param();
        $this->_check_sign();

        $list=get_category([
            'table'         =>'goods_category',
            'field'         =>'id,sid,category_name',
            'level'         =>2,
            'cache_name'    =>'goods_category_level2',
            'map'           =>[['status'=>1],['status'=>1]],
            'cache_name'    =>'ad_goods_category',
            ]);

        if($list) $this->apiReturn(1,['data' => $list]);
        else $this->apiReturn(3);
    }
    
    /*
     * ********************************************************************
     */
    public function position_list(){
    	$this->need_param=array('device','sign');
    	$this->_need_param();
    	$this->_check_sign();
    	
    	$device=I('post.device',1);
    	$where['status']=1;
    	$where['device']=$device;
    	
    	$list=M('ad_position')->field('id,position_name,type,device,num,channel,price,width,height,category_id,content,background_width,background_height')->where($where)->select();
    	if ($list){
    		$data=[];
    		$goods_category	= $this->cache_table('goods_category');
    		foreach ($list as $k=>$v){
    			$catearr=explode(',',$v['category_id']);
    			foreach ($catearr as $vv){
    				$v['cate_name'][]=$goods_category[$vv];
    			}
    			$v['cate_name']=implode('，', $v['cate_name']);
    			$data[$v['id']]=$v;
    		}
    		$this->apiReturn(1,['data'=>$data]);
    	}else {
    		$this->apiReturn(3);
    	}
    }
}