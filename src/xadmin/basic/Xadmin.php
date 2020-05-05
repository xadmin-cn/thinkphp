<?php
namespace xadmincn\xadmin\basic;

use think\facade\Db;
use think\facade\Config;
use think\facade\View;

/**
 *  xadmin框架
 */
class Xadmin extends Xbase
{
    /**
     * 表单信息
     * @var
     */
    public $appForm = [];//表单


    /**
     * 方法里面所使用到的参数
     */
    public $local = [];


    /**
     * 初始化
     */
    public function __construct(){


         parent::__construct();

    }

    /**
     * xadmin应用模板
     * @param string $view_url
     * @return view
     */
    public function appView($view_url){
        Config::set(['view_dir_name'=>''],"view");
        View::assign("local",$this->local);
        echo View::fetch("vendor/xadmincn/thinkphp/src/"."xview/".$this->appTemplate."@/".$view_url);
    }

    /**
     *当前应用模板
     * @param string $view_url
     * @return view
     */
    public function view($view_url){
        Config::set(['view_dir_name'=>'.'],"view");
        View::assign("local",$this->local);
        echo View::fetch($this->appName."/view@/".$view_url);
    }

    /**
     * 模板显示
     */
    public function appAdmin($action=''){
        if($action==""){
            $action = $this->appAction;
        }
        if(!method_exists($this,$action)){
            $this->message("wrong","xadmin下".$action."方法不存在",'','xadmin_method_not_exiest');
        }
        call_user_func(array($this,"parent::".$action));
    }

