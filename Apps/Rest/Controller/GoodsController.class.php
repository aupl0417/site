<?php
/**
+----------------------------------------------------------------------
| RestFull API 
+----------------------------------------------------------------------
| 商品管理
+----------------------------------------------------------------------
| Author: lazycat <673090083@qq.com>
+----------------------------------------------------------------------
*/
namespace Rest\Controller;
use Think\Controller\RestController;
class GoodsController extends CommonController {
    public function index(){
    	redirect(C('sub_domain.www'));
    }

    /**
    * 商品类目
    */
    public function category(){
        //必传参数检查
        $this->need_param=array('sign');
        $this->_need_param();
        $this->_check_sign();

        $sid=I('post.sid')?I('post.sid'):0;

        $list=get_category(array('table'=>'goods_category','field'=>'id,sid,icon,images,category_name,sub_name','level'=>3,'map'=>[['status' =>1],['status' =>1],['status' =>1]],'sid'=>$sid,'cache_name'=>'goods_category_list_'.$sid,'cache_time'=>3600));
        if($list){
            $this->apiReturn(1,array('data'=>$list));
        }else{
            //没有分类
            $this->apiReturn(3);
        }
    }

    /**
    * 子类目
    */
    public function sub_category(){
        //必传参数检查
        $this->need_param=array('sign');
        $this->_need_param();
        $this->_check_sign();

        $sid=I('post.sid')?I('post.sid'):0;

        $list=M('goods_category')->cache(true,C('CACHE_LEVEL.OneDay'))->where(['status' => 1,'sid' => $sid])->field('id,category_name')->order('sort asc')->select();
        if($list) $this->apiReturn(1,['data' => $list]);
        else $this->apiReturn(3);
    }

    /**
    * 首页推荐产品
    * @param int    $_POST['limit'] 读取记录数量
    * @param int    $_POST['imgsize']   缩略图尺寸
    */
    public function hot_goods(){

        //必传参数检查
        $this->need_param=array('sign');
        $this->_need_param();
        $this->_check_sign();

        $limit=I('post.limit')?I('post.limit'):8;
        

        $do=D('GoodsRelation');
        $map['status']=1;
        $map['num']=array('gt',0);
        $map['_string'] = 'shop_id in (select id from '.C('DB_PREFIX').'shop where status=1)';

        //随机取商品

        $count=M('goods')->where($map)->count();
        if(($count-$limit)>0) $limit=rand(0,$count-$limit).','.$limit;
        
        $list=$do->relation(true)->cache(true,C('CACHE_LEVEL.L'))->relationWhere('goods_attr_list','num>0')->relationOrder('goods_attr_list','price asc')->relationField('goods_attr_list','id,attr_name,price,price_market,sale_num,view,concat("/Goods/view/id/",id,".html") as url')->relationLimit('goods_attr_list',1)->where($map)->field('id,goods_name,images,price,sale_num,shop_id,seller_id')->limit($limit)->select();
              

        if($list){			
			foreach($list as $key=>$val){
				if(I('post.imgsize')) $list[$key]['images']=myurl($val['images'],I('post.imgsize'));
				$list[$key]['shop']['shop_url']=shop_url($val['shop']['id'],$val['shop']['domain']);
			}
            $this->apiReturn(1,array('data'=>$list));
        }else{
            $this->apiReturn(3);
        }
    }

    /**
    * 猜您喜欢
    * @param int    $_POST['limit'] 获取数量
    * @param float  $_POST['score_ratio'] 赠送积分倍数
    * @param int    $_POST['imgsize']   缩略图尺寸 
    * @param int    $_POST['is_best']   厨窗
    */
    public function love_goods(){
        //必传参数检查
        $this->need_param=array('sign');
        $this->_need_param();
        $this->_check_sign();

        $limit=I('post.limit')?I('post.limit'):8;
        

        $do=D('GoodsRelation');
        $map['status']  =1;
        $map['is_display']	= 1;
        $map['num']     =array('gt',0);
        $map['_string'] = 'shop_id in (select id from '.C('DB_PREFIX').'shop where status=1)';
        if(I('post.score_ratio')) $map['score_ratio']=I('post.score_ratio');
        if(I('post.is_best')) $map['is_best'] =1;
        if(I('post.is_love')) $map['is_love'] =1;
        if(I('post.category_id')) $map['category_id'] = ['in',sortid(['table' => 'goods_category','sid' => I('post.category_id'),'cache_name' => 'love_goods_cid_'.I('post.category_id')])];

        if(I('post.pr')) $map['pr'] = ['gt',I('post.pr')];
        if(I('post.sale_num')) $map['sale_num'] = ['gt',I('post.sale_num')];


        //随机取商品
        $count=M('goods')->where($map)->count();
        if(($count-$limit)>0) $limit=rand(0,$count-$limit).','.$limit;

        $list=$do->relation(true)->cache(true,C('CACHE_LEVEL.M'))->relationWhere('goods_attr_list','num>0')->relationOrder('goods_attr_list','price asc')->relationField('goods_attr_list','id,attr_name,price,price_market,sale_num,view,concat("/Goods/view/id/",id,".html") as url')->relationLimit('goods_attr_list',1)->where($map)->field('id,goods_name,images,price,sale_num,score_ratio,shop_id,seller_id')->limit($limit)->select();
        

        if($list){
			foreach($list as $key=>$val){
				if(I('post.imgsize')) $list[$key]['images']=myurl($val['images'],I('post.imgsize'));
				$list[$key]['shop']['shop_url']=shop_url($val['shop']['id'],$val['shop']['domain']);
			}
			
            $this->apiReturn(1,array('data'=>$list));
        }else{
            $this->apiReturn(3);
        }        
    }
	/**
    * 获取同类商品
    * @param int    $_POST['id'] 			商品id
    * @param int    $_POST['category_id'] 	类目id
    */
	public function get_same_goods(){
		 //必传参数检查
        $this->need_param=array('sign');
        $this->_need_param();
        $this->_check_sign();
		
        $map['status']      =1;
        $map['is_display']	= 1;
        $map['num']         =array('gt',0);
		$map['id']          = array('neq',I('post.id'));
		$map['category_id'] = I('post.category_id');
        $map['_string']     = 'shop_id in (select id from '.C('DB_PREFIX').'shop where status=1 AND is_test=0)';
       
        $list=D('GoodsRelation')->relation(true)->cache(true,C('CACHE_LEVEL.M'))->relationWhere('goods_attr_list','num>0')->relationOrder('goods_attr_list','price asc')->relationField('goods_attr_list','id,attr_name,price,price_market,sale_num,view,concat("/Goods/view/id/",id,".html") as url')->relationLimit('goods_attr_list',1)->where($map)->field('id,goods_name,images,price,sale_num,score_ratio,shop_id,seller_id')->limit(8)->select();
		
		if($list){
			$this->apiReturn(1,array('data'=>$list));
		}else{
			$this->apiReturn(3);
		}
		
	}

