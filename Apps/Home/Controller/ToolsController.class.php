<?php
namespace Home\Controller;
use Think\Db\Driver\Mongo;
use Think\Model;
class ToolsController extends CommonController {
    public function index(){
		//$name=session(array('name'=>'user','domain'=>C('DOMAIN')));
		//$res=$this->erp_userinfo('748de32406b2d7fc191119dab45523c0');
		dump(C('cfg'));
		$res=$this->curl_get('https://open.999qf.cn/api/auth.json');
		dump($res);

		$do=M('store_notname');
		$list=$do->select();
		foreach($list as $val){
			$file.=$val['keyword'].chr(10);
		}
		file_put_contents('tt.csv', $file);
    }
	
	public function sub_domain(){
		dump(C('APP_SUB_DOMAIN_RULES'));
		$file2='127.0.0.1 ';
		foreach(C('APP_SUB_DOMAIN_RULES') as $key=>$val){
			$file.='127.0.0.1	'.strtolower($key).'.'.C('DOMAIN').chr(13).chr(10);
			$file2.=strtolower($key).'.'.C('DOMAIN').' ';
			//$html=file_get_contents('./Apps/Home/Controller/EmptyController.class.php');
			//$html=str_replace('Home\Controller', $val.'\Controller', $html);
			//file_put_contents('./Apps/'.$val.'/Controller/EmptyController.class.php', $html);
		}
		
		file_put_contents('host.txt',$file.$file2);
	}
	
	public function getsort(){
		set_time_limit(0);
		$url='https://www.taobao.com/markets/tbhome/market-list?spm=a21bo.7724922.8375.2.xePduq#guid-8224';
		$html=curl_file(array('url'=>$url));
		//dump($html);
		preg_match_all("/<a class=\"category-name category-name-level1 J_category_hash\"([\s\S]*?)>([\s\S]*?)<\/a>/ies",$html,$out);
		dump($out);
		
		preg_match_all("/<ul class=\"category-list\"([\s\S]*?)<\/ul>/ies",$html,$out2);
		//dump($out2);
		$do=M('products_sort');
		foreach($out2[0] as $key=>$val){
			$uid=$do->add(array(
				'atime'	=>time(),
				'active'	=>1,
				'name'		=>$out[2][$key]
			));
			preg_match_all("/<li([\s\S]*?)>([\s\S]*?)<\/li>/ies",$val,$out3);
			//dump($out3);
			foreach($out3[0] as $v){
				preg_match_all("/<a([\s\S]*?)>([\s\S]*?)<\/a>/ies",$v,$out4);
				dump($out4);
				
				foreach($out4[2] as $vk=>$vl){
					if($vk==0){
						$sid=$do->add(array(
							'atime'	=>time(),
							'active'	=>1,
							'sid'		=>$uid,
							'name'		=>$vl
						));
					}
				}
				
				foreach($out4[2] as $vk=>$vl){
					if($vk>0){
						$do->add(array(
							'atime'	=>time(),
							'active'	=>1,
							'sid'		=>$sid,
							'name'		=>$vl
						));
					}
				}				
			}
		}
		
	}
	
	
	public function tsort(){
		set_time_limit(0);
		$do=M('products_sort');
		$us=$do->where(array('sid'=>0))->order('sort asc')->select();
		foreach($us as $val){
			$ds=$do->where('sid='.$val['id'])->select();
			foreach($ds as $v){
				$dds=$do->where(array('sid'=>$v['id'],'id'=>array('gt',0)))->select();
				foreach($dds as $d){
					echo $d['name'].'<br>';
					$this->get_tmall($val['name'].' '.$v['name'].' '.$d['name'],$d['id']);
					$do->where('id='.$d['id'])->setField('isget',1);
				}
			}
		}

	}


	public function get_tmall($q,$sid){
		$doc=M('color_sort');
		$dos=M('size_sort');
		
		$do=M('products');
		$url='https://s.taobao.com/search?spm=a230r.1.1998181369.d4919860.1AH46g&q='.urlencode($q).'&commend=all&ssid=s5-e&search_type=item&sourceId=tb.index&initiative_id=tbindexz_20150723&tab=mall';
		$html=curl_file(array('url'=>$url));
		preg_match_all("/\"nid\":\"([\s\S]*?)\"/ies",$html,$out);
		//dump($out);

		foreach($out[1] as $key=>$val){
			if($key<5){
				$data=array();
				$url='https://detail.tmall.com/item.htm?id='.$val;
				if(!$do->where(array('furl'=>$url))->find()){
					$data['furl']=$url;
					echo $url.'<br>';
					$html=curl_file(array('url'=>$url));
					$html=mb_convert_encoding($html,'utf8','gbk');

					preg_match("/<img id=\"J_ImgBooth\" alt=\"([\s\S]*?)\" src=\"([\s\S]*?)\"/ies",$html,$out2);
					$images=MidStr($html,'<ul id="J_UlThumb" class="tb-thumb tm-clear">','</ul>');
					preg_match_all("/<img src=\"([\s\S]*?)\"/ies",$images,$img);
					
					$images=array();
					if($img[1]){
						foreach($img[1] as $pic){
							$images[]='http:'.str_replace('_60x60q90.jpg','',$pic);
						}
					}else{
						$images[]='http:'.str_replace('_430x430q90.jpg','',$out2[2]);
					}
					
					$sku=MidStr($html,'<div class="tb-sku">','</div>');
					$color=MidStr($sku,'<ul data-property="颜色分类"','</ul>');
					preg_match_all("/<span>([\s\S]*?)<\/span>/ies",$color,$cl);
					
					$size=MidStr($sku,'<ul data-property="尺码"','</ul>');
					preg_match_all("/<span>([\s\S]*?)<\/span>/ies",$size,$sl);
					
					$mycolor=$doc->where(array('sid'=>array('gt',0)))->limit(count($cl[1]))->select();
					$mysize=$doc->where(array('sid'=>array('gt',0)))->limit(count($sl[1]))->select();
					

					
					
					//dump($images);exit;
					
					
					$html=MidStr($html,'TShop.Setup(',');');
					
					//echo $html;
					
					



					$html=json_decode($html);
					$html=objectToArray($html);
					//dump($html);exit;
					//echo $html;
					
					$data['atime']=time();
					$data['active']=1;
					$data['name']=$html['itemDO']['title'];
					$data['price']=$html['itemDO']['reservePrice'];				
					$data['price_market']=$data['price']*3;
					$data['code']=$val;
					//$data['nick']=urldecode($html['itemDO']['sellerNickName']);

					$sku=array();
					if($sl[1]){
						foreach($sl[1] as $vk=>$v){
							$sku['size'][]=$v;
							$sku['sizeid'][]=$mysize[$vk]['id'];
						}
						foreach($cl[1] as $vk=>$v){
							$sku['color'][]=$v;
							$sku['colorid'][]=$mycolor[$vk]['id'];
							
							$sku['item'][$mycolor[$vk]['id']]=array(
								'colorid'	=>$mycolor[$vk]['id'],
								'color'		=>$v
							);
							
							foreach($sku['sizeid'] as $vsk=>$vs){
								$sku['item'][$mycolor[$vk]['id']]['dlist'][$vs]=array(
									'colorid'	=>$mycolor[$vk]['id'],
									'color'		=>$v,
									'sizeid'	=>$vs,
									'size'		=>$sku['size'][$vsk],
									'price'		=>$data['price'],
									'num'		=>100,
									'code'		=>$data['code']
									
								);
							}
						}		
					}else{
						foreach($cl[1] as $vk=>$v){
							$sku['color'][]=$v;
							$sku['colorid'][]=$mycolor[$vk]['id'];
							
							$sku['item'][$mycolor[$vk]['id']]=array(
								'colorid'	=>$mycolor[$vk]['id'],
								'color'		=>$v
							);
							
							$sku['item'][$mycolor[$vk]['id']]=array(
									'colorid'	=>$mycolor[$vk]['id'],
									'color'		=>$v,
									'price'		=>$data['price'],
									'num'		=>100,
									'code'		=>$data['code']
									
							);
						}					
						
					}
					
					$data['num']=count($cl[1])*count($sl[1])*100;
					
					//dump($sku);
					//dump($cl);
					//dump($sl);exit;
					$data['sku']='return '.var_export($sku,true).';';
					
					$data['sellerid']=rand(2,4);
					

					//$data['images']=$out2[2]?'http:'.$out2[2]:'';
					$data['images']=$images[0];
					$data['images_album']='return '.var_export($images,true).';';

					$desc=curl_file(array('url'=>'http:'.$html['api']['descUrl']));
					//echo $desc;
					$desc=trim(mb_convert_encoding($desc,'utf8','gbk'));
					$data['content']=substr($desc,10,-2);
					$data['content']=strip_tags($data['content'],'<div><span><img><table><tr><td><thead><tbody><font><strong><br><b><hr>');
					$data['sid']=$sid;
					
					//dump($data);exit;

					//if(!$do->where(array('furl'=>$data['furl']))->find()){
						if($data['name'] && $data['content']){
							$do->add($data);
						}
						//echo $do->getLastSQL();
					//}

					echo $data['name'].'<br>';
					//exit;


					//dump($data);
					//dump($out2);

					usleep(rand(1000,8000));
					//exit;
				}
			}
		}
		

	}

