<?php
namespace xadmincn\xform;

//只读

class Xreadonly extends Xbase
{
    function display($name,$value)
    {
        $value['readonly'] = true;
        return $this->Xform_input("text",$name,$value);
    }

}
