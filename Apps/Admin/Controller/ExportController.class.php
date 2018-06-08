<?php
/**
* 此文件为根据表单模板创建
*/
namespace Admin\Controller;
use Think\Controller;
use Common\Builder\R;
class ExportController extends CommonModulesController {
	protected $name 			='数据导出';	//控制器名称	
	protected $orders_formtpl_id		=148;
	protected $refund_formtpl_id		=149;
	protected $mobile_orders_formtpl_id		=257;
	
	
	/**
     * 导出方案分类列表
	 * Create by liangfeng
     * 2017-06-27
     */
	public function export_category(){
		$year = date('Y',time());
		$do=M('export_category a');

		$count = $do->field('a.id')->select();
		$count = count($count);
		$page= new \Think\Page($count, 10);
		$limit=$page->firstRow.','.$page->listRows;
		$list=$do->field('a.id,a.atime,a.name')->order('id desc')->limit($limit)->select();
		foreach ($list as $k=>$v){
			$list[$k]['url']=U('/Export/edit_export_category',array('id'=>$v['id']));
			$list[$k]['add_url']=U('/Export/add_programme',array('id'=>$v['id']));
		}
		$result['list']=$list;
		$result['listnum']=count($list);
		$result['allnum']=$count;
		$result['page']=$page->show_btn();
		$result['allpage']=$page->allpage();
		
		$this->assign('pagelist',$result);

		$fields=[
				[
						'title'=>'选择',
						'type'=>'html',
						'html'=>'<input type="checkbox" class="i-red-square" name="id[]" id="id[]" value="[id]">',
						'td_attr'=>'width="60" class="text-center"',
						'norder'=>1
				],
				[
						'title'=>'添加时间',
						'field'=>'atime',
				],
				[
						'title'=>'导出方案分类名称',
						'field'=>'name',
				],
				[
						'title'=>'操作',
						'type'=>'html',
						'html'=>'<a href="[url]" class="btn btn-sm btn-info btn-rad btn-trans btn-block m0">修改方案字段</a><a href="[add_url]" class="btn btn-sm btn-info btn-rad btn-trans btn-block m0">新建导出方案</a>',
						'td_attr'=>'width="100" class="text-center"',
						'norder'=>1
				]
		];
		$this->assign('fields',$fields);
		$this->display();
		
	}
	
	/**
     * 新建导出方案分类
	 * Create by liangfeng
     * 2017-06-29
     */
	public function add_export_category(){
		if(IS_POST){
			$post = I('post.');
            $data = $this->data_assemble($post);
			M('export_category')->add($data);
            $this->redirect('Export/export_category');
		}else{
			$this->display('export_category_detail');
		}
	}
	/**
     * 修改导出方案分类
	 * Create by liangfeng
     * 2017-06-29
     */
	public function edit_export_category(){
		if(IS_POST){
			$post = I('post.');
			$data = $this->data_assemble($post);
			M('export_category')->where(['id'=>$post['id']])->save($data);
			$this->redirect('Export/edit_export_category', array('id' => I('post.id')));
		}else{
			$res = M('export_category')->find(I('get.id'));
			$res['condition'] = json_decode($res['condition'],true);
			$res['field'] = json_decode($res['field'],true);

			foreach($res['condition'] as $k => $v){
			    $str = '';
			    if(is_array($v['value'])){
                    $str .= '{';
                    foreach($v['value'] as $ke => $va){
                        $str .= '"'.$ke.'":"'.$va.'",';
                    }
                    $str = substr($str,0,strlen($str)-1);
                    $str .= '}';
                }
				$res['condition'][$k]['value'] = $str;
			}

			$this->assign('res',$res);
			$this->display('export_category_detail');
		}
	}
    /**
     * 将方案分类的数据组装
     * Create by liangfeng
     * 2017-06-30
     */
	private function data_assemble($post){
        foreach($post['label'] as $k => $v){
            $tmp = array();

            $tmp['label'] = $v;
            $tmp['is_open'] = $post['is_open'][$k];
            $tmp['formtype'] = $post['formtype'][$k];
            $tmp['name'] = $post['name'][$k];
            $tmp['value'] = json_decode(html_entity_decode($post['value'][$k]), true);
            $condition[] = $tmp;
        }
        foreach($post['field_label'] as $k => $v){
            $tmp = array();
            $tmp['field_label'] = $v;
            $tmp['field_is_open'] = $post['field_is_open'][$k];
            $tmp['field_value'] = $post['field_value'][$k];
            $field[] = $tmp;
        }

        $condition = json_encode($condition);
        $field = json_encode($field);
        $data['condition'] = $condition;
        $data['field'] = $field;
        $data['name'] = $post['category_name'];
        return $data;
    }
    /**
     * 导出方案列表
     * Create by liangfeng
     * 2017-06-22
     */
    public function programme(){
        $do=M('export_programme a');

        $map['admin_id'] = $_SESSION['admin']['id'];

        $count = $do->where($map)->count();
        $page= new \Think\Page($count, 10);
        $limit=$page->firstRow.','.$page->listRows;
        $list=$do->field('a.id,a.atime,a.category_id,a.name,a.download_path,a.download_atime')->where($map)->order('id desc')->limit($limit)->select();
        foreach ($list as $k=>$v){
            $list[$k]['category_name'] = M('export_category')->where(['id'=>$v['category_id']])->getField('name');
            $list[$k]['url']=U('/Export/edit_programme',array('id'=>$v['id']));
            if($v['download_path']){
                $list[$k]['down_url']= $v['download_path'].'?attname='.$v['download_atime'].' '.$v['name'].'.zip';
            }else{
                $list[$k]['down_url'] = U('/Export/download_excel',array('id'=>$v['id']));;
            }

        }
        $result['list']=$list;
        $result['listnum']=count($list);
        $result['allnum']=$count;
        $result['page']=$page->show_btn();
        $result['allpage']=$page->allpage();
        $this->assign('pagelist',$result);

        $fields=[
            [
                'title'=>'选择',
                'type'=>'html',
                'html'=>'<input type="checkbox" class="i-red-square" name="id[]" id="id[]" value="[id]">',
                'td_attr'=>'width="60" class="text-center"',
                'norder'=>1
            ],
            [
                'title'=>'导出方案名称',
                'field'=>'name',
            ],
            [
                'title'=>'导出方案分类',
                'field'=>'category_name',
				
            ],
            [
                'title'=>'添加时间',
                'field'=>'atime',
            ],


            [
                'title'=>'Excel生成时间',
                'field'=>'download_atime',
            ],
            [
                'title'=>'操作',
                'type'=>'html',
                'html'=>'<a href="[url]" class="btn btn-sm btn-info btn-rad btn-trans btn-block m0">修改导出方案</a><a data-id="[id]" class="btn btn-sm btn-info btn-rad btn-trans btn-block m0 btn-export">生成导出Excel</a><a data-id="[id]" data-total="" class="btn btn-sm btn-info btn-rad btn-trans btn-block m0 btn-export-progress">查询进度</a><a href="[down_url]" target="_blank" class="btn btn-sm btn-info btn-rad btn-trans btn-block m0">下载Excel</a>',
                'td_attr'=>'width="100" class="text-center"',
                'norder'=>1
            ]
        ];
        $this->assign('fields',$fields);
		
		$nav_list = M('export_category')->where(['id'=>['in','1']])->order('atime asc')->select();
		$this->assign('nav_list',$nav_list);
        $this->display();

    }

