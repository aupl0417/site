<?php
// 本类由系统自动生成，仅供测试用途
namespace Admin\Widget;
use Think\Controller;
use Common\Builder\Activity;
class WidgetWidget extends Controller {
	public function create_form($param=array()){
		$param['tpl']=$param['tpl']?$param['tpl']:'vform';
		//dump($param['tpl']);
		$this->assign('param',$param);
		//dump($param['field']);
		$this->display('Widget:'.$param['tpl']);
	}

	public function form_group($param=array()){
		$this->assign('param',$param);
		//dump($param);
		$this->display('Widget:'.$param['tpl']);
	}

	//取部分记录
	public function top($param=null){
		$tpl=$param['tpl']?$param['tpl']:'top';		
		$ac=$param['do']?$param['do']:'M';
		$do=$ac($param['table']);
		
		$limit=$param['limit']?$param['limit']:'';
		$order=$param['order']?$param['order']:'atime desc';
		$field=$param['field']?$param['field']:'';
		$map=$param['map']?$param['map']:'';
		
		$list=$do->where($map)->order($order)->field($field)->limit($limit)->select();
		//echo $do->getLastSQL();
		//dump($list);
		$this->assign('list',$list);
		$listnum=count($list);
		$this->assign('listnum',$listnum);
		$this->assign('param',$param);
		
		$this->display('Widget:'.$tpl);
	}

	public function js(){
		$path=RUNTIME_PATH.'js';
		@mkdir($path);

		$cache_name=MODULE_NAME.'-'.CONTROLLER_NAME.'-'.ACTION_NAME;
		$filename=md5($cache_name).'.js';
		//echo $filename;

		$dir[]='Public/Apps/'.MODULE_NAME.'/';
		$url[]=array(
			'global.js',
		);
		


		$dir[]='Public/Apps/'.MODULE_NAME.'/'.CONTROLLER_NAME.'/';
		$url[]=array(
			ACTION_NAME.'.js',
		);

		$js=F($cache_name);
		$js='';
		if(empty($js)){
			$js='';
			foreach($url as $key=>$val){
				foreach($val as $v){
					if(file_exists($dir[$key].$v)==true){
						$body=trim(compress(file_get_contents($dir[$key].$v)));
						
						$tmp=explode(chr(13).chr(10),$body);
						$file=array();
						foreach($tmp as $l){
							$l=trim($l);
							if($l!=''){
								$file[]=$l;
							}
						}

						//file_put_contents('mobile/'.basename($v),implode(chr(13).chr(10),$file));
						
						$js.=implode(chr(13).chr(10),$file);

					}
				}
			}

			file_put_contents($path.'/'.$filename,$js);
			F($cache_name,$js);
		}

		//if(!file_exists($path.'/'.$filename)){
			//file_put_contents($path.'/'.$filename,$js);
		//}
		echo '<script src="/Runtime/js/'.$filename.'"></script>';

	}
	

	
	/**
	* 根据配置创建表单
	* @param integer $formtpl_id 模板ID
	* @param array $param 
	* @param array $rs 	选项数据
	*/
	public function form($formtpl_id,$rs,$param=null){
		$tpl=$param['tpl']?$param['tpl']:'form';

		$do=D('FormtplGroupRelation');
		$list=$do->relation(true)->relationWhere('FormtplFields','active=1')->relationField('FormtplFields','*')->where(array('formtpl_id'=>$formtpl_id))->order('sort asc')->select();



		//格式化各项数据
		foreach($list as $key=>$val){
			foreach($val['tplfields'] as $vkey=>$v){
				if($rs[$v['name']]) $list[$key]['tplfields'][$vkey]['value']=$rs[$v['name']];
				//html转码
				foreach($v as $vk=>$fv){
					if($fv) $v[$vk]=html_entity_decode($fv);
				}
				//选项数据
				if($v['data']){
					//dump($v['data']);
					$v['data']=eval($v['data']);
					//dump($v['data']);
					$list[$key]['tplfields'][$vkey]['data_setting']	= $v['data'];
					$list[$key]['tplfields'][$vkey]['data']=$v['data']['data'];
					$list[$key]['tplfields'][$vkey]['field']=$v['data']['field'];
					$list[$key]['tplfields'][$vkey]['level']=$v['data']['level'];
				}

				//读取数据并格式化
				if($v['fun_read'] && $rs[$v['name']]){
					$list[$key]['tplfields'][$vkey]['value']=eval($v['fun_read']);
				}

				//默认值
				if($v['default'] && empty($rs)){
					$list[$key]['tplfields'][$vkey]['value']=$v['default'];
				}

			}
		}

		//dump($list);
		$this->assign('list',$list);
		$this->assign('param',$param);
		$this->display('Widget:'.$tpl);
	}
	
	
	//获取分类
	public function sortlist($param=null){
		$tpl=$param['tpl']?$param['tpl']:'sortlist';
		//dump($param);
		$list=get_category($param['option']);	
		//dump($param);
		$this->assign('list',$list);
		$this->assign('param',$param);		
		$this->display($tpl);	
	}
        
