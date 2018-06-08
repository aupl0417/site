<?php  
/**
* 汉字转拼音
* @param string         $_String        要转的汉字
* @param string         $_Code  编码
*/
function Pinyin($_String, $_Code='utf-8')  
{  
        $_DataKey = "a|ai|an|ang|ao|ba|bai|ban|bang|bao|bei|ben|beng|bi|bian|biao|bie|bin|bing|bo|bu|ca|cai|can|cang|cao|ce|ceng|cha".  
                "|chai|chan|chang|chao|che|chen|cheng|chi|chong|chou|chu|chuai|chuan|chuang|chui|chun|chuo|ci|cong|cou|cu|".  
                "cuan|cui|cun|cuo|da|dai|dan|dang|dao|de|deng|di|dian|diao|die|ding|diu|dong|dou|du|duan|dui|dun|duo|e|en|er".  
                "|fa|fan|fang|fei|fen|feng|fo|fou|fu|ga|gai|gan|gang|gao|ge|gei|gen|geng|gong|gou|gu|gua|guai|guan|guang|gui".  
                "|gun|guo|ha|hai|han|hang|hao|he|hei|hen|heng|hong|hou|hu|hua|huai|huan|huang|hui|hun|huo|ji|jia|jian|jiang".  
                "|jiao|jie|jin|jing|jiong|jiu|ju|juan|jue|jun|ka|kai|kan|kang|kao|ke|ken|keng|kong|kou|ku|kua|kuai|kuan|kuang".  
                "|kui|kun|kuo|la|lai|lan|lang|lao|le|lei|leng|li|lia|lian|liang|liao|lie|lin|ling|liu|long|lou|lu|lv|luan|lue".  
                "|lun|luo|ma|mai|man|mang|mao|me|mei|men|meng|mi|mian|miao|mie|min|ming|miu|mo|mou|mu|na|nai|nan|nang|nao|ne".  
                "|nei|nen|neng|ni|nian|niang|niao|nie|nin|ning|niu|nong|nu|nv|nuan|nue|nuo|o|ou|pa|pai|pan|pang|pao|pei|pen".  
                "|peng|pi|pian|piao|pie|pin|ping|po|pu|qi|qia|qian|qiang|qiao|qie|qin|qing|qiong|qiu|qu|quan|que|qun|ran|rang".  
                "|rao|re|ren|reng|ri|rong|rou|ru|ruan|rui|run|ruo|sa|sai|san|sang|sao|se|sen|seng|sha|shai|shan|shang|shao|".  
                "she|shen|sheng|shi|shou|shu|shua|shuai|shuan|shuang|shui|shun|shuo|si|song|sou|su|suan|sui|sun|suo|ta|tai|".  
                "tan|tang|tao|te|teng|ti|tian|tiao|tie|ting|tong|tou|tu|tuan|tui|tun|tuo|wa|wai|wan|wang|wei|wen|weng|wo|wu".  
                "|xi|xia|xian|xiang|xiao|xie|xin|xing|xiong|xiu|xu|xuan|xue|xun|ya|yan|yang|yao|ye|yi|yin|ying|yo|yong|you".  
                "|yu|yuan|yue|yun|za|zai|zan|zang|zao|ze|zei|zen|zeng|zha|zhai|zhan|zhang|zhao|zhe|zhen|zheng|zhi|zhong|".  
                "zhou|zhu|zhua|zhuai|zhuan|zhuang|zhui|zhun|zhuo|zi|zong|zou|zu|zuan|zui|zun|zuo";  

        $_DataValue = "-20319|-20317|-20304|-20295|-20292|-20283|-20265|-20257|-20242|-20230|-20051|-20036|-20032|-20026|-20002|-19990".  
                "|-19986|-19982|-19976|-19805|-19784|-19775|-19774|-19763|-19756|-19751|-19746|-19741|-19739|-19728|-19725".  
                "|-19715|-19540|-19531|-19525|-19515|-19500|-19484|-19479|-19467|-19289|-19288|-19281|-19275|-19270|-19263".  
                "|-19261|-19249|-19243|-19242|-19238|-19235|-19227|-19224|-19218|-19212|-19038|-19023|-19018|-19006|-19003".  
                "|-18996|-18977|-18961|-18952|-18783|-18774|-18773|-18763|-18756|-18741|-18735|-18731|-18722|-18710|-18697".  
                "|-18696|-18526|-18518|-18501|-18490|-18478|-18463|-18448|-18447|-18446|-18239|-18237|-18231|-18220|-18211".  
                "|-18201|-18184|-18183|-18181|-18012|-17997|-17988|-17970|-17964|-17961|-17950|-17947|-17931|-17928|-17922".  
                "|-17759|-17752|-17733|-17730|-17721|-17703|-17701|-17697|-17692|-17683|-17676|-17496|-17487|-17482|-17468".  
                "|-17454|-17433|-17427|-17417|-17202|-17185|-16983|-16970|-16942|-16915|-16733|-16708|-16706|-16689|-16664".  
                "|-16657|-16647|-16474|-16470|-16465|-16459|-16452|-16448|-16433|-16429|-16427|-16423|-16419|-16412|-16407".  
                "|-16403|-16401|-16393|-16220|-16216|-16212|-16205|-16202|-16187|-16180|-16171|-16169|-16158|-16155|-15959".  
                "|-15958|-15944|-15933|-15920|-15915|-15903|-15889|-15878|-15707|-15701|-15681|-15667|-15661|-15659|-15652".  
                "|-15640|-15631|-15625|-15454|-15448|-15436|-15435|-15419|-15416|-15408|-15394|-15385|-15377|-15375|-15369".  
                "|-15363|-15362|-15183|-15180|-15165|-15158|-15153|-15150|-15149|-15144|-15143|-15141|-15140|-15139|-15128".  
                "|-15121|-15119|-15117|-15110|-15109|-14941|-14937|-14933|-14930|-14929|-14928|-14926|-14922|-14921|-14914".  
                "|-14908|-14902|-14894|-14889|-14882|-14873|-14871|-14857|-14678|-14674|-14670|-14668|-14663|-14654|-14645".  
                "|-14630|-14594|-14429|-14407|-14399|-14384|-14379|-14368|-14355|-14353|-14345|-14170|-14159|-14151|-14149".  
                "|-14145|-14140|-14137|-14135|-14125|-14123|-14122|-14112|-14109|-14099|-14097|-14094|-14092|-14090|-14087".  
                "|-14083|-13917|-13914|-13910|-13907|-13906|-13905|-13896|-13894|-13878|-13870|-13859|-13847|-13831|-13658".  
                "|-13611|-13601|-13406|-13404|-13400|-13398|-13395|-13391|-13387|-13383|-13367|-13359|-13356|-13343|-13340".  
                "|-13329|-13326|-13318|-13147|-13138|-13120|-13107|-13096|-13095|-13091|-13076|-13068|-13063|-13060|-12888".  
                "|-12875|-12871|-12860|-12858|-12852|-12849|-12838|-12831|-12829|-12812|-12802|-12607|-12597|-12594|-12585".  
                "|-12556|-12359|-12346|-12320|-12300|-12120|-12099|-12089|-12074|-12067|-12058|-12039|-11867|-11861|-11847".  
                "|-11831|-11798|-11781|-11604|-11589|-11536|-11358|-11340|-11339|-11324|-11303|-11097|-11077|-11067|-11055".  
                "|-11052|-11045|-11041|-11038|-11024|-11020|-11019|-11018|-11014|-10838|-10832|-10815|-10800|-10790|-10780".  
                "|-10764|-10587|-10544|-10533|-10519|-10331|-10329|-10328|-10322|-10315|-10309|-10307|-10296|-10281|-10274".  
                "|-10270|-10262|-10260|-10256|-10254";  
        $_TDataKey   = explode('|', $_DataKey);  
        $_TDataValue = explode('|', $_DataValue);  

        $_Data = (PHP_VERSION>='5.0') ? array_combine($_TDataKey,  $_TDataValue) : _Array_Combine($_TDataKey, $_TDataValue);  
        arsort($_Data);  
        reset($_Data);  

        if($_Code != 'gb2312') $_String = _U2_Utf8_Gb($_String);  
        $_Res = '';  
        for($i=0; $i<strlen($_String); $i++)  
        {  
                $_P = ord(substr($_String, $i, 1));  
                if($_P>160) { $_Q = ord(substr($_String, ++$i, 1)); $_P = $_P*256 + $_Q - 65536; }  
                $_Res .= _Pinyin($_P, $_Data);  
        }  
        return preg_replace("/[^a-z0-9]*/", '', $_Res);  
}  

