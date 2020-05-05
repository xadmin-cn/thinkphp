<?php
namespace xadmincn\xadmin\basic;

use think\facade\Db;
use think\facade\Config;
use think\facade\Cache;


/**
 * Xbase后台类
 */
class Xbase extends Xcontroller
{

    /**
     * 列表页开关操作
     */
    protected function listsSwitch($data){
        if (isset($data['extend_form']) && $data['extend_form'] == "switch") {
            if (!isset($data['id'])) {
                $this->message("error", "id不存在");
            }
            if (!isset($data['name'])) {
                $this->message("error", "字段名称不存在");
            }
            if (!isset($this->appForm[$data['name']])) {
                $this->message("error", "字段不存在");
            }
            $switch = $this->appForm[$data['name']];
            if (isset($fv['switchValue'])) {
                $switchValue = explode("|", $switch['switchValue']);
            } else {
                $switchValue = [1, 0];
            }
            $upd['model'] = $this->getModel();
            if ($data['check'] == 'true') {
                $upd['data'][$data['name']] = $switchValue[0];
            } else {
                $upd['data'][$data['name']] = $switchValue[1];
            }
            //where 搜索
            $where = array();
            if (isset($this->local['where'])) {
                $where = $this->local['where'];
            }
            if (isset($this->localDelete) && $this->localDelete == false) {
                $where['is_delete'] = 0;
            }
            if ($this->appType == "user") {
                $where['user_id'] = $this->getUserid();
            } elseif ($this->appType == 'admin') {
                $where['admin_id'] = $this->getAdminid();
            }
            $upd['where'] = $where;

            $upd['where']['id'] = $data['id'];
            $result = $this->dbUpdate($upd);
            $this->message("success", "修改成功", $result);
        }
    }
    /**
     * 列表获取数据
     */
    protected function listsData(){
        //如果是api接口，则直接获取数据
        if ((isset($this->data['extend_form']) && $this->data['extend_form'] == "data") ||  $this->appType=="api") {
            //字段过滤
            //去掉submit
            $this->viewForm();
            //列表要显示的字段
            $fields = $this->listsField();

            $where = $this->listsWhere();
            $res = $this->listsResult($fields,$where);

            //如果是api接口，则直接获取数据
            if ($this->appType=="api"){
                $this->message("success","请求成功",$res);
            }else{
                $res = $this->listsCheck($fields,$res);
                $res['code'] = 0;
                echo json_encode($res);
                exit;
            }
        }
    }

    private function listsField(){
        $fields = array();
        //获取全部的字段，如果有值则直接读取值
        foreach ($this->appForm as $k =>$v){
            if(!(isset($v['listsShow']) && $v['listsShow']!="hide")){
                $fields[] = $k;
            }
        }
        //如果有自定义字段
        if(isset($this->local['fields']) && $this->local['fields']!=""){
            $fields =$this->local['fields'];
        }
        return $fields;
    }

    private function listsWhere(){

        //字段搜素
        $where =array();
        if(isset($this->local['where'])){
            $where = $this->local['where'];
        }
        //如果是用户的则自动获取xadmin类型的id
        if( $this->appType=='user') {
            $where['user_id'] = $this->getUserid();
        }elseif( $this->appType=='admin') {
            $where['admin_id'] = $this->getAdminid();
        }
        //假删除 $this->localDelete
        if(isset($this->localDelete)  && $this->localDelete==false){
            $where['is_delete'] = 0;
        }
        //搜索字段  $this->local['search']
        if(isset($this->local['search']) && $this->local['search']!=""){
            foreach($this->local['search'] as $k => $v){
                if(isset($this->data[$v]) && $this->data[$v]!=''){
                    $where[$v] = $this->data[$v];
                }
            }
        }
        return $where;
    }

