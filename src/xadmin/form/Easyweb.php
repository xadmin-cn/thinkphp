<?php
namespace xadmincn\xadmin\form;

class Easyweb 
{

    function Xlocalform()
    {
        $display = $this->Xform_css();
        foreach ($this->local['form'] as $key =>$value){
            $formexist = method_exists ($this,"Xform_".$value['type']);
            if($formexist){
                $display .= $this->Xform_item($key,$value);
            }
        }
        $display .= $this->Xform_js();
        return $display;
    }
    function Xadminform()
    {
        $display = $this->Xform_css();
        $display .= "<div class='layui-form'>";
        foreach ($this->local['form'] as $key =>$value){
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


    private function Xform_item($name, $value)
    {
        $func = "Xform_".$value['type'];
        $content = $this->$func($name, $value);
        $display = "";
        $display .= '<div class="layui-form-item">';
        $display .= ' <label class="layui-form-label">' . $value['name'] . '</label>';
        $display .= '  <div class="layui-input-block">';
        $display .=  $content;

        if(isset($value['remark']) && $value['remark']!='') {
            $display .= '  <div class="clearfix"></div>';
            $display .= ' <div class="layui-form-mid layui-word-aux ">'.$value['remark'].'</div>';
        }
        $display .= ' </div></div> ';
        return $display;
    }

    //password密码框
    private function Xform_submit($name, $value)
    {
        return $this->Xform_input("submit",$name,$value);
    }

    //text文本框
    private function Xform_text($name, $value)
    {
        return $this->Xform_input("text",$name,$value);
    }

    //password密码框
    private function Xform_password($name, $value)
    {
        return $this->Xform_input("password",$name,$value);
    }

    //只读
    private function Xform_readonly($name, $value)
    {
        $value['readonly'] = true;
        return $this->Xform_input("text",$name,$value);
    }

    //文本
    private function Xform_label($name, $value)
    {
        $value['readonly'] = true;
        $value['style'] = 'border:none;border-bottom:1px solid #e6e6e6;cursor:default;float:left';
        return $this->Xform_input("text",$name,$value);
    }

    //switch
    private function Xform_switch($name, $value)
    {
        $display = '<div class="left">';
        $display .= ' <input type="checkbox" class="layui-input left"   autocomplete="off" lay-filter="" lay-skin="switch"';
        $display .= " name='{$name}'";
        $display .= " value='{$value['switchValue']}'";
        $display .= " lay-text='{$value['switchKey']}'";
        $display .= '></div>';
        return $display;
    }


    //多文本框
    private function Xform_textarea($name,$value){
        $display = '<textarea  class="layui-textarea left" ';
        $display .= " class='layui-input left'";
        if(isset($value['placeholder'])) {
            $display .= " placeholder='{$value['placeholder']}'";
        }
        $display .= " name='{$name}'";
        if (isset($value['rule']['require'])){
            $display .= ' lay-vertype="tips" lay-verify="required" lay-reqText="'.$value['rule']['require'].'"';
        }
        $display .= " style='";
        if(isset($value['style'])){
            $display .= $value['style'];
        }
        if(isset($value['width']) && $value['width']!=''){
            $display .= ';width:'.$value['width'].'px';
        }
        if(isset($value['height'])  && $value['height']!=''){
            $display .= ';height:'.$value['height'].'px';
        }
        $display .= "'";
        $display .= ">";
        $display .= $value['value'];
        $display .= '</textarea>';
        return $display;
    }

    //单选框
    private function Xform_radio($name,$value){
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


    //单选框
    private function Xform_select($name,$value)
    {
        $display = '<div ';
        if (isset($value['width']) && $value['width'] != '') {
            $display .= ' style="width:' . $value['width'] . 'px"';
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

    //单选框
    private function Xform_image($name,$value){
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


    //单选框
    private function Xform_file($name,$value){
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


    //多选择框
    private function Xform_choose($name,$value){
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


    //图标选择框
    private function Xform_icon($name,$value){
        if(isset($value['form']) && $value['form']=="hidden"){
            $type = "hidden";
        }else{
            $type ="text";
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
        $value['class'] .= "xadmin-icon-".$name;
        $display = $this->Xform_input($type,$name, $value);
        $display .=  ' <div class="layui-btn-group">';
        $display .=  ' <a href="javascript:void(0);" data="'.$name.'" data-url="'.$this->local['thisUrl'].'/extend_form/icon/iconkey/'.$name.'" class="xadmin-form-icon layui-btn layui-btn-primary">选择</a>';
        $display .= '</div>';

        return $display;
    }


    private function Xform_input($type,$name, $value)
    {
        $display = "<input";
        //$display .= " autocomplete='off'";
        $display .= " type='{$type}'";
        $display .= " value='{$value['value']}'";
        $display .= " id='xadmin-form-id-{$name}'";

        if($value['type']!="label" && $value['type']!="submit") {
            $display .= " name='{$name}'";
        }

        if(isset($value['placeholder'])) {
            $display .= " placeholder='{$value['placeholder']}'";
        }

        if(isset($value['readonly']) && $value['readonly']==true){
            $display .= ' readonly ';
        }

        if($type=="submit"){
            $display .= ' lay-submit="" lay-filter="Xsubmit" ';
            if($value['value']==""){
                $value['value'] = "提交";
            }
            $display .= " class=\"layui-btn ";
        }else{
            $display .= " class= \"layui-input left ";
        }

        if(isset($value['class'])){
            $display .= $value['class'];
        }
        $display .= '"';

        $display .= " style='";
        if(isset($value['style'])){
            $display .= $value['style'];
        }
        if(isset($value['width']) && $value['width']!=''){
            $display .= ';width:'.$value['width'].'px';
        }
        if(isset($value['height']) && $value['height']!=''){
            $display .= ';height:'.$value['height'].'px';
        }
        $display .= "' ";
        if (isset($value['rule']['require']) && $value['type']!="image"){
            $display .= ' lay-vertype="tips" lay-verify="required" lay-reqText="'.$value['rule']['require'].'"';
        }
        $display .= ">";
        return $display;
    }

    function Xform_css(){
        $display = '<link  rel="stylesheet" type="text/css" href="//at.alicdn.com/t/font_1678995_svegot6wq8.css" />';//文件的图标
        $display .= '<link  rel="stylesheet" type="text/css" href="/xadmin/Default/css/form.css" />';
        $display .= '<link  rel="stylesheet" type="text/css" href="/xadmin/Default/iconfont/iconfont.css" />';

        return $display;
    }
    function Xform_js(){
        $listsurl = isset($this->local['listsUrl'])?$this->local['listsUrl']:"";
        $display = "<script>var listsUrl='".$listsurl."'</script>";
        $display .= '<script language="javascript" src="/xadmin/Default/js/xform.js"></script>';
        return $display;
    }
}
