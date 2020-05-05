<?php

declare (strict_types=1);

namespace xadmincn\xadmin\basic;

use think\facade\App;
use think\facade\Db;
use think\facade\View;
use think\facade\Request;
use think\facade\Session;
use think\facade\Cookie;
use think\exception\HttpResponseException;
use think\exception\ValidateException;

/**
 * 控制器基础类
 */
abstract class Xcontroller extends Xdb
{

    /**
     * 数据
     */
    public $appData = [];

    /**
     * 所使用的模板
     */
    public $appTemplate = '';

    /**
     * 所使用的应用类型，有all全部，user用户，admin管理员
     */
    public $appType = 'all';


    /**
     * 构造方法
     * @access public
     * @param App $app 应用对象
     */
    public function __construct()
    {
        /**
         * 后台模块框架
         */
        $this->appTemplate = "Easyweb";
        if(isset($this->xadminConfig['template']) && $this->xadminConfig['template']!=""){
            $this->appTemplate = $this->xadminConfig['template'];
        }

        /**
         * viewForm 表单的显示字段
         */
        $this->appViewform = [];
        if(isset($this->xadminConfig['form']) && $this->xadminConfig['form']!=""){
            $this->appViewform = $this->xadminConfig['form'];
        }


        /**
         *  获取模块，方法
         */
        $app =  App();
        $this->request = $app->request;
        $this->appName =  App('http')->getName();
        $this->appModule =  $this->request->controller();
        $this->appAction = $this->request->action();

        /**
         *  获取数据
         */
        $this->data = input();
        $this->dataPost = input("post.");
        $this->dataGet = input("get.");

        //将获取的数据赋能
        $this->local['appForm'] =$this->appForm ;

        //将获取的数据赋能
        $this->local['request'] =$this->data ;

        //加载如模块
        $this->local['module'] = $this->appModule;
        $this->local['action'] = $this->appAction;

        /**
         *  获取本地的地址
         */
        $url = explode("/",$_REQUEST["s"]);
        $thisUrl = "";
        if($url[1] == $this->appName){
            $thisUrl = "/".$this->appName;
        }
        $this->local['thisUrl'] = $thisUrl."/".$this->appModule."/".$this->local['action'];
        //模块地址
        $this->local['moduleUrl'] = $thisUrl."/".$this->appModule;
        //列表页地址，只要在添加页时使用
        $this->local['listsUrl'] = $this->local['moduleUrl'] . "/lists";

        /*
        $app =  App();
        $this->app = $app;
        $this->request = $this->app->request;
        //$this->request = app('request');

        if(!isset( $this->local['site'])){
        $this->local['site'] = array();
    }

        //$this->templates = "/../../../Xadmin/templates/";
        $this->data = $this->getData();

        $this->module =  $this->request->controller();
        $this->action = $this->request->action();
        $this->appname = App('http')->getName();

        $this->cache = new \think\facade\Cache;

        //分页
        $this->page = isset($this->data['page'])?$this->data['page']:1;
        //列表的url地址
        if(isset($this->data['listUrl'])){
            $this->local['listUrl'] =$this->data['listUrl'];
        }else {
            $this->local['listUrl'] = "/" . $this->request->controller() . "/lists";
        }
        $this->local['requestUrl'] = $_SERVER['REQUEST_URI'];
            //当前的url地址
        $this->local['thisUrl'] = "/".$this->request->controller()."/".$this->action;
        $this->local['module'] =$this->request->controller();
        $this->local['action'] = $this->action;
        $this->local['appname'] = strtolower($this->appname);
        $this->local['request'] =$this->data ;
        $this->local['static'] = '/static/'.strtolower($this->appname);

        $this->local['form'] =array();
        if(isset($this->localForm)) {
            $this->local['form'] = $this->localForm;
        }

        //xadmin参数
       // if(!isset($this->local['xadmin']['template'])) {
         //   $this->local['xadmin']['template'] = "Default";//模板
       // }
       // $local['xadmin']['xadminTemplate'] = "";

        $this->user_id = cookie("user_id");
        $this->admin_id = cookie("admin_id");


        /*
        //检查权限
        $check = new  \xadmincn\xadmin\controller\Power();
        $check_result = $check->check($this->appname,$this->local['module'],$this->local['action']);
        if(!$check_result){
           // $this->local['error']['title'] = "没有权限或者方法没找到";
            //$this->xadminView("error");
            //$this->message("wrong","没有权限或者方法没找到");
           // exit;
        }
        //全局变量过滤
        Request::filter(['htmlspecialchars']);
        */

        parent::__construct();
        //var_dump($this->isPost());
       // $this->initialize();
    }

