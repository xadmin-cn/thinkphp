<?php
namespace xadmincn\xadmin\controller;

/**
* 权限
*/
class Power
{
    function check($module,$model,$action){
         $data = $this->getClassMethods($module);

        //如果不存在权限则直接提醒
        if(!isset($data[$model][$action])){
            return false;
        }
        else{
            return true;
        }
    }

    function getClassMethods($module){
        $res = $this->getFileList($module);
        $data =array();
        foreach ($res as $k =>$v){
            $data[$v] = $this->getMethods($module,$v);
        }
        return $data;
    }
    function getMethods($module,$class){
        $_class = 'app\\'.$module.'\\controller\\' . $class;
        $r = new \reflectionclass($_class);
        $methods = array();

        foreach($r->getmethods() as $key=>$methodobj){
            if(strtoupper($_class)==strtoupper($methodobj->class) && $methodobj->ispublic() && !in_array($methodobj->name,[]) &&  $methodobj->name!='__construct') {
                $methods[$methodobj->name]['name'] = $methodobj->name;
            }
        }
        return $methods;
    }
    protected function getFileList($module)
    {

        $path = APP_PATH .  DS .$module. DS . 'controller';

        if (is_dir($path) && is_readable($path)) {
            $dirResourse = opendir($path);
            $fileList = [];
            while (($tar = readdir($dirResourse)) !== false) {
                if ($tar == '.' || $tar == '..') {
                    continue;
                }
                if (stripos($tar, '.php') !== false) {
                    $name = substr($tar, 0, -4);
                    if($name!="Base") {
                        $fileList[] = substr($tar, 0, -4);
                    }
                }
            }
            closedir($dirResourse);
            return $fileList;
        } else {
            return [];
        }
    }


}