	//列表页
    public function listable($param=null){
        $tpl=$param['tpl']?$param['tpl']:'listable';

        $param['level']=$param['level']?$param['level']:0; //深度，适应多级分列表排序

		$order_url=$param['order_url']?$param['order_url']: __ACTION__;

		$colnum=count($param['items']);
		$thead='<thead>';		
		foreach($param['items'] as $key=>$val){			
			$order_icon='';
			$order='';
			if($val['norder']!=1){				
				$order_icon='<a href="'.U($order_url,array_merge(I('get.'),array('orderby'=>$val['field'].'-desc')),false).'"><i class="fa fa-angle-down"></i></a>';
				
				$order=I('get.orderby');
				if($order) {
					$order_str=@explode('-',$order);
					$order_icon=$order_str[1]=='asc'?'<a href="'.U($order_url,array_merge(I('get.'),array('orderby'=>$val['field'].'-desc')),false).'"><i class="fa fa-angle-down"></i></a>':'<a href="'.U($order_url,array_merge(I('get.'),array('orderby'=>$val['field'].'-asc')),false).'"><i class="fa fa-angle-up"></i></a>';
				}
				
			}
			$thead.='<th nowrap="nowrap" '.$val['td_attr'].'><strong>'.$val['title'].'</strong> '.$order_icon.'</th>';
		}
		$thead.='</thead>';
			
		$tbody='<tbody'.($param['thead']==1?' class="no-border-y"':'').' id="sort-'.$param['level'].'">';
		$param['level']++;
		foreach($param['data'] as $val){		
			$tbody.='<tr id="'.$val['id'].'" '.$param['tr_attr'].'>';
			foreach($param['items'] as $v){
				foreach($v as $key=>$vl){
					$v[$key]=url_cmp(array('url_rex'=>$vl,'rs'=>$val));
				}
				//dump($v);
				if($v['function']){
					$value=eval(html_entity_decode($v['function']));
				}else $value=$val[$v['field']];

				switch($v['type']){
					case 'html':
						$value=$v['html'];
					break;
					case 'active':
						$value=$v['data'][$val[$v['field']]];
					break;

					case 'images':
						$value='<img src="'.myurl($val[$v['field']],80).'" class="img-s">';
						
					break;
                    case 'time':
						$value= ((int)$v['data'][2] > time()?1:0);
                        $value= $v['data'][$value];
					break;
					case 'qq':						
						$value= $value?'<a href="http://wpa.qq.com/msgrd?v=3&uin='.$value.'&site=qq&menu=yes" target="_blank"><img src="http://wpa.qq.com/pa?p=1:'.$value.':4" border="0" /> '.$value.'</a>':'';
					break;
				}
				if($v['url']) $value='<a href="'.$v['url'].'" '.($v['target']?'target="'.$v['target'].'"':'').'>'.$value.'</a>';
				$html=$v['before'].'<span '.$v['attr'].'>'.$v['icon'].$value.'</span>'.$v['end'];
				$tbody.='<td '.$v['td_attr'].' style="word-break:break-all">'.$html.'</td>';
			}
			$tbody.='</tr>';

			//是否显示字项详情
			if(!empty($param['item_view'])){		
				$tbody.='<tr class="hide" id="view-'.$val['id'].(isset($val['_id'])?$val['_id']:'').'"><td colspan="'.$colnum.'" class="p10" id="item-'.$val['id'].(isset($val['_id'])?$val['_id']:'').'">';
				$tbody.='</td></tr>';
			}

			if(!empty($val['dlist'])){
				$tmp=array();
				$tmp=$param;
				$tmp['data']=$val['dlist'];
				$tmp['thead']=1;
				$tbody.='<tr id="'.$val['id'].(isset($val['_id'])?$val['_id']:'').'"><td colspan="'.$colnum.'" style="padding:20px 0 20px 80px;">';
				$tbody.=$this->listable($tmp);
				$tbody.='</td></tr>';				
			}
		}
		$tbody.='</tbody>';
		
		return '<table'.($param['thead']==1?' class="no-border"':'').'>'.($param['thead']!==1?$thead:'').$tbody.'</table>';        
    }
    


