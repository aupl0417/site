<?php
/**
 * Created by PhpStorm.
 * User: dttx
 * Date: 2017/2/7
 * Time: 9:26
 */

namespace Common\Form;


class Html
{

    protected $_options = [];

    public function __construct($options = null)
    {
        if ($options) $this->_options = $options;
    }


    /**
     * 实例
     *
     * @param null $options
     * @return Html
     */
    public static function getInstance($options = null) {
        return new self($options);
    }


    public function formStart($options) {

    }

    public function formEnd($options) {

    }

    public function before() {

    }

    public function after() {

    }

    /**
     * form表单
     *
     * @param $options
     * @return string
     */
    public function formHtml() {
        return sprintf('<form method="%s" action="%s" name="%s" id="%s" novalidate="novalidate" class="%s" style="%s" %s>{body}</form>', $this->_options['method'], $this->_options['action'], $this->_options['name'], $this->_options['name'], $this->_options['class'], $this->_options['style'], $this->_options['attrs']);
    }

    /**
     * label
     *
     * @param $options
     * @return string
     */
    public function label() {
        return $this->_options['title'] ? sprintf('<label class="control-label label-block" for="%s">%s %s</label>', is_array($this->_options['name']) ? current($this->_options['name']) : $this->_options['name'], $this->_options['title'], ($this->_options['require'] ? '<span class="text_red">*</span>' : '')) : '';
    }

    /**
     * 表单主体信息
     *
     * @param $html
     * @return string
     */
    public function formGroup($html) {
        return '<div class="form-group">'.$html. ($this->_options['tips'] ? $this->formTips($this->_options['tips']) : '') . '</div>';
    }

    /**
     * 文本框
     *
     * @param $options
     * @return string
     */
    public function formInput() {
        $html           = $this->label();
        $type           = $this->_options['type'] ? : 'text';
        $placeholder    = $this->_options['placeholder'] ? : '请输入' . $this->_options['title'];
        $validate       = $this->_options['validate'] ? Validate::getInstance($this->_options['validate'])->create() : '';
        $attrs          = $this->_options['attrs'];

        if ($type == 'textarea') {
            $html .= sprintf('<textarea name="%s" id="%s" placeholder="%s" class="form-control %s" style="%s" %s %s>'.$this->_options['value'].'</textarea>', $this->_options['name'], $this->_options['name'], $placeholder, $this->_options['class'], $this->_options['style'], $validate, $attrs);
        } else {
            if ($type == 'password') $attrs .= ' autocomplete="new-password"';  //禁止密码自动填充
            $input = sprintf('<input type="%s" name="%s" id="%s" value="%s" placeholder="%s" class="form-control %s" style="%s" %s %s>', $type, $this->_options['name'], $this->_options['name'], $this->_options['value'], $placeholder, $this->_options['class'], $this->_options['style'], $validate, $attrs);
            switch ($type) {
                case 'vcode':
                    $html .= self::col(2, [$input, self::graphicsCode()]);
                    break;
                case 'smscode':
                    $html .= self::col(2, [$input, self::smscodeButton()]);
                    break;
                default:
                    $html .= $input;
            }
            unset($input);
        }
        return $this->formGroup($html);
    }


    /**
     * 时间
     *
     * @return string
     */
    public function formDate() {
        $html = $this->label();
        switch ($this->_options['type']) {
            case 'datetime':
                $html .= '<div class="input-group date '.$this->_options['name'].'" data-date-format="yyyy-mm-dd hh:ii">
                            <input name="'.$this->_options['name'].'" class="form-control form-filter" readonly="false" id="'.$this->_options['name'].'" type="text" value="'.$this->_options['value'].'" style="'.$this->_options['style']. '" ' . ($this->_options['validate'] ? Validate::getInstance($this->_options['validate'])->create() : '') . ' ' . $this->_options['attrs'] . '>
                            <span class="input-group-addon btn default">
                                <span class="glyphicon glyphicon-th fa fa-calendar"></span>
                            </span>
                        </div>';
                break;
            case 'sday':
                if (is_array($this->_options['name'])) {
                    foreach ($this->_options['name'] as $k => $v) {
                        $html .= '<div style="width:40%;" class="input-group date datetime mr10 searchDate" data-min-view="2" data-date-format="yyyy-mm-dd">
                                    <span class="input-group-addon date">'.($k == 0 ? '从' : '到').'</span> <input name="'.$v.'" data-filter="'.($k == 0 ? 'gte' : 'lte').'" class="form-control form-filter" readonly="" id="'.$v.$k.'" type="text">
                                        <span class="input-group-addon btn default"><span class="glyphicon glyphicon-th fa fa-calendar"></span>
                                    </span>
                                </div>';
                    }
                }
                break;
            default :
                $html .= '<div class="input-group date datetime '.$this->_options['name'].'" data-min-view="2" data-date-format="yyyy-mm-dd">
                            <input name="'.$this->_options['name'].'" data-filter="gte" class="form-control form-filter" readonly="" id="'.$this->_options['name'].'" type="text" value="'.$this->_options['value'].'" style="'.$this->_options['style']. '" ' . ($this->_options['validate'] ? Validate::getInstance($this->_options['validate'])->create() : '') . ' ' . $this->_options['attrs'] . '>
                            <span class="input-group-addon btn default">
                                <span class="glyphicon glyphicon-th fa fa-calendar"></span>
                            </span>
                        </div>';
        }

        return $this->formGroup($html);
    }