    private function listsResult($fields,$where){
        if (isset($this->local['listsData'])) {
            $_result = $this->local['listsData'];//一定要包含data和count
            $count = $_result['count'];
            $result = $_result['data'];
        } else {
            $page = 1;
            if (isset($this->data['page']) && $this->data['page'] != '') {
                $page = $this->data['page'];
            }

            $model = $this->getModel();

            //排序
            if (isset($this->local['order'])) {
                $order = $this->local['order'];
            } else {
                $order = "id desc";
            }

            $limit = 10;
            if (isset($data['limit']) && $this->data['limit'] != '') {
                $limit = $this->data['limit'];
            }

            if($this->local['listsType']=='tree'){
                $result = Db::name($model)->where($where)->order("sort_order desc,id asc")->select()->toArray();
            }
            //如果limit为0的情况下则读取全部
            else if(isset($this->local['limit']) && $this->local['limit']==0){
                $result = Db::name($model)->where($where)->field($fields)->order($order)->select()->toArray();
            }
            else {
                $pages = ($page - 1) * $limit;
                $result = Db::name($model)->where($where)->field($fields)->limit($pages, $limit)->order($order)->select()->toArray();
            }

            $count = Db::name($model)->where($where)->count();
        }
        return array("count"=>$count,"data"=>$result);
    }

    private function listsForeign($result){
        $foreign = array();
        foreach($this->appForm as $k => $v) {
            if (isset($v['foreign'])) {
                if(!is_array( $v['foreign'])){
                    $v['foreign'] = json_decode(urldecode($v['foreign']),true);
                }
                $foreign[$k] = $v['foreign'];
            }
        }

        //判断是否外接
        $foreign_result = array();
        if( count($foreign)>0){
            foreach ($foreign as $k =>$v){
                //关键字
                if(is_array($v['key'])){
                    $v['key'] = array_keys($v['key'])[0];
                }
                //值
                if(is_array($v['value'])){
                    $v['value'] = array_keys($v['value'])[0];
                }
                $foreign_res = array();
                foreach($result as $_k=>$_v){
                    $foreign_res[$_v[$k]] = $_v[$k];
                }
                if (count($foreign_res)>0) {
                    //where 搜索
                    $where = array();
                    if(isset($v['where'])){
                        $where =$v['where'];
                    }
                    $foreign_id = join(",", $foreign_res);
                    $ress = db::name($v['model'])->where($where)->where($v['value'], "in", $foreign_id)->field($v['key'] . "," . $v['value'])->select()->toArray();
                    foreach ($ress as $__k => $__v) {
                        $foreign_result[$k][$__v[$v['value']]] = $__v[$v['key']];
                    }
                }
            }
        }

        return $foreign_result;
    }
    private function listsCheck($fields,$result){

        if(isset($this->local['form'])){
            $this->appForm = $this->local['form'];
        }

        //判断是否外接
        $foreign_result = $this->listsForeign($result['data']);

        foreach ($result['data'] as $k => $v) {
            foreach ($v as $key => $value){

                //form存在
                if(isset($this->appForm[$key])){
                    $form = $this->appForm[$key];

                    //级联
                    if (isset($form['foreign'])) {
                        if (isset($foreign_result[$key]) && isset($foreign_result[$key][$value])) {

                            if(isset($form['foreign']['name']) && $form['foreign']['name']!=''){
                                $v[$form['foreign']['name']] = $foreign_result[$key][$value];

                            }else {
                                $value = $foreign_result[$key][$value];
                            }
                        }
                    }

                    //多选表单
                    else if ($form['type'] == "select" || $form['type'] == "radio" || $form['type'] == "checkbox") {
                        if (isset($form["options"][$value])) {
                            $value = $form["options"][$value];
                        }
                    }
                    //单个图片
                    else if ($form['type'] == "image") {
                        $value = "<a href='{$value}' target='_blank'><img src='{$value}' height=100></a>";
                    }
                    //图标
                    else if ($form['type'] == "icon") {
                        $value = "<i class='{$value}' style='line-height: 28px' ></i>";
                    }
                    //时间格式
                    elseif ($form['type'] == "time") {
                        if (!isset($form['format'])) {
                            $format = "Y-m-d H:i:s";
                        } else {
                            $format = $form['format'];
                        }
                        $value = date($format,$value);
                    }
                    //switch格式
                    elseif($form['type']=="switch"){

                        if(!isset($form['switchKey'])){
                            $form['switchKey'] = "是|否";
                        }
                        $switchKey = explode("|",$form['switchKey']);

                        if(isset($form['switchValue'])){
                            $switchValue = explode("|",$form['switchValue']);
                        }else{
                            $switchValue = [1,0];
                        }
                        if(isset($form['listsSet']) && $form['listsSet']==true){
                            $check='';
                            if($value==$switchValue[0]){
                                $check = "checked";
                            }
                            $value ='<input type="checkbox" name="'.$key.'" '.$check.' lay-filter="switch" data-id="'.$value.'" lay-skin="switch" lay-text="'.$form['switchKey'].'"  value="'. $v['id'].'">';
                        }else {
                            $key = array_search($value,  $switchValue);
                            $value = $switchKey[$key];
                        }
                    }

                    $v[$key] = $value;
                }
            }
            $result['data'][$k] = $v;
        }
        return $result;

    }

