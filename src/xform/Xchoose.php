<?php
namespace xadmincn\xform;

//多选择框

class Xchoose extends Xbase
{
    function display($name,$value)
    {
        if(isset($value['form']) && $value['form']=="text"){
            $type = "text";
        }else{
            $type ="hidden";
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
        $value['class'] .= "xadmin-choose-".$name;

        $display = $this->Xform_input($type,$name, $value);
        $display .='<ul class="xadmin-form-radio " data="xadmin-choose-'.$name.'" >';
        foreach($value['options'] as $k=>$v){
            if(is_array($v)){
                $title = $v[0];
                $icon = '<icon class="'.$v[1].'"></icon>';
            }else{
                $title =$v;
                $icon = "";
            }
            $active = "";
            if($k==$value['value']){
                $active = " class='active'";
            }
            $display .='<li '.$active.' ><i></i> '.$icon.'<span  data="'.$k.'">'.$title.'</span></li>';
        }
        $display .='</ul>';
        return $display;
    }

}
