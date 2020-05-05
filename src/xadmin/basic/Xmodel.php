<?php

namespace xadmincn\xadmin\basic;

use think\facade\Db;
use think\Model;
use xadmincn\xadmin\traits\CommonTrait;
class Xmodel extends Model
{

    use CommonTrait;

    private static $errorMsg;

    private static $transaction = 0;

    private static $DbInstance = [];

    const DEFAULT_ERROR_MSG = '操作失败,请稍候再试!';
    private function getModule($module){
        if($module==""){
            $class_name = explode('\\',get_called_class());
            $module = $class_name[2];
        }
        return $module;
    }

    function getOne($where,$fields='',$module=''){
        $module = $this->getModule($module);
        return db::name($module)->where($where)->find();
    }

    /**
     * 设置错误信息
     * @param string $errorMsg
     * @return bool
     */
    protected static function setErrorInfo($errorMsg = self::DEFAULT_ERROR_MSG,$rollback = false)
    {
        if($rollback) self::rollbackTrans();
        self::$errorMsg = $errorMsg;
        return false;
    }

    /**
     * 获取错误信息
     * @param string $defaultMsg
     * @return string
     */
    public static function getErrorInfo($defaultMsg = self::DEFAULT_ERROR_MSG)
    {
        return !empty(self::$errorMsg) ? self::$errorMsg : $defaultMsg;
    }

    /**
     * 开启事务
     */
    public static function beginTrans()
    {
        Db::startTrans();
    }

    /**
     * 提交事务
     */
    public static function commitTrans()
    {
        Db::commit();
    }

    /**
     * 关闭事务
     */
    public static function rollbackTrans()
    {
        Db::rollback();
    }

    /**
     * 根据结果提交滚回事务
     * @param $res
     */
    public static function checkTrans($res)
    {
        if($res){
            self::commitTrans();
        }else{
            self::rollbackTrans();
        }
    }

}