	public function update_products(){
		set_time_limit(0);
		$do=M('products');
		$list=$do->select();
		foreach($list as $val){
			if($val['sku']){
				$sku=eval(html_entity_decode($val['sku']));
				if(empty($sku['size']) && empty($sku['color'])){
					echo $val['id'].$val['name'].'<br>';
					$do->where('id='.$val['id'])->setField('num',100);					
				}elseif(empty($sku['size']) && $sku['color']){
					echo $val['id'].$val['name'].'<br>';
					$do->where('id='.$val['id'])->setField('num',100);
				}
			}
		}
	}
	
	
	public function get_brand(){
		$do=M('brand');
		$dos=M('brand_sort');
		$list=$dos->where(array('sid'=>array('gt',0)))->select();
		foreach($list as $val){
			echo $val['furl'].'<br>';
			$val['furl']='https://list.tmall.com/search_product.htm?q=%C5%AE%D7%B0+%C4%D0%D7%B0';
			$html=curl_file(array('url'=>$val['furl']));
			$html=mb_convert_encoding($html,'utf8','gbk');
			dump($html);
			$page=MidStr($html,'共','页');		
			dump($page);
			
			
			exit;
		}
	}
	
	
	
	//采集品牌
	public function brand(){
		$do=M('brand');
		$dos=M('brand_sort');
		$url='https://brand.tmall.com/brandMap.htm';
		$html=curl_file(array('url'=>$url));
		$html=mb_convert_encoding($html,'utf8','gbk');
		$html=MidStr($html,'<ul class="bF-navList"','</ul>');
		//echo $html;
		
		$html=explode('</li>',$html);
		//dump($html);
		foreach($html as $val){
			preg_match_all("/<a href=\"([\s\S]*?)\"([\s\S]*?)>([\s\S]*?)<\/a>/ies",$val,$out);
			dump($out);
			
			foreach($out[1] as $key=>$v){
				if($key>0){
					if(!$dos->where(array('name'=>$out[3][$key]))->find()){
						$sid=$dos->add(array(
							'atime'	=>time(),
							'active'=>1,
							'sid'	=>$uid,
							'furl'	=>$v,
							'name'	=>$out[3][$key]
						));
					}
					
					/*
					$html=curl_file(array('url'=>$v));
					echo $html;
					$html=mb_convert_encoding($html,'utf8','gbk');
					dump($html);
					$page=MidStr($html,'共','页');
					$page=$page?$page:1;
					
					dump($page);
					*/
					
					
					//exit;
					
				}else{
					if($urs=$dos->where(array('name'=>$out[3][$key]))->find()){
						$uid=$urs['id'];
					}else{
						$uid=$dos->add(array(
							'atime'	=>time(),
							'active'=>1,
							'furl'	=>$v,
							'name'	=>$out[3][$key]
						));						
					}			
				}
				
				
			}
			
			//exit;
		}
		
	}
	
	public function jd_sort(){
		set_time_limit(0);
		$do=M('products_sort');
		$furl='http://www.jd.com/allSort.aspx';
		$html=curl_file(array('url'=>$furl));
		$html=mb_convert_encoding($html,'utf8','gbk');
		$html=MidStr($html,'<div class="w" id="allsort">','<!--彩票-->');
		preg_match_all("/<h2>([\s\S]*?)<\/h2>/ies",$html,$out);
		preg_match_all("/<div class=\"mc\">([\s\S]*?)<\/div>/ies",$html,$out2);
		//dump($out);
		//dump($out2);
		foreach($out[1] as $key=>$val){
			$val=trim(strip_tags($val));
			echo $val.'<br>';
			$uid=$do->add(array(
				'atime'	=>time(),
				'active'	=>1,
				'name'	=>$val
			));
			
			preg_match_all("/<dl([\s\S]*?)>([\s\S]*?)<\/dl>/ies",$out2[1][$key],$out3);
			//dump($out3);
			
			foreach($out3[2] as $vkey=>$v){
				$dt=trim(strip_tags(MidStr($v,'<dt>','</dt>')));
				echo '--'.$dt.'<br>';
				$usid=$do->add(array(
					'atime'	=>time(),
					'active'	=>1,
					'name'	=>$dt,
					'sid'	=>$uid
				));				
				preg_match_all("/<em>([\s\S]*?)<\/em>/ies",$v,$out4);
				foreach($out4[1] as $vv){
					$vv=trim(strip_tags($vv));
					echo '-------'.$vv.'<br>';
					$do->add(array(
						'atime'	=>time(),
						'active'	=>1,
						'name'	=>$vv,
						'sid'	=>$usid
					));						
				}
			}
			
			
		}
		//echo $html;
	}
	
	//获取品牌
	public function get_brand2(){
		set_time_limit(0);
		$do=M('products');
		$dos=M('products_sort');
		$dob=M("brand");
		
		$list=$dos->where('sid=0')->order('sort asc')->select();
		//dump($list);
		
		foreach($list as $val){
			$dlist=$dos->where('sid='.$val['id'])->select();
			//dump($dlist);
			foreach($dlist as $v){
				$plist=$do->where(array('sid'=>array('in',sortid(array('table'=>'products_sort','sid'=>$v['id'])))))->select();
				//dump($plist);
				foreach($plist as $pv){
					$html=curl_file(array('url'=>$pv['furl']));
					$html=mb_convert_encoding($html,'utf8','gbk');
					$bid=MidStr($html,'品牌:&nbsp;','</li>');
					$bid=trim(html_entity_decode($bid));
					echo $bid;
					
					if($rs=$dob->where('name="'.$bid.'"')->find()){
						$rs['sid']=explode(',',$rs['sid']);
						if(!in_array($v['id'],$rs['sid'])){
							$rs['sid'][]=$v['id'];
							$dob->where('id='.$rs['id'])->setField('sid',implode(',',$rs['sid']));
						}
					}else{
						$dob->add(array(
							'atime'	=>time(),
							'active'=>1,
							'memberid'	=>2,
							'sid'	=>$v['id'],
							'name'	=>$bid
						));						
					}
					
					
					//$url='//brand.tmall.com/brandInfo.htm?brandId='.$bid;

					//$html=curl_file(array('url'=>$bid,'referer'=>'https://brand.tmall.com/brandMap.htm'));
					//echo $html;
					//exit;
					usleep(rand(1000,8000));
				}
				
				if(!empty($plist)) break;
			}
		}
		
	}

