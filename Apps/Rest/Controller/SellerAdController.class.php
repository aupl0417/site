<?php
/**
+----------------------------------------------------------------------
| RestFull API
+----------------------------------------------------------------------
| 卖家 - 广告投放管理
+----------------------------------------------------------------------
| Author: lazycat AND 李博<673090083@qq.com>
+----------------------------------------------------------------------
*/
namespace Rest\Controller;
use Think\Controller\RestController;
class SellerAdController extends CommonController {
    protected $action_logs = array('sucai_add','sucai_edit','sucai_delete','create_orders','orders_delete');

    public function _initialize()
    {
        parent::_initialize();
        if ($this->user['shop_type'] == 6) $this->apiReturn(0, '', 1, '个人店不可进行当前操作！');
    }
    
	public function index(){
    	redirect(C('sub_domain.www'));
    }

    /**
    * 我的素材
    * @param string $_POST['openid']    用户openid
    * @param string $_POST['size']      宽高 width x height 非必须
    */
    public function sucai_list(){
        //频繁请求限制,间隔2秒
        $this->_request_check();

        //必传参数检查
        $this->need_param=array('openid','sign');
        $this->_need_param();
        $this->_check_sign();

        $status_name=array('待审核','审核通过','审核未通过');
        if(isset($_POST['size'])){
            $size = explode('x', I('size',''));
            $map['width'] = (int) $size[0];
            $map['height'] = (int) $size[1];
        }
        
        if (isset($_POST['bsize'])) {
            $bsize = explode('x', I('bsize',''));
            $map['background_width'] = (int) $bsize[0];
            $map['background_height'] = (int) $bsize[1];
        }
        
        if(isset($_POST['category'])){
            $map['category_id'] = array('in',I('category',''));
        }

        $map['uid']=$this->uid;
        if(I('post.status')!='') $map['status']=I('post.status');
        $pagesize=I('post.pagesize')?I('post.pagesize'):20;
        $pagelist=pagelist(array(
                'table'         =>'ad_sucai',
                'map'           =>$map,
                'order'         =>'id desc',
                'fields'        =>'etime,ip,uid',
                'fields_type'   =>true,
                'pagesize'      =>$pagesize,
                'action'        =>I('post.action'),
                'query'         =>I('post.query'),
                'p'             =>I('post.p'),
            ));

        if($pagelist['list']){
            foreach($pagelist['list'] as $i => $val){
                $pagelist['list'][$i]['category_name']=nav_sort(['table' => 'goods_category','field' => 'id,sid,category_name','id' => $val['category_id'],'key' => 'category_name']);
                $pagelist['list'][$i]['status_name']=$status_name[$val['status']];
                if($val['status']!=2) $pagelist['list'][$i]['reason']='';
            }
            $this->apiReturn(1,array('data'=>$pagelist));
        }else{
            $this->apiReturn(3);
        }
    }

    /**
    * 添加素材
    * @param string $_POST['openid']    用户openid
    * @param string $_POST['sucai_name']素材名称
    * @param string $_POST['images']    图片
    * @param string $_POST['size']      尺寸,格式为 "宽度x高度"，如：468x60
    */
    public function sucai_add(){
        //频繁请求限制,间隔2秒
        $this->_request_check();

        //必传参数检查
        $this->need_param=array('openid','sucai_name','images','size','category_id','sign');
        $this->_need_param();
        $this->_check_sign();
        
        $size=explode('x',I('post.size'));
        //检查图片尺寸是否符合要求
        $check=A('Tools')->qn_imgsize(I('post.images'),$size[0],$size[1]);
        if(!$check) $this->apiReturn(550);  //图片尺寸不符合要求！
        
        if (!empty(I('post.bsize'))) {
            $bsize = explode('x',I('post.bsize'));
            $bcheck=A('Tools')->qn_imgsize(I('post.background_images'),$bsize[0],$bsize[1]);
            if(!$bcheck) $this->apiReturn(550);  //背景图片尺寸不符合要求！
            $_POST['background_width'] =$bsize[0];
            $_POST['background_height']=$bsize[1];
        }

        //检查素材是否重复
        if(M('ad_sucai')->where(['uid' => $this->uid,'images' => I('post.images'),'category_id' => I('post.category_id')])->count()>0) $this->apiReturn(551);

        $_POST['uid']   =$this->uid;
        $_POST['width'] =$size[0];
        $_POST['height']=$size[1];
        
        if(!$data=D('Common/AdSucai')->create()) $this->apiReturn(4,'',1,D('Common/AdSucai')->getError());
        if(!$data['id']=D('Common/AdSucai')->add()) $this->apiReturn(0);

        $this->apiReturn(1,['data' => $data]);
    }

