<?php
namespace xadmincn\xform;

//文本框

class Xtextarea extends Xbase
{
    function display($name,$value)
    {
        $display = '<textarea  class="layui-textarea left" ';
        $display .= " class='layui-input left'";
        if(isset($value['placeholder'])) {
            $display .= " placeholder='{$value['placeholder']}'";
        }
        $display .= " name='{$name}'";
        if (isset($value['rule']['require'])){
            $display .= ' lay-vertype="tips" lay-verify="required" lay-reqText="'.$value['rule']['require'].'"';
        }
        $display .= " style='";
        if(isset($value['style'])){
            $display .= $value['style'];
        }
        if(isset($value['width']) && $value['width']!=''){
            $display .= ';width:'.$value['width'].'px';
        }
        if(isset($value['height'])  && $value['height']!=''){
            $display .= ';height:'.$value['height'].'px';
        }
        $display .= "'";
        $display .= ">";
        $display .= $value['value'];
        $display .= '</textarea>';
        return $display;
    }

}