	public function test_session(){
		session('mytest','123456');
		dump(session());
	}
	public function echo_session(){
		dump(session());
	}	

	public function csort(){
		set_time_limit(0);
		$do2=M('products_sort');
		$do=M('goods_type','t_');
		$list=$do->where(array('gt_code'=>array('like','%00000')))->select();

		//dump($do->getLastSQL());
		foreach($list as $val){
			dump($val);

			$s1=$do2->add(array(
				'id'		=>$val['gt_id'],
				'atime'		=>time(),
				'ip'		=>get_client_ip(),
				'name'		=>$val['gt_name'],
				'tb_cid'	=>$val['gt_taobaocid'],
				'erp_cid'	=>$val['gt_id'],
				'pinyin'	=>substr(strtoupper(Pinyin($val['gt_name'])),0,1),
				'sid'		=>0
			));

			$str=substr($val['gt_code'],0,2);
			$ls=$do->where(array('_string'=>'gt_code like "'.$str.'%" and gt_code like "%000"','gt_id'=>array('neq',$val['gt_id'])))->order('gt_order asc')->select();
			
			foreach($ls as $v){
				$s2=$do2->add(array(
					'id'		=>$v['gt_id'],
					'atime'		=>time(),
					'ip'		=>get_client_ip(),
					'name'		=>$v['gt_name'],
					'tb_cid'	=>$v['gt_taobaocid'],
					'erp_cid'	=>$v['gt_id'],
					'pinyin'	=>substr(strtoupper(Pinyin($v['gt_name'])),0,1),
					'sid'		=>$s1
				));
				dump($v);
				$vstr=substr($v['gt_code'],2,2);
				$dls=$do->where(array('_string'=>'gt_code like "'.$str.$vstr.'%"','gt_id'=>array('neq',$v['gt_id'])))->order('gt_order asc')->select();

				foreach($dls as $vl){
					$s3=$do2->add(array(
						'id'		=>$vl['gt_id'],
						'atime'		=>time(),
						'ip'		=>get_client_ip(),
						'name'		=>$vl['gt_name'],
						'tb_cid'	=>$vl['gt_taobaocid'],
						'erp_cid'	=>$vl['gt_id'],
						'pinyin'	=>substr(strtoupper(Pinyin($vl['gt_name'])),0,1),
						'sid'		=>$s2
					));					
				}
			}

			dump($do->getLastSQL());

		}



	}


	public function get_1688_attrs(){
		set_time_limit(0);
		$do=M('products_sort');
		$list=get_category(array('table'=>'products_sort','level'=>3));
		$do=M('attribute_sort');
		foreach($list as $val){
			foreach($val['dlist'] as $vl){

				if(empty($vl['dlist'])){					
					
					if(!$rs=$do->where(array('cid'=>$vl['id']))->find()){
						dump($do->getLastSQL());
						$this->get_1688_attr($vl);
					}
					
					
				}else{
					foreach($vl['dlist'] as $v){
						if(!$rs=$do->where(array('cid'=>$v['id']))->find()){
							dump($do->getLastSQL());
							$this->get_1688_attr($v);
						}						
					}
				}
			}
		}
	}


	//采集阿里商品属性
	public function get_1688_attr($val){
		set_time_limit(0);
		//echo 'kk';
		//dump(urlencode(mb_convert_encoding('男装','gbk','utf8')));
		//exit;
		//$list=get_category(array('table'=>'products_sort','level'=>3));
		//$do=M('products_sort');
		//$list=$do->where(array('sid'=>array('gt',0)))->select();
		//foreach($list as $q1){
		//foreach($q1['dlist'] as $q2){
		//foreach($q2['dlist'] as $val){
			//dump($val);
			$url='http://s.1688.com/selloffer/offer_search.htm?keywords='.urlencode(mb_convert_encoding($val['name'],'gbk','utf8'));
			dump($url);

			//header('Content-type: application/octet-stream');
			//header("Content-Disposition: attachment; filename=t.html");
			ob_start();
			readfile($url);
			$htmls=ob_get_contents();
			$htmls=mb_convert_encoding($htmls,'utf8','gbk');
			ob_clean();

			$html=MidStr($htmls,'sm-widget-list','sm-sn-has-more');
			//dump($html);
			preg_match_all("/<label>([\s\S]*?)<\/label>/ies",$html,$out);
			dump($out);
			preg_match_all("/<ul>([\s\S]*?)<\/ul>/ies",$html,$out2);
			//dump($out2);
			$items=array();
			foreach($out2[1] as $v){
				preg_match_all("/<li>([\s\S]*?)<\/li>/ies",$v,$out3);
				//dump($out3[1]);
				$item=array();
				foreach($out3[1] as $vl){
					$str=array();
					$str['tb_attrid']=MidStr($vl,'data-value="','"');
					$str['name']=MidStr($vl,'title="','"');
					$str['tb_attr']=MidStr($vl,'ctype="','"');
					//dump($str);
					//dump(strip_tags($vl));
					$item[]=$str;
				}
				$items[]=$item;
				dump($item);
			}

			$html2=MidStr($htmls,'更多属性:','sm-sn-has-more');
			//dump($html2);
			preg_match_all("/<label([\s\S]*?)>([\s\S]*?)<\/label>/ies",$html2,$out4);
			dump($out4);
			$more=array();
			foreach($out4[0] as $key=>$ol){
				$more[$key][]=trim(strip_tags($ol));
			}

			dump($more);
			preg_match_all("/<ul>([\s\S]*?)<\/ul>/ies",$html2,$out5);


			$items2=array();
			foreach($out5[1] as $v){
				preg_match_all("/<li>([\s\S]*?)<\/li>/ies",$v,$out6);
				//dump($out3[1]);
				$item2=array();
				foreach($out3[1] as $vl){
					$str=array();
					$str['tb_attrid']=MidStr($vl,'data-value="','"');
					$str['name']=MidStr($vl,'title="','"');
					$str['tb_attr']=MidStr($vl,'ctype="','"');
					//dump($str);
					//dump(strip_tags($vl));
					$item2[]=$str;

				}
				$items2[]=$item2;
				dump($items2);
			}

			echo '--------------------------------<br>';
			$do=M('attribute_sort');
			foreach($out[1] as $key=>$v){
				//dump($v);
				if($v=='分类:'){

				}elseif($v=='更多属性:'){

				}else{
					$data=array();
					$data['atime']=time();
					$data['ip']=get_client_ip();
					$data['name']=substr($v,0,-1);
					$data['cid']=$val['id'];

						$insid=$do->add($data);
						dump($data);
						dump($items[$key]);
						foreach($items[$key] as $vl){
							$vl['atime']=time();
							$vl['ip']=get_client_ip();
							$vl['sid']=$insid;
							$do->add($vl);
						}					

					/*
					if(!$rs=$do->where(array('name'=>$data['name']))->find()){
						$insid=$do->add($data);
						dump($data);
						dump($items[$key]);
						foreach($items[$key] as $vl){
							$vl['atime']=time();
							$vl['ip']=get_client_ip();
							$vl['sid']=$insid;
							$do->add($vl);
						}
					}else{
						$ids=sortid(array('table'=>'products_sort','sid'=>$val['sid']));

						$t=0;

						$rs['cid']=@explode(',',$rs['cid']);
						$rs['cid'][]=$val['id'];
						$rs['cid']=array_unique($rs['cid']);

						foreach($rs['cid'] as $cl){
							if(in_array($cl,$ids)) {
								$t=1;
								break;
							}
						}

						if($t==1){
							$do->where('id='.$rs['id'])->setField('cid',implode(',',$rs['cid']));
						}else{
							$insid=$do->add($data);
							dump($data);
							dump($items[$key]);
							foreach($items[$key] as $vl){
								$vl['atime']=time();
								$vl['ip']=get_client_ip();
								$vl['sid']=$insid;
								$do->add($vl);
							}							
						}


						
					}
					*/
					dump($do->getLastSQL());
					
				}
				


			}
			

			foreach($more[0] as $key=>$v){
				//dump($v);

					$data=array();
					$data['atime']=time();
					$data['ip']=get_client_ip();
					$data['name']=$v;
					$data['cid']=$val['id'];
						$insid=$do->add($data);
						dump($data);
						dump($items2[$key]);
						foreach($items2[$key] as $vl){
							$vl['atime']=time();
							$vl['ip']=get_client_ip();
							$vl['sid']=$insid;
							$do->add($vl);
						}					

					/*
					if(!$rs=$do->where(array('name'=>$data['name']))->find()){
						$insid=$do->add($data);
						dump($data);
						dump($items2[$key]);
						foreach($items2[$key] as $vl){
							$vl['atime']=time();
							$vl['ip']=get_client_ip();
							$vl['sid']=$insid;
							$do->add($vl);
						}
					}else{

						$ids=sortid(array('table'=>'products_sort','sid'=>$val['sid']));

						$t=0;

						$rs['cid']=@explode(',',$rs['cid']);
						$rs['cid'][]=$val['id'];
						$rs['cid']=array_unique($rs['cid']);

						foreach($rs['cid'] as $cl){
							if(in_array($cl,$ids)) {
								$t=1;
								break;
							}
						}

						if($t==1){
							$do->where('id='.$rs['id'])->setField('cid',implode(',',$rs['cid']));
						}else{
							$insid=$do->add($data);
							dump($data);
							dump($items2[$key]);
							foreach($items2[$key] as $vl){
								$vl['atime']=time();
								$vl['ip']=get_client_ip();
								$vl['sid']=$insid;
								$do->add($vl);
							}						
						}


					}
					*/
					dump($do->getLastSQL());

				
				

			}
			//file_put_contents('t.txt', $html);

			//dump($html);
			usleep(1000,5000);

			//exit;
		//}}}
	}