	//图表，最近七天统计记录
	public function echart_total(){
		$do=M('totals_basic');
		$list=$do->field('day,member,open_store_success,goods_num')->order('day desc')->limit(7)->select();

		foreach($list as $key=>$val){
			$day[]=$val['day'];
			$member[]=$val['member']==""?0:$val['member'];
			$goods[]=$val['goods_num']==""?0:$val['goods_num'];
			$store[]=$val['open_store_success']==""?0:$val['open_store_success'];
		}

		$option['title']=array('text'=>'最近七天统计');
		$option['tooltip']=array('trigger'=>'axis');
		$option['legend']=array('data'=>array('新增会员','新增商品','新增店铺'));
		$option['toolbox']=array(
				'show'			=>true,
				'feature'		=>array(
						'mark'			=>array('show'=>true),
						'dataView'		=>array('show'=>true,'readOnly'=>false),
						'magicType'		=>array('show'=>true,'type'=>array('line','bar')),
						'restore'		=>array('show'=>true),
						'saveAsImage'	=>array('show'=>true)
					),
			);

		$option['calculable']=true;
		$option['xAxis']=array(array(
				'type'	=>'category',
				'data'	=>$day
			));

		$option['yAxis']=array(array('type'=>'value'));

		$option['series']=array(
				array(
						'name'	=>'新增会员',
						'type'	=>'bar',
						'data'	=>$member,
					),
				array(
						'name'	=>'新增商品',
						'type'	=>'bar',
						'data'	=>$goods,
					),
				array(
						'name'	=>'新增店铺',
						'type'	=>'bar',
						'data'	=>$store,
					),
			);

		echo 'var option='.json_encode($option,JSON_UNESCAPED_UNICODE).';';
	}