    /**
     * 数据验证
     */
    function validate($formdata='',$input=''){

        // 通过URL设置默认值
        $data = array();
        $rule = array();
        $message = array();
        if($formdata==""){
            $formdata = $this->local['form'];
        }
        if($input==""){
            $input = $this->data;
        }


        //如果有token验证token是否正确
        if(isset($input['xadmin_token'])){
            if(!$this->checkToken($input['xadmin_token'])){
                $this->message("error","token错误",'','token');
            }
            else{
               $this->deleteCache('xadmin_token');
            }
        }

        foreach ($formdata as $field => $value) {
            if(isset($input[$field])) {
                $data[$field] = $input[$field];
                //如果有需要验证

            }else{
                $data[$field] = '';
            }
            if (isset($value['rule']) && $value['rule'] != "") {
                $r = array();
                $m = "";

                foreach ($value['rule'] as $k => $v) {
                    if ($v != '' && !in_array($value['type'],['label','submit'])) {
                        $r[] = $k;
                        $_k = explode(":", $k);
                        $message[$field . "." . $_k[0]] = $v;
                    }
                }
                if(count($r)>0) {
                    $rule[$field] = join("|", $r);
                }
            }
        }
        //验证器也要加上数据库连接
        if(isset($this->localDatabase)){
            \think\facade\Config::set(['default' => $this->localDatabase], 'database');
            \think\facade\Db::connect($this->localDatabase, true); // 强制重连
        }

        if($rule!="") {
            try {
                 validate()->message($message)->batch(true)->check($data,$rule);
            } catch (ValidateException $e) {
                // 验证失败 输出错误信息
                $error =  explode("\n",$e->getMessage());
                 $this->message("error",$error[0],$error,'validate');
            }
        }


    }

    public function ctypeaornum($string) {

        if (ctype_alnum($string)) {
            return true;
        } else {
            return false;
        }
    }

    function postCheck($input,$appForm=''){
        $data  = array();
        if($appForm=="" ){
            $appForm= $this->appForm;
        }

        foreach ($appForm as $k =>$v){
            if(isset($input[$k])){
                $data[$k] = $input[$k];
            }

            //switch开关
            if($v['type']=="switch"){
                if(isset($v['switchValue']) && $v['switchValue']!=''){
                    $switchValue = explode("|",$v['switchValue']);
                }else{
                    $switchValue = [1,0];
                }

                if(isset($input[$k])){
                    $data[$k]=$switchValue[0];
                }else{
                    $data[$k]=$switchValue[1];
                }
            }
            if($v['type']=="checkbox"){

                if(isset($input[$k])){
                    $data[$k] = json_encode(array_keys($input[$k]));
                }
            }
            if($v['type']=="images"){
                if(isset($input[$k])){
                    $data[$k] = json_encode(array_values($input[$k]));
                }
            }

            //如果是label则不提交数据
            if($v['type']=="label"){
                unset($this->local['form'][$k]);
                unset($data[$k]);;
            }
            //检查密码，如果是修改的话则将必填去掉
            if($v['type']=="password"){
                if($data[$k]!=''){
                    $data[$k] = $this->passwordHash($data[$k]);
                }
                if($this->action=="modify" && $data[$k]==""){
                    unset($data[$k]);
                }
            }

            if($v['type']=="cascader"){
                if(isset($input[$k])){
                    if(!isset($v['model'])){
                        $data['cascader_id'] = $input[$k];
                        $pid= explode(",",$input[$k]);
                        $data['pid'] = $pid[count($pid)-1];
                    }else{
                        $pid= explode(",",$input[$k]);
                        $data[$k] = $pid[count($pid)-1];
                    }
                }

            }

            //cookie
            if(isset($v['cookie']) && $v['cookie']!=""){
                if(isset($input[$k])){
                    $data[$k] = cookie($v['cookie']);
                }
            }

        }

        //如果有其它的数据存在
        if(isset($this->local['data'])){
            $data = array_merge($data,$this->local['data']);
        }
        return $data;
    }



    /**
     * xview模板
     * @param string $view_url
     * @return view
     */
    public function xview($view_url,$templates="Default"){
        $this->local['system']['copyright'] = "";
        $this->assign("local",$this->local);
        return $this->fetch($templates."@/".$view_url);
    }

    /**
     * xapp模板
     * @param string $view_url
     * @return view
     */
    public function xappView($view_url){
        $this->assign("local",$this->local);
        return $this->fetch("../xapp/".$this->module_name."/view@/".$view_url);
    }
    /**
     * xadmin模板
     * @param string $view_url
     * @return view
     */
    public function xadminView($view_url,$templates="Default"){
        $this->local['system']['copyright'] = "";
        $this->assign("local",$this->local);
        return $this->fetch($templates."@/".$view_url);

    }

    /**
     * 获取自定义的field值。
     */
    function getForm($field,$appForm=""){
        $return = array();
        if($appForm!=""){
            $form =$appForm;
        }else{
            $form = $this->appForm;
        }
        foreach($form as $key =>$value){
            if(in_array($key,$field)){
                $return[$key] = $value;
            }
        }
        $this->appForm = $return;
        return $return;
    }

    /**
     * 获取用户id
     */
    function getUserid()
    {
        if ($this->localType == "user") {
            return cookie('user_id');
        }elseif(isset($this->data['user_id'])){
            return $this->data['user_id'];
        }else{
            $this->message("wrong","user_id不存在");
        }
    }