	public function mc(){
		$cache_name='sm_'.session_id();
		S($cache_name,1,60);

		dump(S($cache_name));
	}

	public function mc2(){
		$cache_name='sm_'.session_id();
		dump(S($cache_name));
	}

	public function clear_attr(){
		set_time_limit(0);
		$do=M('attribute_sort');
		$list=$do->where(array('sid'=>0,'name'=>'价格'))->field('id')->select();

		foreach($list as $val){
			/*
			if(!$ls=$do->where('sid='.$val['id'])->count()){
				//dump($val);
				$do->delete($val['id']);
			}
			*/
			$do->where('sid='.$val['id'])->delete();
			$do->where('id='.$val['id'])->delete();

		}
	}

	//更新宝贝权重
	public function update_pr(){
		set_time_limit(60);
		$do=M('products');
		$list=$do->field('id')->select();
		foreach($list as $val){
			$do->where('id='.$val['id'])->setField('pr',products_pr($val['id']));
		}
	}

	public function rand_hot(){
		$do=M('products');
		$list=$do->order('rand()')->limit(500)->select();
		foreach($list as $val){
			$do->where('id='.$val['id'])->setField('is_promotion',1);
		}
	}


	public function products_num(){
		set_time_limit(0);
		$do=M('store');
		$list=$do->field('id,memberid')->limit(4000,1000)->select();
		foreach($list as $val){
			//dump($val);

			$num=M('products')->where('sellerid='.$val['memberid'])->count();
			$do->where('id='.$val['id'])->setField('num',$num);
			dump($do->getLastSQL());
			//exit;
		}
	}


	public function get_brand3(){
		set_time_limit(0);
		for($i=1;$i<6;$i++){
			$url='http://www.pkpop.com/brand/list/page/'.$i;
			$html=file_get_contents($url);
			$html=MidStr($html,'<div class="brand_list">','<div id="pages">');
			$html=explode('</a>',$html);

			foreach($html as $val){
				$data=array();
				$data['logo']='http://www.pkpop.com'.MidStr($val,'<img src="','"');
				$data['name']=MidStr($val,'<div class="brand_info_name">','</div>');
				$data['atime']=time();
				$data['memberid']=5134;
				$data['active']=1;
				$data['sid']=2;

				if($data['name']){
					M('brand')->add($data);
				}
				
				dump($data);
			}
			dump($html);
			//echo $html;
		}
	}

	public function tpl(){
		$do=M('storemodules');
	}

	public function table_autoid(){
		$do=M();
		$list=$do->query('show tables');
		foreach($list as $val){
			//dump($val);
			echo 'alter table '.$val['tables_in_ylh_for_erp'].' AUTO_INCREMENT='.rand(100100101,100900101).';<br>';
		}
		//dump($list);
	}


	public function cache_test(){
		/*
		S('enhong','123');

		dump(S('enhong'));

		dump(S(null));
		dump(S('enhong'));
		*/

		$m = new \Memcached();

		$m->addServer('10.0.0.21', 12000);

		$res=$m->getAllKeys();

		dump($res);


	}

	public function sort_update(){
		set_time_limit(0);
		$do=M('goods_type');
		$list=$do->select();
		$do=M('products_sort');
		foreach($list as $val){
			$do->where(array('erp_cid'=>$val['gt_id']))->setField('erp_code',$val['gt_code']);
		}
	}