    /**
     * 列表
     * @power true
     */
    private function lists()
    {
        //获取数据
        $data = input();

        //列表的类型
        if(!isset($this->local['listsType'])){
            $this->local['listsType'] ="lists";
        }

        //list页面开关 操作 $data['action']
        $this->listsSwitch($data);

        //获取数据
        $this->listsData();

        //如果是多级列表
        if($this->localExist("listsType",'tree')){
            if($this->localExist("treeField")){
                $casField['id'] = $this->appForm['id'];
                $casField[$this->local['treeField']]= $this->appForm[$this->local['treeField']];
                unset($this->appForm['id']);
                unset($this->appForm[$this->local['treeField']]);
                $this->appForm = array_merge($casField,$this->appForm);
            }
        }



        //按钮是否显示
        if(isset($this->local['showFalse']) && count($this->local['showFalse'])>0){
            foreach($this->local['showFalse'] as $key =>$value){
                $this->local[$value."Show"]=false;
            }
        }


        //显示字段 cols
        $cols = array();

        if(isset($this->local['numberShow']) && $this->local['numberShow']==true) {
            $cols[] = array('type' => 'numbers');
        }

        //是否多选框
        if(!(isset($this->local['checkboxShow']) && $this->local['checkboxShow']==false)) {
            $cols[] = array('type' => 'checkbox', 'fixed' => 'left');
        }else{
            //tree的格式要显示第一条
            $this->local["treeIndex"]=1;
        }


        $form = array();
        $fields = array();
        foreach ($this->appForm as $k =>$v){
            $form[$k]= $v['name'];
            if(!(isset($v['listsShow']) && $v['listsShow']==false)){
                $fields[] = $k;
            }
        }
        //如果有自定义字段
        if(isset($this->local['fields']) && $this->local['fields']!=""){
            $fields =$this->local['fields'];
        }
        foreach($fields as $k => $v){

            if($this->appForm[$v]['type']=="password"){
                continue;
            }
            $col = array('field'=>$v,'title'=>$form[$v]);
            if(isset($this->appForm[$v]['listsWidth'])){
                $col['width'] = $this->appForm[$v]['listsWidth'];
            }else if(isset($this->local['fieldsWidth'][$v])){
                $col['width'] = $this->local['fieldsWidth'][$v];
                $col['dataoff'] = "true";
            }
            if(isset($this->appForm[$v]['listsAlign'])){
                $col['align'] = $this->appForm[$v]['listsAlign'];
            }else if(isset($this->local['fieldsAlign'][$v])){
                $col['align'] = $this->local['fieldsAlign'][$v];
            }

            if($v=='id'){
                $col['minWidth'] =  50;
                $col['cellMinWidth'] =  50;
                //$ar['fixed'] =  'left';
            }
            //判断是否是用户
            if($this->appForm[$v]['type']=="user" ){
                if( $this->localType!='user') {
                    $cols[] =$col;
                }else{
                    unset($this->local['fields'][$k]);
                    unset($this->appForm[$v]);
                }
            }else{
                $cols[] =$col;
            }


        }
        if(!(isset($this->local['toolShow']) && $this->local['toolShow']==false)) {
            $cols[] = array('toolbar' => '#tableBar','fixed' => 'right',  'title' => '操作', 'align' => 'center',  'width' => isset( $this->local['toolWidth'])? $this->local['toolWidth']:'','minWidth' => isset( $this->local['toolMinwidth'])? $this->local['toolMinwidth']:'');

        }

        if(count($cols)>0){
            $this->local['cols'] = str_replace('"',"'",json_encode([$cols]));
        }else{
            $this->message("wrong","没有找到列表显示的字段");
        }

        //搜索字段  $this->local['search']
        $localwhere = "";
        $search =array();
        if(isset($this->local['search']) && $this->local['search']!=""){
            foreach($this->local['search'] as $k => $v){
                if(isset($this->appForm[$v])){
                    $this->appForm[$v]['value'] = '';
                    if(isset($this->data[$v])){
                        $this->appForm[$v]['value'] = $this->data[$v];
                    }

                    if($this->appForm[$v]['type']=="select" || $this->appForm[$v]['type']=="radio" || $this->appForm[$v]['type']=="checkbox"){
                        if(isset($this->appForm[$v]['foreign'])){
                            $option='';
                            if(isset($this->appForm[$v]['options'])){
                                $option =$this->appForm[$v]['options'];
                            }
                            $options = $this->getForeign($this->appForm[$v]['foreign'],$option);
                            $this->appForm[$v]['options']= $options;
                        }
                    }

                    $search[$v] = $this->appForm[$v];
                }
                //where
                if(isset($this->data[$v]) && $this->data[$v]!=''){
                    $localwhere .="/".$v."/".$this->data[$v];
                }
            }
        }

        $this->local['where'] = $localwhere;
        $this->local['search'] =$search;


        //状态栏
        if(isset($this->local['toolShow']) && $this->local['toolShow']==false){
            $this->local['deleteShow']=false;
            $this->local['modifyShow']=false;
            $this->local['detailShow']=false;
        }
        if(isset($this->local['actionShow']) && $this->local['actionShow']==false){
            $this->local['createShow']=false;
            $this->local['deletesShow']=false;
        }
        if(!(isset($this->local['deleteShow']) && $this->local['deleteShow']==false)){
            $this->local['deleteShow'] = true;
        }
        if(!(isset($this->local['modifyShow']) && $this->local['modifyShow']==false)){
            $this->local['modifyShow'] = true;
        }
        if(!(isset($this->local['detailShow']) && $this->local['detailShow']==false)){
            $this->local['detailShow'] = true;
        }
        if(!(isset($this->local['createShow']) && $this->local['createShow']==false)){
            $this->local['createShow'] = true;
        }
        if(!(isset($this->local['deletesShow']) && $this->local['deletesShow']==false)){
            $this->local['deletesShow'] = true;
        }
        if(!(isset($this->local['parameter']))){
            $this->local['parameter'] = '';
        }
        if((isset($this->local['pageShow']) && !$this->local['pageShow']) || (isset($this->local['limit']) && $this->local['limit']==0)){
            $this->local['pageShow'] = false;
        }else{
            $this->local['pageShow'] = true;
        }


        //设置标题
       $this->setTitle("列表");

        $this->appView("lists");
    }

    /**
     * 添加
     * @power true
     */
    private function create()
    {

        //扩展表单，如上传，级联，关联，图标
        $this->extendForm();

        //表单提交数据操作
        //或者api的数据
        if ($this->isPost() || $this->appType=="api") {

            //添加前的操作
            if(method_exists(new $this,'createBefore')){
                $this->createBefore();
            }

            //获取post过来的数据并检查验证,
            $data = $this->checkPostdata();

            //如果timeip不为false的情况下则将默认添加
            if(!(isset($this->local['timeip']) && $this->local['timeip'] == false)) {
                $data['create_time'] = time();
                $data['create_ip'] = $this->getIp();
            }

            if(isset($this->local['data'])){
                $data = array_merge($data, $this->local['data']);
            }

            $model = "";
            if (isset($this->local['model'])) {
                $model = $this->local['model'];
            }

            $database = "";
            if (isset($this->local['database'])) {
                $database = $this->local['database'];
            }

            $id = $this->dbInsert($data,$model,$database);
            if($id>0) {
                $this->addLog($model, $data,$id);

                //成功添加后的操作
                if(method_exists(new $this,'createAfter')){
                    $this->createAfter($id);
                }

            }

            //成功回调
            $this->message("success","添加成功",$id);
        }

        //可以自定义标题
        $this->setTitle("添加");

        //检查表单
        $this->local['viewForm'] = $this->viewForm();

        //设置模板
        $this->appView("create");
    }

