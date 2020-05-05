<?php
namespace xadmincn\xform;

class Xbase
{

   function Xform_input($type,$name, $value)
    {
        if(!isset($value['value'])){
            $value['value'] = "";
        }
        $display = "<input";
        //$display .= " autocomplete='off'";
        $display .= " type='{$type}'";
        $display .= " value='{$value['value']}'";
        $display .= " id='xadmin-form-id-{$name}'";

        if($value['type']!="label" && $value['type']!="submit") {
            $display .= " name='{$name}'";
        }

        if(isset($value['placeholder'])) {
            $display .= " placeholder='{$value['placeholder']}'";
        }

        if(isset($value['readonly']) && $value['readonly']==true){
            $display .= ' readonly ';
        }

        if($type=="submit"){
            $display .= ' lay-submit="" lay-filter="Xsubmit" ';
            if($value['value']==""){
                $value['value'] = "提交";
            }
            $display .= " class=\"layui-btn ";
        }else{
            $display .= " class= \"layui-input left ";
        }

        if(isset($value['class'])){
            $display .= $value['class'];
        }
        $display .= '"';

        $display .= " style='";
        if(isset($value['style'])){
            $display .= $value['style'];
        }
        if(isset($value['width']) && $value['width']!=''){
            if(strpos($value['width'],'%') !==false){
                $display .= ';width:'.$value['width'];
            }else{
                $display .= ';width:'.$value['width'].'px';
            }
        }
        if(isset($value['height']) && $value['height']!=''){
            $display .= ';height:'.$value['height'].'px';
        }
        $display .= "' ";
        if (isset($value['rule']['require']) && $value['type']!="image"){
            $display .= ' lay-vertype="tips" lay-skin="switch" lay-verify="required" lay-reqText="'.$value['rule']['require'].'"';
        }


        $display .= ">";
        return $display;
    }

}