	//清除测试数据
	public function del_test(){
		set_time_limit(0);
		$do=M('member');
		$list=$do->where(array('u_id'=>''))->order('id desc')->limit($_GET['limit'])->select();

		foreach($list as $val){
			echo $val['id'].'-'.$val['username'].'<br>';

			/*
			dump(M('address')->where(array('memberid'=>$val['id']))->delete());
			dump(M('ad')->where(array('memberid'=>$val['id']))->delete());
			dump(M('ad_sucai')->where(array('memberid'=>$val['id']))->delete());
			dump(M('authemail')->where(array('memberid'=>$val['id']))->delete());
			dump(M('authqiye')->where(array('memberid'=>$val['id']))->delete());
			dump(M('bbs')->where(array('userid'=>$val['id']))->delete());
			dump(M('bbs_reply')->where(array('userid'=>$val['id']))->delete());
			dump(M('brand')->where(array('memberid'=>$val['id']))->delete());
			dump(M('cart')->where(array('memberid'=>$val['id']))->delete());
			dump(M('cart')->where(array('sellerid'=>$val['id']))->delete());
			dump(M('category')->where(array('memberid'=>$val['id']))->delete());
			dump(M('complaint')->where(array('memberid'=>$val['id']))->delete());
			dump(M('consult')->where(array('sellerid'=>$val['id']))->delete());
			dump(M('consult')->where(array('userid'=>$val['id']))->delete());
			dump(M('consult_reply')->where(array('userid'=>$val['id']))->delete());
			dump(M('evaluate')->where(array('memberid'=>$val['id']))->delete());
			dump(M('evaluate_member')->where(array('memberid'=>$val['id']))->delete());
			dump(M('evaluate_member')->where(array('sellerid'=>$val['id']))->delete());
			dump(M('evaluate_store')->where(array('memberid'=>$val['id']))->delete());
			dump(M('evaluate_store')->where(array('sellerid'=>$val['id']))->delete());
			dump(M('express')->where(array('memberid'=>$val['id']))->delete());
			dump(M('images')->where(array('memberid'=>$val['id']))->delete());
			dump(M('inventory')->where(array('memberid'=>$val['id']))->delete());
			dump(M('itemrefund')->where(array('memberid'=>$val['id']))->delete());
			dump(M('itemrefund')->where(array('sellerid'=>$val['id']))->delete());
			dump(M('itemrefund_msg')->where(array('memberid'=>$val['id']))->delete());
			dump(M('moneychange')->where(array('memberid'=>$val['id']))->delete());
			dump(M('msg')->where(array('memberid'=>$val['id']))->delete());
			dump(M('msg')->where(array('sellerid'=>$val['id']))->delete());
			dump(M('myenshrine')->where(array('userid'=>$val['id']))->delete());
			dump(M('orders')->where(array('memberid'=>$val['id']))->delete());
			dump(M('orders')->where(array('sellerid'=>$val['id']))->delete());
			dump(M('payhistory')->where(array('memberid'=>$val['id']))->delete());
			dump(M('products')->where(array('sellerid'=>$val['id']))->delete());
			dump(M('products')->getLastSQL());
			dump(M('recharge')->where(array('memberid'=>$val['id']))->delete());
			dump(M('refund')->where(array('memberid'=>$val['id']))->delete());
			dump(M('refund_msg')->where(array('memberid'=>$val['id']))->delete());
			dump(M('store')->where(array('memberid'=>$val['id']))->delete());
			dump(M('store_layout')->where(array('memberid'=>$val['id']))->delete());
			dump(M('store_make_layout')->where(array('memberid'=>$val['id']))->delete());
			dump(M('store_make_modules')->where(array('memberid'=>$val['id']))->delete());
			dump(M('store_make_templates')->where(array('memberid'=>$val['id']))->delete());
			dump(M('store_modules')->where(array('memberid'=>$val['id']))->delete());
			dump(M('support')->where(array('memberid'=>$val['id']))->delete());
			dump(M('support_msg')->where(array('memberid'=>$val['id']))->delete());
			dump(M('verifycode')->where(array('userid'=>$val['id']))->delete());
			dump(M('viewhistory')->where(array('memberid'=>$val['id']))->delete());
			dump(M('withdraw')->where(array('memberid'=>$val['id']))->delete());
			dump(M('xiaobao')->where(array('memberid'=>$val['id']))->delete());
			*/
		}


	}


	public function mongo(){
		$arr=array(
			'atime'			=>date('Y-m-d H:i:s'),
			'ip'			=>get_client_ip(),
			'userid'		=>1,
			'username'		=>'enhong',
			'name'			=>'懒猫',
			'url'			=>'/',
			'Controller'	=>'list',
			'sql'			=>'select *',
			'type'			=>'update'
		);

		echo json_encode($arr);


		//$do=D('Admin','ylh_','mongo://admin:admin@127.0.0.1:27017/ylsc_log');
		//$do = new \Home\Model\AdminModel('admin','ylh_',C('DB_MONGO'));
		//$do->add($arr);
				//dump($do);

		//$Model=new \Think\Model\MongoModel('ylh_admin',null,C('DB_MONGO_CONFIG')); 

		//$data['id'] = 5;
		//$data['score'] = array('inc',2);
		//$Model->add($arr);
		
		//$do=D('Admin','',C('DB_MONGO'));
		//dump($do);

		//$s=$do->add($arr);
		//dump($s);

		//$m = new \MongoClient(); // 连接
		//$db = $m->selectDB("ylsc_log");

		//dump($db);

		//echo date('Y-m-d');

		$str='2016-2-2';

		$str=@explode(',', trim($str));
		foreach($str as $key=>$val){
			$val=explode('-',$val);
			$val[1]=intval($val[1])<10?'0'.intval($val[1]):$val[1];
			$val[2]=intval($val[2])<10?'0'.intval($val[2]):$val[2];
			$str[$key]=implode('-',$val);
		}

		$str=@implode(',',$str);

		dump($str);
	}

	public function clear_cache(){
		/*
		$key_list=F('cache_key_list');
		if($key_list){
			foreach($key_list as $val){
				S($val,null);
			}
		}
		*/

		$url='https://rest.yunlianhui.com/Ad/ads/modules/Mobile/controller/Index/action/home';

		$cache_name=md5($url);		
		S($cache_name,null);
		$url='https://rest.yunlianhui.com/Ad/ads/modules/Home/controller/Index/action/index';

		$cache_name=md5($url);		
		S($cache_name,null);
	}

	public function update_ad(){
		$list=M('ad')->where(array('days'=>array('neq','')))->select();
		foreach($list as $v){

			$str=@explode(',', trim($v['days']));
			foreach($str as $key=>$val){
				$val=explode('-',$val);
				$val[1]=intval($val[1])<10?'0'.intval($val[1]):$val[1];
				$val[2]=intval($val[2])<10?'0'.intval($val[2]):$val[2];
				$str[$key]=implode('-',$val);
			}

			$str=@implode(',',$str);	


			M('ad')->where('id='.$v['id'])->setField('days',$str);
		}

	}



	public function cc(){
		//$res=$this->curl_post('http://www.pfjie.cn',array('parterId'=>'C000000000000002'));

		//dump($res);

		$this->ansyc_file_upload(array('savepath'=>'./Apps/Runtime/js','filename'=>'aaaa.js','content'=>'alsdf'));
	}


	
	public function sms_test(){
		dump(C('cfg.SMS'));
		$data['content']='【乐兑C+消费系统】尊敬的乐兑会员，您本次操作的验证码为：890989，有疑问请联系客服。';
		$data['userid']=C('cfg.SMS')['userid'];
		$data['account']=C('cfg.SMS')['account'];
		$data['password']=C('cfg.SMS')['password'];		
		$data['action']='send';
		$data['mobile']=13711134195;
		
		$api=C('cfg.SMS')['sms'];
		//$res=$this->curl_post($api,$data);
		
		//dump($res);
		
		$str='<?xml version="1.0" encoding="utf-8" ?><returnsms>
 <returnstatus>Success</returnstatus>
 <message>ok</message>
 <remainpoint>50062</remainpoint>
 <taskID>7387111</taskID>
 <successCounts>1</successCounts></returnsms>';
 
 $xml = simplexml_load_string($str);
 dump($xml);
 dump($xml->returnstatus);
		
	}

	//更新店铺权重
	public function store_pr(){
		$do=M('store');
		$list=$do->field('memberid')->select();
		foreach($list as $val){
			store_pr($val['memberid']);
			usleep(20);
		}
	}

	//修改店铺装修错误
	public function fix_store(){
		set_time_limit(0);
		$do=M('store_templates');

		$list=$do->field('templatesid,memberid')->where('memberid>0')->select();
		$do=M('store_modules');
		foreach($list as $val){
			//dump($val);
			$layout=M('store_layout')->field('id')->where(array('templatesid'=>$val['templatesid'],'memberid'=>$val['memberid']))->select();
			//dump(M('store_layout')->getLastSQL());
			//dump($layout);
			$layoutid=arr_id(array('plist'=>$layout));
			//dump($layoutid);
			if($layoutid){
				$res=$do->where(array('memberid'=>$val['memberid'],'layoutid'=>array('not in',$layoutid)))->delete();
				//dump($do->getLastSQL());
				//dump($res);
				M('store_layout')->where(array('memberid'=>$val['memberid'],'id'=>array('not in',$layoutid)))->delete();

				usleep(10);
			}
		}
	}

	public function mstore(){
		$do=M();
		$list=$do->query('select count(*) as num,memberid from ylh_store_make_templates group by memberid order by num desc');

		foreach($list as $val){
			if($val>1){
				echo '<a href="https://work.yunlianhui.com/Store/login/memberid/'.$val['memberid'].'" target="_blank">https://work.yunlianhui.com/Store/login/memberid/'.$val['memberid'].'</a><br>';


			}
		}
	}