	public function echart_money(){

		//$do=M('totals');
		//$list=$do->order('day desc')->limit(7)->select();
		
		$list = M('totals_trans')->field('day,day_order_total,day_order_success_total,day_accept_total,day_refund_money,refund_success_money')->order('day desc')->limit(7)->select();
		foreach($list as $val){
			$day[]=$val['day'];
			$order_total[]=$val['day_order_total']==""?0:$val['day_order_total'];
			$order_success_total[]=$val['day_order_success_total']==""?0:$val['day_order_success_total'];
			$accept_total[]=$val['day_accept_total']==""?0:$val['day_accept_total'];
			//$refund_money[]=$val['day_refund_money']==""?0:$val['day_refund_money'];
			//$refund_success_money[]=($val['refund_success_money']=="")?0:$val['refund_success_money'];
			
		}
//		$list = M('totals_promotion')->field('day_activity_money,ad_money')->order('day desc')->limit(7)->select();
//		foreach($list as $val){
//			$activity_money[]=$val['day_activity_money']==""?0:$val['day_activity_money'];
//			$ad[]=$val['ad_money']==""?0:$val['ad_money'];
//		}

		$option['title']=array('text'=>'最近七天交易统计');
		$option['tooltip']=array('trigger'=>'axis');
		//$option['legend']=array('data'=>array('下单总金额','成交总金额','确认收货总金额','申请退款总金额','退款成功总金额','活动成交总金额','新增广告投放总金额'));
        $option['legend']=array('data'=>array('下单总金额','成交总金额','确认收货总金额'));
		$option['toolbox']=array(
				'show'			=>true,
				'feature'		=>array(
						'mark'			=>array('show'=>true),
						'dataView'		=>array('show'=>true,'readOnly'=>false),
						'magicType'		=>array('show'=>true,'type'=>array('line','bar')),
						'restore'		=>array('show'=>true),
						'saveAsImage'	=>array('show'=>true)
					),
			);
		$option['grid']=array('left'=>'3%','right'=>'4%','bottom'=>'3%','containLabel'=>true);
		$option['calculable']=true;
		$option['xAxis']=array(array(
				'type'	=>'category',
				'boundaryGap'=>false,
				'data'	=>$day
			));

		$option['yAxis']=array(array('type'=>'value'));

		$option['series']=array(
				array(
						'name'	=>'下单总金额',
						'type'	=>'line',
						'stack'	=>'总量',
						'areaStyle'=>array('normal'=>array()),
						'data'	=>$order_total,
					),
    		    array(
    		        'name'	=>'成交总金额',
    		        'type'	=>'line',
    		        'stack'	=>'总量',
    		        'areaStyle'=>array('normal'=>array()),
    		        'data'	=>$order_success_total,
    		    ),
				array(
						'name'	=>'确认收货总金额',
						'type'	=>'line',
						'stack'	=>'总量',
						'areaStyle'=>array('normal'=>array()),
						'data'	=>$accept_total,
					),
//				array(
//						'name'	=>'申请退款总金额',
//						'type'	=>'line',
//						'stack'	=>'总量',
//						'areaStyle'=>array('normal'=>array()),
//						'data'	=>$refund_money,
//					),
//				array(
//						'name'	=>'退款成功总金额',
//						'type'	=>'line',
//						'stack'	=>'总量',
//						'areaStyle'=>array('normal'=>array()),
//						'data'	=>$refund_success_money,
//					),
				/*
				array(
						'name'	=>'活动成交总金额',
						'type'	=>'line',
						'stack'	=>'总量',
						'areaStyle'=>array('normal'=>array()),
						'data'	=>$activity_money,
					),
				*/
//				array(
//						'name'	=>'新增广告投放总金额',
//						'type'	=>'line',
//						'stack'	=>'总量',
//						'areaStyle'=>array('normal'=>array()),
//						'data'	=>$ad,
//					),
			);

		echo 'var option='.json_encode($option,JSON_UNESCAPED_UNICODE).';';

	}
	
	/*
	 * 抽奖统计
	 * 
	 * */
	public function luckdraw_total(){
	

	    $list=M('luckdraw_statistics')->order('atime desc')->limit(7)->select();
	
	    foreach($list as $val){
	        $day[]=$val['day'];
            $luck_num[]=$val['luck_num']==""?0:$val['luck_num'];

	        $use_tangbao[]=$val['use_tangbao']==""?0:$val['use_tangbao'];
            $winning_num[]=$val['winning_num']==""?0:$val['winning_num'];
            $winning_score[]=($val['winning_score']=="")?0:$val['winning_score'];
	    }
	
	    $option['title']=array('text'=>'最近7天抽奖数据统计');
	    $option['tooltip']=array('trigger'=>'axis');
	    $option['legend']=array('data'=>array('抽奖次数','消费唐宝总数','中奖次数','中奖积分数'));
	    $option['toolbox']=array(
	        'show'			=>true,
	        'feature'		=>array(
	            'mark'			=>array('show'=>true),
	            'dataView'		=>array('show'=>true,'readOnly'=>false),
	            'magicType'		=>array('show'=>true,'type'=>array('line','bar')),
	            'restore'		=>array('show'=>true),
	            'saveAsImage'	=>array('show'=>true)
	        ),
	    );
	    $option['grid']=array('left'=>'3%','right'=>'4%','bottom'=>'3%','containLabel'=>true);
	    $option['calculable']=true;
	    $option['xAxis']=array(array(
	        'type'	=>'category',
	        'boundaryGap'=>false,
	        'data'	=>$day
	    ));
	
	    $option['yAxis']=array(array('type'=>'value'));
	
	    $option['series']=array(
	        array(
	            'name'	=>'抽奖次数',
	            'type'	=>'line',
	            'stack'	=>'总量',
	            'areaStyle'=>array('normal'=>array()),
	            'data'	=>$luck_num,
	        ),

	        array(
	            'name'	=>'消费唐宝总数',
	            'type'	=>'line',
	            'stack'	=>'总量',
	            'areaStyle'=>array('normal'=>array()),
	            'data'	=>$use_tangbao,
	        ),
	        array(
	            'name'	=>'中奖次数',
	            'type'	=>'line',
	            'stack'	=>'总量',
	            'areaStyle'=>array('normal'=>array()),
	            'data'	=>$winning_num,
	        ),
	        array(
	            'name'	=>'中奖积分数',
	            'type'	=>'line',
	            'stack'	=>'总量',
	            'areaStyle'=>array('normal'=>array()),
	            'data'	=>$winning_score,
	        ),
	    );
	
	    echo 'var option='.json_encode($option,JSON_UNESCAPED_UNICODE).';';
	
	}
	
