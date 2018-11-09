<?php

class DateOptions {
    private static $weekDay = array("on sunday at 20:00","on monday at 20:00", "on tuesday at 20:00", "on wednesday at 20:00", "on thursday at 20:00", "on friday at 20:00","on saturday at 20:00");
    private static $timeAgo = array("one hour ago", "10 hours ago","one day ago at 20:00", "10 days ago at 20:00", "Just now", "10 minutes ago");
    private static $month = array("25 January 2012 at 20:00","25 February 2012 at 20:00","25 March 2012 at 20:00","25 April 2012 at 20:00","25 May 2012 at 20:00","25 June 2012 at 20:00","25 July 2012 at 20:00","25 August 2012 at 20:00","25 September 2012 at 20:00","25 October 2012 at 20:00","25 November 2012 at 20:00","25 December 2012 at 20:00");
    private static $Date = array("25 January 2012","25 February 2012","25 March 2012","25 April 2012","25 May 2012","25 June 2012","25 July 2012","25 August 2012","25 September 2012","25 October 2012","25 November 2012","25 December 2012");


    public static function getTranslations(){
        $tmp=array(
            "weekDay"=>self::$weekDay,
            "date"=>self::$Date,
            "timeAgo"=>self::$timeAgo,
            "month"=>self::$month
        );
        return $tmp;
    }

    public static function getDateForDatabase($timezone){
        if(!isValidTimezoneId($timezone))return false;
        $time =microtime(true);
        $m = explode('.',$time);
        $micro_time=sprintf("%06d",($time - floor($time)) * 1000000);
        $date=new DateTime( date('Y-m-d H:i:s.'.$micro_time,$time));
        $timezone = new DateTimeZone($timezone);
        $date->setTimeZone($timezone);
        return $date->format("YmdHis").".".substr($m[1],0,3)."  ";
    }

    public static function createTZList() {
        $out = array();
        $tza = timezone_abbreviations_list();
        foreach ($tza as $zone)
            foreach ($zone as $item)
                $out[$item['timezone_id']] = 1;
        unset($out['']);
        ksort($out);
        return array_keys($out);
    }

    public static function isValidTimezoneId($tzid){
        $valid = array();
        $tza = timezone_abbreviations_list();
        foreach ($tza as $zone)
            foreach ($zone as $item)
                $valid[$item['timezone_id']] = true;
        unset($valid['']);
        return !!$valid[$tzid];
    }

    public static function str_diff(DateTime $prev){
        $diff2=null;
        $curr = new DateTime();
        $diff = $curr->diff($prev);
        if($diff->m>0){
            $translate = (self::$month[date("m", $prev->getTimestamp())-1]);
            $translate = str_replace("25",date("d", $prev->getTimestamp()),$translate);
            $diff2 = str_replace("20:00",date("H:i", $prev->getTimestamp()),$translate);
            if(date("Y", $prev->getTimestamp())!=date("Y", $curr->getTimestamp())){
                $diff2 = str_replace("2012",date("Y", $prev->getTimestamp()),$diff2);
            }else{
                $diff2 = str_replace("2012","",$diff2);
            }
        } else if($diff->d>0){
            if(date("W", $prev->getTimestamp())==date("W", $curr->getTimestamp())){
                $diff2 = (self::$weekDay[date("w", $prev->getTimestamp())-1]);
                $diff2 = str_replace("20:00",date("H:i", $prev->getTimestamp()),$diff2);
            }else{
                if($diff->d==1){
                    $diff2 = (self::$timeAgo[2]);
                    $diff2 = str_replace("20:00",date("H:i", $prev->getTimestamp()),$diff2);
                }else{
                    $translate = (self::$timeAgo[3]);
                    $translate = str_replace("10",$diff->d,$translate);
                    $diff2 = str_replace("20:00",date("H:i", $prev->getTimestamp()),$translate);
                }
            }
        } else if($diff->h>0){
            if($diff->h==1)$diff2 = (self::$timeAgo[0]);
            else{
                $translate = (self::$timeAgo[1]);
                $diff2 = str_replace("10",$diff->h,$translate);
            }
        } else if($diff->i>0){
            if($diff->i==1)$diff2 = (self::$timeAgo[4]);
            else{
                $translate = (self::$timeAgo[5]);
                $diff2 = str_replace("10",$diff->i,$translate);
            }
        }else{
            $diff2 = (self::$timeAgo[4]);
        }
        return str_replace("  "," ",$diff2);
    }
}