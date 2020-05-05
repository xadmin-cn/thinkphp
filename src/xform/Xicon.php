<?php
namespace xadmincn\xform;

//图标选择

class Xicon extends Xbase
{
    function display($name,$value)
    {
        if(isset($value['form']) && $value['form']=="hidden"){
            $type = "hidden";
        }else{
            $type ="text";
        }
        if(!isset($value['width']) || $value['width']==""){
            $value['width'] = 100;
        }
        if(!isset($value['style'])){
            $value['style'] = "";
        }
        $value['style'] .= "margin-right:10px";

        if(!isset($value['class'])){
            $value['class'] = "";
        }
        $value['class'] .= "xadmin-icon-".$name;
        $display = $this->Xform_input($type,$name, $value);
        $display .=  ' <div class="layui-btn-group">';
        $display .=  ' <a href="javascript:void(0);" data="'.$name.'" data-url="'.$this->local['thisUrl'].'/extend_form/icon/iconkey/'.$name.'" class="xadmin-form-icon layui-btn layui-btn-primary">选择</a>';
        $display .= '</div>';

        return $display;
    }

}
