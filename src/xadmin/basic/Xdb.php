<?php
namespace xadmincn\xadmin\basic;

use think\facade\Db;
use think\facade\Config;
use xadmincn\xadmin\traits\CommonTrait;
use xadmincn\xadmin\traits\CryptoTrait;

/**
 * Xdb后台类
 */
 class Xdb
{
    public $db;
    use CommonTrait;
    use CryptoTrait;

    public function __construct()
    {

    }

    /**
     * 实例模型
     * $this->loadModel(模型名);
     * $this->模型名 即该模型对象
     */
    protected function model($model="",$database='',$cache="")
    {
        if($model==""){
            if(isset($this->localModel)){
                $model = $this->localModel;
            }else{
                $model = Request()->controller();
            }
        }

        if($cache=="") {
            if (isset($this->localCache)) {
                $cache = $this->localCache;
            }
        }

        if($database!=""){

           $result =   Db::connect($database)->name($model);
            if($cache!=''){
                $result = $result->cache($cache);
            }
            return $result;
        }else{

            $this->setDatabase($database);
            $result =   Db::name($model);
            if($cache!=''){
                $result = $result->cache($cache);
            }
            return $result;
        }
    }

    function setDatabase($database=''){

        //如果没有选择数据库，则获取local的数据库
       if (isset($this->localDatabase)) {
            Config::set(['default' => $this->localDatabase], 'database');
        }
        //每个app都可用重新连接数据库
        elseif (isset($this->localConnection)) {

            $db = [
                // 数据库类型
                'type' => 'mysql',
                // 服务器地址
                'hostname' => '',
                // 数据库名
                'database' => '',
                // 数据库用户名
                'username' => '',
                // 数据库密码
                'password' => '',
                // 数据库连接端口
                'hostport' => '',
                // 数据库连接参数
                'params' => [],
                // 数据库编码默认采用utf8
                'charset' => 'utf8',
                // 数据库表前缀
                'prefix' => '',
            ];
            if (is_array($this->localConnection)) {
                foreach ($db as $k => $v) {
                    if (isset($this->localConnection[$k])) {
                        $db[$k] = $this->localConnection[$k];
                    }
                }
            } else {
                $config = require(ROOT_PATH . $this->localConnection);
                foreach ($db as $k => $v) {
                    if (isset($config[$k])) {
                        $db[$k] = $config[$k];
                    }
                }
            }
            Config::set(['connections' => [config("database.default") => $db]], 'database');
            Db::connect("mysql", true); // 强制重连
        }
    }



    /**
     * 数据删除
     * dbDelete
     * 返回true和false
     */
    function dbDelete($data){
        $model = "";
        if(isset($data['model'])){
            $model = $data['model'];
        }

        $database = "";
        if(isset($data['database'])){
            $database = $data['database'];
        }

        $result =$this->model($model,$database);

        if(isset($data['where'])){
            $result = $result->where($data['where']);
        }
        if(isset($data['wherein'])){
            foreach ($data['wherein'] as $key => $value) {
                $result = $result->where($key,"in",$value);
            }
        }
        if(isset($this->localDelete) && $this->localDelete==false){
            return $result->update(array("is_delete"=>1));
        }else{
            return $result->delete();
        }

    }
     private function dbWhere($result,$where){
        foreach ($where as $key => $value){
            if(is_array($value)){
               $result = $result->where($key,$value[0],$value[1]);
            }else{
                $result = $result->where($key,$value);
            }
        }
        return $result;
    }
    /**
     * 数据更新
     * dbupdate
     * 返回true和false
     */
    function dbUpdate($data){
        $where = array();

        if(isset($data['where'])){
            $where = $data['where'];
        }
        $model ="";
        if(isset($data['model'])){
            $model = $data['model'];
        }
        $database = '';
        if(isset($data['database'])){
            $database = $data['database'];
        }
        $_data = array();
        $result =$this->model($model,$database);
        foreach ($data['data'] as $key => $value) {
            if (is_array($value)) {
                if ($value[0] == "inc") {
                    $result = $result->inc($key, $value[1]);
                }
                if ($value[0] == "dec") {
                    $result = $result->dec($key, $value[1]);
                }
            } else {
                $_data[$key] = $value;
            }
        }

        $res = $result->where($where)->update($_data);


        return $res;

    }