    /**
     * 全部导出方案列表
     * Create by liangfeng
     * 2017-08-07
     */
    public function programmes(){
        $do=M('export_programme a');
        $count = $do->where($map)->count();
        $page= new \Think\Page($count, 10);
        $limit=$page->firstRow.','.$page->listRows;
        $list=$do->field('a.id,a.atime,a.category_id,a.name,a.download_path,a.download_atime,a.admin_id')->where($map)->order('id desc')->limit($limit)->select();
        foreach ($list as $k=>$v){
            $list[$k]['category_name'] = M('export_category')->where(['id'=>$v['category_id']])->getField('name');
            $list[$k]['url']=U('/Export/edit_programme',array('id'=>$v['id']));
            if($v['download_path']){
                $list[$k]['down_url']= $v['download_path'].'?attname='.$v['download_atime'].' '.$v['name'].'.zip';
            }else{
                $list[$k]['down_url'] = U('/Export/download_excel',array('id'=>$v['id']));;
            }
        }
        $result['list']=$list;
        $result['listnum']=count($list);
        $result['allnum']=$count;
        $result['page']=$page->show_btn();
        $result['allpage']=$page->allpage();

        $this->assign('pagelist',$result);

        $fields=[
            [
                'title'=>'选择',
                'type'=>'html',
                'html'=>'<input type="checkbox" class="i-red-square" name="id[]" id="id[]" value="[id]">',
                'td_attr'=>'width="60" class="text-center"',
                'norder'=>1
            ],
            [
                'title'=>'导出方案名称',
                'field'=>'name',
            ],
            [
                'title'=>'导出方案分类',
                'field'=>'category_name',
            ],
            [
                'title'=>'所属雇员ID',
                'field'=>'admin_id',
            ],
            [
                'title'=>'添加时间',
                'field'=>'atime',
            ],
            [
                'title'=>'Excel生成时间',
                'field'=>'download_atime',
            ],
            [
                'title'=>'操作',
                'type'=>'html',
                'html'=>'<a href="[url]" class="btn btn-sm btn-info btn-rad btn-trans btn-block m0">修改导出方案</a><a data-id="[id]" class="btn btn-sm btn-info btn-rad btn-trans btn-block m0 btn-export">生成导出Excel</a><a data-id="[id]" data-total="" class="btn btn-sm btn-info btn-rad btn-trans btn-block m0 btn-export-progress">查询进度</a><a href="[down_url]" target="_blank" class="btn btn-sm btn-info btn-rad btn-trans btn-block m0">下载Excel</a>',
                'td_attr'=>'width="100" class="text-center"',
                'norder'=>1
            ]
        ];
        $this->assign('fields',$fields);

        $nav_list = M('export_category')->order('atime asc')->select();
        $this->assign('nav_list',$nav_list);
        $this->display('programme');

    }

