<?php

class Appchar_Token
{
    private $TOKEN;
    
    public function __construct(){
        if(!$this->get_token()){
            $this->TOKEN = $this->generate_token();
            $this->save_token($this->TOKEN);
        }
    }
    
    public function generate_token(){
        $token =  md5(uniqid(rand(), true));
        return $token;
    }
    
    public function token_validator($token){
        if($token == $this->get_token()){
            return true;
        }else{
            return false;
        }
    }

    private function save_token(){
        if(!$this->get_token()) {
            add_option('appchar_secure_token', $this->TOKEN);
        }
    }
    
    public function get_token(){
        if(get_option('appchar_secure_token',false)){
            return SHA1(get_option('appchar_secure_token',''));
        }
        return false;
    }
}