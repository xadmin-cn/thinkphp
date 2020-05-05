<?php
namespace xadmincn\xapp;

/*
 * 礼品卡
 */

class Giftcard 
{
    //自定义表单
    public  $appForm = [
        'id' => [
            'name' => '表名',
            'type' => 'none',
        ],
        'title' => [
            'name' => '标题标题标',
            'type' => 'text',
            'rule' => ['require'=>"必填"],
        ],
        'content' => [
            'name' => '内容',
            'type' => 'text',
        ],

    ];
    function lists(){
        $this->appAdmin();
    }
    function create(){
        $this->appAdmin();
    }
    function modify(){
        $this->appAdmin();
    }
    function delete(){
        $this->appAdmin();
    }
    function detail(){
        $this->appAdmin();
    }
}