    /**
     * 新增导出方案
     * Create by liangfeng
     * 2017-06-29
     */
	public function add_programme(){
        $cate_info = M('export_category')->find(I('get.id'));
        $cate_info['condition'] = json_decode($cate_info['condition'],true);
        $cate_info['field'] = json_decode($cate_info['field'],true);

        //dump($res);
        $this->assign('res',$cate_info);
	    $this->display('programme_detail');
    }
    /**
     * 修改导出方案
     * Create by liangfeng
     * 2017-06-29
     */
    public function edit_programme(){
        $info = M('export_programme')->find(I('get.id'));
        $info['condition_select'] = json_decode($info['condition_select'],true);
        $info['field_select'] = json_decode($info['field_select'],true);

        $cate_info['id'] = $info['category_id'];
        $cate_info['name'] = $info['category_name'];
        $cate_info['condition'] = json_decode($info['condition'],true);
        $cate_info['field'] = json_decode($info['field'],true);



        foreach($cate_info['field'] as $k=>$v){
            foreach($info['field_select'] as $va){
                if($v['field_value'] == $va['field_value']){
                    unset($cate_info['field'][$k]);
                    break;
                }
            }
        }


        $this->assign('res',$cate_info);
        $this->assign('info',$info);
        $this->display('programme_detail');
    }

    /**
     * 保存导出方案
     * Create by liangfeng
     * 2017-06-30
     */
    public function save_programme(){
	    if(IS_AJAX){
            $post = I('post.');
            //dump($post);exit();
            $tmp = array();
            foreach($post as $k => $v){
                if($k!="category_id" && $k!="field_select" && $k!="name" && $k!='id'){
                    $tmp[$k] = $v;
                }
            }
            $data['category_id'] = $post['category_id'];
            $data['name'] = $post['name'];
            $data['condition_select'] = json_encode($tmp);

            if($post['id']){
                S('export_programme_'.$post['id'].'_total',null);
                //修改
                $field = M('export_programme')->where(['id'=>$post['id']])->getField('field');
                $field = json_decode($field,true);


                foreach($post['field_select'] as $k=>$v){
                    foreach($field as $va){
                        $tmp = array();
                        if($va['field_value'] == $v){
                            $tmp['field_value'] = $v;
                            $tmp['field_label'] = $va['field_label'];
                            $tmpp[] = $tmp;
                            break;
                        }

                    }
                }
                $data['field_select'] = json_encode($tmpp);

                $res = M('export_programme')->where(['id'=>$post['id']])->save($data);
            }else{
                //新增
                $cate_info = M('export_category')->field('name,condition,field')->where(['id'=>$post['category_id']])->find();
                $data['condition'] = $cate_info['condition'];
                $data['field'] = $cate_info['field'];
                $data['admin_id'] = $_SESSION['admin']['id'];
                $field = json_decode($data['field'],true);
                $data['category_name'] = $cate_info['name'];

                foreach($post['field_select'] as $k=>$v){
                    foreach($field as $va){
                        $tmp = array();
                        if($va['field_value'] == $v){
                            $tmp['field_value'] = $v;
                            $tmp['field_label'] = $va['field_label'];
                            $tmpp[] = $tmp;
                            break;
                        }
                    }
                }
                $data['field_select'] = json_encode($tmpp);


                $res = M('export_programme')->add($data);
            }
            if($res !== false){
                $this->ajaxReturn(['status'=>'success','msg'=>'操作成功']);
            }else{
                $this->ajaxReturn(['status'=>'warning','data'=>$data,'msg'=>'操作失败']);
            }
            //dump($data);
            //dump($res);
        }
    }
	
	/**
     * 删除导出方案
     * Create by liangfeng
     * 2017-07-13
     */
	public function del_programme(){
		$ids = I('post.id');
		$ids = implode(',',$ids);
		$res = M('export_programme')->where(['id'=>['in',$ids]])->delete();
		if($res !== false){
			$this->ajaxReturn(['status'=>'success','msg'=>'删除成功']);
		}else{
			$this->ajaxReturn(['status'=>'warning','msg'=>'删除失败']);
		}
		

	}


    /**
     * 生成导出excel
     * Create by liangfeng
     * 2017-07-01
     */
    public function export(){
        include_once(VENDOR_PATH.'/Beanstalkd/Beanstalkd.php');
        //连接beanstalkd
        $bs = new \Beanstalkd(C('BEANSTALKD'));
        if(!$bs->connect()){
            $this->ajaxReturn(['status'=>'warninig','data'=>'连接Beanstalkd失败!','msg'=>'连接失败']);
        }

        $rs = R::getInstance(['url' => ['programme_total' => '/Export/get_programme_count'], 'rest' => ['rest2'], 'data' => [['id' => I('post.id')]]])->multiCurl();

        if($rs['programme_total']['data'] == 0){
            $this->ajaxReturn(['status'=>'warninig','data'=>$rs,'msg'=>'此方案查询没有数据！']);
        }

        S('export_programme_' . $id . '_zip',null);
        S('export_programme_' . $id . '_qiniu',null);
        for($i=1;$i<=$rs['programme_total']['data'];$i++){
            S('export_programme_'.I('post.id').'_'.$i.'_finish',null);
            $api_data = array();
            $api_data['id'] =I('post.id');
            $api_data['p'] = $i;
            $api_data['total'] = $rs['programme_total']['data'];
            $api_data = serialize($api_data);

            $data = array();
            $data['execute'] = 'TrjWorker';
            $data['args'] = ['type'=>'export_data','val'=>$api_data];
            $data  = serialize($data);

            $bs->useTube('trj_export_data');
            $bs->put(0,0,30,$data);
            usleep(100);
        }
        $this->ajaxReturn(['status'=>'success','data'=>$rs,'msg'=>'开始导出方案，请查询进度，待完成后刷新页面下载即可。']);
    }

