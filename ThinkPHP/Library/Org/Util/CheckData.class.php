<?php
/**
+----------------------------------------------------------------------
| RestFull API 
+----------------------------------------------------------------------
| 数据验证类
+----------------------------------------------------------------------
| Author: lazycat <673090083@qq.com>
+----------------------------------------------------------------------
*/

namespace Org\Util;
class CheckData {
	protected $str;	//要验证的数据

    /**
     * 架构函数
     * @access public
     * @param string $this->str  数据
     */
    public function __construct($str) {
    	$this->str=trim($str);
    }

	/**
	 * 是否HTML
	 * @param string $this->str
	 * @return boolean
	 */			
	public function html(){
		return strip_tags($this->str)!=$this->str?ture:false;
	}

	/**
	 * 验证是否为指定长度的字母/数字组合
	 * @param string $this->str
	 * @return boolean
	 */			
	public function text_range($num1,$num2){
	    return (preg_match("/^[a-zA-Z0-9]{".$num1.",".$num2."}$/",$this->str))?true:false;
	}

	/**
	 * 验证是否为指定长度的字母/数字组合
	 * @param string $this->str
	 * @return boolean
	 */			
	public function text_length($num){
	    return (preg_match("/^[a-zA-Z0-9]{".$num."}$/",$this->str))?true:false;
	}

	/**
	 * 验证是否为指定长度数字
	 * @param string $this->str
	 * @param integer $num1,$num2
	 * @return boolean
	 */		
	public function number_range($num1,$num2){
	    return (preg_match("/^[0-9]{".$num1.",".$num2."}$/i",$this->str))?true:false;
	}

	/**
	 * 验证是否为指定长度汉字
	 * @param string $this->str
	 * @param integer $num1,$num2
	 * @return boolean
	 */		
	public function chinese_range($num1,$num2){
	// preg_match("/^[\xa0-\xff]{1,4}$/", $this->string);
	    return (preg_match("/^([\x81-\xfe][\x40-\xfe]){".$num1.",".$num2."}$/",$this->str))?true:false;
	}

	/**
	 * 验证是否为指定长度字符
	 * @param string $this->str
	 * @param integer $num1,$num2
	 * @return boolean
	 */		
	public function string_range($num1,$num2){
	// preg_match("/^[\xa0-\xff]{1,4}$/", $this->string);
		$len=strlen($this->str);
	    return $len>=$num1 && $len<=$num2?true:false;
	}
	/**
	 * 验证身份证号码
	 * @param string $this->str
	 * @return boolean
	 */		
	public function is_idcard(){
	    //return (preg_match('/^\d{6}((1[89])|(2\d))\d{2}((0\d)|(1[0-2]))((3[01])|([0-2]\d))\d{3}(\d|X)$/i',$this->str))?true:false;		
	    return (preg_match('/(^([\d]{15}|[\d]{18}|[\d]{17}x)$)/',$this->str))?true:false;
	}

	/**
	 * 验证邮件地址
	 * @param string $this->str
	 * @return boolean
	 */			
	public function is_email(){
	    return (preg_match('/^[_\.0-9a-z-]+@([0-9a-z][0-9a-z-]+\.)+[a-z]{2,4}$/',$this->str))?true:false;
	}

	/**
	 * 验证电话号码
	 * @param string $this->str
	 * @return boolean
	 */		
	public function is_phone(){
	   return (preg_match("/^((\(\d{3}\))|(\d{3}\-))?(\(0\d{2,3}\)|0\d{2,3}-)?[1-9]\d{6,7}$/",$this->str))?true:false;
	}

	/**
	 * 验证手机号码，支持国际版
	 * @param string $this->str
	 * @return boolean
	 */			
	public function is_mobile(){
	   return (preg_match("/^(0)?1([3|4|5|7|8])+([0-9]){9,10}$/",$this->str))?true:false;
	}

    /**
     * 验证11位手机号码
     */
	public function is_mobile2(){
	    return (preg_match("/^1([3|4|5|7|8])+([0-9]){9}$/",$this->str))?true:false;
    }

	/**
	 * 验证QQ号码
	 * @param string $this->str
	 * @return boolean
	 */		
	public function is_qq(){
		return preg_match("/^[1-9]\d{4,12}$/i", $this->str) ? true : false;
	}
	
	/**
	 * 验证支付密码
	 * @param string $this->str
	 * @return boolean
	 */		
	public function password_safe(){		
		if(strlen($this->str)!=6) return false;
		return (preg_match("/^[0-9]{6}$/", $this->str))?true:false;
	}

    /**
     * 验证域名前缀，5~20位，字母与数字的组合，不能是纯数字
     * @param string $this->str
     * @return boolean
     */
    public function domain(){
        if(preg_match("/^[0-9]+$/", $this->str)) return false;
        return (preg_match("/^[0-9a-z]{5,20}$/", $this->str))?true:false;
    }

    /**
	 * 验证邮编
	 * @param string $this->str
	 * @return boolean
	 */		
	public function is_zip(){
	   return (preg_match("/^[0-9]\d{5}$/",$this->str))?true:false;
	}