    /**
     * add 单独的添加
     * 用在自定义的模板里面
     */
    private function add(){

        //扩展表单，如上传，级联，关联，图标
        $this->extendForm();

        //获取post过来的数据并检查验证,
        $data = $this->checkPostdata();

        //如果timeip不为false的情况下则将默认添加
        if(!(isset($this->local['timeip']) && $this->local['timeip'] == false)) {
            $data['create_time'] = time();
            $data['create_ip'] = $this->getIp();
        }

        if(isset($this->localType) && $this->localType=="user"){
            $data['user_id'] = $this->getUserid();
        }


        //获取model并加入数据库
        $model = "";
        if (isset($this->local['model'])) {
            $model = $this->local['model'];
        }

        $database = "";
        if (isset($this->local['database'])) {
            $database = $this->local['database'];
        }

        $cache = "";
        if(isset($this->local['cache'])){
            $cache= $this->local['cache'];
        }

        $id = $this->dbInsert($data,$model,$database,$cache);

        if($id>0) {
            $this->addLog($model, $data,$id);
        }

        //成功回调
        $this->message("success","添加成功",$id);
    }


    /**
     * 修改
     * @power true
     */
    private function modify()
    {
        $modify = array();
        //扩展表单，如上传，级联，关联，图标
        $this->extendForm();


        //获取搜索信息
        $modify['where'] = $this->getWhere();
        //获取id
        $modify['where']['id'] = $this->getId();
        if ($this->isPost()) {

            //获取post过来的数据并检查验证,
            $data = $this->checkPostdata();

            //如果timeip不为false的情况下则将默认添加
            if(!(isset($this->local['timeip']) && $this->local['timeip'] == false)) {
                $data['modify_time'] = time();
                $data['modify_ip'] = $this->getIp();
            }
            $modify['data'] = $data;
            //修改数据

            $modify['model'] = $this->getModel();

            $modify['database'] = "";
            if (isset($this->local['database'])) {
                $modify['database'] = $this->local['database'];
            }

            $modify['cache'] = "";
            if(isset($this->local['cache'])){
                $modify['cache']= $this->local['cache'];
            }

            $result = $this->dbUpdate($modify);

            //添加管理员记录
            $this->addLog($modify['model'],$data,$result);

            //返回结果
            $this->message("success","修改成功",$result);
        }

        //可以自定义标题
        $this->setTitle("修改");

        //查找数据
        $result = $this->dbFind($modify);
        if($result==""){
            $this->message("wrong","没有找到相应的数据",$result);
        }
        //检查表单
        $this->local['viewForm'] = $this->viewForm($this->appForm,$result);

        $this->appView("create");
    }


    /**
     * 编辑，判断是否有数据，有则修改，无则添加
     * @power true
     */
    private function edit()
    {
        $edit = array();

        //扩展表单，如上传，级联，关联，图标
        $this->extendForm();

        //获取搜索信息
        $edit['where'] = $this->getWhere();
        if ($this->isPost()) {

            //获取post过来的数据并检查验证,
            $data = $this->checkPostdata();
            $edit['data'] = $data;

            //修改数据

            $edit['model'] = $this->getModel();

            $edit['database'] = "";
            if (isset($this->local['database'])) {
                $edit['database'] = $this->local['database'];
            }
            $res = $this->dbFind($edit);
            if($res) {
                $edit['data']['modify_time'] =  time();
                $edit['data']['modify_ip'] =  $this->getIp();
                $res = $this->dbUpdate($edit);
            }else {
                $data['create_time'] =  time();
                $data['create_ip'] =  $this->getIp();
                $res = $this->dbInsert($data,$edit['model']);
            }
            return $this->message('success',"请求成功","","app_error");;
        }
        return $this->message('error',"您的操作有误","","app_error");;

    }

    /**
     * 单独页
     */
    private function page()
    {

        $this->setTitle("修改");

        //扩展表单，如上传，级联，关联，图标
        $this->extendForm();

        //检查表单
        $this->viewForm();

        //model
        $model = $this->getModel();

        //where 搜索
        $where = $this->getWhere();

        if ($this->isPost()) {

            //获取post过来的数据并检查验证,
            $data = $this->checkPostdata();

            //修改数据
            $result = db::name($model)->where($where)->update($data);

            //添加管理员记录
            $this->addLog($model,$data,$result);

            $this->message("success","修改成功",$result);
        }

        if(isset($this->local['model'])){
            $result = db::name($this->local['model'])->where($where)->find();
            $this->checkData($result,"form");
        }
        if(!isset($this->local['pageUrl'])) {
            $this->local['listUrl'] = $this->local['requestUrl'];
        }else{
            $this->local['listUrl'] = $this->local['pageUrl'];
        }

        $this->setView("create");

    }