    /**
     * 查询生成进度
     * Create by liangfeng
     * 2017-08-04
     */
    public function export_progress(){
        $id = I('post.id',0,'int');
        $res = R::getInstance(['url' => ['programme_zip' => '/Export/programme_zip'], 'rest' => ['rest2'], 'data' => [['id' => $id]]])->multiCurl();
        $this->ajaxReturn(['status' => 'success', 'data' => $rs, 'total' => $total, 'msg' => $res['programme_zip']['msg']]);
        exit();

        $total = I('post.total',0,'int');
        if($total == 0){
            $res = R::getInstance(['url' => ['programme_total' => '/Export/get_programme_count'], 'rest' => ['rest2'], 'data' => [['id' => I('post.id')]]])->multiCurl();
            $total = $res['programme_total']['data'];
        }
        if($total > 0) {
            $rs = [];
            $num = 0;
            for ($i = 1; $i <= $total; $i++) {
                $rs[$id . '-' . $i] = S('export_programme_' . $id . '_' . $i . '_finish');
                if(S('export_programme_' . $id . '_' . $i . '_finish') == 1){
                    $num++;
                }
            }

            $str = '此导出共' . $total . '份Excel文件，已生成'.$num.'份Excel文件，';
            if(S('export_programme_' . $id . '_zip') != null){
                $str .= '压缩包已经完成，';
            }else{
                $str .= '压缩包未完成，';
            }
            if(S('export_programme_' . $id . '_qiniu') != null){
                $str .= '上传已经完成，请重新刷新页面下载！';
            }else{
                $str .= '上传未完成！';
            }
            $this->ajaxReturn(['status' => 'success', 'data' => $rs, 'total' => $total, 'msg' => $str]);



        }else{
            $this->ajaxReturn(['status' => 'warning', 'data' => $rs, 'total' => $total, 'msg' => '操作失败']);
        }
    }

    /**
     * 下载excel
     * Create by liangfeng
     * 2017-07-01
     */
    public function download_excel(){
        $res = M('export_programme')->field('name,download_path,download_atime')->find(I('get.id'));
        if(!$res || empty($res['download_path'])){
            exit('没有文件可以下载');
        }
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="'.$res['download_atime'].$res['name'].'.zip"');
        header('Cache-Control: max-age=0');
        echo file_get_contents($res['download_path']);
    }
	
	
	
	/**
     * 话费订单导出
	 * Create by liangfeng
     * 2017-06-10
     */
	 
    public function mobile_orders(){
		$this->get_formtpl_fields($this->mobile_orders_formtpl_id);
		$this->display();
    }
	/**
     * 话费订单保存导出选项
     * Create by liangfeng
     * 2017-05-16
     */
	public function export_mobile_orders_set_save(){
		$res = $this->save_formtpl_fields($this->mobile_orders_formtpl_id,I('post.'));
		$this->ajaxReturn($res);
	}
	
