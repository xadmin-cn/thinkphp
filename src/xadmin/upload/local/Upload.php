<?php
namespace xadmin\upload\local;

use think\facade\Db;
use xadmin\traits\CommonTrait;
use think\exception\ValidateException;
use think\facade\Filesystem;
use think\File;
class Upload
{

    use CommonTrait;

    /**
     * 上传文件返回数组初始值
     * @var array
     */
    protected $uploadInfo = [
        'name' => '',
        'size' => 0,
        'type' => 'image/jpeg',
        'dir' => '',
        'thumb_path' => '',
        'image_type' => '',
        'time' => 0,
    ];

    public function __construct()
    {
        date_default_timezone_set("Asia/Shanghai");
        $app =  App();
        $this->appname =  App('http');
        header("Content-Type: text/html; charset=utf-8");
    }
    private function uploadInit($data){
        //上传文件名
        if(isset($data['fileName'])){
            $fileName = $data['fileName'];
        }else{
            $fileName = "file";
        }
        $this->fileName = $fileName;

        //上传文件大小
        if(isset($data['fileSize'])){
            $fileSizes = $data['fileSize'];
        }else{
            $fileSizes = 5;
        }
        $this->fileSize = $fileSizes;
        $this->fileMax = $fileSizes*1024*1024;

        //上传文件路径
        $path = "";
        if(isset($data['filePath'])){
            $path = $data['filePath'];
        }
        $this->filePath =$path;
        $this->uploadPath = $path."/".date("Y/md");

        //获取上传文件
        if(isset($data['type']) && $data['type']=="base64"){
            $base64Data = $data[$fileName];
            $file = base64_decode($base64Data);

        }else{
            $file = request()->file($fileName);
            //校验文件
            $message  =   [
                $fileName.'.fileSize' => '文件大小不能大于'.$this->fileSize.'M',
            ];
            try {
                validate([$fileName => 'fileSize:'.$this->fileMax])->message($message)->check([$fileName => $file]);
            } catch (\think\exception\ValidateException $e) {
                return $this->returnMessage("error",$e->getMessage());
            }

        }
        $this->file = $file;



    }
    //上传单个文件
    public function uploadImage($data)
    {
        $this->uploadInit($data);

        $fileresult = Filesystem::disk('public')->putFile( $this->uploadPath, $this->file, 'md5');
        if (!$fileresult) return $this->returnMessage("error",'图片上传失败!');
        $filePath = Filesystem::disk('public')->path($fileresult);
        $fileresult = str_replace("\\","/",$fileresult);
        $fileInfo = new File($filePath);
        $url = '/uploads/' . $fileresult;

        //添加数据库
        $add['url'] = $url;
        $add['ext'] = $fileInfo->getExtension();
        $add['name'] = $fileInfo->getFilename();
        $add['type'] ="image";
        $add['file_type'] = $fileInfo->getMime();
        $add['size'] = $fileInfo->getSize();
        $upload_id= $this->uploadCreate($add);

        $data = array(
            'url' => $url,
            'title' => $fileInfo->getFilename(),
            'type' => '.' . $fileInfo->getExtension(),
            'size' =>$fileInfo->getSize(),
        );
        $data['id'] = $upload_id;
        return $this->returnMessage("success","上传成功",$data);
    }