	/*
	 * 商城基础数据统计
	 * 
	 * */
	public function base_total(){
		//基础数据
	    $list=M('totals_basic')->field('day,member,open_store_success,goods_num,comment_num')->order('day desc')->limit(30)->select();
		//促销数据
	    //$list2=M('totals_promotion')->field('brand_num')->order('day desc')->limit(30)->select();
	    foreach($list as $val){
	        $day[]=$val['day'];
	        $member[]=$val['member']==""?0:$val['member'];
	        //$open_store_success[]=$val['open_store_success']==""?0:$val['open_store_success'];
	        $goods_num[]=$val['goods_num']==""?0:$val['goods_num'];
	        //$comment_num[]=($val['comment_num']=="")?0:$val['comment_num'];
	    }
//		foreach($list2 as $val){
//	        $brand_num[]=$val['brand_num']==""?0:$val['brand_num'];
//	    }
	
	    $option['title']=array('text'=>'最近30天新增数据统计');
	    $option['tooltip']=array('trigger'=>'axis');
	    //$option['legend']=array('data'=>array('新增用户数','新增开店成功店铺','新增商品数量','新增品牌推广数量','新增评价数量'));
        $option['legend']=array('data'=>array('新增用户数','新增商品数量'));
	    $option['toolbox']=array(
	        'show'			=>true,
	        'feature'		=>array(
	            'mark'			=>array('show'=>true),
	            'dataView'		=>array('show'=>true,'readOnly'=>false),
	            'magicType'		=>array('show'=>true,'type'=>array('line','bar')),
	            'restore'		=>array('show'=>true),
	            'saveAsImage'	=>array('show'=>true)
	        ),
	    );
	    $option['grid']=array('left'=>'3%','right'=>'4%','bottom'=>'3%','containLabel'=>true);
	    $option['calculable']=true;
	    $option['xAxis']=array(array(
	        'type'	=>'category',
	        'boundaryGap'=>false,
	        'data'	=>$day
	    ));
	
	    $option['yAxis']=array(array('type'=>'value'));
	
	    $option['series']=array(
	        array(
	            'name'	=>'新增用户数',
	            'type'	=>'line',
	            'stack'	=>'总量',
	            'areaStyle'=>array('normal'=>array()),
	            'data'	=>$member,
	        ),
//	        array(
//	            'name'	=>'新增开店成功店铺',
//	            'type'	=>'line',
//	            'stack'	=>'总量',
//	            'areaStyle'=>array('normal'=>array()),
//	            'data'	=>$open_store_success,
//	        ),
	        array(
	            'name'	=>'新增商品数量',
	            'type'	=>'line',
	            'stack'	=>'总量',
	            'areaStyle'=>array('normal'=>array()),
	            'data'	=>$goods_num,
	        ),
//	        array(
//	            'name'	=>'新增品牌推广数量',
//	            'type'	=>'line',
//	            'stack'	=>'总量',
//	            'areaStyle'=>array('normal'=>array()),
//	            'data'	=>$brand_num,
//	        ),
//	        array(
//	            'name'	=>'新增评价数量',
//	            'type'	=>'line',
//	            'stack'	=>'总量',
//	            'areaStyle'=>array('normal'=>array()),
//	            'data'	=>$comment_num,
//	        ),
	    );
	
	    echo 'var option='.json_encode($option,JSON_UNESCAPED_UNICODE).';';
	
	}
	
