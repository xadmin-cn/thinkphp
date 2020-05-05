<?php
namespace xadmincn\xadmin\basic;

//基础配置文件
use xadmincn\xadmin\traits\CommonTrait;

//阿里云短信信息
use AlibabaCloud\Client\AlibabaCloud;
use AlibabaCloud\Client\Exception\ClientException;
use AlibabaCloud\Client\Exception\ServerException;
/*
 * 短信接口
 */

class Xsms extends Xdb
{

    use CommonTrait;
    public $key,$secret,$sign,$type,$phone,$template,$code,
        $status=0,
        $sms_tplid=0,
        $sms_id=0,
        $user_id='',
        $order_no='',
        $merchant=''//商户号，saas系统专用
;

    public function __construct()
    {

    }

    /*
     * key :
     * secret:
     * sign
     */
    function smsSet($data=array()){
        $this->key = $data['key'];
        $this->secret = $data['secret'];
        $this->sign = $data['sign'];
        $this->sms_type = $data['smstype'];//发送短信类型
        $this->validate = array();
        if(isset($data['validate'])){
            $this->validate = $data['validate'];///有效期时间->次数
        }
    }

    function smsSend($data){
        $this->phone = $data['phone'];
        $this->code = $data['code'];
        $this->type = $data['type'];

        if(isset($data['template'])){
            $this->template = $data['template'];//非必填
        }
        if(isset($data['order_no'])){
            $this->order_no = $data['order_no'];//非必填
        }
        if(isset($data['merchant'])){
            $this->merchant = $data['merchant'];//非必填
        }
        if(isset($data['user_id'])){
            $this->user_id = $data['user_id'];//非必填
        }
        if(isset($data['sms_id'])){
            $this->sms_id = $data['sms_id'];//非必填
        }
        if(isset($data['sms_tplid'])){
            $this->sms_tplid = $data['sms_tplid'];//非必填
        }

        //判断短信发送次数
        $validate = $this->validate;
        if(isset($data['validate'])){
            $validate = $data['validate'];
        }
        $count = array();
        unset($data['code']);
        unset($data['validate']);
        $count['model'] = "sms_log";
        $count['where'] = $data;
        foreach($validate as $time => $num){
            $count['where']['create_time'] = [">",time()-(int)$time*60];
            $number = $this->dbCount($count);
            $num = (int)$num;
            if($number>=$num){
                $valid[$time] = $num;
                return  $this->message("error",$time."分钟内只能发送".$num."次短信",$valid,"sms_validate_error","array");
            }
        }

        if($this->sms_type=="aliyun"){
           $return = $this->aliyun();
           $this->smsLogadd();
           return $return;
        }
    }

    function smsValidate($data){
        $check = $this->smsCheck($data,['type',"phone","code"]);
        if($check['result']=="error"){
            return $check;
        }

        $find=array();
        $find['where']['type'] = $data['type'];
        $find['where']['phone'] = $data['phone'];
        $find['where']['code'] = $data['code'];

        if(isset($data['template'])){
            $find['where']['template'] = $data['code'];
        }
        if(isset($data['order_no'])){
            $find['where']['order_no'] = $data['order_no'];
        }
        if(isset($data['merchant'])){
            $find['where']['merchant'] = $data['merchant'];
        }
        if(isset($data['user_id'])){
            $find['where']['user_id'] = $data['user_id'];
        }
        if(isset($data['sms_id'])){
            $find['where']['sms_id'] = $data['sms_id'];
        }
        if(isset($data['sms_tplid'])){
            $find['where']['sms_tplid'] = $data['sms_tplid'];
        }

        $find['order']['id'] = 'desc';
        $find['model'] = "sms_log";
        $smslog = $this->dbFind($find);

        if(!$smslog){
            return  $this->message("error","验证码不正确","","sms_code_error","array");
        }
        $validate_time = 10;//有效期时间10分钟
        if(isset($data['time']) && $data['time']!=''){
            $validate_time = $data['time'];
        }
        if($smslog['create_time']<time()-$validate_time*60 || $smslog['is_validate']==1){
            return  $this->message("error","验证码已过期，请重新申请","","sms_time_error","array");
        }

        $upd = array();
        $upd['where']['id']  =$smslog['id'];
        $upd['data']['is_validate']  =1;
        $upd['data']['validate_time'] = time();
        $upd['data']['validate_ip'] = $this->getIp();
        $upd['model'] = "sms_log";
        $this->dbUpdate($upd);
        return  $this->message("success","验证成功","","","array");

    }