    /*
   * 处理base64编码的图片上传
   * 例如：涂鸦图片上传
  */
     function uploadBase64($data){
        $this->uploadInit($data);
        $url = '/uploads/' .  $this->uploadPath."/";
        $dirname = ROOT_PATH."/public/";
         //创建目录失败
         if(!file_exists($dirname.$url) && !mkdir($dirname.$url,0777,true)){
             return $this->returnMessage("error","目录创建失败");
         }else if(!is_writeable($dirname.$url)){
             return $this->returnMessage("error","目录没有写权限");
         }

         $file['ext'] = strtolower(strrchr($data['name'],'.'));
         $file['name'] = MD5($this->file).$file['ext'];

         if(!(file_put_contents($dirname.$url.$file['name'], $this->file) && file_exists($dirname.$url.$file['name']))){ //移动失败
             return $this->returnMessage("error","写入文件失败");
         }
        $info = getimagesize($dirname.$url.$file['name']);
         $fileInfo = new File($dirname.$url.$file['name']);
         //添加数据库
         $add['url'] = $url.$file['name'];
         $add['ext'] = $fileInfo->getExtension();
         $add['name'] = $file['name'];
         $add['type'] ="image";
         $add['file_type'] =$info['mime'];
         $add['size'] = $fileInfo->getSize();
         $upload_id= $this->uploadCreate($add);
         $data = array(
             'url' => $url.$file['name'],
             'title' => $file['name'],
         );
         $data['id'] = $upload_id;
         return $this->returnMessage("success","上传成功",$data);
    }
    /*
     * 处理base64转化为可上传的图片
    */
    function base64Tourl($data){
        $this->uploadInit($data);
        $url = '/uploads/' .  $this->uploadPath."/";
        $dirname = ROOT_PATH."/public/";
        //创建目录失败
        if(!file_exists($dirname.$url) && !mkdir($dirname.$url,0777,true)){
            return $this->returnMessage("error","目录创建失败");
        }else if(!is_writeable($dirname.$url)){
            return $this->returnMessage("error","目录没有写权限");
        }
        if (preg_match('/^(data:\s*image\/(\w+);base64,)/', $data['data'], $result)){
            $name = md5(time().$data['data']).".".$result[2];
            if (!file_put_contents($dirname.$url.$name, base64_decode(str_replace($result[1], '', $data['data'])))){
                return $this->returnMessage("error","写入文件失败");
            }
        }


        $info = getimagesize($dirname.$url.$name);
        $fileInfo = new File($dirname.$url.$name);
        //添加数据库
        $add['url'] = $url.$name;
        $add['ext'] = $fileInfo->getExtension();
        $add['name'] = $name;
        $add['type'] ="image";
        $add['file_type'] =$info['mime'];
        $add['size'] = $fileInfo->getSize();
        $upload_id= $this->uploadCreate($add);
        $data = array(
            'url' => $url.$name,
            'title' => $name,
        );
        $data['id'] = $upload_id;
        return $this->returnMessage("success","上传成功",$data);
    }

    //添加上传文件
    function uploadCreate($add){
        $upload['admin_id'] =session("admin_id");
        $upload['user_id'] =session("user_id");
        $upload['session_id'] = session_id();
        $upload['module'] =$this->filePath;
        $upload['file_url'] = $add['url'];
        $upload['nid'] = md5($add['url'].time());
        $upload['file_name'] = $add['name'];
        $upload['img_url'] = '';
        $upload['file_type'] = $add['file_type'];
        $upload['extension'] = $add['ext'];
        $upload['type'] = $add['type'];
        $upload['size'] = $add['size'];
        $upload['create_time'] = time();
        $upload['create_ip'] = request()->ip();
        $upload_id = Db::name('upload')->insertGetId($upload);
        return $upload['nid'];
    }
    //上传列表
    function uploadList($data=array()){
        $where = array();
        if(isset($data['where'])){
            $where = $data['where'];
        }
        $page = 1;
        if(isset($data['page'])){
            $page = $data['page'];
        }
        $limit = 10;
        if(isset($data['limit'])){
            $limit = $data['limit'];
        }
        $count =  Db::name('upload')->where($where)->where('is_delete',0)->count();
        $list = Db::name('upload')->where($where)->order("id desc")->where('is_delete',0)->paginate([
            'list_rows'=>$limit,
            'page' => $page,
        ]);
        $data = array();
        $data['list'] = $list->items();
        $data['total'] = $count;
        $data['page'] =$page;
        $data['limit'] = $limit;
        return $this->returnMessage("success","查询成功",$data);
        return $list;
    }

    /**
     * 删除上传的信息
     * @param string $url
     * @param int $imageType
     * @param string $name
     * @param string $thumbPath
     * @return array
     */
    function uploadDelete($data){
        $upd['is_delete']=1;
        $upd['delete_time']=time();
        $upload_id = Db::name('upload')->where("nid","=",$data['id'])->update($upd);
        return $this->Message("success","删除成功");
    }

    /**
     * 水印
     * @param $img_path
     */
    protected function waterImage($img_path)
    {
    }



}