    /**
     * 单条查询
     * dbFind
     */
    function dbFind($data){
        $where = array();
        if(isset($data['where']) && $data['where']!=""){
            $where = $data['where'];
        }
        $model = "";
        if(isset($data['model']) && $data['model']!=""){
            $model = $data['model'];
        }

        $database = '';
        if(isset($data['database'])){
            $database = $data['database'];
        }
        $result =$this->model($model,$database);

        if(isset($data['join'])) {
            $result = $result->alias("this");


            if(isset($data['join']['this']['field'])){
                $result = $result->field($data['join']['this']['field']);
            }elseif(isset($data['fields']) && $data['fields']!=''){
                $result = $result->field($data['fields']);
            }else{
                $result = $result->field('this.*');
            }
            foreach ($data['join'] as $k =>$v){
                $type = "right";
                if(isset($v['type'])){
                    $type = $v['type'];
                }
                if($k!='this'){
                   $result = $result->Join("{$k} {$v['alias']}",$v['where'],$type)->field($v['field']);
                }
            }
            if (count($where) > 0) {
                foreach ($where as $key =>$value){
                    $_where["this.".$key] = $value;
                }
                $result = $result->where($_where);
            }

        }else {
            if (count($where) > 0) {
                $result = $this->dbWhere($result,$where);
            }
            if(isset($data['fields']) && $data['fields']!=''){
                $result = $result->field($data['fields']);
            }
        }

        $whereor = array();
        if(isset($data['whereor']) && $data['whereor']!=""){
            $whereor = $data['whereor'];
        }
        $result = $result->whereor($whereor);

        if(isset($data['order']) ){
            $result = $result->order($data['order']);
        }
        if(isset($data['orderRaw']) ){
            $result = $result->orderRaw($data['orderRaw']);
        }


        return $result->find();

    }

    /**
     * 列表有分页
     * dbLists
     * 返回
     */
    function dbLists($data = array()){

        $model = "";
        if(isset($data['model'])){
            $model = $data['model'];
        }
        $database = "";
        if(isset($data['database'])){
            $database = $data['database'];
        }
        $list =$this->model($model,$database);

        if(isset($data['leftjoin'])){
            $list = $list->alias("this");
            if(isset($data['leftjoin']['this']['field'])){
                $list = $list->field($data['leftjoin']['this']['field']);
            }else{
                $list = $list->field('this.*');
            }
            foreach ($data['leftjoin'] as $k =>$v){
                if($k!='this'){
                    $name = $k;
                    if(isset($v['alias'])){
                        $name = $k." ".$v['alias'];
                    }
                    $list = $list->leftJoin($name,$v['where'])->field($v['field']);
                }
            }

            if(!isset($data['order']) || $data['order']==''){
                $data['order']['this.id'] = "desc";
            }

        }elseif(isset($data['field'])){


            $list = $list->field($data['field']);
        }

        if(isset($data['order']) ){
            $order =array();
            if(is_array($data['order'])){
                foreach($data['order'] as $key => $value) {
                    $order[$key] = $value;
                }
            }else{
                $order[$data['order']] = "desc";
            }
            $list = $list->order($order);
        }

        //查询
        if(isset($data['where']) ){
            $where =array();
            foreach($data['where'] as $key => $value){
                if(is_array($value)){
                    $list = $list->where($key,$value[0],$value[1]);
                }else{
                    $where[$key] = $value;
                }
            }
            $list = $list->where($where);
        }


        //查询
        if(isset($data['group']) ){
            $list = $list->group($data['group']);
            $count = $list->select();
            $count = count($count);
        }else{
            //统计总的数量
            $count = $list->count();
        }



        //分页
        if(isset($data['limit'])){
            $page = $data['limit'][0];
            $limit = $data['limit'][1];
            $pages = ($page - 1) * $limit;
            $list = $list->limit($pages, $limit);
        }


        $list = $list->select()->toArray();


        $data = array();
        $data['lists'] = $list;
        $data['total'] = $count;
        return $data;
    }