	/*
	 * 商城促销数据统计
	 *
	 * */
	public function promotion_total(){
	
	    $do=M('totals_promotion');
	    $list=$do->order('day desc')->limit(30)->select();
	
	    foreach($list as $val){
	        $day[]=$val['day'];
	        $activity_pay_num[]=$val['activity_pay_num']==""?0:$val['activity_pay_num'];
	        $day_activity_money[]=$val['day_activity_money']==""?0:$val['day_activity_money'];
	        $use_coupon_num[]=$val['use_coupon_num']==""?0:$val['use_coupon_num'];
	        $use_coupon_money[]=$val['use_coupon_money']==""?0:$val['use_coupon_money'];
	        $ad_money[]=($val['ad_money']=="")?0:$val['ad_money'];
	        $sucai_num[]=($val['sucai_num']=="")?0:$val['sucai_num'];
	    }
	
	    $option['title']=array('text'=>'最近30天新增数据统计');
	    $option['tooltip']=array('trigger'=>'axis');
	    $option['legend']=array('data'=>array('活动成交笔数','活动成交总金额','被使用优惠券数量','被使用优惠券面值总额','新增广告投放金额','新增投放素材'));
	    $option['toolbox']=array(
	        'show'			=>true,
	        'feature'		=>array(
	            'mark'			=>array('show'=>true),
	            'dataView'		=>array('show'=>true,'readOnly'=>false),
	            'magicType'		=>array('show'=>true,'type'=>array('line','bar')),
	            'restore'		=>array('show'=>true),
	            'saveAsImage'	=>array('show'=>true)
	        ),
	    );
	    $option['grid']=array('left'=>'3%','right'=>'4%','bottom'=>'3%','containLabel'=>true);
	    $option['calculable']=true;
	    $option['xAxis']=array(array(
	        'type'	=>'category',
	        'boundaryGap'=>false,
	        'data'	=>$day
	    ));
	
	    $option['yAxis']=array(array('type'=>'value'));
	
	    $option['series']=array(
	        array(
	            'name'	=>'活动成交笔数',
	            'type'	=>'line',
	            'stack'	=>'总量',
	            'areaStyle'=>array('normal'=>array()),
	            'data'	=>$activity_pay_num,
	        ),
	        array(
	            'name'	=>'活动成交总金额',
	            'type'	=>'line',
	            'stack'	=>'总量',
	            'areaStyle'=>array('normal'=>array()),
	            'data'	=>$day_activity_money,
	        ),
	        array(
	            'name'	=>'被使用优惠券数量',
	            'type'	=>'line',
	            'stack'	=>'总量',
	            'areaStyle'=>array('normal'=>array()),
	            'data'	=>$use_coupon_num,
	        ),
	        array(
	            'name'	=>'被使用优惠券面值总额',
	            'type'	=>'line',
	            'stack'	=>'总量',
	            'areaStyle'=>array('normal'=>array()),
	            'data'	=>$use_coupon_money,
	        ),
	        array(
	            'name'	=>'新增广告投放金额',
	            'type'	=>'line',
	            'stack'	=>'总量',
	            'areaStyle'=>array('normal'=>array()),
	            'data'	=>$ad_money,
	        ),
	        array(
	            'name'	=>'新增投放素材',
	            'type'	=>'line',
	            'stack'	=>'总量',
	            'areaStyle'=>array('normal'=>array()),
	            'data'	=>$sucai_num,
	        ),
	    );
	
	    echo 'var option='.json_encode($option,JSON_UNESCAPED_UNICODE).';';
	
	}
	