    /**
    * 修改素材
    * @param string $_POST['openid']    用户openid
    * @param string $_POST['sucai_name']素材名称
    * @param string $_POST['images']    图片
    * @param string $_POST['size']      尺寸,格式为 "宽度x高度"，如：468x60
    */
    public function sucai_edit(){
        //频繁请求限制,间隔2秒
        $this->_request_check();

        //必传参数检查
        $this->need_param=array('openid','id','sucai_name','images','size','category_id','sign');
        $this->_need_param();
        $this->_check_sign();

        $size=explode('x',I('post.size'));

        //检查图片尺寸是否符合要求
        $check=A('Tools')->qn_imgsize(I('post.images'),$size[0],$size[1]);
        if(!$check) $this->apiReturn(550);  //图片尺寸不符合要求！
        
        if (!empty(I('post.bsize'))) {
            $bsize = explode('x',I('post.bsize'));
            $bcheck=A('Tools')->qn_imgsize(I('post.background_images'),$bsize[0],$bsize[1]);
            if(!$bcheck) $this->apiReturn(550);  //背景图片尺寸不符合要求！
            $_POST['background_width'] =$bsize[0];
            $_POST['background_height']=$bsize[1];
        }
        
        
        //检查素材是否重复
        if(M('ad_sucai')->where(['uid' => $this->uid,'images' => I('post.images'),'category_id' => I('post.category_id'),'id' => ['neq',I('post.id')]])->count()>0) $this->apiReturn(551);

        $_POST['uid']   =$this->uid;
        $_POST['width'] =$size[0];
        $_POST['height']=$size[1];
        $_POST['status']=0;
        if(!$data=D('Common/AdSucai')->create()) $this->apiReturn(4,'',1,D('Common/AdSucai')->getError());
        if(D('Common/AdSucai')->save() === false){
            $this->apiReturn(0);
        }else{
            $this->apiReturn(1,['data' => $data]);
        }

        
    }

    /**
    * 素材详情
    * @param string $_POST['openid']    用户openid
    * @param int $_POST['id']    素材ID
    */
    public function sucai_view(){
        //频繁请求限制,间隔2秒
        $this->_request_check();

        //必传参数检查
        $this->need_param=array('openid','id','sign');
        $this->_need_param();
        $this->_check_sign();

        $status_name=array('待审核','审核通过','审核未通过');

        $rs=M('ad_sucai')->where(['uid' => $this->uid,'id' => I('post.id')])->field('etime,ip,uid',true)->find();
        if($rs){
            //数据格式化
            $rs['category_name']=nav_sort(['table' => 'goods_category','field' => 'id,sid,category_name','id' => $rs['category_id'],'key' => 'category_name']);
            $rs['status_name']=$status_name[$rs['status']];
            if($rs['status']!=2) $rs['reason']='';

            $this->apiReturn(1,['data' => $rs]);
        }else $this->apiReturn(3);

    }

    /**
    * 删除素材
    * @param string $_POST['openid']    用户openid
    * @param int|string $_POST['id']    多个请用逗号隔开
    */
    public function sucai_delete(){
        //频繁请求限制,间隔2秒
        $this->_request_check();

        //必传参数检查
        $this->need_param=array('openid','id','sign');
        $this->_need_param();
        $this->_check_sign();

        if(M('ad_sucai')->where(['uid' => $this->uid,'id' => ['in',I('post.id')]])->delete()) $this->apiReturn(1);
        else $this->apiReturn(0);
    }