    /**
    * 商品列表
    */
    public function goods_list(){
        //频繁请求限制,间隔2秒
        //$this->_request_check();

        //必传参数检查
        $this->need_param=array('sign');
        $this->_need_param();
        $this->_check_sign();

        $map['status']=1;
        $map['is_display']	= 1;
        $map['num']=array('gt',0);

        //搜类目时
        if(I('post.category_id')) {
            $map['category_id']=['in',sortid(['table' => 'goods_category','sid' => I('post.category_id') ,'cache_name' => 'goods_category_search_'.I('post.category_id')])];

            //记录搜索条件
            $category=CURD([
                    'table'         => 'goods_category',
                    'map'           => ['sid' => I('post.category_id'),'status'        => 1],
                    'field'         => 'id,category_name',
                    'order'         => 'sort asc',
                    'cache_name'    => 'list_goods_category_'.I('post.category_id')
                    ]);



            if(empty($category)){
                $category_rs=D('Common/GoodsCategoryUpRelation')->relation(true)->cache(true,C('CACHE_LEVEL.XXL'))->where(['id' => I('post.category_id')])->field('sid')->find();
                $category=$category_rs['category'];
            }

            foreach($category as $i => $val){
                $tmp[$i]    =   [
                                'name'  =>  $val['category_name'],
                                'url'   =>  U('/Index/index',['id' => $val['id']])
                            ];
            }

            $search[]     =   [
                'name'  => '相关类目',
                'data' =>  $tmp,
            ];



            //参数
            $param      =$this->get_goods_param(I('post.category_id'));
            $param      =$this->_goods_param_cmp($param);
            if(!empty($param)) $search     =array_merge($search,$param);


            //导航
            $nav[]      =   nav_sort([
                                'table'     =>'goods_category',
                                'key'       =>'category_name',
                                'field'     =>'id,sid,category_name',
                                'id'        =>I('post.category_id'),
                                'icon'      =>'',
                                'link'      =>U('/Index/index',['id' => '[id]']),
                            ]);
        }else{
            $keyword    =CURD([
                            'table'     =>'search_keyword',
                            'map'       =>['status' =>1],
                            'field'     =>'keyword',
                            'limit'     =>24,
                            'cache_name'=>'list_search_keyword'
                        ]);
            foreach($keyword as $i => $val){
                $tmp[$i]    =   [
                                'name'  =>  $val['keyword'],
                                'url'   =>  C('sub_domain.s').U('/Index/index',['keywords' => $val['keyword']])
                            ];
            }

            $search[]       =   [
                                'name'  => '热搜关键词',
                                'data'  =>  $tmp,
                            ];

            
        }
        
        if (I('post.shop_id')) {  //搜索企业时
            $map['shop_id'] =   I('post.shop_id');
        }

        if(I('post.is_daigou')) $map['is_daigou']   = 1; //是否代购
        
        //搜参数
        if(I('post.option_id')!='' && I('post.option')!='') {
            $map['_string']='id in (select goods_id from '.C('DB_PREFIX').'goods_param where option_id='.I('post.option_id').' and param_value like "%'.I('post.option').'%")';

            $do=D('Common/GoodsParamOptionGruopRelation');
            $tmp=$do->relation(true)->cache(true)->where(['id' => I('post.option_id')])->field('group_id')->find();
            //参数
            $param      =$this->get_goods_param($tmp['category_id']);
            $search     =$this->_goods_param_cmp($param); 

            $nav[]      ='<a href="'.U('/Index/index',['option_id'=>I('option_id'),'option' => I('post.option')]).'">'.I('post.option').'</a>';           
        }

        if(empty($nav)) $nav[]      ='<a href="/">全部商品</a>';

        if(I('post.q'))	{
            $map['goods_name']=array('like','%'.trim(urldecode(I('post.q'))).'%');

            log_add('search_keyword',['atime' =>date('Y-m-d H:i:s'),'ip'=>get_client_ip(),'type'=>1,'shop_id'=>I('post.shop_id'),'keyword'=>I('post.q')]);
        }
        if(I('post.score_ratio')!='') $map['score_ratio']   =I('post.score_ratio');    //赠送积分比例
        if(I('post.is_self')!='')   $map['is_self']         =1;  //是否官推
        if(I('post.is_free')!='')   $mpa['free_express']    =1; //是否包邮

        //价格区间
        if(I('post.min_price')!='' && I('post.max_price')!='') $map['price']=['between',[I('post.min_price'),I('post.max_price')]];
        if(I('post.min_price')!='' && I('post.max_price')=='') $map['price']=['egt',I('post.min_price')];
        if(I('post.min_price')=='' && I('post.max_price')!='') $map['price']=['elt',I('post.max_price')];

        //店铺必须是正常营业状态且非测试店铺
        $shop_sql   =   'shop_id in (select id from '.C('DB_PREFIX').'shop where status=1 and is_test=0)';
        $map['_string'] = $map['_string']?$map['_string'].' and '.$shop_sql:$shop_sql;


        $pagesize=I('post.pagesize')?I('post.pagesize'):20;        

        $order=I('post.order')?I('post.order'):'(pr+pr_extra) desc,id desc';
        if(I('post.sort')){
            $order=str_replace('-', ' ', I('post.sort')).',pr desc,id desc';
        }

        /*
        # 搜店铺
        if(I('post.shop_id')){
            $map['shop_id'] = I('post.shop_id', 0, 'int');
        }
        # 宝贝分类
        if(I('post.shop_category_id')){
            # $map['shop_category_id'] = I('post.shop_category_id', 0, 'int');
            $map['_string'] = 'find_in_set ("'.I('post.shop_category_id').'",shop_category_id)';
        }
        */

        $pagelist=pagelist(array(
                'table'     =>'Common/GoodsRelation',
                'do'        =>'D',
                'map'       =>$map,
                'order'     =>'atime desc',
                'fields'    =>'id,goods_name,images,price,sale_num,seller_id,shop_id,score_ratio,round(score_ratio*price*100,2) as score,is_self,(pr+pr_extra+(unix_timestamp()-unix_timestamp(uptime))/86400) as pr,officialactivity_join_id,officialactivity_price,is_daigou',
                'order'     =>$order,
                'pagesize'  =>$pagesize,
                'relation'  =>true,
                'relationWhere'     =>array('goods_attr_list','num>0'),
                'relationOrder'     =>array('goods_attr_list','price asc'),
                'relationField'     =>array('goods_attr_list','id,attr_name,price,price_market,sale_num,view,concat("/Goods/view/id/",id,".html") as url'),
                'relationLimit'     =>array('goods_attr_list',1),
                'action'            =>I('post.action'),
                'query'             =>I('post.query')?query_str_(I('post.query')):'',
                'p'                 =>I('post.p'),
                'cache_name'        =>md5(implode(',',$_POST).__SELF__),
                'cache_time'        =>C('CACHE_LEVEL.L'),
            ));



        $pagelist['search'] =$search;
        $pagelist['nav']    =$nav;

        if($pagelist['list']){        
            foreach($pagelist['list'] as $key=>$val){
                if(I('post.imgsize')){
                    $pagelist['list'][$key]['images']=myurl($val['images'],I('post.imgsize'));
                }

                $pagelist['list'][$key]['shop']['shop_url']=shop_url($val['shop']['id'],$val['shop']['domain']);
            }
                    
            $this->apiReturn(1,array('data' => $pagelist));
        }else{
            $this->apiReturn(3,array('data' => $pagelist));
        }
    }