	//代购数据统计
	public function echart_daigou(){
		$do=M('totals');
	    $list=$do->order('day desc')->limit(7)->select();
		
		foreach($list as $key=>$val){
			$day[]=$val['day'];
			$data1[]=$val['pc_pay_daigou']==""?0:$val['pc_pay_daigou'];
			$data2[]=$val['pc_daigou_total']==""?0:$val['pc_daigou_total'];
			$data3[]=$val['pc_total_moeny']==""?0:$val['pc_total_moeny'];
			$data4[]=$val['wap_pay_daigou']==""?0:$val['wap_pay_daigou'];
			$data5[]=$val['wap_daigou_total']==""?0:$val['wap_daigou_total'];
			$data6[]=$val['wap_total_moeny']==""?0:$val['wap_total_moeny'];
		}

		$option['title']=array('text'=>'');
		$option['tooltip']=array('trigger'=>'axis');
		$option['legend']=array('data'=>array('PC代购支付数量','PC代购总额','PC销售总额','Wap代购支付数量','Wap代购总额','Wap销售总额'));
		$option['toolbox']=array(
				'show'			=>true,
				'feature'		=>array(
						'mark'			=>array('show'=>false),
						'dataView'		=>array('show'=>false,'readOnly'=>false),
						'magicType'		=>array('show'=>false,'type'=>array('line','bar')),
						'restore'		=>array('show'=>false),
						'saveAsImage'	=>array('show'=>false)
					),
			);

		$option['calculable']=false;
		$option['xAxis']=array(array(
				'type'	=>'category',
				'data'	=>$day
			));

		$option['yAxis']=array(array('type'=>'value'));

		$option['series']=array(

				array(
						'name'	=>'PC代购支付数量',
						'type'	=>'bar',
						'data'	=>$data1,
					),
				array(
						'name'	=>'PC代购总额',
						'type'	=>'bar',
						'data'	=>$data2,
					),
				array(
						'name'	=>'PC销售总额',
						'type'	=>'bar',
						'data'	=>$data3,
					),
				array(
						'name'	=>'Wap代购支付数量',
						'type'	=>'bar',
						'data'	=>$data4,
					),
				array(
						'name'	=>'Wap代购总额',
						'type'	=>'bar',
						'data'	=>$data5,
					),
				array(
						'name'	=>'Wap销售总额',
						'type'	=>'bar',
						'data'	=>$data6,
					),
			);

		echo 'var option='.json_encode($option,JSON_UNESCAPED_UNICODE).';';
	}

	/**
	* 表单生成器
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

	/**
	* 表单生成器
	* @param array $param 字段选项
	* @param array $data 值
	*/
	public function searchform($param,$data,$col=6){
		$html=array();
		$form=new \Org\Util\BuildSearchForm();
		$form->value=$data;
		$i=0;
		foreach($param as $key=>$val){
			//选项数据
			if($val['data']){
				//dump($v['data']);
				$tmp=eval(html_entity_decode($val['data']));

				$val['data']=$tmp['data'];
				$val['field']=$tmp['field'];
			}
			//默认值
			if($val['default']){
				$val['value']=$val['default'];
			}			

			$html[]=$form->$val['formtype']($val)->create();
			$i++;
			if(($i) % $col==0 ) $html[]='</tr><tr>';
		}
		$html=@implode('',$html);

		$html='<table class="no-bordered no-bg"><tr>'.$html.'</tr></table>';
		echo $html;
	}
	/**
	* 商品参数
	*/
	public function param_option($option,$value=''){
		$html=array();
		$form=new \Org\Util\BuildForm();
		
		if(!empty($value)){
			$form->value=$value;		
		}


		foreach($option as $val){
			$data=array();
			if($val['options']) {
				$val['options']=explode(',',trim($val['options']));
				foreach($val['options'] as $v){
					$data[]=array(trim($v));
				}
			}
			//dump($data);
			$item=array();
			$item['formtype']	='text';
			$item['label']		=$val['param_name'];
			$item['name']		='param_'.$val['id'];
			$item['is_need']	=$val['is_need'];

			switch($val['type']){
				case 1:
					$item['formtype']='select';
					$item['field']	=array(0,0);
					$item['data']	=$data;

				break;
				case 2:
					$item['formtype']='checkbox';
					$item['field']	=array(0,0);
					$item['data']	=$data;					
				break;

			}

			$html[]=$form->$item['formtype']($item)->create();
		}

		$html=@implode('',$html);
		echo $html;

	}

