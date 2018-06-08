<?php
/**
+----------------------------------------------------------------------
| RestFull API 
+----------------------------------------------------------------------
| 品牌频道各功能接口
+----------------------------------------------------------------------
| Author: lazycat <673090083@qq.com>
+----------------------------------------------------------------------
*/
namespace Rest\Controller;
use Think\Controller\RestController;
class BrandController extends CommonController {
    public function index(){
    	redirect(C('sub_domain.www'));
    }
    
    /**
    * 推荐品牌
    * @param int $_POST['limit']    数量
    */
    public function best_brand(){
        //必传参数检查
        $this->need_param=array('sign');
        $this->_need_param();
        $this->_check_sign();

        $map['status']  =1;
        $map['is_best'] =1;
        $limit=I('post.limit')?I('post.limit'):12;
        $count=M('brand_ext')->where($map)->count();
        if(($count-$limit)>0) $limit=rand(0,$count-$limit).','.$limit;        

        $do=D('BrandExtShopRelation');
        $list=$do->relation(true)->cache(true)->where($map)->field('uid,brand_id,name,ename,logo,images,about,tag,shop_id')->limit($limit)->select();

        if($list){
            foreach($list as $i => $val){
                $list[$i]['shop_url']=shop_url($val['shop']['id'],$val['shop']['domain']);
            }

            $this->apiReturn(1,['data' => $list]);
        }else $this->apiReturn(3);
    }

    /**
    * 品牌标签
    */
    public function brand_tags(){
        //必传参数检查
        $this->need_param=array('sign');
        $this->_need_param();
        $this->_check_sign();

        $list=M('brand_tags')->cache(true)->where(['status' => 1])->field('id,tag_name')->order('sort asc')->select();
        if($list){
            $this->apiReturn(1,['data' => $list]);
        }else $this->apiReturn(3);
    }

    /**
     * 品牌列表
     */
    public function brand_list(){
        //必传参数检查
        $this->need_param=array('sign');
        $this->_need_param();
        $this->_check_sign();
        
        $map['status']  =1;
        
        $map['tag']=['like','%'.I('post.tag').'%'];
        
        
        $pagesize=I('post.pagesize')?I('post.pagesize'):20;
        $pagelist=pagelist(array(
                'table'     =>'BrandExtShopRelation',
                'do'        =>'D',
                'map'       =>$map,
                'order'     =>'atime desc',
                'fields'    =>'uid,brand_id,name,ename,logo,images,about,tag,shop_id',
                'pagesize'  =>$pagesize,
                'relation'  =>true,
                'action'    =>I('post.action'),
                'query'     =>I('query'),
                'p'         =>I('post.p'),
                'cache_name'=>md5(implode(',',$_POST).__SELF__),
                'cache_time'=>C('CACHE_LEVEL.L'),
            ));
        
        if($pagelist){
            foreach($pagelist['list'] as $i => $val){
                $pagelist['list'][$i]['shop_url']=shop_url($val['shop']['id'],$val['shop']['domain']);
            }

            $this->apiReturn(1,['data' => $pagelist]);
        }else $this->apiReturn(3);
    }
    
    /**
     * 品牌推广图片
     */
    public function brandAdsByPosition(){
    	//必传参数检查
    	$this->need_param=array('position_id','field','limit','sign');
    	$this->_need_param();
    	$this->_check_sign();
    	
    	$position_id=I('post.position_id',0);
    	$field=I('post.field',true);
    	$limit=I('post.limit',22);
    	
    	//and (is_default=1 or FIND_IN_SET("'.date('Y-m-d').'",days))
    	$result=D('Ad')->cache(true)->field($field)->where('position_id='.$position_id.' and status=1 ')->order('sort')->limit($limit)->select();
    	if ($result){
    		$this->apiReturn(1,['data'=>$result]);
    	}else {
    		$this->apiReturn(3);
    	}
    }
    
