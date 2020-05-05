<?php
namespace Xadmin\upload;


class upload
{
    var $upload;
    public function __construct(){
        $this->upload = new \xadmin\upload\local\Upload();
    }
    function upload($data=array()){
        if($data['type']=="images"|| $data['type']=="image"){
            return $this->uploadImages($data);
        }elseif($data['type']=="base64"){
            return $this->upload->uploadBase64($data);
        }elseif($data['type']=="base64Tourl"){
            return $this->upload->base64Tourl($data);
        }

    }
    function delete($data){
        return $this->upload->uploadDelete($data);
    }
    public function uploadImages($data)
    {
        if(request()->isPost()) {
            return $this->upload->uploadImage($data);
        }
    }
    function getList($data){
        if(session("admin_id")){
            $data['where']['admin_id'] = session("admin_id");
        }
        return $this->upload->uploadList($data);
    }

}