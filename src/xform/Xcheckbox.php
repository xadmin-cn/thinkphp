<?php
namespace xadmincn\xform;

//文本框

class Xtext extends Xbase
{
    function display($name,$value)
    {

        return $this->Xform_input("text",$name,$value);
    }

}
