<?php
namespace xadmincn\xform;

//单个图片上传

class Xtest extends Xbase
{
    function display($name,$value)
    {
        $laydata["accept"] = "image";
        $laydata["acceptMime"] = "image/*";
        $laydata["exts"] = "jpg|png|gif|bmp|jpeg";
        $laydata["auto"] = "true";
        $laydata["size"] = "2048";
        $laydata["multiple"] = "false";
        $laydata["number"] = "1";
        $laydata["data"]["extend_form"] = "upload";
        $laydata["data"]["type"] = "image";
        $laydata["data"]["name"] = "{$name}";
        $data = json_encode($laydata);
        $data = str_replace('"',"'",$data);
        $display = $this->Xform_input("hidden",$name, $value);
        if(!isset($value['value']) || $value['value']==""){
            $src = "/xadmin/Default/images/upload_add.png";
            $image_class = "xadmin-form-dashed";
            $class = "hide ";
        }else{
            $src = $value['value'];
            $class = "";
            $image_class = "";
        }

        $display .='<div class="clearfix" style="height: 80px"><img src="'.$src.'"  class="xadmin-form-image xadmin-form-image-'.$name.' '.$image_class.'" lay-data="'.$data.'" style="cursor:pointer;">
        <i class="xadmin-form-image-del  xadmin-form-image-del-'.$name.' fa fa-close '.$class.'" data="'.$name.'" ></i></div>';

        /*
        $display .= '<div class="layui-btn-group" style="margin-left: -5px;float: left">
            <button type="button" class="layui-btn xadmin-form-image" lay-data="'.$data.'"  style="padding: 0 10px;"><i class="layui-icon left">&#xe67c;</i></button>
            <button type="button" class="layui-btn xadmin-form-image-delete" onclick="javascript:$(\'#xadmin-form-id-'.$name.'\').val(\'\');" data=\'{"key":"'.$name.'"}\' style="padding: 0 10px;"><i class="layui-icon layui-icon-delete"></i></button>
            <button type="button" class="layui-btn xadmin-form-image-view" data=\'{"key":"'.$name.'"}\' style="padding: 0 10px;"><i class="layui-icon layui-icon-picture"></i></button>
        </div>';
        */
        return $display;
    }

}
