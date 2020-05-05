<?php

namespace xadmincn\xadmin\basic;

    /**
 * 图片处理类
 *
 * 包括图片压缩、缩放、剪切、旋转、水印（文字或图片）常见功能
 * 支持gif、jpg、jpeg、png、bmp 文件类型
 *
 * @author Administrator
 *
 */
class Ximage{
    private $filename = '';
    private $destname = '';
    private $suffix = '';
    private $imagecreatefrommethod = '';
    private $imagemethod = '';
    private $types = array (1 => 'gif', 2 => 'jpg', 3 => 'png', 6 => 'bmp' );                                           // 图像types 对应表
    private $suffix_method = array('gif'=>'gif', 'jpg'=>'jpeg', 'jpeg'=>'jpeg', 'png'=>'png', 'bmp'=>'wbmp');            // 图像type对应处理方法后缀

    public function image($filename, $destname, $suffix = ''){
        $this->suffix = $suffix;
        $this->filename = $filename;
        $this->destname = $destname;
        $this->imagemethod = 'image'.$this->suffix_method[$this->suffix];
        $this->imagecreatefrommethod = 'imagecreatefrom'.$this->suffix_method[$this->suffix];
    }


    /**
     * 图片缩放裁剪
     * @param $type     类型：0=等比裁剪   1=缩放后居中裁剪   2=缩放后上左裁剪   3=直接坐标裁剪
     * @param $w        宽度
     * @param $h        高度
     * @param $x        x轴坐标
     * @param $y        y轴坐标
     * @param $quality  图片压缩质量默认75（0-100之间）
     */
    public function thumb($type = 0, $w =180, $h = 120, $x = 0, $y = 0, $quality =75){
        $imageinfo = $this->getImageInfo($this->filename);
        $file_ext = $imageinfo['ext'];
        $file_type = $imageinfo['type'];
        $file_width = $imageinfo[0];
        $file_height = $imageinfo[1];
        $file_size = $imageinfo['size'];
        if($file_width < $w && $file_height < $h ){
            return copy($this->filename, $this->destname);
        }
        $thumb_params = $this->getThumTypesParams($type, $file_width, $file_height, $w, $h, $x, $y);

        $createfromfun = $this->imagecreatefrommethod;
        $old_im = $createfromfun($this->filename);

        //创建缩略图片或裁剪图片
        if($file_type != 'gif' && function_exists('imagecreatetruecolor')){
            $thumb_img = imagecreatetruecolor($thumb_params['thumb_w'], $thumb_params['thumb_h']);
        }else{
            $thumb_img = imagecreate($thumb_params['thumb_w'], $thumb_params['thumb_h']);
        }
        //      var_dump($thumb_params);
        //复制图片 原图片的一步复制到创建的缩略或裁剪图里面
        if($type == 3){
            imagecopy($thumb_img, $old_im, 0, 0, $thumb_params['start_x'], $thumb_params['start_y'], $thumb_params['src_w'], $thumb_params['src_h']);
        }else if(function_exists('ImageCopyResampled')){
            imagecopyresampled($thumb_img,$old_im, 0, 0, $thumb_params['start_x'], $thumb_params['start_y'], $thumb_params['thumb_w'], $thumb_params['thumb_h'], $thumb_params['src_w'], $thumb_params['src_h']);
        }else{
            ImageCopyResized($thumb_img,$old_im, 0, 0, $thumb_params['start_x'], $thumb_params['start_y'], $thumb_params['thumb_w'], $thumb_params['thumb_h'], $thumb_params['src_w'], $thumb_params['src_h']);
        }

        //jpeg设置隔行扫描
        $file_type == 'jpeg' && imageinterlace ( $thumb_img, 1 );
        $imagefun = $this->imagemethod;
        if($this->suffix == 'jpg' || $this->suffix = 'jpeg' || $this->suffix = 'png'){
            $thumb_result = @$imagefun ( $thumb_img, $this->destname , $quality);
        }else{
            $thumb_result = @$imagefun ( $thumb_img, $this->destname );
        }
        imagedestroy ( $thumb_img );
        imagedestroy ( $old_im );
        return $thumb_result;
    }
    private function getThumTypesParams($type, $old_w, $old_h, $new_w, $new_h, $start_x = 0, $start_y = 0){
        $params = array();
        switch ($type) {
            case 0: // 等比缩放
                $scale = min ( $new_w / $old_w, $new_h / $old_h ); // 计算缩放比例
                $params['thumb_w'] = ( int ) ($old_w * $scale); // 缩略图尺寸
                $params['thumb_h'] = ( int ) ($old_h * $scale);
                $params['start_x'] = $params['start_y'] = 0;
                $params['src_w'] = $old_w;
                $params['src_h'] = $old_h;
                break;
            case 1: // 缩放后居中裁剪
                $scale1 = round ( $new_w / $new_h, 2 );
                $scale2 = round ( $old_w / $old_h, 2 );
                if ($scale1 > $scale2) {
                    $params['src_h'] = round ( $old_w / $scale1, 2 );
                    $params['start_y'] = ($old_h - $params['src_h']) / 2;
                    $params['start_x'] = 0;
                    $params['src_w'] = $old_w;
                } else {
                    $params['src_w'] = round ( $old_h * $scale1, 2 );
                    $params['start_x'] = ($old_w - $params['src_w']) / 2;
                    $params['start_y'] = 0;
                    $params['src_h'] = $old_h;
                }
                $params['thumb_w'] = $new_w; // 缩略图尺寸
                $params['thumb_h'] = $new_h;
                break;
            case 2: //缩放后上左裁剪
                $scale1 = round ( $new_w / $new_h, 2 );
                $scale2 = round ( $old_w / $old_h, 2 );
                if ($scale1 > $scale2) {
                    $params['src_h'] = round ( $old_w / $scale1, 2 );
                    $params['src_w'] = $old_w;
                } else {
                    $params['src_w'] = round ( $old_h * $scale2, 2 );
                    $params['src_h'] = $old_h;
                }
                $params['start_x'] = 0;
                $params['start_y'] = 0;
                $params['thumb_w'] = $new_w; // 缩略图尺寸
                $params['thumb_h'] = $new_h;
                break;
            case 3: //坐标直接裁剪（无缩放）
                $params['start_x'] = $start_x;
                $params['start_y'] = $start_y;
                $params['thumb_w'] = $new_w; // 缩略图尺寸
                $params['thumb_h'] = $new_h;
                $params['src_w'] = $old_w;
                $params['src_h'] = $old_h;
                break;
            case 4: //直接压缩图片大小（目前只支持jpg、png）
                $params['start_x'] = 0;
                $params['start_y'] = 0;
                $params['thumb_w'] = $old_w; // 缩略图尺寸
                $params['thumb_h'] = $old_h;
                $params['src_w'] = $old_w;
                $params['src_h'] = $old_h;
                break;

            default:
                ;
                break;
        }
        return $params;
    }