function _Pinyin($_Num, $_Data)  
{  
        if    ($_Num>0      && $_Num<160   ) return chr($_Num);  
        elseif($_Num<-20319 || $_Num>-10247) return '';  
        else  {  
                foreach($_Data as $k=>$v){ if($v<=$_Num) break; }  
                return $k;  
        }  
}  

function _U2_Utf8_Gb($_C)  
{  
        $_String = '';  
        if($_C < 0x80) $_String .= $_C;  
        elseif($_C < 0x800)  
        {  
                $_String .= chr(0xC0 | $_C>>6);  
                $_String .= chr(0x80 | $_C & 0x3F);  
        }elseif($_C < 0x10000){  
                $_String .= chr(0xE0 | $_C>>12);  
                $_String .= chr(0x80 | $_C>>6 & 0x3F);  
                $_String .= chr(0x80 | $_C & 0x3F);  
        } elseif($_C < 0x200000) {  
                $_String .= chr(0xF0 | $_C>>18);  
                $_String .= chr(0x80 | $_C>>12 & 0x3F);  
                $_String .= chr(0x80 | $_C>>6 & 0x3F);  
                $_String .= chr(0x80 | $_C & 0x3F);  
        }  
        return iconv('UTF-8', 'GB2312', $_String);  
}  

