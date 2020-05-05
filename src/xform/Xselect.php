<?php
namespace xadmincn\xform;

//文本框

class Xselect extends Xbase
{
    function display($name,$value)
    {

        $display = '<div ';
        if (isset($value['width']) && $value['width'] != '') {
            if(strpos($value['width'],'%') !==false){
                $display .= ' style="width:'.$value['width'].'"';
            }else{
                $display .= ' style="width:'.$value['width'].'px"';
            }

        }

        $display .= '>';
        $display .= '<select class="left" name="'.$name.'" lay-vertype="tips"  >';
        $display .= '<option value="">请选择</option>';
        if (isset($value['options'])) {
            foreach ($value['options'] as $k => $v) {
                $display .= "<option";
                $display .= " value='{$k}'";
                if ($k == $value['value']) {
                    $display .= " selected=''";
                }
                $display .= ">";
                $display .= $v;

                $display .= "</option>";
            }
        }

        $display .= '</select></div>';
        return $display;
    }

}