    /**
     * 多条查询
     * dbSelects
     */
    function dbSelects($data){

        $where = array();
        if(isset($data['where'])){
            $where = $data['where'];
        }


        $model = "";
        if(isset($data['model'])){
            $model = $data['model'];
        }

        $database = '';
        if(isset($data['database'])){
            $database = $data['database'];
        }

        $result =$this->model($model,$database);

        if(isset($data['join'])) {
            $result = $result->alias("this");


            if(isset($data['join']['this']['field'])){
                $result = $result->field($data['join']['this']['field']);
            }elseif(isset($data['fields']) && $data['fields']!=''){
                $result = $result->field($data['fields']);
            }else{
                $result = $result->field('this.*');
            }
            foreach ($data['join'] as $k =>$v){
                $type = "right";
                if(isset($v['type'])){
                    $type = $v['type'];
                }
                if($k!='this'){
                    $result = $result->Join("{$k} {$v['alias']}",$v['where'],$type)->field($v['field']);
                }
            }
            if (count($where) > 0) {
                foreach ($where as $key =>$value){
                    $_where["this.".$key] = $value;
                }
                $result = $result->where($_where);
            }

        }else {
            if(isset($data['fields']) && $data['fields']!=''){
                $result = $result->field($data['fields']);
            }
            if (count($where) > 0) {
                $result = $result->where($where);
            }

            //分页
            if(isset($data['limit'])){
                $page = $data['limit'][0];
                $limit = $data['limit'][1];
                $pages = ($page - 1) * $limit;
                $result = $result->limit($pages, $limit);
            }

        }

        if(isset($data['order']) ){
            $result = $result->order($data['order']);
        }
        if(isset($data['cache']) ){
           $result = $result->cache($data['cache']);
        }
        return $result->select();

    }

    /**
     * 多条查询
     * dbFind
     */
    function dbCount($data){
        $model ="";
        if(isset($data['model'])){
            $model = $data['model'];
        }

        $database = '';
        if(isset($data['database'])){
            $database = $data['database'];
        }
        $result =$this->model($model,$database);

        if(isset($data['where']) ){
            $where =array();
            foreach($data['where'] as $key => $value){
                if(is_array($value)){
                    $result = $result->where($key,$value[0],$value[1]);
                }else{
                    $where[$key] = $value;
                }
            }
            $result = $result->where($where);
        }
        return $result->count();

    }

    /**
     * 数据获取
     * dbDelete
     * 返回true和false
     */
    function dbGetone($where='',$fields,$model='',$database=''){

        $result =$this->model($model,$database);

        if($where!=''){
            $result = $result->where($where);
        }
        if($fields!=''){
            $result = $result->fields($fields);
        }
        $result = $result->find();
        if($result) {
            return $this->returnMessage("success", "查询成功", $result);
        }else{
            return $this->returnMessage("error", "数据不存在", '');
        }
    }


    /**
     * 数据添加
     * dbInsert
     * 返回id
     */
    function dbInsert($data,$model='',$database='',$cache=''){

        $result =$this->model($model,$database,$cache);

        return $result->insertGetId($data);

    }
    /**
     * 数据添加
     * dbInsert
     * 返回id
     */
    function dbInsertAll($data,$model='',$database='',$cache=''){

        $result =$this->model($model,$database,$cache);

        return $result->insertAll($data);

    }

     /**
      * 数据更新
      * dbSave
      * 返回id
      */
     function dbSave($data,$model='',$database='',$cache=''){

         $result =$this->model($model,$database,$cache);

         return $result->save($data);

     }

    /**
     * 开启事务
     */
    public  function dbStart()
    {
        Db::startTrans();
    }

    /**
     * 提交事务
     */
    public  function dbCommit()
    {
        Db::commit();
    }

    /**
     * 关闭事务
     */
    public  function dbRollback()
    {
        Db::rollback();

    }

    /**
     * 根据结果提交滚回事务
     * @param $res
     */
    public  function dbTrans($type)
    {
        if($type=='commit'){
            self::commitTrans();
        }else{
            self::rollbackTrans();
        }
    }



    /**
     * sql语句操作
     * dbSql
     * 返回id
     */
    function dbExecute($sql='',$database=''){
        if ($sql=="") return false;
        if($database!=""){
            $result =   Db::connect($database)->execute($sql);
        }else{
            $this->setDatabase($database);
            $result =  Db::execute($sql);
        }
        return $result;

    }
}