    /**
    * 商品详情
    * @param int $_POST['id']   商品库存ID
    */
    public function view(){
        //必传参数检查
        $this->need_param=array('id','sign');
        $this->_need_param();
        $this->_check_sign();

        //当前属性数据
        $do=M('goods_attr_list');
        $rs=$do->cache(true,C('CACHE_LEVEL.XS'))->where(array('id'=>I('post.id')))->field('id,seller_id,goods_id,attr_id,attr_name,price,price_market,num,sale_num,rate_good,rate_middle,rate_bad,concat("/Goods/view/id/",id,".html") as detail_url')->find();

        if(!$rs) $this->apiReturn(3); //找不到记录

        //店铺已停止营业或关闭！
        if(!M('shop')->where(['uid' => $rs['seller_id'],'status' => 1])->field('id')->find()) $this->apiReturn(148);  

        //库存(SKU)
        $attr_list=$do->cache(true,C('CACHE_LEVEL.XS'))->where(array('goods_id'=>$rs['goods_id']))->getField('attr_id,id,goods_id,attr_name,price,price_market,num,sale_num,rate_good,rate_middle,rate_bad,concat("/Goods/view/id/",id,".html") as detail_url');

        $do=D('Common/GoodsViewRelation');
        //新增条件，商品库存必须大于0
        $goods=$do->where(array('id'=>$rs['goods_id'], 'num' => ['gt', 0]))->field('atime,etime,ip',true)->find();
        $goods['keywords']  = A('Tools')->_scws($goods['goods_name']);
		
		//是否参与了官方活动
		if($goods['officialactivity_join_id'] > 0){
			$goods['activity'] = M('officialactivity_join')->cache(false)->where(['id' => $goods['officialactivity_join_id']])->field('id,day,time,price')->find();
			$goods['activity']['time_dif']	= strtotime($goods['activity']['day'].' '.$goods['activity']['time']) - time();
            //距离活动开始的前12小时开始倒计时
            //if(time() > strtotime($goods['activity']['day'].' '.$goods['activity']['time']) - (3600 * 12)) $goods['is_officialactivity'] = 1;
		}
		if ($goods['is_daigou'] == 1 && $goods['daigou_ratio'] == 0) {
		    $cfg = getSiteConfig('daigou');
		    $goods['daigou_ratio'] = $cfg['daigou_cost_ratio'];
		}
        //0=删除,1=上架,2=仓库,3=主图缺失,4=违夫,5=异常,6=禁止上架
        switch ($goods['status']) {
            case 0:
                    $this->apiReturn(141);
                break;
            case 2:
                    if(I('post.preview')!=1){$this->apiReturn(142);}
                break;
            case 3:
                    $this->apiReturn(143);
                break;
            case 4:
                    $this->apiReturn(144);
                break;
            case 5:
                    $this->apiReturn(145);
                break;
            case 6:
                    $this->apiReturn(146);
                break;
        }
		if(I('post.imgsize')){
			$rs['images']=myurl($goods['images'],I('post.imgsize'));
		}else{
			$rs['images']=$goods['images'];
		}
        
        //属性
        $do=M('goods_attr_value');
        $attr=$do->cache(true,C('CACHE_LEVEL.XS'))->where(array('goods_id'=>$rs['goods_id']))->group('attr_id')->getField('attr_id',true);
        
        $do=D('Common/GoodsAttrValueRelation');
        $attr_value=$do->relation(true)->relationWhere('goods_attr_value','goods_id='.$rs['goods_id'])->cache(false,C('CACHE_LEVEL.XS'))->where(array('id'=>array('in',$attr)))->field('id,attr_name')->order('sort asc')->select();
        //echo $do->getLastSQL();
        //print_r($attr_value);
        foreach($attr_value as $key=>$val){
            foreach($val['option'] as $vkey=>$v){
                $attr_value[$key]['option'][$vkey]['attr']=$val['id'].':'.$v['option_id'];
                if($v['attr_album']){
					$attr_value[$key]['option'][$vkey]['attr_album']=explode(',',$v['attr_album']);
					
					if(I('post.imgsize')){
						$attr_value[$key]['option'][$vkey]['attr_images'] = myurl($attr_value[$key]['option'][$vkey]['attr_images'],I('post.imgsize'));
						foreach($attr_value[$key]['option'][$vkey]['attr_album'] as $vvkey => $vv){
							$attr_value[$key]['option'][$vkey]['attr_album'][$vvkey] =   myurl($vv,I('post.imgsize'));
						}
					}
				}
            }
        }

        //当前库存商品主图
        $attr_id=explode(',',$rs['attr_id']);
        //dump($attr_id);
        foreach($attr_value as $Key=>$val){
            foreach($val['option'] as $vkey=>$v){
                if(in_array($v['attr'], $attr_id) && $v['attr_album']){
                    if(is_array($rs['images_album'])) $rs['images_album']=array_merge($rs['images_album'],$v['attr_album']);
                    else $rs['images_album']=$v['attr_album'];                    
                }
            }
        }

        if($rs['images_album']) {
        	$rs['images_album'][] = $rs['images'];
        	$rs['images']=$rs['images_album'][0];
        }else $rs['images_album']=array($rs['images']);

        $rs['images_album']	= array_values(array_unique($rs['images_album']));
/*
        if(I('post.imgsize')){
            foreach($rs['images_album'] as $i => $val){
                $rs['images_album'][$i] =   myurl($val,I('post.imgsize'));
            }
        }
*/

        $rs['attr_list']        =$attr_list;
        $rs['attr']             =$attr_value;
        $rs['goods']            =$goods;
		$rs['seller']           =M('user')->cache(true,C('CACHE_LEVEL.XXL'))->where(array('id'=>$rs['seller_id']))->field('id,nick,face')->find();
		$rs['shop']             =M('shop')->cache(true,C('CACHE_LEVEL.XXL'))->where(array('uid'=>$rs['seller_id']))->field('id,shop_name,shop_logo,domain,qq,mobile,wang,type_id,fraction_desc,fraction,fraction_speed,fraction_service,about')->find();
        $rs['collocation']      =M('goods_collocation')->cache(true,C('CACHE_LEVEL.XXL'))->where(['goods_id' => $goods['id']])->getField('collocations');
        $rs['category']         =M('goods_category')->where(['id' => $goods['category_id']])->field('id,category_name')->find();
        if ($goods['goods_committed'] != '')    //服务承诺
            $rs['goods_committed']  =M('goods_committed')->cache(true)->where(['id' => ['in', rtrim($goods['goods_committed'], ',')]])->field('name,intro,id')->order('sort asc, id asc')->select();
        $shop_type                      =$this->cache_table('shop_type');
        $rs['shop']['type_name']        =$shop_type[$rs['shop']['type_id']];
        $rs['shop']['shop_url']         =shop_url($rs['shop']['id'],$rs['shop']['domain']);

        if(I('post.isget_content'))     $rs['content']=$this->_goods_content($rs['goods_id']);
        if(I('post.isget_protection'))  $rs['protection']=$this->_goods_protection($goods['protection_id']);
        if(I('post.isget_package'))     $rs['package']=$this->_goods_package($goods['package_id']);
        if(I('post.isget_param'))       $rs['param']=$this->_goods_param($rs['goods_id'],$goods['category_id']);
        if(I('post.isget_rate'))        $rs['rate']=$this->_rate_topN($rs['goods_id'],(I('post.rate_num')?I('post.rate_num'):5));

		M('goods_attr_list')->where(array('id'=>I('post.id')))->setInc('view',1,C('CACHE_LEVEL.S')); 	//浏览次数，60延迟更新
		M('goods')->where(array('id'=>$rs['goods_id']))->setInc('view',1,C('CACHE_LEVEL.S')); 			//浏览次数，60延迟更新

        //所在地
        $express_tpl=M('express_tpl')->where(['id' => $goods['express_tpl_id']])->field('province,city')->find();
        $area       =$this->cache_table('area');
        $rs['city'] =$area[$express_tpl['province']].' '.$area[$express_tpl['city']];

        $this->apiReturn(1,array('data'=>$rs));
    }