	/**
	* 人脉图
	* @param int $uid 用户ID
	*/
	public function renmai($uid){
		if(empty($uid)) exit;

		$do=M('user');
		$rs=$do->where(array('id'=>$uid))->field('id,nick as name')->find();
		$rs['children']=$this->downline_user($uid,10);
		$data=array($rs);

		$option['title']=array('text'=>'关系图');
		$option['toolbox']=array(
				'show'			=>true,
				'feature'		=>array(
						'mark'			=>array('show'=>true),
						'dataView'		=>array('show'=>true,'readOnly'=>false),						
						'restore'		=>array('show'=>true),
						'saveAsImage'	=>array('show'=>true)
					),
			);

		$option['series']=array(
			array(
				'name'			=>'人脉',
				'type'			=>'tree',
	            'orient'		=>'horizontal',  // vertical horizontal
	            'rootLocation'	=>array('x'=>100,'y'=>230), // 根节点位置  {x: 100, y: 'center'}
	            'nodePadding'	=>18,
	            'layerPadding'	=>200,
	            'hoverable'		=>false,
	            'roam'			=>true,
	            'symbolSize'	=>16,
	            'itemStyle'		=>array(
	            		'normal'	=>array(
			                    'color'		=>'#4883b4',
			                    'label'		=>array(
			                        'show'		=>true,
			                        'position'	=>'right',
			                        'formatter'	=>'"{b}"',
			                        'textStyle'	=>array(
			                            'color'		=>'#000',
			                            'fontSize'	=>12,
			                            ),
			                    	),
			                    'lineStyle'		=>array(
			                        	'color'		=>'#ccc',
			                        	'type'		=>'curve' 	// 'curve'|'broken'|'solid'|'dotted'|'dashed'
			                    ),	            				
	            			),
		                'emphasis'	=>array(
		                    'color'	=>'#4883b4',
		                    'label'	=>array(
		                        	'show'=>false,
		                    	),
		                    'borderWidth'=>0
		                ),          		
	            	),
	           	'data'	=>$data,
	        ),
		);
		echo 'var option='.json_encode($option,JSON_UNESCAPED_UNICODE).';';
	}

	public function downline_user($uid,$level=3){
		if($level<=0) return false;
		$level--;
		$do=M('user');

		$list=$do->where('up_uid='.$uid)->field('id,nick as name')->select();
		foreach($list as $key=>$val){
			$list[$key]['children']=$this->downline_user($val['id'],$level);

		}
		return $list;
	}

	/**
	* 对店铺的条件要求
	*/
	public function loadhtml($param=null){
		$this->assign('param',$param);
		$tpl = $param['data_setting']['tpl'] ? $param['data_setting']['tpl'] : __FUNCTION__;
		return $this->fetch($tpl);
	}


    public function brand_category($ids){
        if(empty($ids)) return;
        $ids = explode(',',$ids);
        foreach($ids as $val){
            $rs = M('goods_category')->cache(true)->where(['id' => $val])->field('id,sid,category_name')->find();
            $us = M('goods_category')->cache(true)->where(['id' => $rs['sid']])->field('id,sid,category_name')->find();
            $html.='<li data-id="'.$rs['id'].'" data-first_id="'.$us['id'].'">'.$us['category_name'].' > '.$rs['category_name'].'</li>';
        }

        return $html;
    }

    public function cred_images($images){
        if(empty($images)) return;
        $images = explode(',',$images);
        foreach($images as $val){
            $html.='<li data-url="'.$val.'">';
            $html.='<img src="'.myurl($val,80).'" alt="资质图片"></li>';
        }

        return $html;
    }

    /**
     * 攻取订单是否参与活动
     * @param string $s_no 商家订单号
     */
    public function orders_activity($s_no){
        $result = Activity::getActivityByOrder($s_no);
        if($result){
            foreach($result as $val){
                $html .='<div class="text-danger strong ft16">'.$val['remark'].'</div>';
            }

            echo $html;
        }
    }
}