    /**
    * 创建广告订单
    * @param string $_POST['openid']    用户openid
    * @param int    $_POST['positon_id']广告位ID
    * @param int    $_POST['sort']      广告位顺序
    * @param strint $_POST['days']      投放日期列表
    * @param int    $_POST['sucai_id']  素材ID
    * @param string $_POST['name']      广告标题
    * @param int    $_POST['type']      0商品,1店铺,2站外连接
    * @param int    $_POST['goods_id']   商品ID
    */
    public function create_orders(){
        //频繁请求限制,间隔2秒
        $this->_request_check();

        //必传参数检查
        $this->need_param=array('openid','position_id','sort','days','sucai_id','name','type','sign');
        if($_POST['type']==0) $this->need_param[]='goods_id';

        $this->_need_param();
        $this->_check_sign();

        $days=explode(',',I('post.days'));
        array_unique($days);
        asort($days);
        $sday=reset($days);
        $eday=end($days);

        //判断购买时间段是否正常
        //您投放的时间段中有部分同其它用户冲突，请重新选择投放时间！
        if(!ad_days_check(I('post.days'),I('post.position_id'),I('post.sort'))) $this->apiReturn(552);

        $sucai=M('ad_sucai')->where(['uid' => $this->uid,'id' => I('post.sucai_id')])->field('id,images,background_images')->find();
        $position=M('ad_position')->where(['id' => I('post.position_id')])->field('price,device,background_width')->find();


        $data=[
            'a_no'          =>$this->create_orderno('AD',$this->uid),
            'name'          =>I('post.name'),
            'position_id'   =>I('post.position_id'),
            'status'        =>0,
            'sort'          =>I('post.sort'),
            'sday'          =>$sday,
            'eday'          =>$eday,
            'days'          =>implode(',',$days),
            'num'           =>count($days),
            'images'        =>$sucai['images'],
            'price'         =>$position['price'],
            'type'          =>I('post.type'),
            'sucai_id'      =>I('post.sucai_id'),
            'background_images' =>  $sucai['background_images'],
        ];
        
        
        if ($position['background_width'] > 0) {
            if ($sucai['background_images'] == false) $this->apiReturn(0,'',1,'背景图片不能为空！');
            $data['background_images'] = $sucai['background_images'];
        }
        $data['money']      =$data['price']*$data['num'];
        $data['money_pay']  =$data['money'];
        $data['uid']        =$this->uid;

        switch(I('post.type')){
            case 0:
                # $goods=M('goods_attr_list')->where(['seller_id' => $this->uid,'goods_id' => I('post.goods_id'),'num' => ['gt',0]])->field('id')->find();
                # if(!$goods) $this->apiReturn(554);   //商品不存在
                $data['goods_id']   =I('post.goods_id');
                $attr_id = M('goods_attr_list')->where(['goods_id'=>$data['goods_id'],'num'=>['gt',0]])->getField('id');
                if($position['device'] == 1){
                    $data['url']        = C('sub_domain.item').'/goods/'.$attr_id.'.html';
                }elseif($position['device'] == 2){
                    $data['url']        = '/Goods/view/id/' . $attr_id;
                }else{
                    $data['url']        = '#';
                }
            break;
            case 1:
                $shop=M('shop')->where(['uid' => $this->uid])->field('id,domain')->find();
                if(!$shop) $this->apiReturn(553);   //店铺不存在
                $data['shop_id']    =$shop['id'];
                if($position['device'] == 1){
                    $data['url']        =shop_url($shop['id'],$shop['domain']);
                }elseif($position['device'] == 2){
                    $data['url']        = '/Shop/index/shop_id/' . $data['shop_id'];
                }else{
                    $data['url']        = '#';
                }
            break;
        }

        if(!$data=D('Common/Ad')->create($data)) $this->apiReturn(4,'',1,D('Common/Ad')->getError());
        if(!$data['id']=D('Common/Ad')->add()) $this->apiReturn(0);

        $this->apiReturn(1,['data' => $data]);
    }