    /**
     * 品牌头条&关注
     */
    public function brandTop(){
    	$this->_request_check();
    	$this->need_param=array('pm','pms','gz','uid','sign');
    	$this->_need_param();
    	$this->_check_sign();
    	
    	$p=I('post.pm',0);
    	$pagesize=I('post.pms',16);
    	$gz=I('post.gz',0);
    	$uid=I('post.uid',0);
    	$favarr=array();$shopfavalllist=array();
    	if ($uid){
    		$shopfavalllist=M('shop_fav')->field('shop_id')->where(array('uid'=>$uid))->select();
    		$shop_all_ids=array();
    		foreach ($shopfavalllist as $v){
    			$shop_all_ids[]=$v['shop_id'];
    		}
    	} 
    	if ($gz){
    		if(empty($uid)){
    			$result=M('shop a')->cache(true)->field('a.id,a.shop_name,b.id as brand_id,b.b_name as name,b.b_logo as logo')->join('left join '.C('DB_PREFIX').'brand b on a.id=b.shop_id')->join('left join '.C('DB_PREFIX').'brand_ext c on b.id=c.brand_id')->where(array('a.status'=>1,'b.status'=>1))->order('c.is_best DESC,a.pr DESC')->group('b.id')->limit($p.','.$pagesize)->select();
    		}else {
    			$shopfavlist=M('shop_fav')->field('shop_id')->where(array('uid'=>$uid))->limit($p.','.$pagesize)->select();
    			$shop_ids=array();
    			foreach ($shopfavlist as $v){
    				$shop_ids[]=$v['shop_id'];
    			}
    			$shopfavcount=count($shopfavlist);
    			if ($shopfavcount>=16){
    				$result=M('shop a')->cache(true)->field('a.id,a.shop_name,b.id as brand_id,b.b_name as name,b.b_logo as logo')->join('left join '.C('DB_PREFIX').'brand b on a.id=b.shop_id')->join('left join '.C('DB_PREFIX').'brand_ext c on b.id=c.brand_id')->join('left join '.C('DB_PREFIX').'shop_fav d on a.id=d.shop_id')->where(array('a.status'=>1,'b.status'=>1,'a.id'=>array('in',$shop_ids)))->order('c.is_best DESC,a.pr DESC')->group('b.id')->limit($p.','.$pagesize)->select();
    			}elseif ($shopfavcount>0){
    				$result1=M('shop a')->cache(true)->field('a.id,a.shop_name,b.id as brand_id,b.b_name as name,b.b_logo as logo')->join('left join '.C('DB_PREFIX').'brand b on a.id=b.shop_id')->join('left join '.C('DB_PREFIX').'brand_ext c on b.id=c.brand_id')->where(array('a.status'=>1,'b.status'=>1,'a.id'=>array('in',$shop_ids)))->order('c.is_best DESC,a.pr DESC')->group('b.id')->limit($p.','.$pagesize)->select();
    				$favlimit=16-$shopfavcount;
    				$result2=M('shop a')->cache(true)->field('a.id,a.shop_name,b.id as brand_id,b.b_name as name,b.b_logo as logo')->join('left join '.C('DB_PREFIX').'brand b on a.id=b.shop_id')->join('left join '.C('DB_PREFIX').'brand_ext c on b.id=c.brand_id')->join('left join '.C('DB_PREFIX').'shop_fav d on a.id=d.shop_id')->where(array('a.status'=>1,'b.status'=>1,'a.id'=>array('not in',$shop_all_ids)))->order('c.is_best DESC,a.pr DESC')->group('b.id')->limit($favlimit)->select();
    				$result=array_merge($result1,$result2);
    				
    			}else {
    				$shopfavcount=M('shop_fav')->where(array('uid'=>$uid))->count();
    				$p=$p+(16-$shopfavcount%16);
    				$result=M('shop a')->cache(true)->field('a.id,a.shop_name,b.id as brand_id,b.b_name as name,b.b_logo as logo')->join('left join '.C('DB_PREFIX').'brand b on a.id=b.shop_id')->join('left join '.C('DB_PREFIX').'brand_ext c on b.id=c.brand_id')->where(array('a.status'=>1,'b.status'=>1))->order('c.is_best DESC,a.pr DESC')->group('b.id')->limit($p.','.$pagesize)->select();
    			}
    		}
    	}else {
    		$result=M('brand_ext a')->cache(true)->join('left join '.C('DB_PREFIX').'shop b on a.shop_id=b.id')->field('b.id,a.brand_id,a.name,a.logo,a.shop_id,b.shop_name')->where(array('a.status'=>1))->group('a.brand_id')->order('a.dotime DESC,a.is_best DESC')->limit($p.','.$pagesize)->select();
    	}
    	if ($result){
    		foreach ($result as $k=>$v){
    			$result[$k]['shop_url']=shop_url($v['id']);
    			$result[$k]['shop_gurl']=$result[$k]['shop_url'].'/index/goods/brand_id/'.$v['brand_id'];
    			$result[$k]['images']=myurl($v['logo'],120);
    			if (in_array($v['id'], $shop_all_ids)){
    				$result[$k]['fav']=1;
    			}
    			//商品 
    			$act=M('goods a')->cache(true)->join('left join '.C('DB_PREFIX').'goods_attr_list b on a.id=b.goods_id')->field('a.id,b.id as attr_id,a.goods_name,a.images,a.price')->where(array('a.is_best'=>1,'a.status'=>1,'a.brand_id'=>$v['brand_id']))->limit(3)->select();
    			$result[$k]['tip']='推荐';
    			if (!$act){
    				$act=M('goods a')->cache(true)->join('left join '.C('DB_PREFIX').'goods_attr_list b on a.id=b.goods_id')->field('a.id,b.id as attr_id,a.goods_name,a.images,a.price')->where(array('a.status'=>1,'a.brand_id'=>$v['brand_id']))->order('a.pr DESC')->limit(3)->select();
    				if (!$act){
    					unset($result[$k]);
    					continue;
    				}else {
    					$result[$k]['tip']='热卖';
    				}
    			}
    			if ($gz){
    				$result[$k]['tip']='猜你喜欢';
    			}
    			foreach ($act as $kk=>$vv){
    				$act[$kk]['url']=DM('item').U('/goods/'.$vv['attr_id']);
    				$act[$kk]['images']=myurl($vv['images'],120);
    			}
    			$result[$k]['_']=$act;
    			$result[$k]['size']=count($act);
    			//活动
    			$activity=M('activity a')->cache(true)->join('left join '.C('DB_PREFIX').'activity_type b on a.type_id=b.id')->field('b.activity_name')->where(array('a.shop_id'=>$v['id'],'a.status'=>1))->group('a.type_id')->select();
    			if ($activity){
    				$arr=array();
    				foreach ($activity as $vc){
    					$arr[]=$vc['activity_name'];
    				}
    				$result[$k]['activity']=implode(' | ', $arr);
    			}else {
    				$result[$k]['activity']='暂无活动';
    			}
    		}
    		$this->apiReturn(1,['data'=>$result]);
    	}else {
    		$this->apiReturn(3);
    	}
    }


}