	public function fix_layout(){
		set_time_limit(0);
		$do=M('store_templates');
		$list=$do->field('memberid,templatesid')->where('templatesid=2')->select();

		$do=M('store_layout');
		foreach($list as $val){
			$ls=$do->field('id')->where(array('pageid'=>1,'memberid'=>$val['memberid']))->order('id desc')->select();	
			dump($do->getLastSQL());
			foreach($ls as $k=>$v){
				if($k>1){
					$do->where('id='.$v['id'])->delete();
				}
			}
			$ls=$do->field('id')->where(array('pageid'=>2,'memberid'=>$val['memberid']))->order('id desc')->select();	
			dump($do->getLastSQL());
			foreach($ls as $k=>$v){
				if($k>0){
					$do->where('id='.$v['id'])->delete();
				}
			}
			$ls=$do->field('id')->where(array('pageid'=>3,'memberid'=>$val['memberid']))->order('id desc')->select();	
			foreach($ls as $k=>$v){
				if($k>1){
					$do->where('id='.$v['id'])->delete();
				}
			}
			$ls=$do->field('id')->where(array('pageid'=>4,'memberid'=>$val['memberid']))->order('id desc')->select();	
			foreach($ls as $k=>$v){
				if($k>0){
					$do->where('id='.$v['id'])->delete();
				}
			}

			usleep(2);

		}


	}

	public function fix_store_make(){
		$do=M('store_templates');
		$list=$do->select();
		foreach($list as $val){
			
		}
	}

	function clear_modules(){
		$do=M('store_make_layout');
		$list=$do->select();
		$arrid=arr_id(array('plist'=>$list));
		dump($arrid);

		$do=M('store_make_modules');

		$do->where(array('layoutid'=>array('not in',$arrid)))->delete();
	}

	function c_store(){
		set_time_limit(0);
		$do=M('store');

		$list=$do->select();
		foreach($list as $user){

	        //默认模板ID 100445745
	        $templatesid=100445745;

	        $rs=M('templates')->find($templatesid);
	        $layout=M('store_lib_layout')->where(array('templatesid'=>$rs['id']))->select();


			$do->startTrans();
	        //装修中
	        $sw2=M('store_make_templates')->add(array(
	                    'atime'     =>time(),
	                    'ip'        =>get_client_ip(),
	                    'name'      =>$rs['name'],
	                    'path'      =>$rs['path'],
	                    'cfg'       =>$rs['cfg'],
	                    'active'    =>1,
	                    'memberid'  =>$user['memberid'],
	                    'cfg_box'   =>$rs['cfg_box'],
	                    'cfg_bg'    =>$rs['cfg_bg'],
	                    'cfg_header'=>$rs['cfg_header'],
	                    'cfg_menu'  =>$rs['cfg_menu'],
	                    'templatesid'=>$rs['id']
	        ));

	        $sw3=1;
	        foreach($layout as $val){
	            $modules=M('store_lib_modules')->where(array('layoutid'=>$val['id']))->select();
	            unset($val['id']);
	            $val['memberid']=$user['memberid'];
	            if($sw=M('store_make_layout')->add($val)){
	                foreach($modules as $v){
	                    unset($v['id']);
	                    $v['memberid']=$user['memberid'];
	                    $v['layoutid']=$sw;

	                    if(!M('store_make_modules')->add($v)){
	                        $sw3=0;
	                        break;                       
	                    }
	                }
	            }else{
	                $sw3=0;
	                break;        
	            }
	        }               



	        //正式发布
	        $sw4=M('store_templates')->add(array(
	                    'atime'     =>time(),
	                    'ip'        =>get_client_ip(),
	                    'name'      =>$rs['name'],
	                    'path'      =>$rs['path'],
	                    'cfg'       =>$rs['cfg'],
	                    'active'    =>1,
	                    'memberid'  =>$user['memberid'],
	                    'cfg_box'   =>$rs['cfg_box'],
	                    'cfg_bg'    =>$rs['cfg_bg'],
	                    'cfg_header'=>$rs['cfg_header'],
	                    'cfg_menu'  =>$rs['cfg_menu'],
	                    'templatesid'=>$rs['id']
	        ));

	        $sw5=1;
	        foreach($layout as $val){
	            $modules=M('store_lib_modules')->where(array('layoutid'=>$val['id']))->select();
	            unset($val['id']);
	            $val['memberid']=$user['memberid'];
	            if($sw=M('store_layout')->add($val)){
	                foreach($modules as $v){
	                    unset($v['id']);
	                    $v['memberid']=$user['memberid'];
	                    $v['layoutid']=$sw;

	                    if(!M('store_modules')->add($v)){
	                        $sw5=0;
	                        break;                       
	                    }
	                }
	            }else{
	                $sw5=0;
	                break;        
	            }
	        }               
	   		

			
			if($sw2 && $sw3 && $sw4 && $sw5){
				$do->commit();			
				
			}else{
				$do->rollback();
			}

			usleep(100);

		}
	}

	public function fix_orders(){
		$res=$this->curl_get('https://open.999qf.cn/api/successfulOrder.json');

		$res=json_decode($res);
		//dump($res);
		
		$do=M('orders');
		foreach($res->info as $val){
			if($rs=$do->where(array('id'=>$val->bu_parterNumber))->field('id,memberid,sellerid,money_pay,status')->find()){
				if($rs['status']==1000 || $rs['status']==8000){
					dump($rs);
				}
			}

		}
		


	}

	public function fix_ooo(){
		exit;
		if(empty($_GET['id'])) {echo 'error';exit;}
		$oid=$_GET['id'];
		$do=M('orders');
				$sw1=$do->execute('update '.C('DB_PREFIX').'orders set status=8000,status_item="" where id='.$oid);

				$sw2=M('orders_status')->add(array(
					'atime'			=>time(),
					'ip'			=>get_client_ip(),
					'ordersid'		=>$oid,
					'status'		=>8000,
					'name'			=>'退款完成，交易关闭'
				));		
	}	

	//已确认收货却款更改状态
	public function fix_oo(){
		if(empty($_GET['id'])) {echo 'error';exit;}
		$map['id']=$_GET['id'];
		$do=D('Buyer/OrdersView2');

		if($rs=$do->where($map)->field('id,memberid,sellerid,status,status_item,money_pay,money_refund,money_itemrefund,refundid,refund_sid,itemrefund_num,atime,paytime,confirm_time,buyer_uid,seller_uid')->find()){

			$res=1;
			if($res){
				$do->startTrans();
				$sw1=$do->execute('update '.C('DB_PREFIX').'orders set confirm_time='.time().',status=4000,status_item="" where id='.$rs['id']);

				$sw2=M('orders_status')->add(array(
					'atime'			=>time(),
					'ip'			=>get_client_ip(),
					'ordersid'		=>$rs['id'],
					'status'		=>4000,
					'name'			=>$this->msg_status(4000)
				));

	            //更新交易记录
	            //$sw3=M('member')->where(array('id'=>$rs['sellerid']))->setInc('money',$rs['money_pay']-$rs['money_refund']-$rs['money_itemrefund']);

	            $sw4=M('payhistory')->where(array('ordersid'=>$rs['id']))->save(array('status'=>1,'dotime'=>time()));

	            $s_account=M('member')->field('money_xiaobao')->find($rs['sellerid']);

	            $seller=$this->erp_account($rs['seller_uid']);
	            $seller['money']=$seller['ac_freeMoney'];
	            $seller['money_trade']=$seller['ac_busMoney'];
	            $seller['money_xiaobao']=$s_account['money_xiaobao'];

	            $sw5=M('moneychange')->add(array(
	                'atime'         =>time(),
	                'ip'            =>get_client_ip(),
	                'memberid'      =>$rs['sellerid'],
	                'money'         =>$rs['money_pay']-$rs['money_refund']-$rs['money_itemrefund'],
	                'money_account' =>$seller['money'],
			        'money_xiaobao'	=>$seller['money_xiaobao'],
			        'money_trade'	=>$seller['money_trade'],
					'redscore'		=>$seller['ac_redScore'],
	                //'money_lock'    =>$seller['money_lock'],
	                'ordersid'      =>$rs['id'],
	                'type'          =>1,
	                'sid'           =>8,
	                'name'          =>'[买家确认收货]订单号#'.$rs['id'].'收入：'.($rs['money_pay']-$rs['money_refund']-$rs['money_itemrefund']).'元(订单异常，由[enhong于'.date('Y-m-d').'修复])'
	            ));



	            dump($sw1.'-'.$sw2.'-'.$sw4.'-'.$sw5);

	            //file_put_contents('sw.txt', $sw1.'-'.$sw2.'-'.$sw4.'-'.$sw5.'-'.$sw6.'-'.$sw7);

				if($sw1 && $sw2 && $sw4 && $sw5){
					$do->commit();

					$result['code']=1;
				}else{
					$do->rollback();
					$result['code']=2;
				}
			}else{
				$result['code']=2;
			}

		}else{
			$result['code']=3;
		}
		$result['msg']=$this->msg($result['code']);
		return $result;

	}