    /**
     * 选择类型表单
     *
     * @return string
     */
    public function formSelect() {
        $html = $this->label();
        $this->_options['options'] = array_filter($this->_options['options']);
        $this->_options['element_id'] = str_replace(['[', ']'], '_', $this->_options['name']);    //去掉name里面的中括号
        switch ($this->_options['type']) {
            case 'checkbox' :
                if ($this->_options['options']) {
                    $cntOptions = count($this->_options['options']);
                    $value      = explode(',', $this->_options['value']);
                    $i          = 0;
                    $html      .= '<div class="row">%s</div>';
                    $tmpHtml    = '';
                    $cnt        = count($this->_options['options']);
                    foreach ($this->_options['options'] as $k => $v) {
                        $k = str_replace([' ', "\r\n", "\t", "\r", "\n"], '', $k);
                        $tmpHtml .= '<div class="'.($cnt > 1 ? 'col-md-2' : 'col-md-6').'"><label class="mr20" style=""><div class="icheckbox_square-red" aria-checked="false" aria-disabled="false" style="position: relative;">
                                    <input '.($this->_options['validate'] && $i == 0 ? Validate::getInstance($this->_options['validate'])->create() : '').' id="'.$this->_options['element_id'] . '_' . $i .'" '.(in_array($k, $value) ? 'checked' : '').' type="checkbox" class="i-red-square" name="'.$this->_options['name'] .($cntOptions > 1 ? '[]' : '').'" value="'.$k.'">
                                    <ins class="iCheck-helper"></ins>
                                </div> '.$v.'</label></div>';
                        $i++;
                    }
                    $html = sprintf($html, $tmpHtml);
                }
                break;
            case 'radio' :
                if ($this->_options['options']) {
                    $i = 0;
                    foreach ($this->_options['options'] as $k => $v) {
                        $k = str_replace([' ', "\r\n", "\t", "\r", "\n"], '', $k);
                        $html .= '<label class="mr20" style=""><div class="iradio_square-red" aria-checked="false" aria-disabled="false" style="position: relative;">
                                    <input '.($this->_options['validate'] && $i == 0 ? Validate::getInstance($this->_options['validate'])->create() : '').' id="' . $this->_options['element_id'] . '_' . $i . '" ' . ($k == $this->_options['value'] ? 'checked' : '') . ' type="'.$this->_options['type'].'" class="i-red-square" name="' . $this->_options['name'] . '" value="' . $k . '">
                                    <ins class="iCheck-helper"></ins></div> ' . $v . '</label>';
                        $i++;
                    }
                }
                break;
            default :
                $html .= '<select name="'.$this->_options['name'].'" id="'.$this->_options['element_id'].'" class="form-control'.$this->_options['class'].'" style="'.$this->_options['style'].'" '.$this->_options['attrs'].' '.($this->_options['validate'] ? Validate::getInstance($this->_options['validate'])->create() : '').'>';
                $html .= '<option value="">请选择' . $this->_options['title'] . '</option>';
                if (is_array($this->_options['options'])) {
                    foreach ($this->_options['options'] as $k => $v) {
                        $k = str_replace([' ', "\r\n", "\t", "\r", "\n"], '', $k);
                        $html .= '<option ' . ($this->_options['value'] == $k ? 'selected' : '') . ' value="' . $k . '">' . $v . '</option>';
                    }
                }
                $html .= '</select>';
        }
        return $this->formGroup($html);
    }






    /**
     * 评价
     *
     * @return string
     */
    public function formRate() {
        $html   =   $this->label();
        $value	=	!is_null($this->_options['value']) ? intval($this->_options['value']) : null;
        $labels = '';
        if ($this->_options['options']) {
            foreach ($this->_options['options'] as $k => $v) {
                $labels .= '<label class="square mr10 fs12 fl"><input id="'.$this->_options['name'] . '-' . $k .'" '.($value === $k ? 'checked' : '').' value="'.$k.'" type="radio" name="'.$this->_options['name'].'"><span></span></label>
				<label for="'.$this->_options['name'] . '-' . $k .'" class="fl mr20">'.$v.'</label>';
            }
        }

        $html .= '<div class="clearfix">'.$labels.'</div>';
        return $this->formGroup($html);
    }