    /**
    * 我的广告-即我购买的广告
    * @param string $_POST['openid']    用户openid
    * @param int    $_POST['status']    0待付款，1已付款，2强制下架
    */
    public function orders_list(){
        //频繁请求限制,间隔2秒
        $this->_request_check();

        //必传参数检查
        $this->need_param=array('openid','sign');
        $this->_need_param();
        $this->_check_sign();

        $status_name    =['待付款','已付款','强制下架','投放中','待投放','已过期'];
        $type_name      =['商品','店铺','站外链接'];

        $map['uid']     =$this->uid;
        
        switch (I('post.status')) {
            case 3: //投放中
                $map['status']  =1;
                $map['days']    =['like','%'.date('Y-m-d').'%'];
            break;
            case 4: //待投放
                $map['status']  =1;
                $map['sday']    =['gt',date('Y-m-d')];
            break;
            case 5: //已过期
                $map['status']  =1;
                $map['eday']    =['lt',date('Y-m-d')];
            break;
            
            default:
                if(I('post.status')!='') $map['status']=I('post.status');
            break;
        }

        $pagesize=I('post.pagesize')?I('post.pagesize'):20;
        $pagelist=pagelist(array(
                'table'         =>'Common/AdRelation',
                'do'            =>'D',
                'map'           =>$map,
                'order'         =>'id desc',
                'fields'        =>'etime,ip,subcontent,uid,is_default',
                'fields_type'   =>true,
                'relation'      =>true,
                'pagesize'      =>$pagesize,
                'action'        =>I('post.action'),
                'query'         =>I('post.query'),
                'p'             =>I('post.p'),
            ));


        if($pagelist['list']){
        	$date=date('Y-m-d');
            foreach($pagelist['list'] as $i => $val) {
            	if ($val['status']==1){
            		$arr=explode(',', $val['days']);
            		if (in_array($date, $arr)){
            			$pagelist['list'][$i]['status']=3;
            		}elseif ($val['sday']>$date){
            			$pagelist['list'][$i]['status']=4;
            		}elseif ($val['sday']<$date){
            			$pagelist['list'][$i]['status']=5;
            			foreach ($arr as $v){
            				if ($v>$date){
            					$pagelist['list'][$i]['status']=4;
            				}
            			}
            		}
            	}
            	$pagelist['list'][$i]['status_name']    =$status_name[$pagelist['list'][$i]['status']];
                $pagelist['list'][$i]['type_name']      =$type_name[$val['type']];
                switch($val['type']){
                    case 0:
                        $pagelist['list'][$i]['goods']  =M('goods')->where(['goods' => $val['goods_id']])->field('id,goods_name,images')->find();
                    break;
                    case 1:
                        $pagelist['list'][$i]['shop']  =M('shop')->where(['id' => $val['shop_id']])->field('id,shop_name,shop_logo,concat("'.($_SERVER['HTTPS']=='on'?'https://':'http://').'",if(domain!="",domain,id),".'.C('DOMAIN').'") as shop_url')->find();
                    break;
                }
            }

            $this->apiReturn(1,array('data'=>$pagelist));
        }else{
            $this->apiReturn(3);
        }
    }