    /**
     * 单独页
     */
    private function update()
    {

        $this->setTitle("修改");

        //扩展表单，如上传，级联，关联，图标
        $this->extendForm();

        //检查表单
        $this->viewForm();

        //model
        $model = $this->getModel();

        //where 搜索
        if(isset($this->local['where'])){
            $where = $this->local['where'];
        }
        if ($this->isPost()) {

            //获取post过来的数据并检查验证,
            $data = $this->checkPostdata();

            //修改数据
            $result = db::name($model)->where($where)->update($data);

            //添加管理员记录
            $this->addLog($model,$data,$result);

            //修改的文字
            $msg = "修改成功";
            if(isset($this->local['successMsg'])){
                $msg =$this->local['successMsg'];
            }
            $this->message("success",$msg,$result);
        }

        if(isset($this->local['model'])){
            $result = db::name($this->local['model'])->where($where)->find();
            $this->checkData($result,"form");
        }
        if(!isset($this->local['pageUrl'])) {
            $this->local['listUrl'] = $this->local['requestUrl'];
        }else{
            $this->local['listUrl'] = $this->local['pageUrl'];
        }

        $this->appView("create");
    }

    /**
     * 删除
     * @power true
     */
    private function delete()
    {

        if(method_exists(new $this,'deleteBefore')){
            $this->deleteBefore();
        }

        $this->setTitle("删除");
        $data = $this->getData();

        //where 搜索
        $where = $this->getWhere();


        //如果foreign存在，关联删除,先判断是否有值
        if(isset($this->local['foreign'])) {
            $foreign_model = $this->local['foreign']['model'];
            $foreign_key = $this->local['foreign']['key'];
            //$foreign_value = $this->local['foreign']['value'];
            $where = "";
            if(isset($this->local['foreign']['where'])){
                $where = $this->local['foreign']['where'];
            }
            $message = "有关联的表不能删除。";
            if(isset($this->local['foreign']['message'])){
                $message = $this->local['foreign']['message'];
            }
            $count = db::name($foreign_model)->where($where)->where($foreign_key,"in",$data['id'])->count();
            if($count>0){
                $this->message("error",$message);
            }
        }

        //获取model值
        $this->dbStart();
        $data['where'] = $where;
        $data['model'] = $this->getModel();
        $data['wherein']['id'] = $data['id'];
        $data['database'] = "";
        if (isset($this->local['database'])) {
            $data['database'] = $this->local['database'];
        }
        $result = $this->dbDelete($data);
        if($result){
            //添加管理员记录
            $this->addLog($data['model'],$data,$result);
            //成功添加后的操作
            if(method_exists(new $this,'deleteAfter')){
                $this->deleteAfter();
            }
            $this->dbCommit();
        }else{
            $this->dbRollback();
        }

        $this->message("success","删除成功",$result,"10000");
    }

    /**
     * 详情
     * @power true
     */
    private function detail()
    {
        $this->setTitle("查看");

        //where 搜索
        $where = $this->getWhere();

        //model
        $model = $this->getModel();

        if(isset($this->local['data'])){
            $result = $this->local['data'];
        }else {
            $result = db::name($model)->where($where)->find();
        }

        //成功添加后的操作
        if(method_exists(new $this,'detailAfter')){
            $result = $this->detailAfter($result);
        }

        //api接口
        if($this->appType=="api"){

            if(isset($this->local['returnData'])){
                $result = array_merge($result,$this->local['returnData']);
            }
            $this->message("success","请求成功",$result);
        }

        if(!$result){
            $this->message("wrong","没有找到相应的数据",'','detail_data_empty');
        }

        $this->viewForm("",$result);


        $this->appView("detail");
    }

    /**
     * 模板显示
     */
    public function localView($type=''){
        if($type==""){
            $type = $this->action;
        }
        //xapp应用开发
        if(isset($this->xapp )){
            $class = "\\xapp\\{$this->xapp['app']}\\{$this->xapp['module']}";
            $xapp = new $class($this->xapp['type']);
            $xapp->local = $this->local;
            $xapp->$type();
            exit;
        }
        if(!method_exists($this,$type)){
            $this->message("wrong","xadmin下".$type."方法不存在",'','xadmin_method_not_exiest');
        }
        call_user_func(array($this,"parent::".$type));
    }


}