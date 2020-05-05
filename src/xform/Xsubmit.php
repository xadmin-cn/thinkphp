<?php
namespace xadmincn\xform;

//提交

class Xsubmit extends Xbase
{
    function display($name,$value)
    {
       $display ='<button type="submit" class="layui-btn" lay-submit="" lay-filter="Xsubmit">立即提交</button>';
        $display .=' <button type="reset" class="layui-btn layui-btn-primary">重置</button>';
        return $display;
    }

}
