<?php
namespace xadmincn\xform;

//开关选择

class Xswitch extends Xbase
{
    function display($name,$value)
    {
        $display = '<div class="left">';
        $display .= ' <input type="checkbox" class="layui-input left"   autocomplete="off" lay-filter="" lay-skin="switch"';
        $display .= " name='{$name}'";
        $display .= " value='{$value['switchValue']}'";
        $display .= " lay-text='{$value['switchKey']}'";
        $display .= '></div>';
        return $display;
    }

}
