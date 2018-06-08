<?php

/**
 * Created by PhpStorm.
 * User: dttx
 * Date: 2017/2/7
 * Time: 9:25
 */
namespace Common\Form;
class Form
{


    /**
     *  使用例子
     *
     *
     *  $forms = Form::getInstance()->text(['name' => 'name', 'title' => '用户名', 'require' => 1, 'validate' => ['required']])
     *       ->vcode(['name' => 'vcode', 'title' => '图行验证码', 'require' => 1, 'validate' => ['required']])
     *       ->smscode(['name' => 'smscode', 'title' => '短信验证码', 'require' => 1, 'validate' => ['required']])
     *       ->dates(['name' => 'date', 'title' => '日期', 'type' => 'dates', 'require' => 1, 'validate' => ['required'], 'options' => ['a' => 1]])
     *       ->mutilImages(['name' => 'images', 'title' => '多图', 'require' => 1])
     *       ->singleImages(['name' => 'pic', 'title' => '单图', 'require' => 1])
     *       ->address(['name' => 'addr', 'title' => '收货地址', 'require' => 1, 'options' => M('shopping_address')->cache(true)->where(['uid' => session('user.id')])->select(), 'url' => DM('my') . U('/addr')])
     *       ->ueditor(['name' => 'contents', 'title' => '内容', 'require' => 1, 'validate' => ['required']])
     *       ->district(['name' => ['province' => 'province', 'city' => 'city', 'district' => 'district', 'town' => 'town'], 'title' => '地区', 'require' => 1, 'validate' => ['required']])
     *       ->submit(['title' => '提交申请'])
     *       ->create();
     *       $this->assign('forms', $forms);
     *
     * 可传参数
     *
     * $options = [
     *       'name',             //name
     *       'placeholder',      //文本框提示内容
     *       'title',            //label标题
     *       'tips',             //提示
     *       'require',          //是否必须
     *       'options',          //参数选项
     *       'validate',         //验证       参数：http://www.runoob.com/jquery/jquery-plugin-validate.html
     *       'type',             //类型
     *       'style',            //样式
     *       'class',            //样式类
     *       'attrs',            //其他属性
     *       'value',            //值
     *   ];
     */


    protected $_formOptions = [];   //表单配置

    protected $_html = '';          //html

    protected $_js = '';            //javascript

    protected $_script = '';

//    protected $_htmlObj;            //html 对象
//
//    protected $_jsObj;              //javascript 对象

    protected $_uploadJs = false;   //是否引入上传文件javascript

    protected $_datetimeJs = false; //是否引入时间javascript

    protected $_checkboxCss = false;    //checkboxCss

    public static $_instance;   //单例

    /**
     * Form constructor.
     * @param array $formOptions
     *      name        表单名称        默认为自动生成
     *      method      提交类型        默认为post
     *      action      提交地址        默认为当前地址
     *      headers     header          默认为当前action
     *      ajax        是否为ajax提交   默认为ajax提交
     *      gourl       ajax提交成功后跳转的地址 默认为刷新当前页面
     */
    function __construct($formOptions = null)
    {
        if ($formOptions) $this->_formOptions = $formOptions;
        if (!isset($this->_formOptions['name'])) $this->_formOptions['name'] = 'form-' . NOW_TIME . rand(10000,99999);   //表单name
        if (!isset($this->_formOptions['method'])) $this->_formOptions['method'] = 'post';
        if (!isset($this->_formOptions['action'])) $this->_formOptions['action'] = __SELF__;
        if (!isset($this->_formOptions['headers'])) $this->_formOptions['headers'] = enCryptRestUri(__ACTION__);
        if (!isset($this->_formOptions['ajax'])) $this->_formOptions['ajax'] = true;
//        if (!isset($this->_formOptions['htmlObj'])) $this->_htmlObj = 'Html';
//        if (!isset($this->_formOptions['jsObj'])) $this->_jsObj = 'Js';
    }


    /**
     * 实例
     *
     * @param null $formOptions
     * @return Form
     */
    public static function getInstance($formOptions = null) {
//        if (self::$_instance instanceof self == false) self::$_instance = new self($formOptions);
//        return self::$_instance;
        return new self($formOptions);
    }


    //input文本框

    /**
     * 单行文本框
     *
     * @param $options
     * @return $this
     */
    public function text($options) {
        $options['type'] = 'text';
        return $this->input($options);
    }

    /**
     * 密码
     *
     * @param $options
     * @return $this
     */
    public function password($options) {
        $options['type'] = 'password';
        return $this->input($options);
    }

    /**
     * 隐藏文本域
     *
     * @param $options
     * @return $this
     */
    public function hidden($options) {
        $options['type'] = 'hidden';
        return $this->input($options);
    }