    /**
     * 选择商品
     *
     * @return string
     */
    public function formGoods() {
        $html   = $this->label();
        $goods  = '';
        $url    = $this->_options['url'];
        //$url    = U('/goods/choose', ['type' => $this->_options['name'], 'id' => $this->_options['value']['aid'], 'field' => $this->_options['name']]);
        if (!empty($this->_options['value']['goods'])) {
            foreach ($this->_options['value']['goods'] as $k => $v) {
                $goods .= '<li data-name="'.$this->_options['name'].'" data-id="'.$v['id'].'" data-path="'.$v['images'].'" class="text-center">
							<div class="li-img-box">
								<a href="javascript:;" title="商品图片">
									<img src="'.$v['images'].'">
								</a>
							</div>
							<div class="delete-images" onclick="reMoveGoods($(this));">
								<div class="selected-icon">
									<i class="fa fa-times"></i>
								</div>
							</div>
						</li>';
            }
        }

        $html .= '<input type="hidden" name="'.$this->_options['name'].'" value="'.($this->_options['value']['goodsIds'] ? : '').'" />
                    <a href="javascript:;" data-type="vmodal" data-title="商品选择" data-url="'.$url.'" data-val="'.$this->_options['value'].'" class="btn btn-rad btn-trans btn-success m0">'.$this->_options['title'].'</a>
                    <div>
                        <ul id="images-list-'.$this->_options['name'].'" class="images-select-box">
                            '.$goods.'
                        </ul>
                    </div><div class="clear"></div>';
        return $this->formGroup($html);
    }