    private function smsCheck($data,$field){

        if(in_array("type",$field)){
            if(!isset($data['type']) || $data['type']==""){
                return  $this->message("error","类型不能为空","","sms_type_empty","array");
            }

        }

        if(in_array("phone",$field)){
            if(!isset($data['phone']) || $data['phone']==""){
                return  $this->message("error","手机不能为空","","sms_phone_empty","array");
            }
        }

        if(in_array("code",$field)){
            if(!isset($data['code']) || $data['code']==""){
                return  $this->message("error","验证码不能为空","","sms_code_empty","array");
            }
        }
        return  $this->message("success","验证通过","","sms_code_empty","array");

    }
    private function smsLogadd($data=array()){
        $data['merchant'] = $this->merchant;
        $data['user_id'] = $this->user_id;
        $data['sms_id'] = $this->sms_id;
        $data['sms_tplid'] = $this->sms_tplid;
        $data['order_no'] = $this->order_no;


        $data['phone'] = $this->phone;
        $data['code'] = $this->code;
        $data['type'] = $this->type;
        $data['template'] = $this->template;
        $data['sms_type'] = $this->sms_type;
        $data['status'] = $this->status;
        $data['create_time'] = time();
        $data['create_ip'] = $this->getIp();
        $data['msg'] = $this->smsMsg();
        $this->dbInsert($data,"sms_log");
    }
    /*
     * key :
     * secret:
     * sign
     */
    //阿里云
    private function aliyun(){
        $accessKeyId = $this->key;
        $accessKeySecret  =  $this->secret;
        $SignName =  $this->sign;
        AlibabaCloud::accessKeyClient($accessKeyId, $accessKeySecret)
            ->regionId('cn-hangzhou')
            ->asDefaultClient();
        try {
            //$this->status=1;
            //return  $this->message("success","发送成功","","","array");
            $result = AlibabaCloud::rpc()
                ->product('Dysmsapi')
                // ->scheme('https') // https | http
                ->version('2017-05-25')
                ->action('SendSms')
                ->method('POST')
                ->host('dysmsapi.aliyuncs.com')
                ->options([
                    'query' => [
                        'RegionId' => "cn-hangzhou",
                        'PhoneNumbers' => $this->phone,
                        'SignName' => $this->sign,
                        'TemplateCode' => $this->template,
                        'TemplateParam' => "{code:".$this->code."}",
                    ],
                ])
                ->request();
            $res = $result->toArray();
            if($res['Message']=="OK"){
                $this->status=1;
                return  $this->message("success","发送成功","","","array");
            }else{
                return $this->message("error","发送失败","","","array");
            }
        } catch (ClientException $e) {
            $this->status=2;
            return $this->message("error", $e->getErrorMessage() . PHP_EOL,"","","array");
        } catch (ServerException $e) {
            $this->status=2;
            return $this->message("error", $e->getErrorMessage() . PHP_EOL,"","","array");
        }
    }
    private function smsMsg(){
        if($this->sms_type=="aliyun") {
            try {
                $result = AlibabaCloud::rpc()
                    ->product('Dysmsapi')
                    // ->scheme('https') // https | http
                    ->version('2017-05-25')
                    ->action('QuerySmsTemplate')
                    ->method('POST')
                    ->host('dysmsapi.aliyuncs.com')
                    ->options([
                        'query' => [
                            'RegionId' => "cn-hangzhou",
                            'TemplateCode' => $this->template,
                        ],
                    ])
                    ->request();
               $res = $result->toArray();
               if($res['Message']=='OK'){
                   return str_replace('${code}',$this->code,$res['TemplateContent']);
               }
            } catch (ClientException $e) {
                return $e->getErrorMessage() . PHP_EOL;
            } catch (ServerException $e) {
                return $e->getErrorMessage() . PHP_EOL;
            }
        }
    }

}