	/**
	 * 话费订单导出数据
	 * Create by liangfeng
	 * 2017-05-16
	 */
	public function export_mobile_orders_file(){
		
		set_time_limit(0);
		
		$export_fields = M('formtpl')->where(array('id'=>$this->mobile_orders_formtpl_id))->getField('export_fields');
		if($export_fields){
			
			//检查是否有选择导出字段
			if(isset($_POST['field']) && !empty($_POST['field'])){
				$field_ids = '';
				//将选中的导出字段排序
				foreach($_POST['field'] as $k => $v){
					$field_ids .= $v.',';
				}
				$field_ids = substr($field_ids,0,strlen($field_ids)-1); 
			
				$fields = M('formtpl_fields')->field('name,label')->where(' id IN ('.$field_ids.')')->order('instr("'.$field_ids.'",id)')->select();
				
				if(count($fields) < 8){
					return false;
				}
				//excel横列排序
				$out_option_orders = 'A';
				foreach($fields as $k => $v){
					$out_excel_option[$out_option_orders]['descript'] = $v['label'];
					$out_excel_option[$out_option_orders]['field'] = $v['name'];
					$out_option_orders++;
					$field_names .= $v['name'].',';
				}
				$field_names = substr($field_names,0,strlen($field_names)-1); 
				
				
				
			}else return false;
			//dump($field_names);exit;
			$cfg = eval(html_entity_decode($export_fields));
			
			
			//dump($fields);exit;
			//dump($cfg);exit;
			
			if($cfg['return_status_sum']){
				$return_status_sum = 0;
				foreach($cfg['return_status_sum'] as $v){
					$return_status_sum += $v;
				}
				switch($return_status_sum){
					case 1: $map['return_status'] = ['in','1,29'];
					break;
					case 2: $map['return_status'] = ['in','4'];
					break;
					case 4: $map['return_status'] = ['not in','1,4,29'];
					break;
					case 3: $map['return_status'] = ['in','1,4,29'];
					break;
					case 5: $map['return_status'] = ['not in','4'];
					break;
					case 6: $map['return_status'] = ['not in','1,29'];
					break;
					case 7: 
					break;
				}				
			}
			
			if($cfg['status'])	$map['status']	=	['in',$cfg['status']];
			if($cfg['terminal']) 	$map['terminal']	=	['in',$cfg['terminal']];
			if($cfg['pay_type'])	$map['pay_type']	=	['in',$cfg['pay_type']];

			if(empty($cfg['sday'])) $cfg['sday']	=	'2016-07-01';
			if(empty($cfg['eday'])) $cfg['eday']	=	date('Y-m-d',time()+86400);
			$map[$cfg['day_field']]	=	['between',[$cfg['sday'],$cfg['eday']]];			
			
			if($sql)	$map['_string']	=	implode(' and ',$sql);
			$list	=	M('mobile_orders')->field('id,'.$field_names)->where($map)->order('id desc')->limit(1000)->select();
			//dump(M()->getlastsql());
			
			foreach($list as $k => $v){
				//将数据中的字段转换
				foreach($v as $ke => $va){
					if($ke=='status'){
						$list[$k][$ke] = ['已删除','已拍下','已付款','已发货','已收货','已评价','已归档','','','','已关闭','已关闭'][$va];
					}
					if($ke=='pay_type'){
						$list[$k][$ke] = ['','余额','唐宝','微信','','支付宝','','银联'][$va];
					}
					if($ke=='recharge_type'){
						$list[$k][$ke] = ['','话费','流量'][$va];
					}
					if($ke=='terminal'){
						$list[$k][$ke] = ['PC','WAP','IOS','ANDROID'][$va];
					}
					if($ke=='operator'){
						$list[$k][$ke] = ['','移动','联通','电信'][$va];
					}
					if($ke=='type'){
						$list[$k][$ke] = ['','奖励积分','不奖励积分'][$va];
					}
					
					if($ke=='return_status'){
						$list[$k][$ke] = mobile_return_code($va,1)['return_status'];
					}
					if($ke=='transtat'){
						$list[$k][$ke] = mobile_return_code($va,1)['transtat'];
					}
					if($ke=='seller_id' || $ke=='uid'){
						$list[$k][$ke] = M('user')->cache(true)->where(['id'=>$va])->getField('nick');
					}
					if($ke=='shop_id'){
						$list[$k][$ke] = M('shop')->cache(true)->where(['id'=>$va])->getField('shop_name');
					}
				}
			}
			//dump($list);
			D('Admin/Excel')->outExcel($list,$out_excel_option,'话费订单信息');
			
		}
		
	}
    /**
     * 订单导出
	 * Create by liangfeng
     * 2017-05-16
     */
	 
