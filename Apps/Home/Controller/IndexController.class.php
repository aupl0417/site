<?php
namespace Home\Controller;
class IndexController extends CommonController {

    protected $_ids =   [];

    public function test(){
        $s  =   microtime(true);
        $model  =   M('goods_category');
        
        
        
        
        $this->_ad(19, 'slider');                 //slider
        
        /*
        $this->_ad(134, 'mainTop'); //内容部分顶部广告
        $this->_ad(135, 'newOne');  //乐兑新品1
        $this->_ad(136, 'newTwo');  //乐兑新品2
        
        
        $this->_ad(137, 'brandOne');   //品牌1
        $this->_ad(138, 'brandTwo');   //品牌2
        
        
        $this->_ad(139, 'fineOne');    //  精品1
        $this->_ad(141, 'fineTwo');    //  精品2
        $this->_ad(143, 'fineThree');    //  精品3
        $this->_ad(144, 'fineFour');    //  精品4
        
        
        $this->_ad(145, 'floorOne1');   //1楼-1
        $this->_ad(146, 'floorOne2');   //1楼-2
        $this->_ad(147, 'floorOne3');   //1楼-3
        $this->_ad(148, 'floorOne4');   //1楼-4
        $this->_ad(149, 'floorOneHot');   //1楼-热搜
        $cate[0]  =   $model->where(['sid' => ['in', '100845564,100841648'], 'is_hot' => 2, 'status' => 1])->field('category_name,id')->order('sort asc')->select();
        $this->assign('firstCate', $cate[0]);
        
        
        $cate[1] = $model->where(['sid' => ['in', '100841621,100845554'], 'is_hot' => 2, 'status' => 1])->field('category_name,id')->order('sort asc')->select();
        $this->assign('twoCate', $cate[1]);
        $cate[2] = $model->where(['sid' => ['in', '100845890,100841651'], 'is_hot' => 2, 'status' => 1])->field('category_name,id')->order('sort asc')->select();
        $this->assign('threeCate', $cate[2]);
        $cate[3] = $model->where(['sid' => ['in', '100841645,100841633'], 'is_hot' => 2, 'status' => 1])->field('category_name,id')->order('sort asc')->select();
        $this->assign('fourCate', $cate[3]);
        $cate[4] = $model->where(['sid' => ['in', '100845562,100845567'], 'is_hot' => 2, 'status' => 1])->field('category_name,id')->order('sort asc')->select();
        $this->assign('fiveCate', $cate[4]);
        
        
        $this->_ad(154, 'floorTwo1');   //1楼-1
        $this->_ad(153, 'floorTwo2');   //1楼-2
        $this->_ad(152, 'floorTwo3');   //1楼-3
        $this->_ad(151, 'floorTwo4');   //1楼-4
        $this->_ad(150, 'floorTwoHot');   //1楼-热搜
        
        $this->_ad(164, 'floorThree1');   //1楼-1
        $this->_ad(163, 'floorThree2');   //1楼-2
        $this->_ad(162, 'floorThree3');   //1楼-3
        $this->_ad(161, 'floorThree4');   //1楼-4
        $this->_ad(160, 'floorThreeHot');   //1楼-热搜
        
        $this->_ad(169, 'floorFour1');   //1楼-1
        $this->_ad(168, 'floorFour2');   //1楼-2
        $this->_ad(167, 'floorFour3');   //1楼-3
        $this->_ad(166, 'floorFour4');   //1楼-4
        $this->_ad(165, 'floorFourHot');   //1楼-热搜
        
        $this->_ad(174, 'floorFive1');   //1楼-1
        $this->_ad(173, 'floorFive2');   //1楼-2
        $this->_ad(172, 'floorFive3');   //1楼-3
        $this->_ad(171, 'floorFive4');   //1楼-4
        $this->_ad(170, 'floorFiveHot');   //1楼-热搜
        */
        
        $this->_ad(22, 'featured1');              //乐兑精选 1
        $this->_ad(23, 'featured2');              //乐兑精选 2
        $this->_ad(21, 'recommend');                 //推荐
        
        //一楼
        $this->_ad(24, 'firstBrandOne');                 //一楼品牌1
        $this->_ad(25, 'firstBrandTwo');                 //一楼品牌2
        $this->_ad(26, 'firstBrandAds');                 //一楼品牌广告
        $this->_ad(27, 'firstFeaturedOne');                 //一楼精选热卖1
        $this->_ad(28, 'firstFeaturedTwo');                 //一楼精选热卖2
        $this->_ad(29, 'firstFeaturedThree');                 //一楼精选热卖3
        $this->_ad(30, 'firstLikeOne');                 //一楼猜你喜欢1
        $this->_ad(31, 'firstLikeTwo');                 //一楼猜你喜欢2
        $this->_ad(32, 'firstLikeThree');                 //一楼猜你喜欢3
        //$this->_ad(97, 'firstLikeBrandOne');                 //一楼猜你喜欢品牌1
        //$this->_ad(98, 'firstLikeBrandTwo');                 //一楼猜你喜欢品牌2
        //$this->_ad(99, 'firstLikeBrandThree');                 //一楼猜你喜欢品牌ads
        $this->_ad(96, 'firstBottmAds');                        //一楼下方广告位
        $firstCate  =   $model->cache(true)->where(['sid' => '100841621', 'is_hot' => 2, 'status' => 1])->field('category_name,id')->order('sort asc')->select();
        $this->assign('firstCate', $firstCate);
        
        //二楼
        $this->_ad(40, 'twoBrandOne');                 //二楼品牌1
        $this->_ad(43, 'twoBrandTwo');                 //二楼品牌2
        $this->_ad(55, 'twoBrandAds');                 //二楼品牌广告
        $this->_ad(61, 'twoFeaturedOne');                 //二楼精选热卖1
        $this->_ad(68, 'twoFeaturedTwo');                 //二楼精选热卖2
        $this->_ad(75, 'twoFeaturedThree');                 //二楼精选热卖3
        $this->_ad(82, 'twoLikeOne');                 //二楼猜你喜欢1
        $this->_ad(95, 'twoLikeTwo');                 //二楼猜你喜欢2
        $this->_ad(33, 'twoLikeThree');                 //二楼猜你喜欢3
        //$this->_ad(100, 'twoLikeBrandOne');                 //二楼猜你喜欢品牌1
        //$this->_ad(107, 'twoLikeBrandTwo');                 //二楼猜你喜欢品牌2
        //$this->_ad(114, 'twoLikeBrandThree');                 //二楼猜你喜欢品牌ads
        $this->_ad(121, 'twoBottmAds');                //二楼下方广告位
        $twoCate  =   $model->cache(true)->where(['sid' => '100841633', 'is_hot' => 2, 'status' => 1])->field('category_name,id')->order('sort asc')->select();
        $this->assign('twoCate', $twoCate);
        
        //三楼
        $this->_ad(41, 'threeBrandOne');                 //二楼品牌1
        $this->_ad(44, 'threeBrandTwo');                 //二楼品牌2
        $this->_ad(56, 'threeBrandAds');                 //二楼品牌广告
        $this->_ad(62, 'threeFeaturedOne');                 //二楼精选热卖1
        $this->_ad(69, 'threeFeaturedTwo');                 //二楼精选热卖2
        $this->_ad(76, 'threeFeaturedThree');                 //二楼精选热卖3
        $this->_ad(83, 'threeLikeOne');                 //二楼猜你喜欢1
        $this->_ad(94, 'threeLikeTwo');                 //二楼猜你喜欢2
        $this->_ad(34, 'threeLikeThree');                 //二楼猜你喜欢3
        //$this->_ad(101, 'threeLikeBrandOne');                 //二楼猜你喜欢品牌1
        //$this->_ad(108, 'threeLikeBrandTwo');                 //二楼猜你喜欢品牌2
        //$this->_ad(115, 'threeLikeBrandThree');                 //二楼猜你喜欢品牌ads
        $this->_ad(122, 'threeBottmAds');                //二楼下方广告位
        $threeCate  =   $model->cache(true)->where(['sid' => '100845564', 'is_hot' => 2, 'status' => 1])->field('category_name,id')->order('sort asc')->select();
        $this->assign('threeCate', $threeCate);
        
        //四楼
        $this->_ad(42, 'fourBrandOne');                 //二楼品牌1
        $this->_ad(49, 'fourBrandTwo');                 //二楼品牌2
        $this->_ad(57, 'fourBrandAds');                 //二楼品牌广告
        $this->_ad(63, 'fourFeaturedOne');                 //二楼精选热卖1
        $this->_ad(70, 'fourFeaturedTwo');                 //二楼精选热卖2
        $this->_ad(77, 'fourFeaturedThree');                 //二楼精选热卖3
        $this->_ad(84, 'fourLikeOne');                 //二楼猜你喜欢1
        $this->_ad(93, 'fourLikeTwo');                 //二楼猜你喜欢2
        $this->_ad(35, 'fourLikeThree');                 //二楼猜你喜欢3
        //$this->_ad(102, 'fourLikeBrandOne');                 //二楼猜你喜欢品牌1
        //$this->_ad(109, 'fourLikeBrandTwo');                 //二楼猜你喜欢品牌2
        //$this->_ad(116, 'fourLikeBrandThree');                 //二楼猜你喜欢品牌ads
        $this->_ad(123, 'fourBottmAds');                //二楼下方广告位
        $fourCate  =   $model->cache(true)->where(['sid' => '100845554', 'is_hot' => 2, 'status' => 1])->field('category_name,id')->order('sort asc')->select();
        $this->assign('fourCate', $fourCate);
        
        //五楼
        $this->_ad(45, 'fiveBrandOne');                 //二楼品牌1
        $this->_ad(50, 'fiveBrandTwo');                 //二楼品牌2
        $this->_ad(58, 'fiveBrandAds');                 //二楼品牌广告
        $this->_ad(64, 'fiveFeaturedOne');                 //二楼精选热卖1
        $this->_ad(71, 'fiveFeaturedTwo');                 //二楼精选热卖2
        $this->_ad(78, 'fiveFeaturedThree');                 //二楼精选热卖3
        $this->_ad(85, 'fiveLikeOne');                 //二楼猜你喜欢1
        $this->_ad(92, 'fiveLikeTwo');                 //二楼猜你喜欢2
        $this->_ad(36, 'fiveLikeThree');                 //二楼猜你喜欢3
        //$this->_ad(103, 'fiveLikeBrandOne');                 //二楼猜你喜欢品牌1
        //$this->_ad(110, 'fiveLikeBrandTwo');                 //二楼猜你喜欢品牌2
        //$this->_ad(117, 'fiveLikeBrandThree');                 //二楼猜你喜欢品牌ads
        $this->_ad(124, 'fiveBottmAds');                //二楼下方广告位
        $fiveCate  =   $model->cache(true)->where(['sid' => '100841624', 'is_hot' => 2, 'status' => 1])->field('category_name,id')->order('sort asc')->select();
        $this->assign('fiveCate', $fiveCate);
        
        //六楼
        $this->_ad(46, 'sixBrandOne');                 //二楼品牌1
        $this->_ad(51, 'sixBrandTwo');                 //二楼品牌2
        $this->_ad(54, 'sixBrandAds');                 //二楼品牌广告
        $this->_ad(65, 'sixFeaturedOne');                 //二楼精选热卖1
        $this->_ad(72, 'sixFeaturedTwo');                 //二楼精选热卖2
        $this->_ad(79, 'sixFeaturedThree');                 //二楼精选热卖3
        $this->_ad(86, 'sixLikeOne');                 //二楼猜你喜欢1
        $this->_ad(91, 'sixLikeTwo');                 //二楼猜你喜欢2
        $this->_ad(37, 'sixLikeThree');                 //二楼猜你喜欢3
        //$this->_ad(104, 'sixLikeBrandOne');                 //二楼猜你喜欢品牌1
        //$this->_ad(111, 'sixLikeBrandTwo');                 //二楼猜你喜欢品牌2
        //$this->_ad(118, 'sixLikeBrandThree');                 //二楼猜你喜欢品牌ads
        $this->_ad(125, 'sixBottmAds');                //二楼下方广告位
        $sixCate  =   $model->cache(true)->where(['sid' => '100841645', 'is_hot' => 2, 'status' => 1])->field('category_name,id')->order('sort asc')->select();
        $this->assign('sixCate', $sixCate);
        
        //七楼
        $this->_ad(47, 'sevenBrandOne');                 //二楼品牌1
        $this->_ad(52, 'sevenBrandTwo');                 //二楼品牌2
        $this->_ad(59, 'sevenBrandAds');                 //二楼品牌广告
        $this->_ad(66, 'sevenFeaturedOne');                 //二楼精选热卖1
        $this->_ad(73, 'sevenFeaturedTwo');                 //二楼精选热卖2
        $this->_ad(80, 'sevenFeaturedThree');                 //二楼精选热卖3
        $this->_ad(87, 'sevenLikeOne');                 //二楼猜你喜欢1
        $this->_ad(90, 'sevenLikeTwo');                 //二楼猜你喜欢2
        $this->_ad(38, 'sevenLikeThree');                 //二楼猜你喜欢3
        //$this->_ad(105, 'sevenLikeBrandOne');                 //二楼猜你喜欢品牌1
        //$this->_ad(112, 'sevenLikeBrandTwo');                 //二楼猜你喜欢品牌2
        //$this->_ad(119, 'sevenLikeBrandThree');                 //二楼猜你喜欢品牌ads
        $this->_ad(126, 'sevenBottmAds');                //二楼下方广告位
        $sevenCate  =   $model->cache(true)->where(['sid' => '100845562', 'is_hot' => 2, 'status' => 1])->field('category_name,id')->order('sort asc')->select();
        $this->assign('sevenCate', $sevenCate);
        
        //八楼
        $this->_ad(48, 'eightBrandOne');                 //二楼品牌1
        $this->_ad(53, 'eightBrandTwo');                 //二楼品牌2
        $this->_ad(60, 'eightBrandAds');                 //二楼品牌广告
        $this->_ad(67, 'eightFeaturedOne');                 //二楼精选热卖1
        $this->_ad(74, 'eightFeaturedTwo');                 //二楼精选热卖2
        $this->_ad(81, 'eightFeaturedThree');                 //二楼精选热卖3
        $this->_ad(88, 'eightLikeOne');                 //二楼猜你喜欢1
        $this->_ad(89, 'eightLikeTwo');                 //二楼猜你喜欢2
        $this->_ad(39, 'eightLikeThree');                 //二楼猜你喜欢3
        //$this->_ad(106, 'eightLikeBrandOne');                 //二楼猜你喜欢品牌1
        //$this->_ad(113, 'eightLikeBrandTwo');                 //二楼猜你喜欢品牌2
        //$this->_ad(120, 'eightLikeBrandThree');                 //二楼猜你喜欢品牌ads
        $this->_ad(127, 'eightBottmAds');                //二楼下方广告位
        $eightCate  =   $model->cache(true)->where(['sid' => '100841648', 'is_hot' => 2, 'status' => 1])->field('category_name,id')->order('sort asc')->select();
        $this->assign('eightCate', $eightCate);
        
       
        # print_r($this->_data);exit;

        $e  =   microtime(true);

        //各楼层猜你喜欢
        $love_cfg = [
        	'clothe' 		=> 100841621,	//服装
        	'food' 			=> 100841633,	//食品
        	'makeup' 		=> 100845564,	//美妆100845564
        	'jewellery' 	=> 100845554,	//珠宝
        	'bag' 			=> 100841624,	//鞋包
        	'baby' 			=> 100841645,	//母婴
        	'digital' 		=> 100845562,	//数码
        	'house' 		=> 100841648	//家居
        ];
        
        //$cateids = '';
//         foreach ($cate as $k => $v) {
//             foreach ($v as $val) {
//                 //$cateids .= $val['id'] . ',';
//                 $this->api('/Goods/love_goods',['is_love' => 1,'imgsize' => 163,'limit' => 10,'category_id' => $val['id']],'is_love,imgsize,category_id,limit');
//                 $love_goods[$val['id']] = $this->_data['data'];
//             }
//         }
        //dump($love_goods);
//         if ($cateids) {
//             $this->api('/Goods/love_goods',['is_love' => 1,'imgsize' => 140,'limit' => 12,'category_id' => trim($cateids, ',')],'is_love,imgsize,category_id,limit');
//         }
//         dump($this->_data);
        foreach($love_cfg as $key => $val){
        	$this->api('/Goods/love_goods',['is_love' => 1,'imgsize' => 140,'limit' => 10,'category_id' => $val],'is_love,imgsize,category_id,limit');
        	$love_goods[$key] = $this->_data['data'];
        }
        # 获取广告
        $ids = array_keys($this->_ids);
        $this->api('/Ad/ads',['position_id'=>implode(',', $ids)]);
        $this->_adVar($this->_data['data']);
        $this->assign('love_goods',$love_goods);
        //dump($love_goods);
        $this->assign('_ads', $ids);
        $this->display('index');
    }
    
    
    public function index() {
        
        $model  =   M('goods_category');
        
        
        $this->_ad(134, 'mainTop'); //内容部分顶部广告
        
        $this->_ad(19, 'slider');                 //slider
        
        
        $this->_ad(135, 'newOne');  //乐兑新品1
        $this->_ad(136, 'newTwo');  //乐兑新品2
        
        
        $this->_ad(137, 'brandOne');   //品牌1
        $this->_ad(138, 'brandTwo');   //品牌2
        
        
        $this->_ad(139, 'fineOne');    //  精品1
        $this->_ad(141, 'fineTwo');    //  精品2
        $this->_ad(143, 'fineThree');    //  精品3
        $this->_ad(144, 'fineFour');    //  精品4
        
        
        $this->_ad(145, 'floorOne1');   //1楼-1
        $this->_ad(146, 'floorOne2');   //1楼-2
        $this->_ad(147, 'floorOne3');   //1楼-3
        $this->_ad(148, 'floorOne4');   //1楼-4
        $this->_ad(149, 'floorOneHot');   //1楼-热搜
        $cate[0]  =   $model->where(['sid' => ['in', '100845564,100841648'], 'is_hot' => 2, 'status' => 1])->field('category_name,id')->order('sort asc')->select();
        $this->assign('firstCate', $cate[0]);
        
        
        $cate[1] = $model->where(['sid' => ['in', '100841621,100841624,100845554'], 'is_hot' => 2, 'status' => 1])->field('category_name,id')->order('sort asc')->select();
        $this->assign('twoCate', $cate[1]);
        $cate[2] = $model->where(['sid' => ['in', '100845890,100841651'], 'is_hot' => 2, 'status' => 1])->field('category_name,id')->order('sort asc')->select();
        $this->assign('threeCate', $cate[2]);
        $cate[3] = $model->where(['sid' => ['in', '100841645,100841633'], 'is_hot' => 2, 'status' => 1])->field('category_name,id')->order('sort asc')->select();
        $this->assign('fourCate', $cate[3]);
        $cate[4] = $model->where(['sid' => ['in', '100845562,100845567'], 'is_hot' => 2, 'status' => 1])->field('category_name,id')->order('sort asc')->select();
        $this->assign('fiveCate', $cate[4]);
        
        
        $this->_ad(154, 'floorTwo1');   //1楼-1
        $this->_ad(153, 'floorTwo2');   //1楼-2
        $this->_ad(152, 'floorTwo3');   //1楼-3
        $this->_ad(151, 'floorTwo4');   //1楼-4
        $this->_ad(150, 'floorTwoHot');   //1楼-热搜
        
        $this->_ad(164, 'floorThree1');   //1楼-1
        $this->_ad(163, 'floorThree2');   //1楼-2
        $this->_ad(162, 'floorThree3');   //1楼-3
        $this->_ad(161, 'floorThree4');   //1楼-4
        $this->_ad(160, 'floorThreeHot');   //1楼-热搜
        
        $this->_ad(169, 'floorFour1');   //1楼-1
        $this->_ad(168, 'floorFour2');   //1楼-2
        $this->_ad(167, 'floorFour3');   //1楼-3
        $this->_ad(166, 'floorFour4');   //1楼-4
        $this->_ad(165, 'floorFourHot');   //1楼-热搜
        
        $this->_ad(174, 'floorFive1');   //1楼-1
        $this->_ad(173, 'floorFive2');   //1楼-2
        $this->_ad(172, 'floorFive3');   //1楼-3
        $this->_ad(171, 'floorFive4');   //1楼-4
        $this->_ad(170, 'floorFiveHot');   //1楼-热搜
        
        
        
        foreach ($cate as $k => $v) {
            foreach ($v as $val) {
                //$cateids .= $val['id'] . ',';
                $this->api('/Goods/love_goods',['is_love' => 1,'imgsize' => 163,'limit' => 12,'category_id' => $val['id']],'is_love,imgsize,category_id,limit');
                $love_goods[$val['id']] = $this->_data['data'];
            }
        }
        
        $ids = array_keys($this->_ids);
        $this->api('/Ad/ads',['position_id'=>implode(',', $ids)]);
        $this->_adVar($this->_data['data']);
        
        $this->assign('love_goods',$love_goods);
        //dump($love_goods);
        
        $this->assign('_ads', $ids);
        $this->display('test');
    }
    
    
    public function diy() {
        if (session('admin') == false) {
            exit;
        }
        $model  =   M('goods_category');
        
        
        $this->_ad(134, 'mainTop'); //内容部分顶部广告
        
        $this->_ad(19, 'slider');                 //slider
        
        
        $this->_ad(135, 'newOne');  //乐兑新品1
        $this->_ad(136, 'newTwo');  //乐兑新品2
        
        
        $this->_ad(137, 'brandOne');   //品牌1
        $this->_ad(138, 'brandTwo');   //品牌2
        
        
        $this->_ad(139, 'fineOne');    //  精品1
        $this->_ad(141, 'fineTwo');    //  精品2
        $this->_ad(143, 'fineThree');    //  精品3
        $this->_ad(144, 'fineFour');    //  精品4
        
        
        $this->_ad(145, 'floorOne1');   //1楼-1
        $this->_ad(146, 'floorOne2');   //1楼-2
        $this->_ad(147, 'floorOne3');   //1楼-3
        $this->_ad(148, 'floorOne4');   //1楼-4
        $this->_ad(149, 'floorOneHot');   //1楼-热搜
        $cate[0]  =   $model->where(['sid' => ['in', '100845564,100841648'], 'is_hot' => 2, 'status' => 1])->field('category_name,id')->order('sort asc')->select();
        $this->assign('firstCate', $cate[0]);
        
        
        $cate[1] = $model->where(['sid' => ['in', '100841621,100841624,100845554'], 'is_hot' => 2, 'status' => 1])->field('category_name,id')->order('sort asc')->select();
        $this->assign('twoCate', $cate[1]);
        $cate[2] = $model->where(['sid' => ['in', '100845890,100841651'], 'is_hot' => 2, 'status' => 1])->field('category_name,id')->order('sort asc')->select();
        $this->assign('threeCate', $cate[2]);
        $cate[3] = $model->where(['sid' => ['in', '100841645,100841633'], 'is_hot' => 2, 'status' => 1])->field('category_name,id')->order('sort asc')->select();
        $this->assign('fourCate', $cate[3]);
        $cate[4] = $model->where(['sid' => ['in', '100845562,100845567'], 'is_hot' => 2, 'status' => 1])->field('category_name,id')->order('sort asc')->select();
        $this->assign('fiveCate', $cate[4]);
        
        
        $this->_ad(154, 'floorTwo1');   //1楼-1
        $this->_ad(153, 'floorTwo2');   //1楼-2
        $this->_ad(152, 'floorTwo3');   //1楼-3
        $this->_ad(151, 'floorTwo4');   //1楼-4
        $this->_ad(150, 'floorTwoHot');   //1楼-热搜
        
        $this->_ad(164, 'floorThree1');   //1楼-1
        $this->_ad(163, 'floorThree2');   //1楼-2
        $this->_ad(162, 'floorThree3');   //1楼-3
        $this->_ad(161, 'floorThree4');   //1楼-4
        $this->_ad(160, 'floorThreeHot');   //1楼-热搜
        
        $this->_ad(169, 'floorFour1');   //1楼-1
        $this->_ad(168, 'floorFour2');   //1楼-2
        $this->_ad(167, 'floorFour3');   //1楼-3
        $this->_ad(166, 'floorFour4');   //1楼-4
        $this->_ad(165, 'floorFourHot');   //1楼-热搜
        
        $this->_ad(174, 'floorFive1');   //1楼-1
        $this->_ad(173, 'floorFive2');   //1楼-2
        $this->_ad(172, 'floorFive3');   //1楼-3
        $this->_ad(171, 'floorFive4');   //1楼-4
        $this->_ad(170, 'floorFiveHot');   //1楼-热搜
        
        
        
        foreach ($cate as $k => $v) {
            foreach ($v as $val) {
                //$cateids .= $val['id'] . ',';
                $this->api('/Goods/love_goods',['is_love' => 1,'imgsize' => 163,'limit' => 12,'category_id' => $val['id']],'is_love,imgsize,category_id,limit');
                $love_goods[$val['id']] = $this->_data['data'];
            }
        }
        
        $ids = array_keys($this->_ids);
        $this->api('/Ad/ads',['position_id'=>implode(',', $ids)]);
        $this->_adVar($this->_data['data']);
        
        $this->assign('love_goods',$love_goods);
        //dump($love_goods);
        //$this->assign('_ads', $ids);
        $this->display();
    }
    