    /**
     * 商品属性
     *
     * @return string
     */
    public function formGoodsAttr() {
        $html = '<div class="row goods-attr">%s</div>';
        $colMd= '<div class="col-md-4">%s</div>';
        $colTitle = '<div class="goods-attr-item"><div class="col-md-12"><h4>%s</h4></div>';
        $content = '';
        $colMds = '';
        foreach ($this->_options['options'] as $k => $v) {
            $content .= sprintf($colTitle, $v['attr_name']);
            foreach ($v['attr_options'] as $key => $val) {
                $tmpId = $v['id'] . ':' . $key;
                $checked = false;
                if (isset($this->_options['value']) && !empty($this->_options['value'])) {
                    foreach ($this->_options['value'] as $value) {
                        $tmpValue = explode(',', $value['attr_id']);
                        $tmpName  = explode(',', $value['attr']);
                        if ($tmpName) {
                            $countTmpName = count($tmpName);
                            for ($j=0;$j<$countTmpName;$j++) {
                                $a = substr($tmpName[$j], strripos($tmpName[$j], ':')+1);
                                $b = substr($tmpName[$j], 0, strripos($tmpName[$j], ':'));
                                if ($a != false && $b != false && $b == $tmpId) {
                                    $val = $a;
                                    break;
                                }
                            }
                        }
                        if ($tmpValue) {
                            $countTmpValue = count($tmpValue);
                            for ($i=0;$i<$countTmpValue;$i++) {
                                if ($tmpValue[$i] == $tmpId) {
                                    $checked = true;
                                    //$tmpName = $value['attr_name'];
                                    break;
                                }
                            }
                        }
                    }
                }
                $tmpHtml = '<div class="input-group" style="margin-bottom:0;"><span class="input-group-addon" style="padding:0 10px;">
                                <div class="icheckbox_square-red" aria-checked="false" aria-disabled="false" style="position: relative;">
                                    <input '.($checked === true ? 'checked' : '').' type="checkbox" id="attr_id_'.$v['id'].'_'.$key.'" name="values[attr_id]['.$v['id'].':'.$key.']" value="'.$v['id'].':'.$key.'" class="i-red-square" style="">
                                    <ins class="iCheck-helper" style=""></ins>
                                </div>
                            </span><input type="text" name="values[attr_value]['.$v['id'].':'.$key.']" id="attr_value_'.$v['id'].'_'.$key.'" class="form-control" value="'.($val).'" placeholder="请输'.$v['attr_name'].'名称"><a title="上传图片" onclick="attrUploadImages($(this));" href="javascript:;" class="input-group-addon" style="padding:0 10px;">选择图片</a></div>';
                //if ($k == 0) {
                    $imagesHtml = '<ul class="images-select-box" style="margin: 5px 0 15px 0;height:70px;">
                                       %s
                                    </ul>';
                    $valuesHtml = '';
                    if (!empty($this->_options['values'])) {    //attr_list_value
                        foreach ($this->_options['values'] as $keys => $vals) {
                            if ($vals['option_id'] == $key) {
                                if (!empty($vals['attr_album'])) {
                                    $tmpValuesImages = explode(',', $vals['attr_album']);
                                    foreach ($tmpValuesImages as $keyImage => $valImage) {
                                        $valuesHtml .= '<li data-path="'.$valImage.'" class="text-center" style="width: 70px;height: 70px; padding: 0;margin-right: 5px;">
                                            <div class="li-img-box" style="width: 70px; height: 70px;">
                                                <a href="'.$valImage.'" data-title="大图" class="image-zoom" title="大图">
                                                    <img style="width: 55px; height: 55px;" src="'.$valImage.'">
                                                </a>
                                            </div>
                                            <div data-name="images" class="delete-images" onclick="reMoveAttrImages($(this));">
                                                <div class="selected-icon"><i class="fa fa-times"></i></div>
                                            </div>
                                        </li>';
                                    }
                                }
                                $valuesHtml .= '<input type="hidden" class="attrAlbum" value="'.$vals['attr_album'].'" name="values[images]['.$v['id'].':'.$key.']" value=""><input class="valuesIds" type="hidden" name="values[id]['.$v['id'].':'.$key.']" value="'.$vals['id'].'" />';
                            }
                        }
                    }
                    if ($valuesHtml == ''){
                        $valuesHtml = '<input type="hidden" class="attrAlbum" name="values[images]['.$v['id'].':'.$key.']" value="">';
                    }
                    $imagesHtml = sprintf($imagesHtml, $valuesHtml);
                    $tmpHtml .= $imagesHtml;
                //}

                $colMds .= sprintf($colMd, $tmpHtml);
            }
            $content .= $colMds . '</div><div class="clear"></div>';
            $colMds = '';
        }

        unset($k,$key,$v,$val,$value,$tmpId,$checked);
        //值
        $valuesHtml = '';
        if (isset($this->_options['value']) && is_array($this->_options['value'])) {
            $i=1;
            foreach ($this->_options['value'] as $k => $v) {
                $valuesHtml .= '<tr>
                                    <td class="text-center" nowrap="">'.$i.'</td>
                                    <td class="attr-item-name" style="width:100px;" nowrap="">
                                        '.$v['attr_name'].'<input type="hidden" id="attr_sku_attr_id_'.$k.'" name="attrs[attr_id][]" value="'.$v['attr_id'].'">
                                        <input type="hidden" id="attr_sku_attr_'.$k.'" name="attrs[attr][]" value="'.$v['attr'].'">
                                        <input type="hidden" id="attr_sku_id_'.$k.'" name="attrs[id][]" value="'.$v['id'].'">
                                        <input type="hidden" id="attr_sku_attr_name_'.$k.'" name="attrs[attr_name][]" value="'.$v['attr_name'].'">
                                    </td>
                                    <td><div class="form-group" style="margin:0;"><div class="input-group" style="margin:0;"><input type="text" required="true" number="true" min="0.1" id="attr_sku_price_'.$k.'" name="attrs[price][]" value="'.$v['price'].'" class="form-control"><a href="javascript:;" onclick="copySome($(this));" class="input-group-addon" style="padding-left:5px;padding-right:5px;"><i class="fa fa-arrow-down"></i></a></div></div></td>
                                    <td><div class="form-group" style="margin:0;"><div class="input-group" style="margin:0;"><input type="text" number="true" min="0" id="attr_sku_price_market_'.$k.'" name="attrs[price_market][]" value="'.$v['price_market'].'" class="form-control"><a href="javascript:;" onclick="copySome($(this));" class="input-group-addon" style="padding-left:5px;padding-right:5px;"><i class="fa fa-arrow-down"></i></a></div></div></td>
                                    <td><div class="form-group" style="margin:0;"><div class="input-group" style="margin:0;"><input type="text" number="true" min="0" id="attr_sku_price_purchase_'.$k.'" name="attrs[price_purchase][]" value="'.$v['price_purchase'].'" class="form-control"><a href="javascript:;" onclick="copySome($(this));" class="input-group-addon" style="padding-left:5px;padding-right:5px;"><i class="fa fa-arrow-down"></i></a></div></div></td>			
                                    <td><div class="form-group" style="margin:0;"><div class="input-group" style="margin:0;"><input type="text" number="true" min="0" name="attrs[num][]" value="'.$v['num'].'" class="form-control"><a href="javascript:;" onclick="copySome($(this));" class="input-group-addon" style="padding-left:5px;padding-right:5px;"><i class="fa fa-arrow-down"></i></a></div></div></td>
                                    <td><div class="form-group" style="margin:0;"><div class="input-group" style="margin:0;"><input type="text" id="attr_sku_code_'.$k.'" name="attrs[code][]" value="'.$v['code'].'" class="form-control"><a href="javascript:;" onclick="copySome($(this));" class="input-group-addon" style="padding-left:5px;padding-right:5px;"><i class="fa fa-arrow-down"></i></a></div></div></td>
                                    <td><div class="form-group" style="margin:0;"><div class="input-group" style="margin:0;"><input type="text" id="attr_sku_barcode_'.$k.'" name="attrs[barcode][]" value="'.$v['barcode'].'" class="form-control"><a href="javascript:;" onclick="copySome($(this));" class="input-group-addon" style="padding-left:5px;padding-right:5px;"><i class="fa fa-arrow-down"></i></a></div></div></td>
                                    <td><div class="form-group" style="margin:0;"><div class="input-group" style="margin:0;"><input type="text" required="true" number="true" min="0.01" id="attr_sku_weight_'.$k.'" name="attrs[weight][]" value="'.$v['weight'].'" class="form-control"><a href="javascript:;" onclick="copySome($(this));" class="input-group-addon" style="padding-left:5px;padding-right:5px;"><i class="fa fa-arrow-down"></i></a></div></div></td>
                                </tr>';
                ++$i;
            }
        }// <!--min="1" id="attr_sku_num_'.$k.'" maxlength="8"-->

        $contents = '<div class="clearfix"></div> <div class="row mt20 pt20" style="border-top: solid 1px #F0F0F0"><div class="col-md-12"><table id="goodsAttrTable">
                        <thead>
                            <tr>
                                <th class="text-center" nowrap="">顺序</th>
                                <th nowrap="">属性</th>
                                <th nowrap="">销售价</th>
                                <th nowrap="">市场价</th>
                                <th nowrap="">成本价</th>
                                <th nowrap="">库存</th>
                                <th nowrap="">编号</th>
                                <th nowrap="">条形码</th>
                                <th nowrap="">重量(Kg)</th>	
                            </tr>
                        </thead>
                        <tbody id="goods-attr-list-box">
                        %s
                        </tbody>	
                    </table></div></div>';
        return sprintf($html, $content) . sprintf($contents, $valuesHtml);
    }