	/**
	 * 验证url地址
	 * @param string $this->str
	 * @return boolean
	 */		
	public function is_url(){
	   return (preg_match("/^(http:\/\/)?(https:\/\/)?([\w\d-]+\.)+[\w-]+(\/[\d\w-.\/?%&amp;=]*)?$/",$this->str))?true:false;
	}


	/**
	 * 验证IP地址
	 * @param string $this->str
	 * @return boolean
	 */		
	public function is_ip(){
	   return (preg_match("/^(25[0-5]|2[0-4][0-9]|[0-1]{1}[0-9]{2}|[1-9]{1}[0-9]{1}|[1-9])\.(25[0-5]|2[0-4][0-9]|[0-1]{1}[0-9]{2}|[1-9]{1}[0-9]{1}|[1-9]|0)\.(25[0-5]|2[0-4][0-9]|[0-1]{1}[0-9]{2}|[1-9]{1}[0-9]{1}|[1-9]|0)\.(25[0-5]|2[0-4][0-9]|[0-1]{1}[0-9]{2}|[1-9]{1}[0-9]{1}|[0-9])$/",$this->str))?true:false;
	}

	/**
	 * 验证非空
	 * @param string $this->str
	 * @return boolean
	 */	
	public function required(){
		return empty($this->str) || is_null($this->str)?true:false;
	}

	/**
	 * 验证字符串是否为数字,字母,中文和下划线构成
	 * @param string $this->str
	 * @return bool
	 */
	public function is_check_string(){
		if(preg_match('/^[\x{4e00}-\x{9fa5}\w_]+$/u',$this->str)){
			return true;
		}else{
			return false;
		}
	}


	/**
	 * 验证是否为指定长度数字,字母,中文和下划线构成
	 * @param string $this->str
	 * @param integer $num1,$num2
	 * @return boolean
	 */		
	public function username($num1,$num2){
	// preg_match("/^[\xa0-\xff]{1,4}$/", $this->string);
	    return (preg_match("/^([\x{4e00}-\x{9fa5}\w_]|[0-9a-zA-Z]){".$num1.",".$num2."}$/u",$this->str))?true:false;
	}

	/**
	 * 是否为整数
	 * @param int $this->str
	 * @return boolean
	 */
	public function is_number(){
		if(preg_match('/^[-\+]?\d+$/',$this->str)){
			return true;
		}else{
			return false;
		}
	}
	/**
	 * 是否为正整数
	 * @param int $this->str
	 * @return boolean
	 */
	public function is_positive_number(){
		if(ctype_digit ($this->str)){
			return true;
		}else{
			return false;
		}
	}
	/**
	 * 是否为小数
	 * @param float $this->str
	 * @return boolean
	 */
	public function is_decimal(){
		if(preg_match('/^[-\+]?\d+(\.\d+)?$/',$this->str)){
			return true;
		}else{
			return false;
		}
	}
	/**
	 * 是否为正小数
	 * @param float $this->str
	 * @return boolean
	 */
	public function is_positive_decimal(){
		if(preg_match('/^\d+(\.\d+)?$/',$this->str)){
			return true;
		}else{
			return false;
		}
	}

	/**
	* 是否为大于0
	*/
	public function gt0(){
		if($this->str>0) return true;
		else return false;
	}

	/**
	* 是否为大于等于0
	*/
	public function egt0(){
		if($this->str >= 0) return true;
		else return false;
	}
	/**
	* 是否为大于$num
	* @param number $num
	*/
	public function gt($num){
		if($this->str > $num) return true;
		else return false;
	}
	/**
	* 是否为小于$num
	* @param number $num
	*/	
	public function lt($num){
		if($this->str < $num) return true;
		else return false;
	}

	/**
	* 是否为大于或等于$num
	* @param number $num
	*/	
	public function egt($num){
		if($this->str >= $num) return true;
		else return false;
	}

	/**
	* 是否为小于或等于$num
	* @param number $num
	*/	
	public function elt($num){
		if($this->str <= $num) return true;
		else return false;
	}

	/**
	* 是否为等于$num
	* @param number $num
	*/	
	public function eq($num){
		if($this->str == $num) return true;
		else return false;
	}


	/**
	 * 是否为英文
	 * @param string $this->str
	 * @return boolean
	 */
	public function is_english(){
		if(ctype_alpha($this->str))
			return true;
		else
			return false;
	}
	/**
	 * 是否为中文
	 * @param string $this->str
	 * @return boolean
	 */
	public function is_chinese(){
		if(preg_match('/^[\x{4e00}-\x{9fa5}]+$/u',$this->str))
			return true;
		else 
			return false;
	}



	/**
	 * 判断是否为图片
	 * @param string $this->str	图片文件路径
	 * @return boolean
	 */
	public function is_image(){
		if(file_exists($this->str) && getimagesize($this->str===false)){
			return false;
		}else{
			return true;
		}
	}