    /**
    * 商品详情内容
    * @param int    $_POST['goods_id']  商品ID
    */
    public function goods_content(){
        //必传参数检查
        $this->need_param=array('goods_id','sign');
        $this->_need_param();
        $this->_check_sign();

        $rs=$this->_goods_content(I('post.goods_id'));
        if($rs) $this->apiReturn(1,array('data'=>$rs));
        else $this->apiReturn(3);
    }    

    /**
    * 商品包装介绍
    * @param int    $_POST['package_id']  包装模板ID
    */
    public function goods_package(){
        //必传参数检查
        $this->need_param=array('package_id','sign');
        $this->_need_param();
        $this->_check_sign();

        $rs=$this->_goods_package(I('post.package_id'));
        if($rs) $this->apiReturn(1,array('data'=>$rs));
        else $this->apiReturn(3);
    }      

    /**
    * 商品售后保障介绍
    * @param int    $_POST['protection_id']  售后模板ID
    */
    public function goods_protection(){
        //必传参数检查
        $this->need_param=array('protection_id','sign');
        $this->_need_param();
        $this->_check_sign();

        $rs=$this->_goods_protection(I('post.protection_id'));
        if($rs) $this->apiReturn(1,array('data'=>$rs));
        else $this->apiReturn(3);
    }      

