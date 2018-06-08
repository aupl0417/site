<?php
/**
 * Created by PhpStorm.
 * User: dttx
 * Date: 2017/2/7
 * Time: 9:56
 */

namespace Common\Form;


class Js
{

    /**
     * 时间日期相关js及样式
     *
     * @return string
     */
    public static function dateJs() {
        return '<script src="/Public/CSS/flatdream/js/bootstrap.datetimepicker/js/bootstrap-datetimepicker.min.js"></script>
                <script src="/Public/CSS/flatdream/js/bootstrap.datetimepicker/js/bootstrap-datetimepicker.zh-CN.js"></script>
                <link rel="stylesheet" href="/Public/CSS/flatdream/js/bootstrap.datetimepicker/css/bootstrap-datetimepicker.css" />
                <link rel="stylesheet" type="text/css" href="/Public/CSS/flatdream/js/jquery.icheck/skins/square/_all.css">';
    }

    /**
     * 文件上传相关js及样式
     *
     * @return string
     */
    public static function uploadJs() {
        return '<script type="text/javascript" src="/Public/Webuploader/js/webuploader.js"></script>
                <link rel="stylesheet" type="text/css" href="/Public/Webuploader/css/webuploader.css">';
    }

    /**
     * checkbox
     *
     * @return string
     */
    public static function checkboxCss() {
        return '<link rel="stylesheet" type="text/css" href="/Public/CSS/flatdream/js/jquery.icheck/skins/square/_all.css">';
    }

    /**
     * 文件上传
     *
     * @param $options
     * @return string
     */
    public static function uploadJsFun($options) {
        return 'uploadFile('.json_encode($options).');' . "\r\n";
    }


    /**
     * 时间文本框
     *
     * @param $options
     * @return string
     */
    public static function dateJsFun($options) {
        return 'dateTimeFun('.json_encode($options).');' . "\r\n";
    }

    /**
     * 生成JS脚本
     *
     * @param string $script
     * @param string $before
     * @return string
     */
    public static function create($script = null, $before = null) {
        return $before . '<script>'.$script.'</script>';
    }

    /**
     * Validate 数据认证
     *
     * @param $formId
     * @return string
     */
    public static function validate($formId, $params) {
        return '$(document).ready(function() {
                    $("#'.$formId.'").validate('.$params.');
                });';
    }

    /**
     * 使用ajax提交数据
     *
     * @param $options
     * @return string
     */
    public static function ajaxSubmit($options) {
        if (!isset($options['gourl']) && empty($options['gourl'])) {
            $options['gourl'] = 'ref();';
        } else {
            $options['gourl'] = 'gourl({url:'.$options['gourl'].'});';
        }
        $loginUrl = DM('user', '/login');
        $params = '{
                    errorClass: "help-block animation-slideDown",
                    errorElement: "div",
                    debug:true,
                    ignore: [],
                    errorPlacement: function (e, a) {
                        //console.log(e);
                        a.parents(".form-group").append(e)
                        //'.($options['errorBox'] ? 'e.appendTo("'.$options['errorBox'].'"); ' : 'a.parents(".form-group").append(e)').'
                    },
                    highlight: function (e) {
                        $(e).closest(".form-group").removeClass("has-success has-error").addClass("has-error"),
                            $(e).closest(".help-block").remove()
                    },
                    success: function (e) {
                        e.closest(".form-group").removeClass("has-success has-error"),
                            e.closest(".help-block").remove()
                    },
                    showErrors:function(errorMap,errorList) {
                        //console.log(this.numberOfInvalids());
                        if(this.numberOfInvalids() > 0) {
                            $("#error-box").html("您还有 " + this.numberOfInvalids() + " 处错误未处理，点击处理！");
                        }
                        //talert({status:0, msg:"您还有 " + this.numberOfInvalids() + " 个错误未处理."});
                        this.defaultShowErrors();
                    },
            
                    invalidHandler: function (event, validator) { //验证前错误提示
                        // success1.hide();
                        //error1.show();
                    },
                    submitHandler: function() {
                        //alert("提交事件!");
                        $(".btn-form-submit").addClass("disabled");
                        setTimeout("$(\'.btn-form-submit\').removeClass(\'disabled\');", 3000);
                        '.$options['script'].'
                        $("#'.$options['name'].'").ajaxSubmit({
                            type : "'.$options['method'].'",
                            url : "'.$options['action'].'",
                            dataType : "json",
                            headers : {"Accept-Action" : "'.$options['headers'].'"},
                            //clearForm : true,
                            //resetForm : true,
                            success : function(res) {
                                talert({status:res.code,msg:res.msg});
                                if(res.code == 1) {
                                    //解除提示绑定
                                    $(window).unbind(\'beforeunload\');
                                    setTimeout(function() {
                                        '.$options['gourl'].'
                                    }, 1000);
                                } else if(res.code == 401) {
                                    setTimeout(function() {
                                        gourl({url:"'.$loginUrl.'"});
                                    }, 1000);
                                }
                            }
                        });
                    }
                }';
        return self::validate($options['name'], $params);
    }

    /**
     * 不使用ajax提交
     *
     * @param $options
     * @return string
     */
    public static function submit($options) {
        $params = '{
                    errorClass: "help-block animation-slideDown",
                    errorElement: "div",
                    debug:true,
                    errorPlacement: function (e, a) {
                        a.parents(".form-group").append(e)
                        //'.($options['errorBox'] ? 'e.appendTo("'.$options['errorBox'].'"); ' : 'a.parents(".form-group").append(e)').'
                    },
                    highlight: function (e) {
                        $(e).closest(".form-group").removeClass("has-success has-error").addClass("has-error"),
                            $(e).closest(".help-block").remove()
                    },
                    success: function (e) {
                        e.closest(".form-group").removeClass("has-success has-error"),
                            e.closest(".help-block").remove()
                    },
                    showErrors:function(errorMap,errorList) {
                        //console.log(this.numberOfInvalids());
                        //talert({status:0, msg:"您还有 " + this.numberOfInvalids() + " 个错误未处理."});
                        if(this.numberOfInvalids() > 0) {
                            $("#error-box").html("您还有 " + this.numberOfInvalids() + " 处错误未处理.");
                        }
                        this.defaultShowErrors();
                    },
                    invalidHandler: function (event, validator) { //验证前错误提示
                        // success1.hide();
                        //error1.show();
                    },
                    submitHandler: function(form) {
                        '.$options['script'].'
                        $(".btn-form-submit").addClass("disabled");
                        setTimeout("$(\'.btn-form-submit\').removeClass(\'disabled\');", 3000);
                        form.submit();
                    }
                }';
        return self::validate($options['name'], $params);
    }

    /**
     * 隐藏域判断
     *
     * @param $options
     * @return string
     */
    public static function required($options) {
        $script = 'var ' . $options['name'] . '_var = $("#'.$options['name'].'").val();' . "\r\n";
        $script .= 'if ('.$options['name'] . '_var'.' == "" || '.$options['name'] . '_var'.' == undefined) {talert({status:0,msg:"'.$options['title'].'不能为空"});return false;}' . "\r\n";
        return $script;
    }
}