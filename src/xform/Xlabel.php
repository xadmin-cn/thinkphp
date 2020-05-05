<?php
namespace xadmincn\xform;

//显示框

class Xlabel extends Xbase
{
    function display($name,$value)
    {

        $value['readonly'] = true;
        $value['style'] = 'border:none;border-bottom:1px solid #e6e6e6;cursor:default;float:left';
        return $this->Xform_input("text",$name,$value);
    }

}