    /**
     * 数字文本域
     *
     * @param $options
     * @return $this
     */
    public function number($options) {
        $options['type'] = 'number';
        return $this->input($options);
    }


    /**
     * 多行文本域
     *
     * @param $options
     * @return $this
     */
    public function textarea($options) {
        $options['type'] = 'textarea';
        return $this->input($options);
    }

    /**
     * 图像验证码
     *
     * @param $options
     * @return $this
     */
    public function vcode($options) {
        $options['type'] = 'vcode';
        return $this->input($options);
    }

    /**
     * 短信验证码
     *
     * @param $options
     * @return $this
     */
    public function smscode($options) {
        $options['type'] = 'smscode';
        return $this->input($options);
    }

    /**
     * 文本框
     *
     * @param $options
     * @return $this
     */
    public function input($options) {
        $this->_html .= Html::getInstance($options)->formInput();
        return $this;
    }

    //选择

    /**
     * 下拉菜单
     *
     * @param $options
     * @return $this
     */
    public function select($options) {
        $this->_html .= Html::getInstance($options)->formSelect();
        return $this;
    }

    /**
     * 多项选择
     *
     * @param $options
     * @return $this
     */
    public function checkbox($options) {
        $this->_checkboxCss = true;
        $options['type'] = 'checkbox';
        return $this->select($options);
    }


    /**
     * 单项选择
     *
     * @param $options
     * @return $this
     */
    public function radio($options) {
        $this->_checkboxCss = true;
        $options['type'] = 'radio';
        return $this->select($options);
    }

    /**
     * 地区选择
     *
     * @param $options
     * @return $this
     */
    public function district($options) {
        $this->_html .= Html::getInstance($options)->formDistrict();
        return $this;
    }


    /**
     * 地址选择
     *
     * @param $options
     * @return $this
     */
    public function address($options) {
        $this->_html .= Html::getInstance($options)->formAddress();
        return $this;
    }


    //文件上传

    /**
     * 单图上传
     *
     * @param $options
     * @return $this
     */
    public function singleImages($options) {
        //if (isset($options['require'])) $this->_script .= Js::required($options);    //加入script判断
        $options['type'] = 'single';
        return $this->files($options);
    }


    /**
     * 多图上传
     *
     * @param $options
     * @return $this
     */
    public function mutilImages($options) {
        //if (isset($options['require'])) $this->_script .= Js::required($options);    //加入script判断
        $options['type'] = 'mutil';
        return $this->files($options);
    }


    /**
     * 文件上传
     *
     * @param $options
     * @return $this
     */
    public function files($options) {
        if (isset($options['require'])) $this->_script .= Js::required($options);    //加入script判断
        $this->_uploadJs = true;    //引入文件上传javascript代码
        $this->_js .= Js::uploadJsFun($options);   //文件上传js函数
        $this->_html .= Html::getInstance($options)->formUpload();
        return $this;
    }

    //时间日期


    /**
     * 日期
     *
     * @param $options
     * @return $this
     */
    public function dates($options) {
        $this->_datetimeJs = true;  //引入时间选择javascript代码
        $options['options']['name'] = $options['name'];
        $this->_js .= Js::dateJsFun($options['options']);
        $this->_html .= Html::getInstance($options)->formDate();
        return $this;
    }

    /**
     * 时间
     *
     * @param $options
     * @return $this
     */
    public function datetime($options) {
        $options['type'] = 'datetime';
        return $this->dates($options);
    }

    /**
     * 时间搜索 xxx-xxx
     *
     * @param $options
     * @return $this
     */
    public function searchDay($options) {
        $options['type'] = 'sday';
        return $this->dates($options);
    }

    //按钮

    /**
     * 普通按钮
     *
     * @param $options
     * @return $this
     */
    public function button($options) {
        $this->_html .= Html::getInstance($options)->formButton();
        return $this;
    }

    /**
     * 提交按钮
     *
     * @param $options
     * @return $this
     */
    public function submit($options) {
        $options['type'] = 'submit';
        return $this->button($options);
    }

    /**
     * 重置按钮
     *
     * @param $options
     * @return $this
     */
    public function reset($options) {
        $options['type'] = 'reset';
        return $this->button($options);
    }

    //其他

    public function ueditor($options) {
        $this->_html .= Html::getInstance($options)->formUeditor();
        return $this;
    }


    /**
     * 商品选择
     *
     * @param $options
     * @return $this
     */
    public function goods($options) {
        $this->_html .= Html::getInstance($options)->formGoods();
        return $this;
    }

    /**
     * 评价评分
     *
     * @param $options
     * @return $this
     */
    public function rate($options) {
        $this->_html .= Html::getInstance($options)->formRate();
        return $this;
    }