    /**
     * 获取管理员id
     */
    function getAdminid(){
        if ($this->localType == "admin") {
            return $this->getCache('admin_id');
        }elseif(isset($this->data['admin_id'])){
            return $this->data['admin_id'];
        }else{
            //$this->message("error","admin_id不存在");
        }
    }
    /**
     * 获取菜单
     */
    function getMenu($data){

        $res = $this->dbSelects($data);
        $arr = array();
        foreach ($res as $k => $v) {
            $arr[strtolower($v["nid"])] = array("pid" => $v["pid"], "id" => $v['id'], "title" => $v['title'], "url" => $v['url'], "icon" => $v['icon']);
        }

       return $this->_getMenu($arr,0);
    }
    private function _getMenu($data, $parent_id,$pid_data='',$key='pid'){
        $tree = array();
        foreach($data as $k => $v)
        {
            if($v[$key] == $parent_id)
            {
                unset($data[$k]);
                $tre = $this->_getMenu($data, $v['id']) ;
                if( $pid_data!='' ){
                    if($parent_id!=$pid_data){
                        $v['children'] =$tre;
                    }
                }elseif(count($tre)>0){
                    $v['children'] =$tre;
                }
                $tree[$k] = $v;
            }
        }
        return $tree;
    }


    protected function assign(...$vars)
    {
        View::assign(...$vars);
    }
    protected function isPost(...$vars)
    {
        return $this->request->isPost();
    }



    protected function getPost(...$vars)
    {
        if(count(input("post."))==0){
            return false;
        }
        return input("post.");
    }

    protected function getData(...$vars)
    {
        return input();
    }
    protected function fetch(string $template = '')
    {
        echo View::fetch($template);
    }
    protected function display(string $template = '')
    {
        echo View::display($template);
    }
    protected function redirect(...$args){
        throw new HttpResponseException(redirect(...$args));
    }
    //设置缓存
     function setCache($key,$val,$type="cookie",$time='3600'){
        if(isset($this->local['site']['cache'])){
            $type = $this->local['site']['cache'];
        }
        if($type=="cookie"){
            if(is_array($val)){
                $val = json_encode($val);
            }
            Cookie::set($key,$val,$time);
            Cookie::save();
        }else{
            Session::set($key,$val);
            Session::save();
        }
    }
    //获取缓存
     function getCache($key,$type="cookie"){

        if(isset($this->local['site']['cache'])){
            $type = $this->local['site']['cache'];
        }

        if($type=="cookie") {

            if (Cookie::get($key) == "") {
                return '';
            }
            $cache = Cookie::get($key);
            if ($cache == "") {
                return "";
            }
            $cache = htmlspecialchars_decode($cache);
            $_cache = json_decode($cache, true);
            if ($_cache == "") {
                return $cache;
            }
            return $_cache;
        }else if($type=="session"){
            return Session::get($key);
        }else{
            if(Session::get($key)==""){
                return $this->getCache($key,"cookie");
            }else{
                return Session::get($key);
            }
        }
    }
    //获取缓存
    protected function deleteCache($keys,$type="cookie"){
        if(isset($this->local['site']['cache'])){
            $type = $this->local['site']['cache'];
        }
        if($type=="cookie"){
            if(is_array($keys)){
                foreach ($keys as $k =>$v){
                    Cookie::delete($v);
                }
            }else{
                Cookie::delete($keys);
            }
        }else{
            if(is_array($keys)){
                foreach ($keys as $k =>$v){
                    Session::delete($v);
                }
            }else{
                Session::delete($keys);
            }
            Session::save();
        }
    }

     function setToken(){
        $type = "session";
        if( $this->getCache("xadmin_token",$type)=="" ) {
            $xadmin_token = md5(time()."xadmin_token".rand(1000,9999));
            $this->setCache("xadmin_token", $xadmin_token, $type);
            return "<input type='hidden' name='xadmin_token' id='xadmin_token' value='".$xadmin_token."''>";
        }else{
            return "<input type='hidden' name='xadmin_token' id='xadmin_token' value='".$this->getCache("xadmin_token",$type)."'>";
        }
    }

    function checkToken($token=''){
        if( $this->getCache("xadmin_token","session")==$token ) {
            return true;
        }else{
            return false;
        }
    }



    /**
     * 判断local数据是否存在
     */
    protected function localExist($vars,$value='')
    {
        if(!isset($this->local[$vars])) return false;
        if($value!='' && $this->local[$vars]!=$value) return false;
        return true;
    }

    /**
     * 判断data数据是否存在
     */
    protected function dataExist($vars,$value='')
    {
        if(!isset($this->data[$vars])) return false;
        if($value!='' && $this->data[$vars]!=$value) return false;
        return true;
    }

    //获取设置信息
    protected function getSetting(){
        $find = array();
        $find['model']="setting";
        $find['cache']='setting';
        $find['join']['setting_type']['alias'] = "type";
        $find['join']['setting_type']['where'] ="this.type_id=type.id";
        $find['join']['setting_type']['field'] = "type.nid as type_nid";
        $find['join']['setting_type']['type'] = "left";
        $res = $this->dbSelects($find);
        foreach ($res as $key => $value){
            $set[$value['type_nid']][$value['nid']] = $value['value'];
        }
        return $set;
    }

}