    /**
    * 订单详情
    * @param string $_POST['openid']    用户openid
    * @param string $_POST['a_no']      订单号
    */
    public function orders_view(){
        //频繁请求限制,间隔2秒
        $this->_request_check();

        //必传参数检查
        $this->need_param=array('openid','a_no','sign');
        $this->_need_param();
        $this->_check_sign();

        $type_name      =['商品','店铺','站外链接'];

        $do=D('Common/AdRelation');
        $rs=$do->relation(true)->where(['uid' => $this->uid,'a_no' => I('post.a_no')])->field('etime,ip,subcontent,uid,is_default',true)->find();

        if(!$rs) $this->apiReturn(3);

        $rs['type_name']      =$type_name[$rs['type']];
        $date=date('Y-m-d');
        if ($rs['status']==1){
        	$arr=explode(',', $rs['days']);
        	if (in_array($date, $arr)){
        		$rs['status']=3;
        	}elseif ($rs['sday']>$date){
        		$rs['status']=4;
        	}elseif ($rs['sday']<$date){
        		$rs['status']=5;
        		foreach ($arr as $v){
        			if ($v>$date){
        				$rs['status']=4;
        			}
        		}
        	}
        }
        switch($rs['type']){
            case 0:
                $rs['goods']  =M('goods')->where(['goods' => $rs['goods_id']])->field('id,goods_name,images')->find();
            break;
            case 1:
                $rs['shop']  =M('shop')->where(['id' => $rs['shop_id']])->field('id,shop_name,shop_logo,concat("'.($_SERVER['HTTPS']=='on'?'https://':'http://').'",if(domain!="",domain,id),".'.C('DOMAIN').'") as shop_url')->find();
            break;
        }

        //取最近一个月内不可购买的日期
        $result=ad_days_check('',$rs['position_id'],$rs['sort'],1);
        $calendar=calendar(array('days'=>$result['days_use'],'isuse'=>1));
//         $calendar=calendar(array('sday'=>$rs['sday'],'eday'=>$rs['eday'],'days'=>$rs['days']));

        $this->apiReturn(1,['data' => $rs,'calendar' => $calendar]);
    }

    /**
    * 删除订单
    * @param string $_POST['openid']    用户openid
    * @param string $_POST['a_no']      订单号
    */
    public function orders_delete(){
        //频繁请求限制,间隔2秒
        $this->_request_check();

        //必传参数检查
        $this->need_param=array('openid','a_no','sign');
        $this->_need_param();
        $this->_check_sign();

        $do=M('ad');
        $rs=$do->where(['uid' => $this->uid,'a_no' => I('post.a_no')])->field('id,status')->find();

        if(!$rs) $this->apiReturn(3);

        //只有未付款的订单才可以删除
        if($rs['status']!=0) $this->apiReturn(555);

        if($do->where(['a_no' => I('post.a_no')])->delete()) $this->apiReturn(1);
        $this->apiReturn(0);
    }

    /**
    * 广告统计
    * @param string $_POST['openid']    用户openid
    */
    public function ad_total(){
        //必传参数检查
        $this->need_param=array('openid','sign');
        $this->_need_param();
        $this->_check_sign();

        $res=A('Total')->user_ad($this->uid);

        $this->apiReturn(1,['data' => $res]);
    }
    
    /**
     * 检查是否存在符合要求素材
     */
    public function checksucai(){
    	//必传参数检查
    	$this->need_param=array('id','w','h','bw','bh','openid','sign');
    	$this->_need_param();
    	$this->_check_sign();
    	
    	$id=I('post.id',0);
    	$w=I('post.w',0);
    	$h=I('post.h',0);
    	$bw=I('post.bw',0);
    	$bh=I('post.bh',0);
    	$where['uid']=$this->uid;
    	$where['width']=$w;
    	$where['height']=$h;
    	if ($bw) $where['background_width']=$bw;
    	if ($bh) $where['background_height']=$bh;
    	
    	$category=M('ad_position')->where(['id'=>$id])->getField('category_id');
    	$shop_cate=M('shop')->field('category_id,category_second')->where(['uid'=>$this->uid,'status'=>1])->find();
    	if ($category&&$shop_cate){
    		$shop_cateArr=explode(',', $shop_cate['category_id'].','.$shop_cate['category_second']);
    		$categoryArr=explode(',', $category);
    		$isset=false;
    		foreach ($categoryArr as $v){
    			foreach ($shop_cateArr as $vv){
    				if ($v==$vv){
    					$isset=true;
    					break;
    				}
    			}
    		}
    		if ($isset){
    			$result=M('ad_sucai')->where($where)->getField('id');
    			if ($result){
    				$this->apiReturn(1,['data'=>1]);//符合条件可投放
    			}else {
    				$this->apiReturn(1,['data'=>4]);//没有可用素材
    			}
    		}else {
    			$this->apiReturn(1,['data'=>3]);//店铺经营类目与广告位类目不符
    		}
    	}else {
    		$this->apiReturn(1,['data'=>2]);//没有可投放类目
    	}
    	
    }
}