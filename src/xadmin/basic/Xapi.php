<?php
namespace xadmincn\xadmin\basic;


 class Xapi extends Xbase
{

    function localApi($type='lists'){

        call_user_func(array($this,"parent::".$type));
    }
    private function returnMsg($data){
        $res['result'] = "success";
        $res['resultMsg'] = "请求成功";
        $res['resultCode'] = "success";
        $res['resultData'] = $data;
        //$res['resultDigest'] =  $this->priEncrypt($data,"",config("info")['xwpirvateKey']);
        $this->jsonMessage($res);
    }
    function lists(){

        //$this->selectDatabase();
        $data = array();
        $data['model'] = $this->localModel;
        $data['fields'] = isset($this->local['fields'])?$this->local['fields']:"";
        $data['where'] = isset($this->local['where'])?$this->local['where']:"";
        $data['whereor'] = isset($this->local['whereor'])?$this->local['whereor']:"";
        $res = $this->dbLists($data);

        $this->returnMsg($res);
    }

    function create(){
        $data = $this->checkPostdata();
         $res = $this->dbInsert($data,$this->localModel);
         $this->jsonMessage($res);
    }

     function detail(){
         $data = array();
         $data['model'] = $this->localModel;
         $data['fields'] = isset($this->local['fields'])?$this->local['fields']:"";
         $data['where'] = isset($this->local['where'])?$this->local['where']:"";
         $data['whereor'] = isset($this->local['whereor'])?$this->local['whereor']:"";
         $res = $this->dbFind($data);
         $this->returnMsg($res);
     }
    function check(){
        /*
        if(!request()->isPost()){
            return $this->message("error","您的操作有误",'',"99900");
        }
        $input = input("post.");
        $return = array();
        //商户号
        if(!isset($input['merchant']) ){
            return $this->message("error","商户号不能为空",'',"99901");
        }

        //版本号
        if(!isset($input['version']) || $input['version']!="V1.0"){
            return $this->message("error","版本号错误",'',"99903");
        }
        $return['version'] =  $input['version'];

        $member = Db::name("Member")->where("merchant","=",$input['merchant'])->find();
        if(!$member || $member['merchant']=="" ){
            return $this->message("error","商户号不存在",'',"99902");
        }
        $return['merchant'] =  $input['merchant'];

        //商户号状态
        if($member['is_verify']!=1){
            return $this->message("error","此商户已关闭",'',"99904");
        }

        //加密方式
        if(!isset($input['signType']) || $input['signType']!="RSA2"){
            return $this->message("error","目前加密方式不存在",'',"99905");
        }
        $return['signType'] =  $input['signType'];


        //签名
        if(!isset($input['signDigest']) || $input['signDigest']==""){
            return $this->message("error","签名不能为空",'',"99906");
        }

        //数据格式化
        foreach ($input as $k =>$v){
            $input[$k] = htmlspecialchars_decode($v);
        }
        //数据验签
        $public_pem = $member['public_pem'];

        if(!$this->pubDecrypt($input,"",$public_pem,"signDigest")){
            return $this->message("error","签名不正确",'',"99907");
        }

        //白名单
        if($member['white_ip']==''){
            //return $this->message("error","白名单ip为空",'',"99908");
        }else {
            $ips = explode(",", $member['white_ip']);
            $ip = $this->request->ip();
            if (!in_array($ip, $ips)) {
               return $this->error("error","此ip不是白名单ip","99909");
            }
        }
        $input['member_id']=$member['id'];

        $return['result'] =  'success';
        $return['resultCode'] =  '1000';
        $return['resultMsg'] =  '请求成功';

        $this->member = $member;
        $this->return = $return;
        $this->input = $input;
             */
        //每个app都可用重新连接数据库

    }
}