	//已付款却未更改状态
	public function fix_o(){
		if(empty($_GET['id'])) {echo 'error';exit;}
		$id=I('get.id');
		$do=D('Buyer/OrdersView2');
				if($rs=$do->where(array('id'=>$id))->field('id,atime,status,status_item,money_pay,point,memberid,sellerid,buyer_uid,seller_uid,is_qf')->find()){
					//取用户账号余额

						//消保
						$buyer=M('member')->where(array('id'=>$rs['memberid']))->field('money_xiaobao')->find();

						$account=$this->erp_account($rs['buyer_uid']);
						$user['moeny']=$account['ac_freeMoney'];

						//dump($account);exit;

						/*
						if($user['money']<$rs['money_pay']){
							$result['code']=1010;
							//余额不足中断
							$result['msg']=$this->msg($result['code']);
							return $result;						
						}
						*/

						$do->startTrans();
						$sw1=$do->execute('update '.C('DB_PREFIX').'orders set status=2000,status_item="",paytime='.time().',paytype="account" where id='.$rs['id']);
						
						$sw2=M('orders_status')->add(array(
							'atime'			=>time(),
							'ip'			=>get_client_ip(),
							'isys'			=>1,
							'ordersid'		=>$rs['id'],
							'status'		=>2000,
							'name'			=>$this->msg_status(2000)
						));

						//$sw3=$do->execute('update '.C('DB_PREFIX').'member set money=money-'.$rs['money_pay'].',point=point+'.$rs['point'].' where id='.$rs['memberid']);


						//资金异动
						$sw31=M('moneychange')->add(array(
			                'atime'     	=>time(),
			                'ip'        	=>get_client_ip(),
			                'ordersid'  	=>$rs['id'],
			                'memberid'		=>$rs['memberid'],
			                'money'    		=>$rs['money_pay']*-1,
			                'type'			=>0,
			                'sid'			=>1,
			                'money_account'	=>$user['moeny'],
			                'money_xiaobao'	=>$buyer['money_xiaobao'],
			                'money_trade'	=>$account['ac_busMoney'],
							'redscore'		=>$account['ac_redScore'],
			                //'money_lock'	=>$user['money_lock'],
			                'name'    		=>'订购商品[订单号#'.$rs['id'].']，支出'.$rs['money_pay'].'元(订单异常，由[enhong于'.date('Y-m-d').'修复])'        		
		        		));


				

						//买家交易记录
						$sw5=M('payhistory')->add(array(
							'atime'		=>time(),
							'ip'		=>get_client_ip(),
							'point'		=>$rs['point'],
							'memberid'	=>$rs['memberid'],
							'ordersid'	=>$rs['id'],
							'money'		=>$rs['money_pay']*-1,
							'status'	=>0,
							'type'		=>0,
							'name'		=>'订购商品[订单号#'.$rs['id'].']，支出'.$rs['money_pay'].'元(订单异常，由[enhong于'.date('Y-m-d').'修复])'						
						));


						//卖家交易记录
						$sw6=M('payhistory')->add(array(
							'atime'		=>time(),
							'ip'		=>get_client_ip(),
							'point'		=>$rs['point'],
							'memberid'	=>$rs['sellerid'],
							'ordersid'	=>$rs['id'],
							'money'		=>$rs['money_pay'],
							'status'	=>0,
							'type'		=>1,
							'name'		=>'售出商品[订单号#'.$rs['id'].']，收入'.$rs['money_pay'].'元(订单异常，由[enhong于'.date('Y-m-d').'修复])'						
						));


						//更新商品售出数量及库存
						//库存更新还未做处理，后续再加上
						$sw7=1;
						$cart=M('cart')->where(array('ordersid'=>$rs['id']))->field('id,productsid,num,colorid,sizeid')->select();
						foreach($cart as $v){
							if(false == $sw7 = D('Inventory')->lock(true)->execute('update '.C('DB_PREFIX').'inventory set num=num-'.$v['num'].',sale_num=sale_num+'.$v['num'].' where productsid='.$v['productsid'].' and colorid='.$v['colorid'].' and sizeid='.$v['sizeid'].' and num>'.($v['num']-1))){
								dump($sw7);
								dump(D('Inventory')->getLastSQL());
								break;
							}

							if(false == $sw7 = D('Products')->lock(true)->execute('update '.C('DB_PREFIX').'products set num=num-'.$v['num'].',sale_num=sale_num+'.$v['num'].' where id='.$v['productsid'].' and num>'.($v['num']-1))){
								dump($sw7);
								break;
							}							
						}


						//通知 未定义消息模板，后续有空再定义
						$sw8=M('msg')->add(array(
							'atime'		=>time(),
							'ip'		=>get_client_ip(),
							'sid'		=>2,
							'sellerid'	=>$rs['sellerid'],
							'ordersid'	=>$rs['id'],
							'name'		=>'售出'.$rs['num'].'件商品，订单号：#'.$rs['id']
						));

						dump($sw1 .'-'. $sw2 .'-'.  $sw31 .'-'. $sw5 .'-'. $sw6.'-'. $sw7 .'-'. $sw8);

						if($sw1 && $sw2 && $sw31 && $sw5 && $sw6 && $sw7 && $sw8){
							$do->commit();
							$result['code']=1;
						}else{
							$do->rollback();
							$result['code']=2;
						}						
		}
	}

	public function mq(){
		$m=new \Think\Cache\Driver\Memcached();

	}

	//更新上架时间
	/*
    public function auto_uptime(){
        set_time_limit(0);
        $st=time();
        $do=M();
        //$do->execute('update '.C('DB_PREFIX').'products set uptime=uptime+(86400*7) where uptime<'.(time()-86400*7));
        $list=M('products')->where(array('_string'=>'atime<'.(time()-87400*7)))->field('id,atime')->select();
        //dump($list);
        //dump(count($list));
        foreach($list as $val){
            echo date('Y-m-d H:i:s',$val['atime']).'<br>';
            //echo ((time()-$val['uptime'])/86400).'<br>';

            $n=intval((time()-$val['atime'])/86400/7);
            //echo $n.'<br>';
            $t=$val['atime']+(86400*$n*7);
            //echo $t.'<br>';

            echo date('Y-m-d H:i:s',$t).'<br>';
            M('products')->where('id='.$val['id'])->setField('uptime',$t);

            echo '<br><br>';

            usleep(20);
        }

        echo time()-$st;
    }
    */	