function _Array_Combine($_Arr1, $_Arr2)  
{  
        for($i=0; $i<count($_Arr1); $i++) $_Res[$_Arr1[$i]] = $_Arr2[$i];  
        return $_Res;  
} 

//echo Pinyin('这是WEB开发网'); //默认是gb编码
//echo Pinyin('这是WEB开发网',1); //第二个参数随意设置即为utf8编码


/**
* 获取某个类目下面的所有属性
* @param integer $id 类目ID
*/
function GetAttr($id){
        $attr = M('attribute_sort');
        $map['sid'] = 0;
        $map['cid'] = $id;
        $date = $attr->where($map)->field('name')->select();

        $temp = '';
        foreach($date as $k=>$v ){
                $temp .= $val['name'].',';
        };
        return $temp;
}

/**
* 设置栏目- 表单生成
* @param ingeter $sid   分组ID
*/
function config_item($sid){
        $do=M('config');
        $list=$do->where(array('sid'=>$sid,'active'=>1))->order('sort asc')->select();

        foreach($list as $key=>$val){
                $config=eval(html_entity_decode($val['config']));

                $item[$key]['type']=$val['type'];
                $item[$key]['textname']=$val['title'];
                $item[$key]['name']=$val['name'];
                $item[$key]['tips']=$config['tips'];
                $item[$key]['attr']=html_entity_decode($config['attr']);
                $item[$key]['class']=$config['class'];
                $item[$key]['style']=$config['style'];
                $item[$key]['prev']=$config['prev'];
                $item[$key]['next']=$config['next'];
                $item[$key]['placeholder']=$config['placeholder'];
                $item[$key]['is_need']=$config['is_need'];
                $item[$key]['value']=$val['value'];

                //$item[$key]['icon_left']=html_entity_decode($config['icon_left']);
                //$item[$key]['icon_right']=html_entity_decode($config['icon_right']);

                $item[$key]['addon_left']=html_entity_decode($config['addon_left']);
                $item[$key]['addon_right']=html_entity_decode($config['addon_right']);


                if(trim($config['data'])) {
                        $config['data']=eval(html_entity_decode($config['data']));
                        $item[$key]['data']=$config['data']['data'];
                        $item[$key]['field']=$config['data']['field'];
                }

                if($val['fun_before']){
                        $item[$key][$val['name'].'_before']=eval(html_entity_decode($val['fun_before']));
                }

                if($val['fun_after']){
                        $item[$key][$val['name'].'_after']=eval(html_entity_decode($val['fun_after']));
                }                               
                                
                if($val['fun_read'] && $param['data'][$val['name']]){
                        //dump(html_entity_decode($val['fun_read']));
                        $item[$key][$val['name'].'_read']=eval(html_entity_decode($val['fun_read']));   
                        $item[$key]['value']=$item[$key][$val['name'].'_read'];                                       
                }

        }

        $item2['type']='hidden';
        $item2['name']='sid';
        $item2['value']=$sid;

        $item=array_merge($item,array($item2));

        return $item;

}

