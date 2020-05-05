<?php
namespace app\xapp\giftcard;

/*
 * 礼品卡回收
 */

use xadmincn\Xadmin;

class GiftcardRecycle extends Xadmin
{
    //自定义表单
    public  $appForm = [
        'id' => [
            'name' => '表名',
            'type' => 'none',
        ],
        'cardNo' => [
            'name' => '卡号',
            'type' => 'text',
            'rule' => ['require'=>"礼品卡号必填"],
        ],
        'realname' => [
            'name' => '收款人',
            'type' => 'text',
            'rule' => ['require'=>"收款人必填"],
        ],
        'phone' => [
            'name' => '手机号',
            'type' => 'text',
            'rule' => ['require'=>"手机号必填"],
        ],
        'account' => [
            'name' => '银行账户',
            'type' => 'text',
            'rule' => ['require'=>"银行账户必填"],
        ],
        'bank' => [
            'name' => '所属银行',
            'type' => 'text',
            'rule' => ['require'=>"所属银行必填"],
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
    function edit(){
        $this->appAdmin();
    }
}