    /**
    * 商品参数
    * @param int    $_POST['goods_id']  商品ID
    */
    public function goods_param(){
        //必传参数检查
        $this->need_param=array('goods_id','sign');
        $this->_need_param();
        $this->_check_sign();

        $rs=$this->_goods_param(I('post.goods_id'),I('post.cid'));
        if($rs) $this->apiReturn(1,array('data'=>$rs));
        else $this->apiReturn(3);
    }  
    /**
    * 商品最新n条评价
    * @param int    $_POST['goods_id']  商品ID
    */
    public function goods_rate_topN(){
        //必传参数检查
        $this->need_param=array('goods_id','sign');
        $this->_need_param();
        $this->_check_sign();

        $rs=$this->_rate_topN(I('post.goods_id'),I('post.limit'));
        if($rs) $this->apiReturn(1,array('data'=>$rs));
        else $this->apiReturn(3);
    }    

    /**
    * 商品评价
    * @param int    $_POST['goods_id']  商品ID
    */
    public function goods_rate(){
        //必传参数检查
        $this->need_param=array('goods_id','sign');
        $this->_need_param();
        $this->_check_sign();
        $rate_name=[
            '-1'    =>'差评',
            '0'     =>'中评',
            '1'     =>'好评'
        ];
        $pagesize=I('post.pagesize')?I('post.pagesize'):15;
        $order='atime desc';
        $map['goods_id']=I('post.goods_id');
        $map['status']=1;
        if(I('post.rate')!='') $map['rate']=I('post.rate');
        $pagelist=pagelist(array(
                'table'     =>'Common/OrdersGoodsCommentRelation',
                'do'        =>'D',
                'map'       =>$map,
                'order'     =>'atime desc',
                'fields'    =>'atime,like_num,uid,orders_goods_id,rate,content,images,is_anonymous,id',
                'order'     =>$order,
                'pagesize'  =>$pagesize,
                'relation'  =>true,
                'action'    =>I('post.action'),
                'query'     =>I('post.query')?query_str_(I('post.query')):'',
                'p'         =>I('post.p'),
                'cache_name'=>md5(implode(',',$_POST).__SELF__),
                'cache_time'=>C('CACHE_LEVEL.L'),
                'ajax'      =>1,
                'page_js'   =>'gotoPage($(this), \'comments\');',
            ));
		$user_level	= $this->cache_table('user_level');
        foreach($pagelist['list'] as $key=>$val){
            if ($pagelist['list'][$key]['images']) {
                $pagelist['list'][$key]['tmp']  =   explode(',', rtrim($pagelist['list'][$key]['images'], ','));
                $cntImages  =   count($pagelist['list'][$key]['tmp']);
                for ($i = 0; $i < $cntImages; $i ++) {
                    $pagelist['list'][$key]['bigImages'][$i]        =   $pagelist['list'][$key]['tmp'][$i];
                }
            }
            $pagelist['list'][$key]['images']						=	imgsize_cmp($val['images'],50);
            $pagelist['list'][$key]['user']['level_name']			=	$user_level[$val['user']['level_id']];
            $pagelist['list'][$key]['user']['nick']                 =   $val['is_anonymous'] == 1 ? '匿名' : hiddenChineseStr($val['user']['nick']);
            $pagelist['list'][$key]['user']['face']					=	myurl($val['user']['face'],80);
            $pagelist['list'][$key]['rate_name']					=	$rate_name[$val['rate']];
        }
        $count['rate_good']		=M('orders_goods_comment')->where(['goods_id' => I('post.goods_id'),'rate' => 1])->count();
        $count['rate_middle']	=M('orders_goods_comment')->where(['goods_id' => I('post.goods_id'),'rate' => 0])->count();
        $count['rate_bad']		=M('orders_goods_comment')->where(['goods_id' => I('post.goods_id'),'rate' => -1])->count();

        $count['rate_num']=array_sum($count);        

        if($pagelist['list']){
            $this->apiReturn(1,array('data'=>$pagelist,'count'=>$count));
        }else{
            $this->apiReturn(3);
        }
    }