    /**
     * 商品搭配
     *
     * @return string
     */
    public function formGoodsCollocation() {
        $value = '';
        $listItem = '';
        if (isset($this->_options['value']) && !empty($this->_options['value'])) {
            $value = unserialize($this->_options['value']);
        }
        //<input class="group-goods" type="hidden" name="collocation[id][]" value="'.$v['id'].'">
        $html = '<div class="row"><div class="col-md-12 text-right"><button type="button" class="btn btn-rad btn-trans btn-primary btn-group-plus">添加分组</button></div><div class="col-md-12 group-goods-box">%s</div></div>';
        if (!empty($value)) {
            foreach ($value as $k => $v) {

                $goodsList = '';
                if (!empty($v)) {
                    //取出商品
                    $goods = M('goods')->cache(true)->where(['id' => ['in', $v['goods']]])->field('id,images')->select();
                    foreach ($goods as $val) {
                        $goodsList .= '<li id="'.$k.$val['id'].'" data-name="'.$k.'" data-id="'.$val['id'].'" data-path="'.myurl($val['images'], 160).'" class="text-center">
                                            <div class="li-img-box">
                                                <a href="javascript:;" title="商品图片">
                                                    <img src="'.myurl($val['images'], 160).'">
                                                </a>
                                            </div>
                                            <div class="delete-images" onclick="reMoveGroupGoods($(this));">
                                                <div class="selected-icon"><i class="fa fa-times"></i></div>
                                            </div>
                                        </li>';
                    }
                }

                $listItem .= '<div class="row mt20 pt20 group-list-item" id="group-list-item-'.$k.'" style="border-top:solid 1px #f0f0f0">
                                <div class="col-md-4">
                                    <div class="input-group">
                                        <span class="input-group-addon">分组名称</span>
                                        <input type="text" class="form-control group-name" name="collocation[name][]" value="'.$v['name'].'" placeholder="分组名称">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="input-group">
                                        <span class="input-group-addon">分组排序</span>
                                        <input type="text" class="form-control group-sort" name="collocation[sort][]" value="'.$v['sort'].'" placeholder="分组排序">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <input class="group-goods" type="hidden" name="collocation[goods][]" value="'.$v['goods'].'">
                                    <a href="javascript:;" onclick="chooseGoods($(this), '.$k.');" class="btn btn-rad btn-trans btn-success m0">选商品</a>
                                    <a href="javascript:;" onclick="removeGroupItem($(this));" title="移除分组" class="btn btn-rad btn-trans btn-primary ml20">移除分组</a>
                                </div>
                                <div class="col-md-12 group-goods-list">
                                    <ul id="images-list-'.$k.'" class="images-select-box">
                                    '.$goodsList.'
                                    </ul>
                                </div>
                            </div>';
            }
        }
        return sprintf($html, $listItem);
    }