    /**
     * api 1 = ad ,2 = ads
     */
    private function ads() {
        $data   =   [
            'slider'            =>  ['api' => 1, 'id' => 19], //slider
            'recommend'         =>  ['api' => 1, 'id' => 21], //推荐
            'featured'          =>  ['api' => 2, 'id' => '22,23'],  //乐兑精选
            'firstBrandOne'     =>  ['api' => 1, 'id' => 24],   //一楼品牌1
            'firstBrandTwo'     =>  ['api' => 1, 'id' => 25],   //一楼品牌2
            'firstBrandAds'     =>  ['api' => 1, 'id' => 26],   //一楼品牌广告
            'firstFeaturedOne'  =>  ['api' => 1, 'id' => 27],   //一楼精选热卖1
            'firstFeaturedTwo'  =>  ['api' => 1, 'id' => 28],   //一楼精选热卖2
            'firstFeaturedThree'=>  ['api' => 1, 'id' => 29],   //一楼精选热卖3
            'firstLikeOne'      =>  ['api' => 1, 'id' => 30],   //一楼猜你喜欢1
            'firstLikeTwo'      =>  ['api' => 1, 'id' => 31],   //一楼猜你喜欢2
            'firstLikeThree'    =>  ['api' => 1, 'id' => 32],   //一楼猜你喜欢3
        ];
        return $data;
    }
    