/**
* 表单类型
* @param string $type   表单类型
*/
function formtype($type){
        foreach(C('FORM_TYPE') as $key=>$val){
                if($val['value']==$type){
                        return $val['name'];
                        exit;
                }
        }
}


//取类目路径
function get_sort($ids,$table){
        $do=M($table);
        $list=$do->where(array('id'=>array('in',$ids)))->field('id,name')->select();

        $html='';
        foreach($list as $val){
                $html.='<div class="btn btn-xs btn-default m3">'.$val['name'].'</div>';
        }


        return $html;
}

/**
* 会员认证资料
* @param array $rs 会员记录
* @param string $url 连接地址
*/
function user_info($rs,$url=''){
        $str=$url?'<div class="ft14 md10"><a href="'.$url.'">'.$rs['username'].'</a></div>':'<div class="ft14 md10">'.$rs['username'].'</div>';

        $str.='<div>';
        if($rs['is_name']==1) $str.='<span class="btn btn-xs btn-rad btn-trans btn-default">个人</span>';
        elseif($rs['is_name']==2) $str.='<span class="btn btn-xs btn-rad btn-trans btn-default">企业</span>';
        $str.=$rs['is_store']?'<span class="btn btn-xs btn-rad btn-trans btn-default">店</span>':'';
        $str.=$rs['is_xiaobao']?'<span class="btn btn-xs btn-rad btn-trans btn-default">保</span>':'';
        $str.=$rs['is_email']?'<span class="btn btn-xs btn-rad btn-trans btn-default">邮件</span>':'';
        $str.=$rs['is_mobile']?'<span class="btn btn-xs btn-rad btn-trans btn-default">手机</span>':'';

        $str.='</div>';

        return $str;
}

/**
* 显示购物车宝贝标题及规格
* @param array $val     购物车中的宝贝记录
*/
function cart_products($val){
        $str='<div class="ft14 md10"><a href="'.C('sub_domain.detail').'/item/'.$val['productsid'].'.html" target="_blank">'.($val['name']?$val['name']:$val['products_name']).'</a></div>';

        $str.='<div class="text-gray">';
        if($val['color']) $str.='<span class="mr20">颜色：'.$val['color'].'</span>';
        if($val['size']) $str.='<span class="mr20">尺码：'.$val['size'].'</span>';
        $str.='</div>';

        return $str;
}

/**
* 卖家信息
* @param array $param   卖家记录
*/
function shop_card($param){        
        if($param['qq']) $str.='<a href="http://wpa.qq.com/msgrd?v=3&uin='.$param['qq'].'&site=qq&menu=yes" target="_blank"><img src="http://wpa.qq.com/pa?p=1:'.$param['qq'].':4" border="0" /></a> ';
        if($param['wang']) $str.='<a href="http://amos1.taobao.com/msg.ww?v=2&amp;uid='.$param['wang'].'&amp;s=2" target="_blank" title="'.$param['wang'].'"><img alt="点击这里给我发消息" src="http://amos1.taobao.com/online.ww?v=2&amp;uid='.$param['wang'].'&amp;s=2" align="absBottom" border="0"></a> ';
        $str.='<a href="'.shop_url($param['id'],$param['domain']).'" target="_blank">'.$param['shop_name'].'</a>';
        return $str;
}

/**
* 评价图标
* @param integer $level  等级
*/
function level_img($level){
        $str='<img src="/Public/images/level'.$level.'.png">';
        return $str;
}