    /**
     * 图片及文件上传
     *
     * @return string
     */
    public function formUpload() {
        $picList = $this->_options['value'] ? explode(',', rtrim($this->_options['value'], ',')) : '';
        $images = '';
        $html   = $this->label();
        if (!empty($picList)) {
            foreach ($picList as $v) {
                $images .= '<li id="" data-path="'.$v.'" class="text-center">
                    <div class="li-img-box">
                        <a href="'.$v.'" data-title="大图" class="image-zoom" title="大图">
                            <img src="'.$v.'">
                        </a>
                    </div>
                    <div data-name="'.$this->_options['name'].'" class="delete-images" onclick="reMove($(this));">
                        <div class="selected-icon"><i class="fa fa-times"></i></div>
                    </div>
                </li>';
            }
        }


        $html .= '<div class="uploader">
		    <div id="filePicker_'.$this->_options['name'].'">选择图片</div>
	    	<ul id="images-list-'.$this->_options['name'].'" class="images-select-box">
               '.$images.' 
			</ul>
		    <input name="'.$this->_options['name'].'" type="hidden" id="'.$this->_options['name'].'" value="'.$this->_options['value'].'" />
		</div><div class="clear"></div>';

        return $this->formGroup($html);
    }


    /**
     * 地址选择
     *
     * @return string
     */
    public function formAddress() {
        $html   = $this->label();
        $addr   = '';
        if (!empty($this->_options['options'])) {
            foreach ($this->_options['options'] as $k => $v) {
                $addr .= '<div class="item '.($v['id'] == $this->_options['value'] ? 'active' : ($k == 0 ? 'active' : '')).'" onclick="select_address_item($(this))" data-id="'.$v['id'].'" data-field="'.$this->_options['name'].'">
                            <div><span class="ft16">'.$v['linkname'].'</span> , '.$v['mobile'] . (!empty($v['tel']) ? ',' . $v['tel'] : '') .'</div>
                            <div class="text-gray">'.$v['province_name'] . $v['city_name'] . $v['district_name'] . $v['town_name'] . $v['street'] . '</div>
                        </div>';
            }
        }

        $html  .= '<input type="hidden" name="'.$this->_options['name'].'" id="'.$this->_options['name'].'" value="'.(!empty($this->_options['value']) ? $this->_options['value'] : $this->_options['options'][0]['id']).'" />
                    <div class="pull-right">
                        <a href="'.$this->_options['url'].'" target="_blank" class="btn-primary btn-xs btn-trans">管理地址</a>
                    </div>
                    <div class="clearfix md10"></div>
                    <div class="select-address">
                    '. $addr .'
                    </div>';
        return $this->formGroup($html);
    }

    /**
     * 编辑器
     *
     * @return string
     */
    public function formUeditor() {
        $html   = $this->label();
        $html  .= W('Common/Builder/ueditor', array(array('name' => $this->_options['name'], 'value' => $this->_options['value']), true));
        return $this->formGroup($html);
    }

    /**
     * 表单button按钮
     *
     * @return string
     */
    public function formButton() {
        return sprintf('<div class="text-center"><button name="%s" class="btn btn-lg btn-rad btn-trans btn-primary btn-form-submit %s" type="%s" style="%s" %s>%s</button></div>', $this->_options['name'], $this->_options['class'], $this->_options['type'] ? : 'button', $this->_options['style'], $this->_options['attrs'], $this->_options['title']);
    }

    /**
     * 地区选择
     *
     * @return string
     */
    public function formDistrict() {
        $html   = $this->label();
        $html  .= W('Common/Builder/chinaCity', array(array('name' => $this->_options['name'], 'value' => array_filter($this->_options['value'])), true));
        return $html;
    }