    /**
     * 设置标题
     * @param string $view_url
     * @return view
     */
    protected function setTitle($title="列表"){
        if(isset($this->localTitle) && $this->localTitle!="") {
            $title = $this->localTitle.$title;
        }
        if(!isset($this->local['title']) || $this->local['title']=="") {
            $this->local['title'] =$title;
        }else{
            $GLOBALS["title"][strtolower($this->appModule).strtolower($this->appAction)] = $this->local['title'];
        }

    }
    /**
     * 设置模板
     */
    protected function setView($view="",$type=""){
        if($type!=""){
            if($type=="app"){
                $this->appView($view);
            }
            elseif($type=="xapp"){
                $this->xappView($view);
            }
            elseif($type=="xadmin"){
                $this->xadminView($view);
            }
            elseif($type=="xview"){
                $this->xview($view);
            }
            elseif($type=="view"){
                $this->view($view);
            }
        }else {
            if (isset($this->local['view']) && $this->local['view'] != '') {
                $this->view($this->local['view']);

            } elseif (isset($this->local['xview']) && $this->local['xview'] != '') {
                $this->xview($this->local['xview']);
            } else if (isset($this->local['appView'])) {
                $this->appView($this->local['appView']);
            } else if (isset($this->local['xappView']) && $this->local['xappView'] != '') {
                $this->xappView($this->local['xappView']);
            } elseif (isset($this->local['xadminView'])) {
                $this->xadminView($this->local['xadminView']);
            } else {
                $this->view($view);
            }
        }
    }

    protected function setConfig($obj="",$data=""){
        Config::set(['default' => 'dev'], 'database');
        Db::connect("dev",true); // 强制重连
    }


    /**
     * 获取post过来的数据
     * 如果data有值，则将用data的值
     */
    protected function getPostdata(){
        $data = $this->getPost();

        if(isset($this->local['data'])){
            $data = array_merge($data,$this->local['data']);
        }
        if(isset($this->postData)){
            $data = array_merge($data,$this->postData);
        }
        return $data;
    }

    /**
     * 获取模块
     */
    protected function getModel(){
        //获取model值
        if (isset($this->local['model'])) {
            $model = $this->local['model'];
        } else if (isset($this->localModel)){
            $model = $this->localModel;
        }else {
            $model = $this->appModule;
        }
        return $model;
    }
    /**
     * 获取搜索信息
     */
     function getWhere(){
        //where 搜索
        if(isset($this->local['where'])){
            $where = $this->local['where'];
        }
        $data = $this->getData();

        if(isset($this->local['idName']) && $this->local['idName']!=""){
            $idName = $this->local['idName'];
            $where[$idName] = $data[$idName];
        }else{
            $where['id'] = $data['id'];;
        }

        if($this->appType=="user"){
            $where['user_id'] = $this->getUserid();
        }elseif( $this->appType=='admin') {
            $where['admin_id'] = $this->getAdminid();
        }
        if(isset($this->localDelete) && $this->localDelete==false){
            $where['is_delete'] = 0;
        }
        return $where;
    }

    /**
     * 获取id，在modify和detail处可用
     */
    protected function getId(){
        if(isset($this->local['idName']) && $this->local['idName']!=""){
            $idName = $this->local['idName'];
        }else{
            $idName ="id";
        }
        if(!(isset($this->data[$idName]) && $this->data[$idName]!='')){
            $this->message("wrong","你所请求的id不能为空");
        }
        return $this->data[$idName];
    }


