<?php
namespace xadmincn\xform;

//单文本框

class Xradio extends Xbase
{
    function display($name,$value)
    {

        $display = "";
        foreach($value['options'] as $k=>$v){
            $display .= "<input";
            $display .= " type='radio'";
            $display .= " autocomplete='off'";
            $display .= " class='left'";
            $display .= " placeholder='{$value['placeholder']}'";
            $display .= " value='{$k}'";
            $display .= " name='{$name}'";
            $display .= " title='{$v}'";
            if($k==$value['value']){
                $display .= " checked=''";
            }
            $display .= ">";
        }
        return $display;
    }

}
