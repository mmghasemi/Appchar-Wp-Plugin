<?php

class Appchar_schedule
{
    private $VERSION = '1.0.0';
    public function __construct(){
    }
    public function is_on_schedule(){
//        $zone =  get_option('timezone_string', "Asia/Tehran");
//        if( empty( $zone ) ) $zone = "Asia/Tehran";
//        date_default_timezone_set("$zone");
        $current_day 	= strtolower(date_i18n('w'));
        $hour	= intval(date_i18n('H'));
        $showtime = get_option('appchar_schedule_time','');
        if($showtime != ''){
            if(isset($showtime[$current_day]) && is_array($showtime[$current_day])) {
                foreach ($showtime[$current_day] as $value) {
                    $value = explode('-', $value);
                    $value = explode(':', $value[0]);
                    $value = intval($value[0]);
                    if ($hour >= $value && $hour < ($value + 1)) {
                        return true;
                    }
                }
            }
        }
        return false;
    }
}