    /**
     * 图片水印（图片和文字）
     * @param $type     类型：0=图片水印  1=文字水印
     * @param $water    水印图片路径
     * @param $words    水印文字
     * @param $pos      水印位置
     * @param $opty     水印透明度
     */
    public function water($type = 0, $water = '',$words = '', $pos = 0, $opty = 80, $font = '', $fontsize = 13, $words_color = array('R'=>0, 'G'=>0, 'B'=>0)){
        //图片信息
        $imageinfo = $this->getImageInfo($this->filename);
        $file_width = $imageinfo[0];
        $file_height = $imageinfo[1];
        $createfromfun = $this->imagecreatefrommethod;
        $image_res = $createfromfun($this->filename);

        if($type == 0){
            //水印图片信息
            $waterinfo = $this->getImageInfo($water);
            $water_width = $waterinfo[0];
            $water_height = $waterinfo[1];
            $water_type = $waterinfo['type'];
            $watercreatefun = 'imagecreatefrom'.$water_type;
            $water_res = $watercreatefun($water);

            // 剪切水印
            $water_width > $file_width && $water_width = $file_width;
            $water_height > $file_height && $water_height = $file_height;
            $pos_scale = $this->getwaterposscale($pos, $file_width, $file_height,$water_width, $water_height);

            // 设定图像的混色模式
            imagealphablending ( $image_res, true );
            // 添加水印
            imagecopymerge ( $image_res, $water_res, $pos_scale['x'], $pos_scale['y'], 0, 0, $water_width, $water_height, $opty);
            $imagefun = $this->imagemethod;
            $water_result = $imagefun($image_res, $this->destname);
            imagedestroy ( $image_res );
            imagedestroy ( $water_res );
            return $water_result;
        }else{
            //水印文字
            $water_words = mb_convert_encoding($words, "html-entities", "utf-8");
            $font_h = imagefontheight($fontsize);
            $font_w = imagefontwidth($fontsize);
            $pos_scale = $this->getwaterposscale($pos,$file_width, $file_height, 175, 20);
            $posX = $pos_scale['x'];
            $posY = $pos_scale['y'];
            $color=imagecolorallocate($image_res,0,0,0);
            imagettftext($image_res,$fontsize, 0, $posX,$posY,$color,$font,$water_words);
            $words_res = imagecreatetruecolor($file_width, $file_height);
            imagecopy($words_res,$image_res,0,0,0,0,$file_width,$file_height);
            $imagefun = $this->imagemethod;
            $water_result = $imagefun($image_res, $this->destname);
            imagedestroy ( $image_res );
            imagedestroy ( $words_res );
            return $water_result;
        }
    }
    private function getwaterposscale($pos, $old_w, $old_h, $water_w, $water_h){
        $pos_scale = array();
        switch ($pos) {
            case 0 : //随机
                $posX = rand ( 0, ($old_w - $water_w) );
                $posY = rand ( 0, ($old_h - $water_h) );
                break;
            case 1 : //1为顶端居左
                $posX = 0;
                $posY = 0+$water_h;
                break;
            case 2 : //2为顶端居中
                $posX = ($old_w - $water_w) / 2;
                $posY = 0;
                break;
            case 3 : //3为顶端居右
                $posX = $old_w - $water_w;
                $posY = 0;
                break;
            case 4 : //4为中部居左
                $posX = 0;
                $posY = ($old_h - $water_h) / 2;
                break;
            case 5 : //5为中部居中
                $posX = ($old_w - $water_w) / 2;
                $posY = ($old_h - $water_h) / 2;
                break;
            case 6 : //6为中部居右
                $posX = $old_w - $water_w;
                $posY = ($old_h - $water_h) / 2;
                break;
            case 7 : //7为底端居左
                $posX = 0;
                $posY = $old_h - $water_h;
                break;
            case 8 : //8为底端居中
                $posX = ($old_w - $water_w) / 2;
                $posY = $old_h - $water_h;
                break;
            case 9 : //9为底端居右
                $posX = $old_w - $water_w;
                $posY = $old_h - $water_h;
                break;
            default : //随机
                $posX = rand ( 0, ($old_w - $water_w) );
                $posY = rand ( 0, ($old_h - $water_h) );
                break;
        }
        $pos_scale['x'] = $posX;
        $pos_scale['y'] = $posY;
        return $pos_scale;
    }