    /**
     * 二级菜单多选框
     *
     * @return string
     */
    public function categoryCheckbox() {
        $html = $this->label();
        $html .= '<div><ul class="nav nav-tabs border-d">%s</ul><div class="tab-content tab-content-noborder mb0" style=\'border: none;box-shadow: none;\'>%s</div></div>';
        $tabs = '';
        $pane = '';
        $value= explode(',', $this->_options['value']);
        if (is_array($this->_options['options'])) {
            foreach ($this->_options['options'] as $k => $v) {
                $tabs .= '<li '.($k == 0 ? 'class="active"' : '').'><a href="#'.$this->_options['name'].$k.'" data-toggle="tab">'.$v[$this->_options['correspond']['name']].'</a></li>';
                $pane .= '<div class="tab-pane '.($k == 0 ? 'active' : '').'" id="'.$this->_options['name'].$k.'">';
                    foreach ($v[$this->_options['correspond']['child']] as $key => $val) {
                        $pane .= '<label class="mr20" style="">
                                    <input '. (in_array($val[$this->_options['correspond']['id']], $value) ? 'checked ' : '') . ($this->_options['validate'] && $key == 0 ? Validate::getInstance($this->_options['validate'])->create() : '').' type="checkbox" class="i-red-square" name="'.$this->_options['name'].'[]" value="'.$val[$this->_options['correspond']['id']].'"> '.$val[$this->_options['correspond']['name']].'
                                </label>';
                    }
                $pane .= '</div>';
            }
        }
        return $this->formGroup(sprintf($html, $tabs, $pane));
    }


    /**
     * 模态框
     *
     * @return string
     */
    public function formModal() {
        $html  = $this->label();
        $html .= '<input id="'.$this->_options['name'].'" type="hidden" name="'.$this->_options['name'].'" value="'.($this->_options['value'] ? : '').'" />
                    <a href="javascript:;" data-type="vmodal" data-footer="false" data-title="选择'.$this->_options['title'].'" data-url="'.$this->_options['url'].'" data-val="'.$this->_options['value'].'" class="btn btn-rad btn-trans btn-success m0">选择'.$this->_options['title'].'</a>
                    <div>
                        <ul id="images-list-'.$this->_options['name'].'" class="images-select-box"></ul>
                    </div><div class="clear"></div>';
        return $this->formGroup($html);
    }

    /**
     * 表单tips
     *
     * @param $html
     * @return string
     */
    public function formTips($html) {
        return '<div class="tips-form text-gray ft12">'.$html.'</div>';
    }

    /**
     * 图形验证码
     *
     * @return string
     */
    public static function graphicsCode() {
        return '<a href="javascript:void(0)" data-url="'.DM('user') . U('/verify').'" class="verify" title="点击图片更换验证码">
					<img src="'.DM('user') . U('/verify').'" alt="验证码" class="verifyimg" style="height:35px;">
				</a>';
    }

    /**
     * 获取短信验证码按钮
     *
     * @return string
     */
    public static function smscodeButton() {
        return '<span class="input-group-btn"><button type="button" class="btn btn-primary smscode bg-8ac text_white" onclick="sendMsg(this);">获取验证码</button></span>';
    }


    /**
     * subject: 选择方法
     * api: formShopAuthFunctions
     * author: Mercury
     * day: 2017-03-24 17:18
     * [字段名,类型,是否必传,说明]
     * @return string
     */
    public function formShopAuthFunctions()
    {
        $html  = $this->label();
        $html .= '<table class="no-bg"><thead><th>模块</th><th>页面</th></thead><tbody>%s</tbody></table>';
        $i     = 0;
        $tmpss = '';
        if ($this->_options['value']) $this->_options['value'] = explode(',', $this->_options['value']);
        foreach ($this->_options['options'] as $v) {
            $controllerCheckBoxs = '<div class=""><label class="mr20" style=""><div class="icheckbox_square-red" aria-checked="false" aria-disabled="false" style="position: relative;">
                <input id="'.$this->_options['element_id'] . '_' . $i .'" type="checkbox" class="i-red-square checkeNextdAll" name="" value="">
                <ins class="iCheck-helper"></ins>
            </div> '.$v['title'].'</label></div>';
            $tmps = '';
            foreach ($v['child'] as $key => $val) {
                $tmp = '';
                $controllerCheckBox = '<div class=""><label class="mr20" style=""><div class="icheckbox_square-red" aria-checked="false" aria-disabled="false" style="position: relative;">
                                    <input id="'.$this->_options['element_id'] . '_' . $i .'" type="checkbox" class="i-red-square checkeNextdAll" name="" value="">
                                    <ins class="iCheck-helper"></ins>
                                </div> '.$val['title'].'</label></div>';
                foreach ($val['child'] as $value) {
                    $tmp .= '<div class="pull-left"><label class="mr20" style=""><div class="icheckbox_square-red" aria-checked="false" aria-disabled="false" style="position: relative;">
                                    <input '.($this->_options['validate'] && $i == 0 ? Validate::getInstance($this->_options['validate'])->create() : '').' id="'.$this->_options['element_id'] . '_' . $i .'" '.(in_array($value['id'], $this->_options['value']) ? 'checked' : '').' type="checkbox" class="i-red-square" name="'.$this->_options['name'] .'[]" value="'.$value['id'].($value['inline'] ? ','.$value['inline'] : '').'">
                                    <ins class="iCheck-helper"></ins>
                                </div> '.$value['page_name'].'</label></div>';
                }
                $tmps .= '<tr '.($key > 0 ? 'style="border-top:solid 1px #dadada"':'').'><td width="20%" style="border: 0;vertical-align: middle">'.$controllerCheckBox.'</td><td style="border-bottom:0;border-right:0;vertical-align: middle">'.$tmp.'</td></tr>';
            }
            $tmpss .= '<tr>
		        <td>'.$controllerCheckBoxs.'</td>
		        <td width="85%" style="padding:0">
		        	<table class="no-bg">
		        		<tbody>
		        			'.$tmps.'
		        		</tbody>
		        	</table>
		        </td>
		    </tr>';
        }
        return $this->formGroup(sprintf($html, $tmpss));
    }