    /**
    * 商品详情
    * @param int $goods_id 商品ID
    */
    public function _goods_content($goods_id){
        $do=M('goods_content');
        $rs=$do->cache(true,C('CACHE_LEVEL.XL'))->where(array('goods_id'=>$goods_id))->field('content')->find();
		$rs['content']=html_entity_decode($rs['content']);
        return $rs;
    }

    /**
    * 商品保障
    * @param int $id 商品保障模板ID
    */
    public function _goods_protection($id){
        $do=M('goods_protection');
        $rs=$do->cache(true,C('CACHE_LEVEL.XL'))->where(array('id'=>$id))->field('content')->find();
		$rs['content']=html_entity_decode($rs['content']);
        return $rs;
    }

    /**
    * 商品包装
    * @param int $id 商品包装模板ID
    */
    public function _goods_package($id){
        $do=M('goods_package');
        $rs=$do->cache(true,C('CACHE_LEVEL.XL'))->where(array('id'=>$id))->field('content')->find();
		$rs['content']=html_entity_decode($rs['content']);
        return $rs;
    }

    /**
    * 商品参数
    * @param int $goods_id 商品包装模板ID
    * @param int $cid  商品类目ID
    */
    public function _goods_param($goods_id,$cid=''){
        if(empty($cid)){
            $crs=M('goods')->cache(true,C('CACHE_LEVEL.XL'))->where(array('id'=>$goods_id))->field('category_id')->find();
            $cid=$crs['category_id'];
        }

        //dump($cid);

        //$do=M('goods_param_group');
        //$list=$do->cache(true,C('CACHE_LEVEL.XL'))->where(array('category_id'=>$cid,'status'=>1))->order('sort asc')->field('id,group_name')->select();
        $list=$this->_goods_param_group($cid);
        $do=D('Common/GoodsParamGroupOptionRelation');
        foreach($list as $key=>$val){
            $list[$key]['param']=$do->relation(true)->relationWhere('goods_param','goods_id='.$goods_id)->cache(true,C('CACHE_LEVEL.XS'))->where(array('group_id'=>$val['id']))->field('id,param_name')->select();
        }
        return $list;
    }

    /**
    * 根据类目取参数
    * @param int    $_POST['cid']   类目ID
    */
    public function _goods_param_group($cid){
        $do=D('Admin/GoodsParamOptionRelation');
        $list=$do->relation(true)->where(array('status'=>1,'category_id'=>$cid))->field('id,group_name')->select();     
        if(empty($list)){
            $rs=M('goods_category')->where(['id' => $cid])->field('id,sid')->find();
            //dump($rs);
            if($rs['sid']>0) $list=$this->get_goods_param($rs['sid']);
            else return false;
        }

        return $list;       
    }    

    /**
    * 商品topN条评价
    * @param int $goods_id 商品ID
    * @param int $cid 商品类目ID
    * @param int $limit 取n条评价
    */

    public function _rate_topN($goods_id,$limit=5){
        $rate_name=[
            '-1'    =>'差评',
            '0'     =>'中评',
            '1'     =>'好评'
        ];

        $do=D('Common/OrdersGoodsCommentRelation');
        $list=$do->cache(true,C('CACHE_LEVEL.L'))->relation(true)->where(array('goods_id'=>$goods_id,'status'=>1))->field('atime,like_num,uid,orders_goods_id,rate,content,images,is_anonymous')->order('atime desc')->limit($limit)->select();

        $user_level	=	$this->cache_table('user_level');
		foreach($list as $key=>$val){
            $list[$key]['images']						=	imgsize_cmp($val['images'],50);

            $list[$key]['user']['level_name']			=	$user_level[$val['user']['level_id']];
            $list[$key]['user']['nick']                 =   $val['is_anonymous'] == 1 ? '匿名' : hiddenChineseStr($val['user']['nick']);
            $list[$key]['user']['face']					=	myurl($val['user']['face'],80);
            $list[$key]['rate_name']					=	$rate_name[$val['rate']];
        }

        return $list;
    }