	/**
	 * 是否为合法的身份证(支持15位和18位)
	 * @param string $this->str
	 * @return boolean
	 */
	/*
	public function is_card(){
		if(preg_match('/^[1-9]\d{7}((0\d)|(1[0-2]))(([0|1|2]\d)|3[0-1])\d{3}$/',$this->str)||preg_match('/^[1-9]\d{5}[1-9]\d{3}((0\d)|(1[0-2]))(([0|1|2]\d)|3[0-1])\d{4}$/',$this->str))
			return true;
		else 
			return false;
	}
	*/
	/**
	 * 验证身份证号
	 * @param $this->str
	 * @return bool
	 */
	function is_card(){
	    $vCity = array(
	        '11','12','13','14','15','21','22',
	        '23','31','32','33','34','35','36',
	        '37','41','42','43','44','45','46',
	        '50','51','52','53','54','61','62',
	        '63','64','65','71','81','82','91'
	    );
	 
	    if (!preg_match('/^([\d]{17}[xX\d]|[\d]{15})$/', $this->str)) return false;
	 
	    if (!in_array(substr($this->str, 0, 2), $vCity)) return false;
	 
	    $this->str = preg_replace('/[xX]$/i', 'a', $this->str);
	    $vLength = strlen($this->str);
	 
	    if ($vLength == 18)
	    {
	        $vBirthday = substr($this->str, 6, 4) . '-' . substr($this->str, 10, 2) . '-' . substr($this->str, 12, 2);
	    } else {
	        $vBirthday = '19' . substr($this->str, 6, 2) . '-' . substr($this->str, 8, 2) . '-' . substr($this->str, 10, 2);
	    }
	 
	    if (date('Y-m-d', strtotime($vBirthday)) != $vBirthday) return false;
	    if ($vLength == 18)
	    {
	        $vSum = 0;
	 
	        for ($i = 17 ; $i >= 0 ; $i--)
	        {
	            $vSubStr = substr($this->str, 17 - $i, 1);
	            $vSum += (pow(2, $i) % 11) * (($vSubStr == 'a') ? 10 : intval($vSubStr , 11));
	        }
	 
	        if($vSum % 11 != 1) return false;
	    }
	 
	    return true;
	}	



	/**
	 * 验证日期格式是否正确
	 * @param string $this->str
	 * @param string $format
	 * @return boolean
	 */
	public function is_date($format='Y-m-d'){
		$t=date_parse_from_format($format,$this->str);
		if(empty($t['errors'])){
			return true;
		}else{
			return false;
		}
	}

	/**
	 * 用php从身份证中提取生日,包括15位和18位身份证 
	 * @param string $IDCard
	 */
	function get_idcard_info($IDCard){ 
	    $result['error']=0;//0：未知错误，1：身份证格式错误，2：无错误 
	    $result['flag']='';//0标示成年，1标示未成年 
	    $result['tdate']='';//生日，格式如：2012-11-15 
	    if(!eregi("^[1-9]([0-9a-zA-Z]{17}|[0-9a-zA-Z]{14})$",$IDCard)){ 
	        $result['error']=1; 
	        return $result; 
	    }else{ 
	        if(strlen($IDCard)==18){ 
	            $tyear=intval(substr($IDCard,6,4)); 
	            $tmonth=intval(substr($IDCard,10,2)); 
	            $tday=intval(substr($IDCard,12,2)); 
	            if($tyear>date("Y")||$tyear<(date("Y")-100)){ 
	                $flag=0; 
	            }elseif($tmonth<0||$tmonth>12){ 
	                $flag=0; 
	            }elseif($tday<0||$tday>31){ 
	                $flag=0; 
	            }else{ 
	                $tdate=$tyear."-".$tmonth."-".$tday." 00:00:00"; 
	                if((time()-mktime(0,0,0,$tmonth,$tday,$tyear))>18*365*24*60*60){ 
	                    $flag=0; 
	                }else{ 
	                    $flag=1; 
	                } 
	            } 
	        }elseif(strlen($IDCard)==15){ 
	            $tyear=intval("19".substr($IDCard,6,2)); 
	            $tmonth=intval(substr($IDCard,8,2)); 
	            $tday=intval(substr($IDCard,10,2)); 
	            if($tyear>date("Y")||$tyear<(date("Y")-100)){ 
	                $flag=0; 
	            }elseif($tmonth<0||$tmonth>12){ 
	                $flag=0; 
	            }elseif($tday<0||$tday>31){ 
	                $flag=0; 
	            }else{ 
	                $tdate=$tyear."-".$tmonth."-".$tday." 00:00:00"; 
	                if((time()-mktime(0,0,0,$tmonth,$tday,$tyear))>18*365*24*60*60){ 
	                    $flag=0; 
	                }else{ 
	                    $flag=1; 
	                } 
	            } 
	        } 
	    } 
	    $result['error']=2;//0：未知错误，1：身份证格式错误，2：无错误 
	    $result['is_adult']=$flag;//0标示成年，1标示未成年 
	    $result['birthday']=$tdate;//生日日期 
	    return $result; 
	}


}
