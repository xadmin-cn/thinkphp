<?php
namespace xadmincn\xform;

//单个文件上传

class XuploadFile extends Xbase
{
    function display($name,$value)
    {
        $laydata["accept"] = "file";
        //$laydata["acceptMime"] = "image/*";
        $laydata["exts"] = "zip|rar|7z";
        $laydata["auto"] = "true";
        $laydata["size"] = "50480";
        $laydata["multiple"] = "false";
        $laydata["number"] = "1";
        $laydata["data"]["extend_form"] = "upload";
        $laydata["data"]["type"] = "image";
        $laydata["data"]["name"] = "{$name}";
        $data = json_encode($laydata);
        $data = str_replace('"',"'",$data);
        $display = $this->Xform_input("hidden",$name, $value);
        if(!isset($value['value']) || $value['value']==""){
            $src = "/xadmin/Default/images/upload_file.png";
            $class = "hide";
        }else{
            $src = $value['value'];
            $class = "";
        }

        $display .='<div class="clearfix" style="height: 80px">';
        $display .= '<div  class="xadmin-form-file xadmin-form-file-'.$name.'" lay-data="'.$data.'" style="cursor:pointer;">
<icon class="iconfont iconshangchuan"></icon>
</div>';
        // $display .=' <img src="'.$src.'"  class="xadmin-form-file xadmin-form-file-'.$name.'" lay-data="'.$data.'" style="cursor:pointer;">';


        $display .=' <i class="xadmin-form-file-del  xadmin-form-file-del-'.$name.' fa fa-close '.$class.'" data="'.$name.'" ></i>';
        $display .='</div>';

        $display .= '<div class="layui-hide xadmin-form-file-progress xadmin-form-file-progress-'.$name.'" id="upload_progress" style="width: 87px;margin-top: 12px">
<div class="layui-progress" lay-showpercent="true" lay-filter="upload_progress">
<div class="layui-progress-bar layui-bg-blue" lay-percent="0%"></div>
</div>
</div>';

        return $display;
    }

}
