<?php

namespace xadmincn\xadmin\basic;

use think\template\TagLib;
use think\facade\View;
use xadmincn\xadmin\basic\Xcontroller;
 class Xtag extends TagLib{

    protected $tags = [
        'test' => ['attr'=> 'field', 'close'=>0],
        'form' => ['attr'=> 'template,localform,fields,value,test', 'close'=>0]
    ];

     public function tagTest($tag)
     {
         return view::fetch("aa");
         return $tag['field'];
     }
     public function tagForm()
     {
         return view::fetch("../Xview/Default@/form");
     }



}