/**
* 雇员登录日志
* @param array $param   要记录的各项参数
*/
function admin_login_log($param){
        $data=array(
                'session_id'=>session_id(),
                'atime'         =>date('Y-m-d H:i:s'),                  //时间
                'ip'            =>get_client_ip(),                      //IP
                'userid'        =>session('admin.id'),                  //雇员ID
                'name'          =>session('admin.username'),            //雇员姓名
                'table'         =>$param['table'],                      //数据表
                'type'          =>$param['type'],                       //操作类型 UPDATE|INSERT|DELETE|LOGIN
                'sql'           =>$param['sql'],                        //SQL
                'res'           =>$param['res'],                        //操作结果 1成功,0失败
                'insid'         =>$param['insid'],                      //INSERT成功返回的ID
                'url'           =>__SELF__,
                'modules'       =>MODULE_NAME,
                'controller'    =>CONTROLLER_NAME,
                'action'        =>ACTION_NAME
        );

        $do=new \Think\Model\MongoModel(C('DB_MONGO_CONFIG.DB_PREFIX').'admin',null,C('DB_MONGO_CONFIG'));
        $do->add($data);
}

/**
* 订单状态
* @param integer $status    退款状态
* @param integer $orders_status    订单状态
*/
function refund_status($status,$type,$orders_status){
    switch($orders_status){
        case 1:
            $cmp_status=1;
        break;
        case 2:
            if($type==1) $cmp_status=3;
            else $cmp_status=2;
        break;
    }

    $result='<div class="btn btn-xs btn-rad btn-trans m0 btn-block">'.C('orders_code')[$cmp_status][$status].'</div>';
    return $result;
}


/**
* 图片输出
* @param string $url 图片url
* @param integer $width 图片宽度
* @param integer $height 图片高度
*/
function images($url,$width=80,$height='',$type=''){
    return '<a class="image-zoom" href="'.$url.'" title="大图"><img src="'.myurl($url,$width,$height,2,'',$type).'" alt="图片"></a>';
}

/**
* 状态输出
*/
function status($code,$data=null){
    if(is_null($data)) {
        $data[0]='<div class="btn btn-xs btn-rad btn-trans m0 btn-default">锁定</div>';
        $data[1]='<div class="btn btn-xs btn-rad btn-trans m0 btn-success">正常</div>';
        $result=$data[$code];
    }else{
        $result='<div class="btn btn-xs btn-rad btn-trans m0 '.$data[$code][1].'">'.$data[$code][0].'</div>';
    }

 

    return $result;
}

/**
* 连接
* @param string $text 链接文本
* @param string $url 链接
* @param string $target 目标窗口
*/
function linkurl($text,$url,$target='_self'){
    return '<a href="'.$url.'" target="'.$target.'">'.$text.'</a>';
}


/**
* 商品标题-用于商品列表时显示标题且显示违规信息
* @param string $goods_name 商品标题
* @param int $status 商品状态
* @param int $goods_id 商品ID
*/
function goods_name($val){
    $html='<div><a href="'.C('sub_domain.item').'/goods/'.$val['attr_list']['id'].'" target="_blank">'.$val['goods_name'].'</a></div>';
	/*
    if($val['status']==4){
        $rs=M('goods_illegl')->where(['goods_id'=>$val['id']])->find();
        $html.='<div class="mt10"><div class="text-danger strong pull-left ">违规原因：<span class="btn btn-xs btn-trans btn-rad illegl-view" data-id="'.$val['id'].'" data-url="/Goodsillegl/view/id/'.$rs['id'].'/from_goods/'.$val['id'].'">详情</span></div>'.($rs['illegl_point']?'<div class="text-danger pull-right">扣'.$rs['illegl_point'].'分</div>':'').'</div><div class="clearfix"></div>';
        $html.='<div class="text-gray">'.$rs['reason'].'</div>';
        
    }
	*/
    return $html;
}


/**
 * 取序列化图片
 */
function images_unserialize($val){
    $val = unserialize(html_entity_decode($val));

    foreach($val as $v){
        $html .= '<a class="image-zoom" href="'.$v.'" title="大图"><img src="'.myurl($v,100,60,2,'',1).'" alt="图片"></a> ';
    }

    return $html;
}

