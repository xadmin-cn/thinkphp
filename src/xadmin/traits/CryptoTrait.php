<?php

namespace xadmincn\xadmin\traits;

trait CryptoTrait
{
    
    //私钥加密
    function priEncrypt($data,$private_url='',$private_data=''){
        $data = $this->ascSort($data);
        if($private_url!='') {
            $private_key = file_get_contents(APP_PATH . $this->appName . $private_url);
        }else{
            $private_key =$private_data;
        }
        $res =  openssl_pkey_get_private($private_key);
        if($res==false){
            $this->errorMessage("私钥不可用");
        }
        openssl_sign($data, $sign,$res,OPENSSL_ALGO_SHA256);
        $sign = base64_encode($sign);
        return $sign;
    }
    //公钥加密
    function pubEncrypt($data,$public_url='',$public_data=''){
        $data = $this->ascSort($data);
        if($public_url!='') {
            $public_key = file_get_contents(APP_PATH . $this->appName . $public_url);
        } else{
            $public_key =$public_data;
         }

        $res =  openssl_pkey_get_public($public_key);
        if($res==false){
            $this->errorMessage("公钥不可用");
        }
        openssl_sign($data, $sign,$res,OPENSSL_ALGO_SHA256);
        $sign = base64_encode($sign);
        return $sign;
    }
    //私钥验签
    function priDecrypt($data,$private_url,$private_data,$digest_type='digest'){
        $sign = base64_decode($data[$digest_type]);
        unset($data[$digest_type]);
        $data = $this->ascSort($data);
        if($private_url!='') {
            $private_key =file_get_contents(APP_PATH.$this->appName.$private_url);
        }else{
            $private_key =$private_data;
        }
        $res =  openssl_pkey_get_private_url($private_key);
        if($res==false){
            $this->errorMessage("私钥不可用");
        }
        $result = openssl_verify($data,$sign ,$res,OPENSSL_ALGO_SHA256);
        return $result;
    }
    //公钥验签
    function pubDecrypt($data,$public_url,$public_data='',$digest_type='digest'){
        if(!isset($data[$digest_type])){
                return false;
        }
        $sign = base64_decode($data[$digest_type]);
        unset($data[$digest_type]);
        $data = $this->ascSort($data);
        if($public_url!='') {
            $public_key = file_get_contents(APP_PATH . $this->appName . $public_url);
        }else{
            $public_key = $public_data;
        }
        $res =  openssl_pkey_get_public($public_key);
        if($res==false){
            $this->errorMessage("公钥不可用");
        }
        $result = openssl_verify($data,$sign ,$res,OPENSSL_ALGO_SHA256);

        return $result;
    }
    public function ascSort($params = array())
    {
        if (!empty($params)) {
            ksort($params);
            if ($params) {
                $str = '';
                foreach ($params as $k => $val) {
                    if($val!=''){
                        $str .= $k . '=' . $val . '&';
                    }
                }
                $strs = rtrim($str, '&');

                return $strs;
            }
        }
        return false;
    }

    //私钥加密
    function priJiami($data,$private_url='',$private_data=''){
        if($private_url!='') {
            $private_key = file_get_contents(APP_PATH . $this->appName . $private_url);
        }else{
            $private_key =$private_data;
        }
        $res =  openssl_pkey_get_private($private_key);
        if($res==false){
            $this->errorMessage("私钥不可用");
        }
        openssl_sign($data, $sign,$res,OPENSSL_ALGO_SHA256);
        $sign = base64_encode($sign);
        return $sign;
    }

    //公钥加密
    function pubJiami($data,$public_url='',$public_data=''){
        if($public_url!='') {
            $public_key = file_get_contents(APP_PATH . $this->appName . $public_url);
        }else{
            $public_key =$public_data;
        }
        $pubKey =  openssl_pkey_get_public($public_key);
        if($pubKey==false){
            $this->errorMessage("公钥不可用");
        }
        openssl_public_encrypt($data, $encrypted, $pubKey);
        $sign = base64_encode($encrypted);
        return $sign;
    }

    //私钥解密
    function priJiemi($data,$private_url='',$private_data=''){
        if($private_url!='') {
            $private_key = file_get_contents(APP_PATH . $this->appName . $private_url);
        }else{
            $private_key =$private_data;
        }
        $priKey =  openssl_pkey_get_private($private_key);
        if($priKey==false){
            $this->errorMessage("私钥不可用");
        }
        $data = base64_decode($data);
        openssl_private_decrypt($data, $decrypted, $priKey);
        return $decrypted;
    }


    /**
     * 密码加密和验证
     * @param
     * @return
     */
    function passwordHash($password,$type='hash',$sem='xadmin.cn'){
        if($type=="md5"){
            return md5($password.$sem);
        }elseif($type=="xadmin"){
            return password_hash($password.$sem, PASSWORD_BCRYPT, ["cost" => 10]);
        }elseif($type=="hash"){
            return password_hash($password.$sem, PASSWORD_BCRYPT, ["cost" => 8]);
        }
    }
    function passwordVerify($password,$verify,$type='hash',$sem='xadmin.cn'){
        if($type=="md5"){
            if ($verify!=md5($password.$sem)){
                return false;
             }
            return true;
        }elseif($type=="xadmin"){
            return password_verify($password.$sem, $verify);
        }elseif($type=="hash"){
            return password_verify($password.$sem, $verify);
        }
    }



}