    /**
     * 添加操作记录，如果是用户的话则加入
     */
    protected function addLog($model,$datas,$result)
    {
        $data = array();
        $admin_info = $this->getCache("admin_info","session");
        $data['admin_id'] =$admin_info['id'];

        $auser_info = $this->getCache("user_info","session");
        $data['user_id'] =$auser_info['id'];

        $data['username'] =$admin_info['username'];
        $data['phone'] =$admin_info['phone'];
        $data['realname'] =$admin_info['realname'];
        $data['email'] =$admin_info['email'];
        $data['app'] =$this->appName;
        $data['action'] =$this->appAction;
        $data['module'] =$this->appModule;
        $data['model'] =$model;

        $data['url'] =$_SERVER['REQUEST_URI'];
        $data['data'] =json_encode($datas,JSON_UNESCAPED_UNICODE );

        $data['create_time'] = time();
        $data['create_ip'] =$this->getIp();

        $this->dbInsert($data,"operate_log");
    }
    /**
     * 用户加入日志记录
     */
    private function addUserlog($model,$datas)
    {
        $data = array();
        $auser_info = $this->getCache("user_info","session");
        return ;
        $data['user_id'] =$auser_info['id'];
        $data['username'] =$auser_info['username'];
        $data['phone'] =$auser_info['phone'];
        $data['realname'] =$auser_info['realname'];
        $data['email'] =$auser_info['email'];
        $data['action'] =$this->appAction;
        $data['module'] =$this->appModule;
        $data['model'] =$model;

        $data['url'] =$_SERVER['REQUEST_URI'];
        $data['data'] =json_encode($datas,JSON_UNESCAPED_UNICODE );

        $data['addtime'] = time();
        $data['addip'] =$this->getIp();

        $this->dbInsert($data,"admin_log");
    }