    /**
     * 生成grouphtml
     *
     * @param $group
     * @return string
     */
    public function group($group) {
        $groupNav       = '';
        $groupContent   = '';
        $html = '<div class="tab-container">
						<ul class="nav nav-tabs">
						  %s
						</ul>
						<div class="tab-content">
						  %s
						</div>
					</div>';
        foreach ($group['title'] as $k => $v) {
            $groupNav .= '<li '.($k == 0 ? 'class="active"' : '').'><a href="#group-'.$k.'" data-toggle="tab">'.$v.'</a></li>' . "\r\n";
        }
        unset($k,$v);
        foreach ($group['html'] as $k => $v) {
            $groupContent .= '<div class="tab-pane '.($k == 0 ? 'active' : '').'" id="group-'.$k.'">'.$v.'</div>' . "\r\n";
        }
        unset($k,$v, $group);
        return sprintf($html, $groupNav, $groupContent);
    }


    /**
     * subject: 游戏促销面额选择
     * api: formLuckdrawSelect
     * author: Mercury
     * day: 2017-05-13 14:19
     * [字段名,类型,是否必传,说明]
     * @return string
     */
    public function formLuckdrawSelect()
    {
        $cnt  = $this->_options['value'] ? count($this->_options['value']) : 0;
        $html = '<div class="row select-box">%s</div>';
        $tmps = '';
        $htmls= '';
        $i    = 0;
        $options = function ($val) {
            $h = '';
            foreach ($this->_options['options'] as $k => $v) {
                $h .= '<option '.($val == $k ? 'selected' : '').' data-price="'.$v.'" value="'.$k.'">面额 '.$v.' 元</option>';
            }
            return $h;
        };

        do {
            $tmps = '<div class="col-sm-4">
                        <div class="form-group">
                            <label class="control-label label-block">'.$this->_options['title'][0].' <span class="text_red">*</span></label>
                            <select id="'.$this->_options['name'][0] .'_select_' . $i .'" required class="form-control" name="'.$this->_options['name'][0].'[]">
                                <option value="">--请选择--</option>
                                '.$options($this->_options['value'][$i]['id']).'
                            </select>
                        </div>
                    </div>
                    <div class="col-sm-4">
                    <div class="form-group">
                        <label class="control-label label-block">'.$this->_options['title'][1].' <span class="text_red">*</span></label>
                        <input value="'.$this->_options['value'][$i]['min_price'].'" min="0" name="'.$this->_options['name'][1].'[]" type="number" id="'.$this->_options['name'][0] .'_input_' . $i .'" class="form-control" required placeholder="'.$this->_options['title'][1].'">
                    </div>
                    </div>
                    <div class="col-xs-4">
                        <div class="form-group">
                            <label class="control-label label-block">&nbsp;</label>
                            '.($i == 0 ? '<a href="javascript:;" class="btn btn-success btn-trans btn-plus">
                                <i class="fa fa-plus"></i> 再加一个
                            </a>' : '<a href="javascript:;" onclick="removeThisSelect($(this));" class="btn btn-primary btn-trans">
                                <i class="fa fa-minus"></i> 移除当前
                            </a>').'
                        </div>
                    </div>';
                $htmls .= sprintf($html, $tmps);
            $i++;
        } while ($i < $cnt);
        return $htmls . '<div class="plus-select-box"></div>';
    }
    
    /**
     * 生成列数
     *
     * @param $col      列数
     * @param $html     html
     * @return string
     */
    public static function col($col, $html) {
        $colArr = [1 => 12, 2 => 6, 3 => 4, 4 => 3, 6 => 2, 12 => 1];   //列数数组
        $tmp = '<div class="row">';
        for ($i = 1; $i <= $col; $i++) {
            $tmp .= '<div class="col-sm-'.$colArr[$col].'">'.$html[$i-1].'</div>';
        }
        $tmp .= '</div>';
        return $tmp;
    }

    function __destruct()
    {
        if ($this->_options) $this->_options = null;
        // TODO: Implement __destruct() method.
    }
}