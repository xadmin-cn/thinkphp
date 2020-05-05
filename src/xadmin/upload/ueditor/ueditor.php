<?php

namespace xadmin\upload\ueditor;
use Xadmin\upload\upload;

/**
 * Class UeditorController
 * @package Admin\Controller
 */
class ueditor
{
    private $sub_name = array('date', 'Y/m-d');
    private $savePath = 'temp/';

    public function __construct()
    {
        $this->upload= new upload();
        date_default_timezone_set("Asia/Shanghai");
        error_reporting(E_ERROR | E_WARNING);
        header("Content-Type: text/html; charset=utf-8");
    }
    
	public function index($data){
		
        $config_data = json_decode(preg_replace("/\/\*[\s\S]+?\*\//", "", file_get_contents(XADMIN_PATH."upload/ueditor/config.json")), true);
        $action = $data['action'];
        switch ($action) {
            case 'config':
                $result =  json_encode($config_data);
                break;
            /* 上传图片 */
            case 'uploadimage':
		        //$fieldName = $CONFIG2['imageFieldName'];
                $data['type'] = "image";
                $res = $this->upload->upload($data);
                if($res['result']=="success"){
                    $res['data']['state'] = 'SUCCESS';
                    echo json_encode($res['data']);
                    exit;
                }
		        break;
            /* 列出图片 */
            case 'listimage':
                $data['type'] = "image";
                $data['limit'] = 20;
                $res = $this->upload->getList($data);
                if($res['result']=="success"){
                    $ress['data']['start'] = 0;
                    $ress['data']['total'] =  $res['data']['total'];
                    $ress['data']['state'] = 'SUCCESS';
                    foreach($res['data']['list'] as $key => $value){
                        $ress['data']['list'][$key]['url'] = $value['file_url'];
                    }
                    echo json_encode($ress['data']);
                    exit;
                }
                break;

            /* 上传涂鸦 */
            case 'uploadscrawl':
                $data['name'] = "scrawl.png";
                $data['fileName'] = 'upfile';
                $data['type'] = "base64";
                $res = $this->upload->upload($data);
                if($res['result']=="success"){
                    $res['data']['state'] = 'SUCCESS';
                    echo json_encode($res['data']);
                    exit;
                }

		        break;
            /* 上传视频 */
            case 'uploadvideo':
		        $fieldName = $config_data['videoFieldName'];
		        $result = $this->upFile($fieldName);
		        break;
            /* 上传文件 */
            case 'uploadfile':
		        $fieldName = $config_data['fileFieldName'];
		        $result = $this->upFile($fieldName);
                break;

            /* 列出文件 */
            case 'listfile':
			    $allowFiles = $config_data['fileManagerAllowFiles'];
			    $listSize = $config_data['fileManagerListSize'];
			    $path = $config_data['fileManagerListPath'];
			    $get = $_GET;
			    $result = $this->fileList($allowFiles,$listSize,$get);
                break;
            /* 抓取远程文件 */
            case 'catchimage':
		    	$config = array(
			        "pathFormat" => $config_data['catcherPathFormat'],
			        "maxSize" => $config_data['catcherMaxSize'],
			        "allowFiles" => $config_data['catcherAllowFiles'],
			        "oriName" => "remote.png"
			    );
			    $fieldName = $config_data['catcherFieldName'];
			    /* 抓取远程图片 */
			    $list = array();
			    isset($_POST[$fieldName]) ? $source = $_POST[$fieldName] : $source = $_GET[$fieldName];
				
			    foreach($source as $imgUrl){
			        $info = json_decode($this->saveRemote($config,$imgUrl),true);
			        array_push($list, array(
			            "state" => $info["state"],
			            "url" => $info["url"],
			            "size" => $info["size"],
			            "title" => htmlspecialchars($info["title"]),
			            "original" => htmlspecialchars($info["original"]),
			            "source" => htmlspecialchars($imgUrl)
			        ));
			    }

			    $result = json_encode(array(
			        'state' => count($list) ? 'SUCCESS':'ERROR',
			        'list' => $list
			    ));
                break;
            default:
                $result = json_encode(array(
                    'state' => '请求地址出错'
                ));
                break;
        }

        /* 输出结果 */
        if(isset($_GET["callback"])){
            if(preg_match("/^[\w_]+$/", $_GET["callback"])){
                echo htmlspecialchars($_GET["callback"]).'('.$result.')';
            }else{
                echo json_encode(array(
                    'state' => 'callback参数不合法'
                ));
            }
        }else{
            echo $result;
        }
	}

}