    /**
     * 表单扩展
     */
    protected function extendForm(){

        $this->extendUpload();//加入上传操作
        $this->extendCascader();//加入级联操作
        $this->extendAssoc();//多个选择
        $this->extendIcon();//图标
    }
    /**
     * icon图标
     */
    private function extendIcon(){
        if(isset($this->data['extend_form']) && $this->data['extend_form'] =="icon") {
            $return = Cache::get('awesome');
            if (empty($return)) {
                $url  = "http://code.zoomla.cn/boot/font.html";
                $content = $this->curl($url);
                preg_match_all('/<i\s+class="fa\s+([^"]+)"\s+aria-hidden="true">/is', $content, $icons);
                $return = $icons[1] ? $icons[1] : [];
                Cache::set('awesome', $return, 2592000);
            }
            $this->assign("data",$return);
            $this->view("icon");
            exit;
        }
    }
    /**
     * assoc选择
     */
    private function extendAssoc(){
        if(isset($this->data['extend_form']) && $this->data['extend_form'] =="assoc") {
            if (!(isset($this->data['assocId']) && $this->data['assocId'] != '')) {
                $this->message("wrong", "你的操作有误");
            }
            $assoc = json_decode(urldecode($this->data['foreign']), true);
            if ($assoc['model'] == "user") {
                unset($this->local['where']['user_id']);
            }
            $this->local['url'] = $_SERVER['REQUEST_URI'];

            $data['limit'] = 7;
            if (isset($this->local['limit'])) {
                $data['limit'] = $data['limit'];
            }
            $this->local['limit'] = $data['limit'];

            if (isset($this->data['action']) && $this->data['action'] == "data") {
                if (isset($this->local['where'])) {
                    $data['where'] = $this->local['where'];
                }
                if ($this->appType == "user") {
                    $data['user_id'] = $this->getUserid();
                } elseif ($this->appType == 'admin') {
                    $data['admin_id'] = $this->getAdminid();
                }

                if (isset($this->data['keywords']) && $this->data['keywords'] != '') {
                    $data['where'][] = [array_keys($assoc['value'])[0], 'like', "%" . $this->data['keywords'] . "%"];
                }
                $data['fields'] = [array_keys($assoc['key'])[0], array_keys($assoc['value'])[0]];
                $data['model'] = $assoc['model'];
                $result = $this->dbLists($data);
                $res['count'] = $result['data']['total'];
                $res['data'] = $result['data']['list'];
                $res['code'] = 0;
                $this->jsonMessage($res);
            }


            $this->local['key']['k'] = array_keys($assoc['key'])[0];
            $this->local['key']['v'] = array_values($assoc['key'])[0];
            $this->local['value']['k'] = array_keys($assoc['value'])[0];
            $this->local['value']['v'] = array_values($assoc['value'])[0];

            $this->view("assoc");
            exit;
        }
    }
    /**
     * 上传
     */
    private function extendUpload(){
        if (isset($this->data['extend_form']) && $this->data['extend_form'] == "upload") {
            if (isset($this->data['type']) && $this->data['type'] == "image") {
                $upload = new \xadmin\upload\upload;
                $this->data['filePath'] = $this->appname . "/" . $this->appModule;

                //type如果存在且等于ueditor
                if (isset($this->data['upload_type'])) {
                    if ($this->data['upload_type'] == "ueditor") {
                        $ueditor = new \xadmin\upload\ueditor\ueditor();
                        return $ueditor->index($this->data);
                        exit;
                    }
                } else {
                    $this->data['upload_type'] = "image";
                }

                if (isset($this->data['action']) && $this->data['action'] == "delete") {
                    return $upload->delete($this->data);
                } else {
                    $data = array();
                    if (isset($this->data['upload_name']) && isset($this->appForm[$this->data['upload_name']])) {
                        $data = $this->appForm[$this->data['upload_name']];
                    }
                    $data['filePath'] = $this->appname;
                    $data['type'] = $this->data['upload_type'];

                    if ($this->appType == "user") {
                        $data['user_id'] = $this->getUserid();
                    } elseif ($this->appType == 'admin') {
                        $data['admin_id'] = $this->getAdminid();
                    }
                    $result = $upload->upload($data);
                    $result['data']['name'] = $this->data['name'];
                    if (isset($this->data['editor_type']) && $this->data['editor_type'] == "layui") {
                        $res = array();
                        $res['code'] = 0;
                        $res['msg'] = '上传成功';
                        $res['data']['src'] = $result['data']['url'];
                        return $this->jsonMessage($res);
                    }
                    return $this->jsonMessage($result);
                }
            }
        }
    }
    /**
     * cascader 级联操作
     */
    private function extendCascader(){

        if(isset($this->data['extend_form']) && $this->data['extend_form'] =="cascader"){
            $foreign = '';
            if(isset($this->local['form'][$this->data['formkey']])){
                $foreign = $this->local['form'][$this->data['formkey']];
            }
            $model = $this->getModel();
            if(isset($foreign['model'])){
                $model = $foreign['model'];
            }else{
                $model = $this->getModel();
            }

            $result = Db::name($model);

            $pid_data =0;

            if(isset($this->data['value']) && $this->data['value']!=''){
                $parent_id= explode(",",$this->data['value']);
                $pid_data = $parent_id[count($parent_id)-1];
            }

            if(isset($this->data['cascaderid']) && $this->data['cascaderid']>0){
                $result = $result->where("pid","<>",$this->data['cascaderid']);
            }

            $result = $result->select();

            $parent_id = array();
            if(isset($this->data['formkey']) && isset($this->appForm[$this->data['formkey']]['cascaderField'])){
                $key = $this->appForm[$this->data['formkey']]['cascaderField'];
            }else{
                $key = "pid";
            }

            $arrs[] = array("value"=>"0","label"=>"根目录");
            if(count($result)>0) {
                foreach ($result as $k => $v) {
                    $parent_id = explode(",",$v[$key]);
                    $v[$key] = $parent_id[count($parent_id)-1];
                    $arr[$k] = array($key => $v[$key], "value" => $v['id'], "label" => $v['title']);
                }
                $arr = $this->_cascaderGetTree($arr, 0,$pid_data);
                if (count($arr) > 0) {
                    $arrs = array_merge($arrs, $arr);
                }
            }

            return $this->message("success","查询成功",$arrs);
        }
    }
    private function _cascaderGetTree($data, $parent_id,$pid_data='',$key='pid'){
        $tree = array();
        foreach($data as $k => $v)
        {
            if($v[$key] == $parent_id)
            {
                unset($data[$k]);
                $tre = $this->_cascaderGetTree($data, $v['value'],$pid_data,$key) ;
                if( $pid_data!='' ){
                    if($parent_id!=$pid_data){
                        $v['children'] =$tre;
                    }
                }elseif(count($tre)>0){
                    $v['children'] =$tre;
                }
                $tree[] = $v;
            }
        }
        return $tree;
    }