    public function t1(){
    	$a='enhong';
    	$str='lskdflskf{$a}lkdflsdf';
    	echo $str;
    }

    /*
    public function t2(){
                    $rs=D('Cart/RechargeView')->where(array('paytype'=>1,'orderno'=>'2016031201000510080602828431618','active'=>0))->field('id,money,memberid,erp_rechargeid,ordersid,buyer_uid')->find();

                    if($rs){
                        $buyer=M('member')->where(array('id'=>$rs['memberid']))->field('money_xiaobao')->find();
                        $do=M();

                        $res=$this->erp_recharge_status(array('ca_id'=>$rs['erp_rechargeid'],'userID'=>$rs['buyer_uid']));
                        if($res){
                            $user=$this->erp_account($rs['buyer_uid']);

                            $do->startTrans();

                            $sw2=M('recharge')->where(array('id'=>$rs['id'],'active'=>0))->save(array(
                                    'paytime'       =>time(),
                                    'active'        =>1,
                                    'trade_status'  =>$notify->data['result_code'],
                                    'openid'        =>$notify->data['openid'],
                                    'transaction_id'=>$notify->data['transaction_id']                                   
                            ));           

                            $sw3=M('moneychange')->add(array(
                                    'atime'         =>time(),
                                    'ip'            =>get_client_ip(),
                                    'memberid'      =>$rs['memberid'],
                                    'money'         =>$rs['money'],
                                    'sid'           =>5,
                                    'ordersid'      =>$rs['id'],
                                    'money_account' =>$user['ac_freeMoney'],
                                    'money_trade'   =>$user['ac_busMoney'],
                                    'money_xiaobao' =>$buyer['money_xiaobao'],
                                    'name'          =>'微信扫码充值订购商品[#'.$rs['ordersid'].']'
                            ));

                            if($sw2 && $sw3){
                                $do->commit();
                                $ordersid=explode(',',$rs['ordersid']);
                                $n=0;  //n个订单支付成功
                                foreach($ordersid as $val){
                                    $res=$this->b_orders_pay($val);
                                    if($res['code']==1) $n++;
                                }
                            }else{
                                $do->rollback();
                            }

                        }
                        
                    }	
    }

    */
    public function t3(){

    	$check=new \Org\Util\CheckForm();

    	$check->set_item('127.0.0.1',array(array('function'=>'required','msg'=>'IP必填项'),array('function'=>'is_ip','msg'=>'IP格式错误！')));  	
    	$check->set_item('440111198207080910',array(array('function'=>'required','msg'=>'身份证为必填项'),array('function'=>'is_card','msg'=>'身份证格式错误！')));  	

    	$res=$check->check_data();
    	dump($res);



    	$res=$this->erp_get_orders(array('beginTime'=>'2016-02-01','endTime'=>'2016-03-10'));
    	dump($res);

    }

    //处理问题订单
    public function t4(){
    	set_time_limit(0);
    	//$res=S('erp_orders');
    	//if(empty($res)){
			$apiurl=C('cfg.erp')['apiurl'].'/successfulOrder.json';
			$param['parterId']=C('cfg.erp')['pid'];

    	//}

		$stime=time();
		for($i=1;$i<20;$i++){
			$endtime=$stime-86400*$i;
			$param['beginTime']=date('Y-m-d',$endtime);
			$param['endTime']=date('Y-m-d',$endtime+86400);
			dump($param);
			$res=$this->curl_post($apiurl,$param);
			$res=json_decode($res);
			foreach($res->info as $val){
				dump($val);
			}

			usleep(1000);
		}

    }

    public function t5(){
    	/*
    	$do=D('Mai/Store');
    	$data['name']='asdsdf3453';
    	$data['email']='lsdkfjs';

    	$res=$do->create($data);
    	dump($res);

    	$check=new \Org\Util\CheckData($data['name']);

    	dump($do->getError());

    	dump($check->username(5,15));
    	*/

    	$do=A('Cron/Orders');

    	$do->time_range=array('between',time()-300,time());

    	

    	dump($do->get_orders());
    }



	public function checkBOM ($filename) {
		$contents = file_get_contents($filename);
		$charset[1] = substr($contents, 0, 1);
		$charset[2] = substr($contents, 1, 1);
		$charset[3] = substr($contents, 2, 1);
		if (ord($charset[1]) == 239 && ord($charset[2]) == 187 && ord($charset[3]) == 191) {
			return true;
		}
		else return false;
	} 

	public function t6(){
		//$res=$this->checkBOM('./test.php');
		//dump($res);
		//$dir=new \Org\Util\Dir();
		//$list=$dir->getList('./ThinkPHP');
		//dump($list);
		//$list=$dir->getListExt('./ThinkPHP',array('.php'));

		//$list=get_dir(array('path'=>'E:\mysite\yunlianhui\mygit\web\ThinkPHP'));

		//dump($list);

		$this->get_phpfile('E:/mysite/yunlianhui/mygit/web');
	}
    
	public function get_phpfile($path){
				$dir=new \Org\Util\Dir();
				$list=$dir->getList($path);		
				
				$dirlist=array();
				foreach($list as $key=>$val){
					if(is_dir($path.'/'.$val) && $val!='.' && $val!='..' && $val!='.git'){

						$res=$this->get_phpfile($path.'/'.$val);


					}elseif($val!='.' && $val!='..' && $val!='.git'){
						$dirlist[]=$path.'/'.$val;

						if(substr($val,-4,4)=='.php'){
							if($this->checkBOM($path.'/'.$val))	echo $path.'/'.$val.'<br>';
						}
					}
				}

				return $dirlist;
	}  

	public function t7(){
		/*
		$do=D('Address');
		$res=$do->lock(true)->where('id=100211765')->save(array('street'=>'天河路'));
		dump($res);
		dump($do->getLastSQL());

		dump(S('form_token_'.session_id()));
		*/
		$value='499324859238475';
		$check=new \Org\Util\CheckData($value);
		dump($check->number_range(5,10));

		$do=D('Cart/Address');
		$data['mobile']='515340';
		$res=$do->create($data);
		if(!$res) dump($do->getError());
	}

	public function t8(){
		$id=$this->create_orderno();
		file_put_contents($id.'.txt', time());

	}
	
	public function repairExpress() {
	    if (isset($_GET['u']) && I('get.u') == 'mercury') {
	        /*$snos = M('orders_shop')->where(['status' => 3, 'express_company' => ''])->getField('s_no,express_code', true);
	        if ($snos) {
	            $api = new \Think\Model\MongoModel('ylh_api',null,C('DB_MONGO_CONFIG'));
	            $data= [];
	            foreach ($snos as $k => $v) {
	                $data[] = $api->where(['url' => ['like', '/SellerOrders/send_express'],'post' => ['like', $k]])->find();
	            }
	            $do = M('orders_shop');
	            $do->startTrans();
	            $s_no = null;
	            if (!empty($data)) {
	                foreach ($data as $v) {
	                    $postArr = ('return ' . trim($v['post'], ',') . ';');
	                    $post = eval(html_entity_decode($postArr));
	                    dump($post);
	                    $express_company = M('express_company')->where(['id' => $post['express_company_id']])->getField('company');
	                    dump($express_company);
	                    if (!$do->where(['s_no' => $post['s_no']])->save(['express_code' => $post['express_code'], 'express_company_id' => $post['express_company_id'], 'express_company' => $express_company])) {
	                        echo $do->getLastSql();
	                        $s_no = $v['s_no'];
	                        goto error;
	                    }
	                }
	            }
	            $do->commit();
	            error :
	            echo $s_no;
	            $do->rollback();
	        }*/
	    }
	}
}