/**
 * 取多个分类导航
 */
function nav_category($ids,$table){
    $ids = explode(',',$ids);
    foreach($ids as $val){
        $html .= '<div>';
        $html .= nav_sort(array('table'=>$table,'field'=>'id,sid,category_name','id'=>$val,'key'=>'category_name','cache_name'=>$table.'_nav_'.$val));
        $html .= '</div>';
    }

    return $html;
}

/**
 * 获取上级导航名称
 * @param integer $pid
 */
function getChannelPid($pid) {
    $name = '顶级导航';
    if ($pid > 0) {
        $name = M('channel')->where(['id' => $pid])->getField('name');
    }
    return $name;
}

/**
 * 根据商品ID检查状态
 */
function goods_status($val){
    if($val['goods_id']) {
        $goods = M('goods')->where(['id' => $val['goods_id']])->field('status')->find();
        $status = ['删除','上架','仓库','异常','违规'];
        $btn    = ['','btn-success','btn-warning','',''];
        $html = '<div>'.$val['goods_id'].'</div>';
        $html .= '<a href="/Goods/edit/id/'.$val['goods_id'].'" target="_blank" class="btn btn-xs btn-trans '.$btn[$goods['status']].'">'.$status[$goods['status']].'</a>';
        return $html;
    }
}

/**
 * 优惠使用场景
 * Create by lazycat
 * 2017-04-21
 */
function coupon_use_type($val){
    switch($val['use_type']){
        case 2:
            $html = '<div class="btn btn-block btn-xs btn-trans btn-rad btn-info" onclick="show_item('.$val['id'].')">指定店铺</div>';
            break;
        case 3:
            $html = '<div class="btn btn-block btn-xs btn-trans btn-rad btn-success" onclick="show_item('.$val['id'].')">指定商品</div>';
            break;
        case 4:
            $html = '<div class="btn btn-block btn-xs btn-trans btn-rad btn-warning" onclick="show_item('.$val['id'].')">指定类目</div>';
            break;
        default:
            $html = '<div class="btn btn-block btn-xs btn-trans btn-rad">通用型</div>';
    }

    return $html;
}

/**
 * 话费充值状态码
 * Create by lazycat
 * 2017-05-13
 */
function mobile_return_code($val,$notips=0){
    $error_code = array(  //错误代码
        0   => '-',
        1   => '成功',
        2   => '重复订单， 与原交易不一致',
        3   => '单号重复， 交易已经接受',
        4   => '交易正在处理中',
        5   => '错误的交易指令',
        6   => '接口版本错',
        7   => '代理商校验错',
        8   => '不存在的代理商',
        9   => '其他错误',
        10  => '未定义(保留)',
        13  => '面值不正确',
        14  => '交易已经过期',
        17  => '超过约定交易限额',
        18  => '交易结果不能确定',
        20  => '校验失败',
        21  => '代理商已经暂停交易',
        22  => '交易品种没有定义',
        23  => '暂不支持指定号码充值',
        24  => '不能为该用户充值',
        25  => '指定充值号码与指定类别不一致',
        26  => '该代理商未开通该品种',
        28  => '成功金额小于申报金额',
        29  => '成功金额大于申报金额',
        30  => '充值号码错误',
        31  => '交易信息不存在',
        32  => '代理商错误率太高， 暂停',
        33  => '代理商余额不足',
        34  => '扣代理商款项失败',
        36  => '充值金额与交易金额不符',
        50  => '退款中',
        51  => '退款成功',
    );

    $post_success       = [1,3,4,10,18];    //受理成功编码！
    $recharge_success   = [1,29];           //充值成功编码！

    $res['transtat']        = $error_code[$val['transtat']];
    $res['return_status']   = $error_code[$val['return_status']];
	if($notips == 0){
		if(in_array($val['transtat'],$post_success))            $res['transtat']        = '<span class="text-success">已受理</span>';
		if(in_array($val['return_status'],$recharge_success))   $res['return_status']   = '<span class="text-success">充值成功</span>';
	}
    return $res;
}


?>