    /**
     * 获取商品列表
     * @param int $_POST['type']        类型 销量 收藏
     * @param int $_POST['shop_id']     店铺id
     * @param int $_POST['num']         获取多少条记录
     * @param int $_POST['is_best']     是否是卖家推荐
     */
    public function sale_and_fav(){

        $this->need_param = array('type','shop_id');
        $this->_need_param();
        $this->_check_sign();

        $where['status'] = 1;
        $where['shop_id'] = I('post.shop_id', 0, 'int');
        if( I('post.is_best', 0, 'int') ){
            $where['is_best'] = 1;
        }

        $t = I('post.type', 1, 'int');
        switch ($t) {
            // 销量
            case 1:
                $order = 'sale_num desc';
                break;
            // 收藏
            case 2:
                $order = 'fav_num desc';
                break;
            default:
                $this->apiReturn(0, [ 'msg' => '类型错误' ]);
                break;
        }

        $limit = I('post.num', 0, 'int');
        if($limit <= 0){
            $limit = 5;
        }

        $list = M('goods')->where($where)->order($order)->limit($limit)->select();

        if($list === false){
            $this->apiReturn(0, [ 'msg' => '获取失败' ]);
        }else{
            if(isset($list[0])){
                $this->apiReturn(1, [ 'data' => $list ]);
            }else{
                $this->apiReturn(3, [ 'msg' => '没有找到数据' ]);
            }
        }

    }

    /**
    * 店铺列表
    */
    public function shop_list(){
        //频繁请求限制,间隔2秒
        //$this->_request_check();

        //必传参数检查
        $this->need_param=array('sign');
        $this->_need_param();
        $this->_check_sign();

        //搜类目时
        if(I('post.category_id')) {
            $tmp=upsid(['table' => 'goods_category','id' => I('post.category_id')]);
            $map['_string']='find_in_set('.$tmp[0].',category_id)';
            //dump($map);

            //记录搜索条件
            $category=CURD([
                    'table'         => 'goods_category',
                    'map'           => ['sid' => 0,'status'        => 1],
                    'field'         => 'id,category_name',
                    'order'         => 'sort asc',
                    'cache_name'    => 'list_goods_category_'
                    ]);

            if(empty($category)){
                $category_rs=D('Common/GoodsCategoryUpRelation')->relation(true)->cache(true,C('CACHE_LEVEL.XXL'))->where(['id' => I('post.category_id')])->field('sid')->find();
                $category=$category_rs['category'];
            }

            $tmp=[];
            foreach($category as $i => $val){
                $tmp[$i]    =   [
                                'name'  =>  $val['category_name'],
                                'url'   =>  U('/Index/shop',['id' => $val['id']])
                            ];
            }
            $search[]     =   [
                'name'  => '相关类目',
                'data' =>  $tmp,
            ];


            //导航
            $nav[]      =   nav_sort([
                                'table'     =>'goods_category',
                                'key'       =>'category_name',
                                'field'     =>'id,sid,category_name',
                                'id'        =>I('post.category_id'),
                                'icon'      =>'',
                                'link'      =>U('/Index/shop',['id' => '[id]']),
                            ]);
        }else{
            $keyword    =CURD([
                            'table'     =>'search_keyword',
                            'map'       =>['status' =>1],
                            'field'     =>'keyword',
                            'limit'     =>24,
                            'cache_name'=>'list_search_keyword'
                        ]);
            foreach($keyword as $i => $val){
                $tmp[$i]    =   [
                                'name'  =>  $val['keyword'],
                                'url'   =>  C('sub_domain.s').U('/Index/index',['keywords' => $val['keyword']])
                            ];
            }

            $search[]       =   [
                                'name'  => '热搜关键词',
                                'data'  =>  $tmp,
                            ];

            
        }

        $map['status']  = 1;
        $map['is_test'] = 0;
        if(I('post.q')) {
            //$map['shop_name']=array('like','%'.trim(I('post.q')).'%');
            if($map['_string']) $map['_string'].=' and ';
            $map['_string'] = 'shop_name like "%'.urldecode(I('post.q')).'%" or about like "%'.urldecode(I('post.q')).'%" or id in (select shop_id from '.C('DB_PREFIX').'goods where goods_name like "%'.urldecode(I('post.q')).'%")';

            log_add('search_keyword',['atime' =>date('Y-m-d H:i:s'),'ip'=>get_client_ip(),'type'=>2,'keyword'=>I('post.q')]);
        }
        $map['goods_num']   =['gt',0];

        //dump($map);

        $pagesize=I('post.pagesize')?I('post.pagesize'):10;
        $order=I('post.order')?I('post.order'):'pr desc,sale_num desc,fraction desc,id desc';
        if(I('post.sort')){
            $order=str_replace('-', ' ', I('post.sort'));
        }

        $pagelist=pagelist(array(
                'table'     =>'Common/ShopGoodsRelation',
                'do'        =>'D',
                'map'       =>$map,
                'order'     =>'pr desc,sale_num desc,goods_num desc',
                'fields'    =>'id,shop_name,shop_logo,domain,qq,wang,sale_num,goods_num,about,province,city,fraction_speed,fraction_service,fraction_desc,fraction',
                'order'     =>$order,
                'pagesize'  =>$pagesize,
                'relation'  =>true,
                'action'    =>I('post.action'),
                'query'     =>I('post.query')?query_str_(I('post.query')):'',
                'p'         =>I('post.p'),
                'cache_name'=>md5(implode(',',$_POST).__SELF__),
                'cache_time'=>C('CACHE_LEVEL.L'),                
            ));

        $pagelist['search'] =$search;
        $pagelist['nav']    =$nav;

        if($pagelist['list']){
			$area 	=	$this->cache_table('area');
			foreach($pagelist['list'] as $key=>$val){
				$pagelist['list'][$key]['shop_logo']	=	myurl($val['shop_logo'],I('post.imgsize')?I('post.imgsize'):100);
				$pagelist['list'][$key]['province']    	=	$area[$val['province']];
                $pagelist['list'][$key]['city']         =   $area[$val['city']];
                $pagelist['list'][$key]['shop_url']     =   shop_url($val['id'],$val['domain']);
                foreach($val['goods'] as $i => $v){
                    $pagelist['list'][$key]['goods'][$i]['attr_list_id']=M('goods_attr_list')->where(['goods_id'=>$v['id']])->getField('id');
                    if(I('post.imgsize')) $pagelist['list'][$key]['goods'][$i]['images']    =   myurl($v['images'],I('post.imgsize'));
                }
			}			
            $this->apiReturn(1,array('data'=>$pagelist));
        }else{
            $this->apiReturn(3,array('data'=>$pagelist));
        }
    }