    /**
     * 模态框
     *
     * @param $optionx
     */
    public function modal($options) {
        if (isset($options['require'])) $this->_script .= Js::required($options);    //加入script判断
        $this->_html .= Html::getInstance($options)->formModal();
        return $this;
    }


    /**
     *
     *
     * @param $options
     *
     */
    public function htmls($options) {

    }


    /**
     * 多选分类
     *
     * @param $options
     * @return $this
     */
    public function categoryCheckbox($options) {
        $this->_checkboxCss = true; //加载checkbox css样式
        if (!isset($options['correspond'])) {
            $options['correspond']['id']    = 'id';
            $options['correspond']['name']  = 'name';
            $options['correspond']['child'] = 'child';
        }
        $this->_html .= Html::getInstance($options)->categoryCheckbox();
        return $this;
    }


    /**
     * 单选分类
     *
     * @param $options
     * @return $this
     */
    public function categorySelect($options) {
        return $this;
    }

    /**
     * 判断后执行
     *
     * @param $bool
     * @param $options
     * @return mixed
     */
    public function callback($bool, $options) {
        if ($bool == true) {
            if (!isset($options['callback'])) $options['callback'] = 'text';
            return $this->{$options['callback']}($options);
        }
        return $this;
    }


    /**
     * 商品参数
     *
     * @param $options
     * @return $this
     */
    public function goodsParams($options, $value = null) {
        if (!empty($options) && is_array($options)) {
            foreach ($options as $k => $v) {
                $option = [];
                switch ($v['type']) {
                    case 1:
                        $callback = 'select';
                        break;
                    case 2:
                        $callback = 'checkbox';
                        break;
                    default:
                        $callback = 'text';
                }
                $option['title'] = $v['param_name'];


                //赋值
                if ($value) {
                    foreach ($value as $val) {
                        if ($v['id'] == $val['option_id']) {
                            $option['value'] = $val['param_value'];
                            $option['name'] = 'param['. $v['id'] . '_'  . $val['id'] . ']';
                            break;
                        }
                    }
                }


                if (!array_key_exists('name', $option)) $option['name'] = 'param['. $v['id'] . ']';


                if ($v['is_need'] == 1) {
                    $option['require'] = 1;
                    $option['validate'] = ['required'];
                }
                if (isset($v['options'])) {
                    $optionsTmp = explode(',', $v['options']);
                    $option['options'] = array_combine($optionsTmp, $optionsTmp);
                }
                unset($optionsTmp);
                $this->{$callback}($option);
            }
        }
        return $this;
    }

    public function shopAuthFunctions($options)
    {
        $this->_checkboxCss = true;
        $this->_html .= Html::getInstance($options)->formShopAuthFunctions();
        return $this;
    }

    /**
     * 商品属性
     *
     * @param $options
     * @return $this
     */
    public function goodsAttr($options) {
        $this->_html .= Html::getInstance($options)->formGoodsAttr();
        return $this;
    }


    /**
     * 商品搭配
     *
     * @param $options
     * @return $this
     */
    public function goodsCollocation($options) {
        $this->_html .= Html::getInstance($options)->formGoodsCollocation();
        return $this;
    }


    /**
     * subject: 参与游戏优惠券面值选择
     * api: luckdrawSelect
     * author: Mercury
     * day: 2017-05-13 14:00
     * [字段名,类型,是否必传,说明]
     * @param $options
     * @return $this
     */
    public function luckdrawSelect($options)
    {
        $this->_html .= Html::getInstance($options)->formLuckdrawSelect();
        return $this;
    }



    /**
     * 创建表单
     *
     * @return mixed
     */
    public function create() {
        $data['html']   = str_replace('{body}', $this->_html, Html::getInstance($this->_formOptions)->formHtml());
        $jsBefore = '';
        if ($this->_datetimeJs === true) $jsBefore .= Js::dateJs();
        if ($this->_uploadJs === true) $jsBefore .= Js::uploadJs();
        if ($this->_checkboxCss) $jsBefore .= Js::checkboxCss();
        if ($this->_script) $this->_formOptions['script'] = $this->_script;
        $data['js']     = Js::create($this->_js . ($this->_formOptions['ajax'] == true ? Js::ajaxSubmit($this->_formOptions) : Js::submit($this->_formOptions)), $jsBefore);
        if ($jsBefore) $jsBefore = null;
        $this->free();
        return $data;
    }

    /**
     * 回收变量
     */
    protected function free() {
        if ($this->_formOptions) $this->_formOptions = null;
        if ($this->_html) $this->_html = null;
        if ($this->_js) $this->_js = null;
    }

    function __destruct()
    {
        $this->free();
        // TODO: Implement __destruct() method.
    }
}