    /**
     * 图片旋转
     * @param $rotation   角度整数（90 180 270）
     * @param $bg              旋转过后空白处的颜色 ,默认白色。比如：array('R'=>255, 'G'=>255, 'B'=>255)，RGB整数值
     */
    public function rotate($rotation ,$bg = array('R'=>255, 'G'=>255, 'B'=>255)){
        $localimagemethod = $this->imagemethod;
        $localimagefrommethod = $this->imagecreatefrommethod;
        $im = $localimagefrommethod($this->filename);
        $white=imagecolorallocate($im, $bg['R'], $bg['G'], $bg['B']);
        $rotated_res =imagerotate($im, $rotation,$white);
        return $localimagemethod($rotated_res, $this->destname);
    }

    /*********************************************base method*******************************/
    private function getImageInfo($filename) {
        $imageinfo = @getimagesize ( $filename );
        $imageinfo ['size'] = @filesize ( $filename);
        if (isset ( $this->types [$imageinfo [2]] )) {
            $imageinfo ['ext'] = $imageinfo ['type'] = $this->types [$imageinfo [2]];
        } else {
            $imageinfo ['ext'] = $imageinfo ['type'] = 'jpg';
        }
        $imageinfo ['type'] == 'jpg' && $imageinfo ['type'] = 'jpeg';
        $imageinfo ['size'] = @filesize ( $filename );
        return $imageinfo;
    }
}