    /**
    * 品牌
    * @param string $_POST['openid']  用户openid
    */
    public function brand(){
        //必传参数检查
        $this->need_param=array('openid','sign');
        $this->_need_param();
        $this->_check_sign();
        $map['uid'] = $this->uid;
        if (isset($_POST['status'])) $map['status'] = I('post.status');
        $list=M('brand')->where($map)->field('id,b_name,b_ename,b_logo,status')->order('b_name asc')->select();
        if($list) $this->apiReturn(1,['data' => $list]);
        else $this->apiReturn(3);
    }


    /**
    * 热搜关键词
    */
    public function hot_keywords(){
        //必传参数检查
        $this->need_param=array('sign');
        $this->_need_param();
        $this->_check_sign();

        $limit=I('post.limit')?I('post.limit'):8;
        $list=M('search_keyword')->cache(true,C('CACHE_LEVEL.XXL'))->where(['status' => 1])->limit($limit)->getField('keyword',true);
        if($list){
            $this->apiReturn(1,['data' => $list]);
        }else $this->apiReturn(3);
    }

    /**
    * 根据类目取属性
    * @param int    $_POST['cid']   类目ID
    */
    public function get_goods_attr($cid){
        $do=M('goods_attr');
        $list=$do->cache(true,C('CACHE_LEVEL.OneDay'))->where(array('status'=>1,'category_id'=>$cid,'sid'=>0))->field('id,attr_name')->order('sort asc')->select();


        if(empty($list)){
            $rs=M('goods_category')->cache(true,C('CACHE_LEVEL.OneDay'))->where(['id' => $cid])->field('id,sid')->find();
            //dump($rs);
            if($rs['sid']>0) $list=$this->get_goods_attr($rs['sid']);
            else return false;
        }

        return $list;
    }

    /**
    * 根据类目取参数
    * @param int    $_POST['cid']   类目ID
    */
    public function get_goods_param($cid){
        $do=D('Admin/GoodsParamOptionRelation');
        $list=$do->relation(true)->cache(true,C('CACHE_LEVEL.OneDay'))->where(array('status'=>1,'category_id'=>$cid))->field('id,group_name')->select();     
        if(empty($list)){
            $rs=M('goods_category')->cache(true,C('CACHE_LEVEL.OneDay'))->where(['id' => $cid])->field('id,sid')->find();
            //dump($rs);
            if($rs['sid']>0) $list=$this->get_goods_param($rs['sid']);
            else return false;
        }

        return $list;       
    }
	
	
	
    /**
    * 参数格式化
    */
    public function _goods_param_cmp($param){
        foreach($param as $i => $val){
            foreach($val['param_option'] as $k => $v){
                //1,2表示单选和多选项
                if(in_array($v['type'],[1,2])){
                    $tmp=[];
                    $options=explode(',',$v['options']);
                    foreach($options as $o){
                        $tmp[]  =['name' =>$o,'url' => U('/Index/index/',['option_id' => $v['id'],'option' => $o])];
                    }
                    $search[]    =   [
                        'name'  =>  $v['param_name'],
                        'data'  =>  $tmp,
                    ];
                }
            }
        }
        return $search;        
    }

    /**
     * 举报
     */
    public function complaints() {
        $this->need_param = array('type','goods_id', 'openid', 'shop_id', 'attr_id', 'sign', 'content');
        $this->_need_param();
        $this->_check_sign();
        $do = D('GoodsComplaints');
        $data = I('post.');
        $data['uid'] = $this->uid;
        if ($do->create($data) == false) $this->apiReturn(0, '', 1, $do->getError());
        if ($do->add() == false) $this->apiReturn(0);
        $this->apiReturn(1);
    }
}