    public function orders(){
		$this->get_formtpl_fields($this->orders_formtpl_id);		
		$this->display();
    }
	/**
     * 订单保存导出选项
     * Create by liangfeng
     * 2017-05-16
     */
	public function export_orders_set_save(){
		$res = $this->save_formtpl_fields($this->orders_formtpl_id,I('post.'));
		$this->ajaxReturn($res);
	}
	/**
	 * 订单导出数据
	 * Create by liangfeng
	 * 2017-05-16
	 */
	public function export_orders_file(){
		set_time_limit(0);
		
		$export_fields = M('formtpl')->where(array('id'=>$this->orders_formtpl_id))->getField('export_fields');
		
		if($export_fields){
			//检查是否有选择导出字段
			if(isset($_POST['field']) && !empty($_POST['field'])){
				$field_ids = '';
				//将选中的导出字段排序
				foreach($_POST['field'] as $k => $v){
					$field_ids .= $v.',';
				}
				$field_ids = substr($field_ids,0,strlen($field_ids)-1); 
				
				$fields = M('formtpl_fields')->field('name,label')->where(' id IN ('.$field_ids.')')->order('instr("'.$field_ids.'",id)')->select();

				$fields[] = ['label'=>'收货地址','name'=>'link_arress'];
				$fields[] = ['label'=>'收货人','name'=>'link_name'];
				$fields[] = ['label'=>'收货人手机号码','name'=>'mobile'];
				$fields[] = ['label'=>'收货人固定电话','name'=>'tel'];

				//excel横列排序
				$out_option_orders = 'A';
				foreach($fields as $k => $v){
					$out_excel_option[$out_option_orders]['descript'] = $v['label'];
					$out_excel_option[$out_option_orders]['field'] = $v['name'];
					$out_option_orders++;
					$field_names .= $v['name'].',';
				}
				$field_names = substr($field_names,0,strlen($field_names)-1); 
				
				//订单商品表的字段
				$orders_goods_field = array(
					//array('label'=>'分账金额','name'=>'inventory_monry'),
					//array('label'=>'成本价','name'=>'cost_price'),
					//array('label'=>'录入时间','name'=>'purchase_time'),
					//array('label'=>'订单利润','name'=>'profit_price'),
					//array('label'=>'财务录入运费退款','name'=>'refund_express_price'),
					//array('label'=>'财务录入退款总金额','name'=>'refund_totals_price'),
					//array('label'=>'录入退款时间','name'=>'refund_time'),
				);
				foreach($orders_goods_field as $v){
					$out_excel_option[$out_option_orders]['descript'] = $v['label'];
					$out_excel_option[$out_option_orders]['field'] = $v['name'];
					$out_option_orders++;
				}
				
				
			}else return false;
			//dump($field_names);exit;
			$cfg = eval(html_entity_decode($export_fields));
			
			
			
			//dump($fields);exit;
			//dump($cfg);exit;
			if($cfg['inventory_type']) $map['inventory_type'] = ['in',$cfg['inventory_type']];
			if($cfg['status'])	$map['status']	=	['in',$cfg['status']];
			if($cfg['terminal']) 	$map['terminal']	=	['in',$cfg['terminal']];
			if($cfg['pay_type'])	$map['pay_type']	=	['in',$cfg['pay_type']];
			if($cfg['score_type'])	$map['score_type']	=	['in',$cfg['score_type']];

			if(empty($cfg['sday'])) $cfg['sday']	=	'2016-07-01';
			if(empty($cfg['eday'])) $cfg['eday']	=	date('Y-m-d',time()+86400);
			$map[$cfg['day_field']]	=	['between',[$cfg['sday'],$cfg['eday']]];

			if(empty($cfg['snum'])) $cfg['snum']	=	0;
			if(empty($cfg['enum'])) $cfg['enum']	=	10000000;
			$map[$cfg['num_field']]	=	['between',[$cfg['snum'],$cfg['enum']]];
			//自营选择
			if($cfg['shop_type']){
				//只选择非自营
				if($cfg['shop_type'][0] == '2'){
					$sql[] = 'shop_id in (select id from '.C('DB_PREFIX').'shop where type_id != 1)';
					
				//选择全部
				}else if($cfg['shop_type'][1] == '2'){
				
				//只选择自营
				}else{
					$sql[] = 'shop_id in (select id from '.C('DB_PREFIX').'shop where type_id = 1)';
				}
			}
			
			//代购选择
			if($cfg['is_daigou']){
				//只选择代购
				if($cfg['is_daigou'][0] == '1'){
					$map['daigou_cost']	=	['gt',0];
				//选择全部
				}else if($cfg['is_daigou'][1] == '1'){
				
				//只选择自营
				}else{
					$map['daigou_cost']	=	['eq',0];
				}
			}
			
			if($cfg['shop_name']) $sql[]	=	'shop_id in (select id from '.C('DB_PREFIX').'shop where shop_name="'.$cfg['shop_name'].'")';
			if($cfg['nick']) $sql[]	=	'seller_id in (select id from '.C('DB_PREFIX').'user where nick="'.$cfg['nick'].'")';

			if($sql)	$map['_string']	=	implode(' and ',$sql);
			$list	=	M('orders_shop')->where($map)->order('id desc')->limit(2000)->select();
			log_add('export_log',['atime' => date('Y-m-d H:i:s'),'sql' => M()->getlastsql()]);
			//dump(M('orders_shop')->getlastsql());exit;
            //dump($field_names);
			$add_num = 0;
			foreach($list as $k => $v){


			    //$area = M('area')->field('a_name')->where('id in (select province from ylh_orders where o_no = "'.$v['o_no'].'")')->select();


				//将数据中的字段转换
				foreach($v as $ke => $va){
					if($ke=='status'){
						$data = array('已删除','已拍下','已付款','已发货','已收货','已评价','已归档','','','','已关闭','已关闭');
						$list[$k+$add_num][$ke] = $data[$va];
					}else if($ke=='score_type'){
						$data = array('','金积分','第三方支付','','银积分');
						$list[$k+$add_num][$ke] = $data[$va];
					}else if($ke=='express_type'){
						$data = array('','快递','EMS');
						$list[$k+$add_num][$ke] = $data[$va];
					}else if($ke=='inventory_type'){
						$data = array('扣除货款模式','扣除库存积分模式');
						$list[$k+$add_num][$ke] = $data[$va];
					}else if($ke=='terminal'){
						$data = array('PC','WAP','IOS','ANDROID');
						$list[$k+$add_num][$ke] = $data[$va];
					}else if($ke=='uid' || $ke=='seller_id'){
						$user_info = M('user')->cache(true)->field('nick')->where('id = '.$va)->find();
						$list[$k+$add_num][$ke] = $user_info['nick'];
					}else if($ke=='shop_id'){
						$shop_info = M('shop')->cache(true)->field('shop_name')->where('id = '.$va)->find();
						$list[$k+$add_num][$ke] = $shop_info['shop_name'];
					}else{
						$list[$k+$add_num][$ke] = ''.(string)$va.'';
					}
				}


                //查询收货地址
                $area=$this->cache_table('area');
                $order_link = M('orders')->field('province,city,district,town,street,linkname,tel,mobile')->where(['o_no'=>$v['o_no']])->find();
                $list[$k+$add_num]['link_arress'] = $area[$order_link['province']].$area[$order_link['city']].$area[$order_link['district']].$area[$order_link['town']].$order_link['street'];
                $list[$k+$add_num]['link_name'] = $order_link['linkname'];
                $list[$k+$add_num]['tel'] = $order_link['tel'];
                $list[$k+$add_num]['mobile'] = $order_link['mobile'];


				//真正的利润
				$profit_price = 0;
				//分账金额
				$inventory_monry = 0;
				//订单商品最早的成本录入时间
				$first_purchase_time = 0;
				
				$order_goods = M('orders_goods')->field('id,goods_name,attr_name,price,num,total_price_edit,score_ratio,score,cost_price,profit_price,refund_express_price,refund_totals_price,purchase_time,refund_time')->where('s_id = '.$v['id'])->select();
				foreach($order_goods as $ke => $va){
					//商品真正的利润
					$va['profit_price'] = $va['profit_price']+$va['refund_express_price']+$va['refund_totals_price'];
					//商品分账金额(保留2位小数其他舍去)
					$va['inventory_monry'] = sprintf("%.2f",substr(sprintf("%.3f", 0.08*$va['score']/100), 0, -1));

					//循环字段（将订单商品表的字段换成订单表的字段）
					foreach($va as $key => $val){
						$tmp = array();
						$tmp['shop_id'] = '商品名称：'.$va['goods_name'];
						$tmp['uid'] = '商品属性：'.$va['attr_name'];

						$tmp['goods_price'] = $va['price'];
						$tmp['goods_num'] = $va['num'];
						$tmp['pay_price'] = $va['total_price_edit'];
						/*
						$tmp['score'] = $va['score'];
						//$tmp['status'] = '积分比例：'.$va['score_ratio'];
						$tmp['cost_price'] = $va['cost_price'];
						$tmp['profit_price'] = $va['profit_price'];
						$tmp['refund_express_price'] = $va['refund_express_price'];
						$tmp['refund_totals_price'] = $va['refund_totals_price'];
						$tmp['purchase_time'] = strtotime($va['purchase_time']) !== false ? $va['purchase_time'] : '';
						$tmp['refund_time']   = $va['refund_time'];
						*/
						//$tmp['inventory_monry'] = $va['inventory_monry'];
					}
					//将换算好的数据覆盖原来
					$order_goods[$ke] = $tmp;
					
					//订单累计真正的利润
					$profit_price += $va['profit_price'];
					//订单累计分账金额
					$inventory_monry += $va['inventory_monry'];

					//获取商品中最早录入时间
					$purchase_time = strtotime($va['purchase_time']);
					if($first_purchase_time == 0 && $purchase_time !== false){
						$first_purchase_time = $purchase_time;
					}else if($purchase_time !== false && $first_purchase_time !== 0 && $purchase_time <= $first_purchase_time){
						$first_purchase_time = $purchase_time;
					}
				}
				$list[$k+$add_num]['profit_price'] = $profit_price;
				$list[$k+$add_num]['inventory_monry'] = $inventory_monry;
				$list[$k+$add_num]['purchase_time'] = $first_purchase_time > 0 ? date('Y-m-d H:i:s',$first_purchase_time) : '';
				
				//在订单列表中插入商品信息
				array_splice($list,$k+1+$add_num,0,$order_goods);
				//echo $k+1+$add_num;
				$add_num = $add_num+count($order_goods);
				//unset($t);
				//dump($list);
				//dump($t);
				//echo $k;
			}
			//array_splice($list,1,0,$t);
			//array_splice($list,2,0,$t);
			//dump($list);
			D('Admin/Excel')->outExcel($list,$out_excel_option,'订单信息');
		}else return false;

	}

