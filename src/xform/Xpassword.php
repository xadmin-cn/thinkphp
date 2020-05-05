<?php
namespace xadmincn\xform;

//密码框

class Xpassword extends Xbase
{
    function display($name,$value)
    {
        return $this->Xform_input("password",$name,$value);
    }

}
