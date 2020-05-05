<?php
namespace xadmincn\xadmin\basic;

use Qiniu\Storage\UploadManager;
use Qiniu\Auth;
/*
 * 礼品卡
 */

class Xupload
{
    public function __construct()
    {

    }

    //七牛云上传
    function qiniu($file=""){
        $accessKey = "cRvRqP3cpdGOY7sA558GN4DnIal0c453VQOxXK9-";
        $secretKey = "KN-ZC6p432oSwya2rqmdKPyaQkcy3QkQ2SNvAzG5";
        $bucketName ="lipinkagou";
        $domain ="https://img.lipinkagou.com";
        $upManager = new UploadManager();
        $auth = new Auth($accessKey, $secretKey);
        $token = $auth->uploadToken($bucketName);
        $key = time() . rand(10000, 99999) . '.png' ;
        list($result, $error) = $upManager->putFile($token, $key, $file);
        return $domain."/".$result['key'];
    }

}