    /**
     * 检查每一个表单，每一次添加或修改的时候都需要检查表单。也就是有操作的情况下都要对表单做一个检测、modify create
     */
    function viewForm($appForm='',$result=""){
        if($appForm==""){
            $appForm= $this->appForm;
        }

        //检查
        if(!isset($appForm)){
            $this->message("wrong","没有找到相应的表单",'','form_error');
        }

        foreach ($appForm as $k =>$v){
            //公共参数检查

            //名称必填，可以填为空，但是name字段必须存在
            if(!isset($v['name'])){
                $this->message("wrong",$k."的【name】名称不存在",'','form_name_error');
            }

            //表单类型必填，可以填为空，但是type字段必须存在
            if(!isset($v['type']) || $v['type']==""){
                $this->message("wrong",$k."的【type】名称不存在",'','form_type_error');
            }

            //value 表单要显示的值
            if(!isset($v['value'])){
                $v['value'] = "";
            }

            //rule权限
            if(!isset($v['rule'])){
                $v['rule'] = "";
            }


            //备注信息
            if(!isset($v['remark'])){
                $v['remark'] = "";
            }

            //提醒信息
            if(!isset($v['info'])){
                $v['info'] = "";
            }

            //宽度
            if(!isset($v['width'])){
                $v['width'] = "";
            }
            //height
            if(!isset($v['height'])){
                $v['height'] = "";
            }

            //listsShow
            if(!(isset($v['listsShow']) && $v['listsShow']==false)){
                $v['listsShow'] = true;
            }

            //createShow
            if(!(isset($v['createShow']) && $v['createShow']==false)){
                $v['createShow'] = true;
            }

            //modifyShow
            if(!(isset($v['modifyShow']) && $v['modifyShow']==false)){
                $v['modifyShow'] = true;
            }

            //detailShow
            if(!(isset($v['detailShow']) && $v['detailShow']==false)){
                $v['detailShow'] = true;
            }

            //表单里面显示名字
            if(!isset($v['placeholder'])){
                $v['placeholder'] = "";
            }

            //检查密码，如果是修改的话则将必填去掉
            if($v['type']=="password"){
                if($this->appAction=="modify"){
                    unset($appForm[$k]['rule']['require']);
                }
            }

            //开关
            elseif($v['type']=="switch"){
                if(!isset($v['options'])){
                    $switchKey = "是|否";
                    $switchValue = 1;
                }else{
                    $switch = array_values($v['options']);
                    $switchValue = array_keys($v['options'])[0];
                    $switchKey = join("|",$switch);
                }

                $v['switchKey'] = $switchKey;
                $v['switchValue'] = $switchValue;
            }

            //checkbox
            elseif($v['type']=="checkbox"){
                $v['checkboxValue'] = array();
                if($v['value']!=""){
                    $v['checkboxValue'] = explode(",",$v['value']);
                }
            }

            //单图片上传
            elseif( $v['type']=="image" || $v['type']=="images" || $v['type']=="editor"){
                if(!isset($v['file']['size'])){
                    $v['fileSize'] = 2;
                }else{
                    $v['fileSize'] = $v['file']['size'];
                }
                if(!isset($v['file']['ext'])){
                    $v['fileExt'] = 'jpg,png,gif';
                }else{
                    $v['fileExt'] = $v['file']['ext'];
                }
            }

            //单图片上传
            elseif( $v['type']=="images"){
                if(!isset($v['file']['num'])){
                    $v['fileNum'] = 5;
                }else{
                    $v['fileNum'] = $v['file']['num'];
                }
            }

            //级联选择
            elseif($v['type']=="assoc"){
                $v['foreign'] = urlencode(json_encode($v['foreign']));
            }


            //如果是用户类型
            elseif($v['type']=="user"){
                if($this->appType=="user" ){
                    unset($appForm[$k]);
                }else {
                    if($this->appAction=="create"){
                        $v['type'] = "assoc";
                    }
                    $v['foreign'] = urlencode(json_encode(['model' => 'user', 'key' => ['id' => "ID"], 'field' => ['username' => '用户名']]));
                }
            }


            //单独的如果有foreign信息，则第一时间进行读取操作。。
            if(isset($v['foreign'])){
                $options = $this->getForeign($appForm[$k]['foreign']);
                if($options) {
                    $v['options'] = $options;
                }
            }

            if($result!="" ){
                if( isset($result[$k]) && ($result[$k]!='' || $result[$k]==0)) {
                    $value = $result[$k];
                    //如果是密码的话则明细不显示，修改为空
                    if ($v['type'] == "password") {
                        if ($this->appAction == "detail") {
                            unset($appForm[$k]);
                        } else if ($this->appAction == "modify") {
                            $value = '';
                        }
                    } //级联
                    else if ($v['type'] == "cascader") {
                        $appForm[$k]['cascader_id'] = $result['cascader_id'];
                    }

                    //数据格式
                    if ($value != '') {
                        if (!is_numeric($value)) {
                            $value = htmlspecialchars_decode($value);
                        }
                    }
                    $v['value'] = $value;
                }
            }

             $appForm[$k] = $v;

        }

        unset($appForm["create_time"]);
        unset($appForm["create_ip"]);
        unset($appForm["modify_time"]);
        unset($appForm["modify_ip"]);

        //重新定义表单字段
        $this->appForm = $appForm;
        $this->local['appForm'] = $this->appForm ;
        $xform = new \xadmincn\xform\Xform();
        return $xform->display( $this->appForm ,$this->appViewform,$this->appTemplate,$this->local['listsUrl']);
    }

