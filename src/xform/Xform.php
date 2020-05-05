<?php
namespace xadmincn\xform;

class Xform
{

    function display($appForm,$viewFrom,$Template,$listsUrl)
    {

        if(!isset($viewFrom['theme'])){
            $viewFrom['theme'] = $Template;
        }
        $func = "xform".$viewFrom['theme'];
        return $this->$func($appForm,$viewFrom,$listsUrl);

    }

    private function xformEasyweb($appForm,$viewFrom,$listsUrl)
    {

        //提交按钮
        $appForm['submit']['type'] = "submit";

        $display = "<script>var listsUrl='".$listsUrl."'</script>";
        $display .= '<link  rel="stylesheet" type="text/css" href="/xadmincn/Easyweb/libs/layui/css/layui.css" />';
        $display .= '<link  rel="stylesheet" type="text/css" href="/xadmincn/Easyweb/module/admin.css" />';
        $display .= '<script language="javascript" src="/xadmincn/xadmin/plugins/jquery/jquery-3.4.1.min.js"></script>';
        $display .= '<script language="javascript" src="/xadmincn/Easyweb/libs/layui/layui.js"></script>';
        $display .= '<script language="javascript" src="/xadmincn/Easyweb/xadmin/xadmin.js"></script>';
        $display .= '<script language="javascript" src="/xadmincn/Easyweb/xadmin/xform.js"></script>';
        $display .= '<form class="layui-form">';
        foreach ($appForm as $name =>$value){
            if(class_exists('\xadmincn\xform\X'.$value['type'])){
                $class = '\xadmincn\xform\X'.$value['type'];

                if(isset($value['width']) && $value['width']=="" && isset($viewFrom['width'])){
                    $value['width'] = $viewFrom['width'];
                }

                if($value['type']=="submit"){
                    $value['name'] = "";
                }

                $xform = new $class();

                $display .= '<div class="layui-form-item">';
                $display .= ' <label class="layui-form-label">' . $value['name'] . '</label>';
                $display .= '  <div class="layui-input-block">';
                $display .=  $xform->display($name,$value);

                if(isset($value['remark']) && $value['remark']!='') {
                    $display .= '  <div class="clearfix"></div>';
                    $display .= ' <div class="layui-form-mid layui-word-aux ">'.$value['remark'].'</div>';
                }
                $display .= ' </div></div> ';
            }
        }
        $display .= "</form>";
        return $display;
    }



    /*
    function Xadminform()
    {
        $display = $this->Xform_css();
        $display .= "<div class='layui-form'>";
        foreach ($this->appForm as $key =>$value){
            $formexist = method_exists ($this,"Xform_".$value['type']);
            if($formexist){
                $display .= $this->Xform_item($key,$value);
            }
        }
        $value = array();
        if(isset($this->local['submitName'])) {
            $value['value'] = $this->local['submitName'];
        }else{
            $value['value'] = "提交";
        }
        if(isset($this->local['submitUrl'])) {
            $url = $this->local['submitUrl'];
        }else{
            $url = "";
        }
        $value['type']= "submit";
        $value['name']= "";
        $display .= $this->Xform_item("submit",$value);
        $display .= "</div>";
        $display .= $this->Xform_js();
        return $display;
    }
    */

}