    /**
     * 获取某个广告位图片
     * @param int $position_id 广告位ID
     */
    private function _ad($position_id, $var){
        $this->_ids[$position_id] = $var;
    }

    /**
     * 广告赋值
     */
    public function _adVar($apiData){
        $_ids = $this->_ids;
        foreach ($apiData as $key => $value) {
            $this->assign($_ids[$key], $value['data']);
        }
    }


    /**
     * seo
     */
    public function seoSet(){
        # 需要登录了后台才开启seo设置
        $s = session('admin');
        isset($s['id']) or die();
        
        $url = explode(',', I('url'));
        array_walk_recursive($url, function(&$value, $key){
            $value = strtolower($value);
        });
        $where = array(
            'modules'       => $url[0],
            'controller'    => $url[1],
            'action'        => $url[2],
        );
        $one = M('seo')->where($where)->find();
        # 是否有参数设置
        if(! empty($one['params'])){
            $params = json_decode(html_entity_decode($one['params']),true);
            $params_ = array();
            foreach ($params as $ko => $vo) {
                $params_[] = array(
                    'key' => $vo['key'],
                    'val' => $vo['val'],
                );
            }
            $one['params'] = $params_;
        }
        
        $this->assign('one', $one);
        $this->assign('paramLength', $one['params'] ? count($one['params']) : 0 );
        $this->assign('url',$url);
        $this->display('seo');
        
    }

    /**
     * 保存seo设置
     */
    public function seoSave(){
        IS_POST or die();
        # 需要登录了后台才开启seo设置
        $s = session('admin');
        isset($s['id']) or die();
        # 是否有参数设置
        if(isset($_POST['paramsKey'])){
            $key = I('post.paramsKey');
            $val = I('post.paramsVal');
            $params = array();
            foreach ($key as $ko => $vo) {
                $params[] = array(
                    'key' => $vo,
                    'val' => $val[$ko],
                );
            }
            $_POST['params'] = json_encode($params);
        }

        if(isset($_POST['id'])){
            # edit
            $post = I('post.');
            $id = $post['id'];
            unset($post['id']);
            $result = M('seo')->where(['id' => $id])->data($post)->save();
        }else{
            # add
            $_POST['ip']            = get_client_ip();
            $_POST['modules']       = strtolower($_POST['modules']);
            $_POST['controller']    = strtolower($_POST['controller']);
            $_POST['action']        = strtolower($_POST['action']);
            $result = M('seo')->data(I('post.'))->add();
        }
        
        if($result === false){
            $this->ajaxReturn(['code' => 0]);
        }else{
            $this->ajaxReturn(['code' => 1]);
        }
    }


}