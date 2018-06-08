<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2009 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
namespace Org\Util;

class Calendar
{
    private $year;
    private $month;
    private $weeks  = array('日','一','二','三','四','五','六');
    private $sday;
    private $eday;
    private $days;
    private $isuse;
     
    function __construct($options = array()) {
        $this->year = date('Y');
        $this->month = date('m');
         
        $vars = get_class_vars(get_class($this));
        foreach ($options as $key=>$value) {
            if (array_key_exists($key, $vars)) {
                $this->$key = $value;
            }
        }
    }
     
    function display()
    {
        $html='<table class="calendar">';
        //$this->showChangeDate();
        $html.=$this->showWeeks();
        if($this->isuse==1) $html.=$this->showDays_use($this->year,$this->month,$this->days);
        else $html.=$this->showDays($this->year,$this->month,$this->days);
        $html.='</table>';

        return $html;
    }
     
    private function showWeeks()
    {
        $html='<thead><tr>';
        foreach($this->weeks as $title)
        {
            $html.='<th>'.$title.'</th>';
        }
        $html.='</tr></thead>';

        return $html;
    }
     
    private function showDays($year, $month,$daylist='')
    {
        if(!is_array($daylist)) $daylist=@explode(',',$daylist);

        $firstDay = mktime(0, 0, 0, $month, 1, $year);
        $starDay = date('w', $firstDay);
        $days = date('t', $firstDay);

        $endDay = date('w', mktime(0, 0, 0, $month, $days, $year));
        //echo $endDay.'<br>';
        $html='<tr>';
        for ($i=0; $i<$starDay; $i++) {
            $html.='<td>&nbsp;</td>';
        }
         
        for ($j=1; $j<=$days; $j++) {
            $day=$year.'-'.(intval($month)<10?'0'.intval($month):$month).'-'.(intval($j)<10?'0'.$j:$j);
            $i++;
            if ($j == date('d') && $year==date('Y') && $month==date('m')) {
                $html.='<td class="today '.(in_array($day,$daylist)?'active':'').'">'.$j.'</td>';
            }
            elseif($j < date('d') && $year==date('Y') && $month==date('m')){
                $html.='<td class="expire '.(in_array($day,$daylist)?'active':'').'">'.$j.'</td>';
            }else {
                $html.='<td class="hover '.(in_array($day,$daylist)?'active':'').'" data-day="'.$day.'">'.$j.'</td>';
            }
            if ($i % 7 == 0) {
                $html.='</tr><tr>';
            }
        }
         
        if((6-$endDay)>0){ //补全表格
            for($k=0;$k<(6-$endDay);$k++){
                $html.='<td>&nbsp;</td>';
            }
        }

        $html.='</tr>';
        return $html;
    }


    //被使用的日期变为不可选
    private function showDays_use($year, $month,$daylist='')
    {
        if(!is_array($daylist)) $daylist=@explode(',',$daylist);

        $firstDay = mktime(0, 0, 0, $month, 1, $year);
        $starDay = date('w', $firstDay);
        $days = date('t', $firstDay);

        $endDay = date('w', mktime(0, 0, 0, $month, $days, $year));
        //echo $endDay.'<br>';
        $html='<tr>';
        for ($i=0; $i<$starDay; $i++) {
            $html.='<td>&nbsp;</td>';
        }
         
        for ($j=1; $j<=$days; $j++) {
            $day=$year.'-'.(intval($month)<10?'0'.intval($month):$month).'-'.(intval($j)<10?'0'.$j:$j);
            $i++;
            if ($j == date('d') && $year==date('Y') && $month==date('m')) {
                $html.='<td class="today">'.$j.'</td>';
            }
            elseif($j < date('d') && $year==date('Y') && $month==date('m')){
                $html.='<td class="expire">'.$j.'</td>';
            }else {
                $html.='<td class="'.(in_array($day,$daylist)?'isuse':'hover').'" data-day="'.$day.'">'.$j.'</td>';
            }
            if ($i % 7 == 0) {
                $html.='</tr><tr>';
            }
        }
         
        if((6-$endDay)>0){ //补全表格
            for($k=0;$k<(6-$endDay);$k++){
                $html.='<td>&nbsp;</td>';
            }
        }

        $html.='</tr>';
        return $html;
    }
    
     
    private function preYearUrl($year,$month)
    {
        $year = ($this->year <= 1970) ? 1970 : $year - 1 ;
         
        return 'year='.$year.'&month='.$month;
    }
     
    private function nextYearUrl($year,$month)
    {
        $year = ($year >= 2038)? 2038 : $year + 1;
         
        return 'year='.$year.'&month='.$month;
    }
     
    private function preMonthUrl($year,$month)
    {
        if ($month == 1) {
            $month = 12;
            $year = ($year <= 1970) ? 1970 : $year - 1 ;
        } else {
            $month--;
        }        
         
        return 'year='.$year.'&month='.$month;
    }
     
    private function nextMonthUrl($year,$month)
    {
        if ($month == 12) {
            $month = 1;
            $year = ($year >= 2038) ? 2038 : $year + 1;
        }else{
            $month++;
        }
        return 'year='.$year.'&month='.$month;
    }
     
}