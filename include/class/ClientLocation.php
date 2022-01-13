<?php

class ClientLocation
{
    public static $apiUrl = "http://ip-api.com/json/";
    public static function ApiRequest(){
        $request = wp_remote_get(self::$apiUrl.self::getTheUserIp());
        $body = wp_remote_retrieve_body( $request );
        return json_decode( $body );
    }
    public static function getTheUserIp() {
        if ( ! empty( $_SERVER['HTTP_CLIENT_IP'] ) ) {
            //check ip from share internet
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
            //to check ip is pass from proxy
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        return apply_filters( 'appchar_get_ip', $ip );
    }
    public static function getCountry(){
        return self::ApiRequest()->countryCode;
    }
}