    /**
     * 退款导出
	 * Create by liangfeng
     * 2017-05-16
     */
    public function refund(){
		$this->get_formtpl_fields($this->refund_formtpl_id);		
		$this->display();
    }
	/**
     * 退款保存导出选项
     * Create by liangfeng
     * 2017-05-16
     */
	public function export_refund_set_save(){
		$res = $this->save_formtpl_fields($this->refund_formtpl_id,I('post.'));
		$this->ajaxReturn($res);
	}
	
	/**
	 * 退款导出数据
	 * Create by liangfeng
	 * 2017-05-16
	 */
	public function export_refund_file(){
		//检查是否有选择导出字段
		$export_fields = M('formtpl')->where(array('id'=>$this->refund_formtpl_id))->getField('export_fields');
		if(isset($_POST['field']) && !empty($_POST['field'])){
			$field_ids = '';
			//将选中的导出字段排序
			foreach($_POST['field'] as $k => $v){
				$field_ids .= $v.',';
			}
			$field_ids = substr($field_ids,0,strlen($field_ids)-1); 
			
			$fields = M('formtpl_fields')->field('name,label')->where(' id IN ('.$field_ids.')')->order('instr("'.$field_ids.'",id)')->select();
			if(count($fields) < 8){
				return false;
			}
			//excel横列排序
			$out_option_orders = 'A';
			foreach($fields as $k => $v){
				$out_excel_option[$out_option_orders]['descript'] = $v['label'];
				$out_excel_option[$out_option_orders]['field'] = $v['name'];
				$out_option_orders++;
				$field_names .= $v['name'].',';
				
				if($v['name'] == 's_no'){
					$order_fields = array(
						array('label'=>'运费金额','field'=>'express_price'),
						array('label'=>'商品金额','field'=>'goods_price_edit'),
						array('label'=>'修改后的运费','field'=>'express_price_edit'),
						array('label'=>'实付金额','field'=>'pay_price'),
						array('label'=>'订单总金额','field'=>'total_price'),
						array('label'=>'支付时间','field'=>'pay_time'),
						array('label'=>'支付方式','field'=>'pay_type'),
						array('label'=>'库存结算方式','field'=>'inventory_type'),
					);
					foreach($order_fields as $va){
						$out_excel_option[$out_option_orders]['descript'] = $va['label'];
						$out_excel_option[$out_option_orders]['field'] = $va['field'];
						$out_option_orders++;
						$order_field_names .= $va['field'].',';
					}
				}
			}
			$field_names = substr($field_names,0,strlen($field_names)-1); 
			$order_field_names = substr($order_field_names,0,strlen($order_field_names)-1); 
			
		}else return false;

		//dump($order_field_names);
		//exit;
		$cfg = eval(html_entity_decode($export_fields));

		if($cfg['status'])	$map['status']	=	['in',$cfg['status']];
		if($cfg['type']) 	$map['type']	=	['in',$cfg['type']];

		if(empty($cfg['sday'])) $cfg['sday']	=	'2016-07-01';
		if(empty($cfg['eday'])) $cfg['eday']	=	date('Y-m-d',time()+86400);
		$map[$cfg['day_field']]	=	['between',[$cfg['sday'],$cfg['eday']]];

		if(empty($cfg['snum'])) $cfg['snum']	=	0;
		if(empty($cfg['enum'])) $cfg['enum']	=	10000000;
		$map[$cfg['num_field']]	=	['between',[$cfg['snum'],$cfg['enum']]];

		if($cfg['shop_name']) $sql[]	=	'shop_id in (select id from '.C('DB_PREFIX').'shop where shop_name="'.$cfg['shop_name'].'")';
		if($cfg['nick']) $sql[]	=	'seller_id in (select id from '.C('DB_PREFIX').'user where nick="'.$cfg['nick'].'")';

		if($sql)	$map['_string']	=	implode(' and ',$sql);
		$list	=	M('refund')->field('id,s_id,'.$field_names)->where($map)->order('id desc')->limit(1000)->select();
		//dump($list);
		//dump(M()->getlastsql());
		//exit;
		foreach($list as $k => $v){
			//将数据中的字段转换
			foreach($v as $ke => $va){
				if($ke=='status'){
					$data = array('','退款','卖家拒绝','修改','同意','买家寄出商品','卖家寄出商品',10=>'买家可申诉',11=>'卖家未收到退货',12=>'买家未收到货',20=>'退款已取消',100=>'退款已完成');
					$list[$k+$add_num][$ke] = ' '.$data[$va];
				}else if($ke=='type'){
					$data = array('','退货退款','只退款','退运费');
					$list[$k+$add_num][$ke] = ' '.$data[$va];
				}else if($ke=='express_type'){
					$data = array('','快递','EMS');
					$list[$k+$add_num][$ke] = ' '.$data[$va];
				}else if($ke=='inventory_type'){
					$data = array('非即时结算','即时结算');
					$list[$k+$add_num][$ke] = ' '.$data[$va];
				}else if($ke=='terminal'){
					$data = array('PC','WAP','IOS','ANDROID');
					$list[$k+$add_num][$ke] = ' '.$data[$va];
				}else if($ke=='uid' || $ke=='seller_id'){
					$user_info = M('user')->cache(true)->field('nick')->where('id = '.$va)->find();
					$list[$k+$add_num][$ke] = ' '.$user_info['nick'];
				}else if($ke=='shop_id'){
					$shop_info = M('shop')->cache(true)->field('shop_name')->where('id = '.$va)->find();
					$list[$k+$add_num][$ke] = ' '.$shop_info['shop_name'];
				}else{
					$list[$k+$add_num][$ke] = ' '.$va;
				}
			}
			if(isset($list[$k]['s_no'])){
				$res = M('orders_shop')->field($order_field_names)->where('id="'.$v['s_id'].'"')->find();
				$list[$k]['express_price'] = $res['express_price'];
				$list[$k]['goods_price_edit'] = $res['goods_price_edit'];
				$list[$k]['express_price_edit'] = $res['express_price_edit'];
				$list[$k]['pay_price'] = $res['pay_price'];
				$list[$k]['total_price'] = $res['total_price'];
				$list[$k]['pay_time'] = $res['pay_time'];
				$data = array('非即时结算','即时结算');
				$list[$k]['inventory_type'] = $data[$res['inventory_type']];
				$data = array('','余额','唐宝','支付宝','微信');
				$list[$k]['pay_type'] = $data[$res['pay_type']];
				
			}
			
			
		}
		

		D('Admin/Excel')->outExcel($list,$out_excel_option,'退款信息');
	}
	
	/**
     * 读取导出的字段
     * Create by liangfeng
     * 2017-05-16
     */
	private function get_formtpl_fields($id){
		$export_fields = M('formtpl')->where(array('id'=>$id))->getField('export_fields');
		if($export_fields) $this->assign('rs',eval(html_entity_decode($export_fields)));
	
    	$fields=M('formtpl_fields')->where(array('formtpl_id'=>$id))->field('atime,etime,ip',true)->order('sort asc')->select();
		$this->assign('allfields',$fields);
		//dump($fields);
	}
	
	/**
     * 保存导出的字段
     * Create by liangfeng
     * 2017-05-16
     */
	private function save_formtpl_fields($id,$param){
		if(!isset($param['field']) || empty($param['field'])){
			return ['status'=>'warning','msg'=>'导出字段不能为空！'];
		}
		if(false!==M('formtpl')->where(['id' => $id])->save(['export_fields' => 'return '.var_export($param,true).';'])){
			return ['status'=>'success','msg'=>'操作成功！'];
		}else{
			return ['status'=>'warning','msg'=>'操作失败！'];
		}
		
		
	}


}