    //前面获取关联数据
    protected function getForeign($foreign){
        if(isset($foreign)){
            $find = array();
            if(!(isset($foreign['model']) && $foreign['model']!='')){
                $foreign['model'] = $this->getModel();
            }
            $find['model'] = $foreign['model'];
            $foreign_key= $foreign['key'];
            $foreign_value= $foreign['value'];
            $foreign_field="";
            if(isset($foreign['type']) && $foreign['type']=="assoc"){
                $foreign_field .= ",pid";
            }
            if(isset($foreign['field'])){
                $foreign_field .= ",".$foreign['field'];
            }

            if(is_array($foreign_key)){
                $foreign_key = array_keys($foreign_key)[0];
            }if(is_array($foreign_value)){
                $foreign_value = array_keys($foreign_value)[0];
            }
            $find['field'] =$foreign_key.",".$foreign_value.$foreign_field;

            if(isset($foreign['where'])){
                $find['where'] = $foreign['where'];
            }
            $res = $this->dbLists($find);
            $option = array();
            if($res['total']>0){
                //获取关联值
                if(isset($foreign['type']) && $foreign['type']=="assoc"){
                    foreach ($res['lists'] as $_k => $_v) {
                        if($_v['pid']==0){
                            $option[$_v[$foreign_value]]['data'] = $_v;
                        }else{
                            $option[$_v['pid']]['options'][] = $_v;
                        }
                    }
                }else {
                    foreach ($res['lists'] as $_k => $_v) {
                        $option[$_v[$foreign_value]] = $_v[$foreign_key];
                    }
                }
            }else{
               return false;
            }
            return  $option;
        }
    }



    /**
     * 检查获取过来的数据
     */
    protected function checkPostdata(){
        $data = $this->getPostdata();

        //验证数据
        $this->validate($this->appForm,$data);

        $data = $this->postCheck($data,$this->appForm);

        unset($data['id']);

        if(isset($this->appType) && $this->appType=="user"){
            $data['user_id'] = $this->getUserid();
        }elseif( isset($this->appType) && $this->appType=='admin') {
            $data['admin_id'] = $this->getAdminid();
        }
        $this->local['data'] = $data;

        return $data;
    }

}
