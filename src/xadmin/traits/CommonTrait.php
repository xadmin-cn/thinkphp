<?php
namespace xadmincn\xadmin\traits;

trait CommonTrait
{
    /**
     * Curl 请求
     * @param string $url
     */

    function curl($url, $data = '',  $headers =array(), $referer = null,$cookie = null) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        $header = array();
        if(count($headers)>0){
            foreach($headers as $key => $value){
                $header[] = $key.":".$value;
            }

        }
        if ($data!='') {
            curl_setopt($ch, CURLOPT_POST, true);
            if(is_array($data)){
                $data = http_build_query($data);
            }
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        }
       // $header[] = array("Content-type:text/xml; charset=utf-8","Accept:text/xml");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_COOKIE, $cookie);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_REFERER, $referer);

        $content = curl_exec($ch);
        curl_close($ch);
        return $content;
    }

    /**
     * 获取图片转为base64
     * @param string $avatar
     * @return bool|string
     */
    public static function setImageBase64($avatar = '', $timeout = 9)
    {
        try {
            $url = parse_url($avatar);
            $url = $url['host'];
            $header = [
                'User-Agent: Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:45.0) Gecko/20100101 Firefox/45.0',
                'Accept-Language: zh-CN,zh;q=0.8,en-US;q=0.5,en;q=0.3',
                'Accept-Encoding: gzip, deflate',
                'Host:' . $url
            ];
            $dir = pathinfo($url);
            $host = $dir['dirname'];
            $refer = $host . '/';
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_REFERER, $refer);
            curl_setopt($curl, CURLOPT_URL, $avatar);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($curl, CURLOPT_ENCODING, 'gzip');
            curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, $timeout);
            curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
            $data = curl_exec($curl);
            $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            curl_close($curl);
            if ($code == 200) return "data:image/jpeg;base64," . base64_encode($data);
            else return false;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * 获取当前域名
     */
    function getDomain(){
        return input('server.REQUEST_SCHEME') . '://' . input('server.SERVER_NAME');
    }


    /**
     * 获取域名第一个域名，用来做三级域名使用。
     */
    function getDomainApp(){
        $domain =  input('server.SERVER_NAME');
        return explode(".",$domain)[0];
    }

    /**
     * 获取当前IP
     */
    function getIp(){
        return request()->ip();
    }

    /**
     * 写入文件
     */
    function writeFile($filename,$content){
        $myfile = fopen($filename, "w") or die("Unable to open file!");
        fwrite($myfile, $content);
        fclose($myfile);
    }

    /**
     * 消息提醒
     * type 为json和html
     * @param string
     * @return bool|string
     */
    function message($results,$resultMsg,$data="",$resultCode='',$type='json'){
        //错误页面
        if($results=="wrong"){
            $this->assign("resultMsg",$resultMsg);
            $this->assign("data",$data);
            $this->appView("wrong");
            exit;
        }
        $result['result'] = $results;
        $result['resultMsg'] = $resultMsg;
        if($data!='') {
            $result['resultData'] = $data;
        }
        if($resultCode!=''){
            $result['resultCode'] = $resultCode;
        }
        if($type=="json") {
            header('Content-type: application/json');
            exit(json_encode($result, JSON_UNESCAPED_UNICODE));
        }elseif($type=="array") {
            return $result;
        }else{
            echo json_encode($result, JSON_UNESCAPED_UNICODE);exit;
        }
    }
    function returnMessage($results,$resultMsg,$data="",$resultCode='',$type='json'){

        $result['result'] = $results;
        $result['resultMsg'] = $resultMsg;
        $result['data']="";
        if($data!='') {
            $result['data'] = $data;
        }
        if($resultCode!=''){
            $result['resultCode'] = $resultCode;
        }
        return $result;
    }
    function errorMessage($msg,$data="",$resultCode='9999',$type='json'){

        $result['result'] = "error";
        $result['resultMsg'] = $msg;
        if($data!='') {
            $result['data'] = $data;
        }
        if($resultCode!=''){
            $result['resultCode'] = $resultCode;
        }
        if($type=="json") {
            header('Content-type: application/json');
            exit(json_encode($result, JSON_UNESCAPED_UNICODE));
        }else{
            return $result;
        }
    }
    function successMessage($resultMsg,$data="",$resultCode='1000',$type='json'){
        $result['result'] = "success";
        $result['resultMsg'] = $resultMsg;
        if($data!='') {
            $result['data'] = $data;
        }
        if($resultCode!=''){
            $result['resultCode'] = $resultCode;
        }
        if($type=="json") {
            header('Content-type: application/json');
            exit(json_encode($result, JSON_UNESCAPED_UNICODE));
        }else{
            return $result;
        }
    }
    function jsonMessage($data){
        header('Content-type: application/json');
        exit(json_encode($data, JSON_UNESCAPED_UNICODE));
    }

    /**
     * 判断手机号
     * @param string $phone
     * @return bool|string
     */
    function isPhone($phone=''){
        if($phone=="") return false;
        $rule = '^1(3|4|5|6|7|8|9)[0-9]\d{8}$^';
        $result = preg_match($rule, $phone);
        if ($result) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * json 格式化
     */
    function jsonFormat($input){
        $input = str_replace("{","{<br style='margin-left: 50px'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;",$input);
        $input = str_replace(",",",<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;",$input);
        $input = str_replace("}","<br>}",$input);
        return $input;
    }

    /**
     * json 格式化
     */
    function jsonDecode($input){
        return json_decode($input,true);
    }

    /**
     * json 格式化
     */
    function jsonEncode($input){
        return json_encode($input,true);
    }

    /**
     * xml 格式化
     */
    function xmlformat($xml){
        $Dom = new \DOMDocument('1.0', 'utf-8');
        $Dom->loadXML($xml);
        $Dom->formatOutput = true;
        return $Dom->saveXml();;
    }


}