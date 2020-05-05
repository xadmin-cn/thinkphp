<?php
namespace xadmincn;

use xadmincn\xadmin\basic\Xadmin as xadmincn;

define('XADMIN_PATH', __DIR__ );
/**
 * xadmincn主入口
 * Auth:Ahui
 * Web:www.xadmin.cn
 * doc:thinkphp.doc.xadmin.cn
 */
class Xadmin extends xadmincn
{
    /**
     * xadminp配置文件
     * @var
     */
    protected $xadminConfig = [];


    /**
     * Request实例
     * @var \think\Request
     */
    protected $request;

    /**
     * 应用实例
     * @var \think\App
     */
    protected $app;

    /**
     * 是否批量验证
     * @var bool
     */
    protected $batchValidate = false;

    /**
     * 控制器中间件
     * @var array
     */
    protected $middleware = [
        \think\middleware\SessionInit::class
    ];

    /**
     * 构造方法
     * @access public
     * @param  App  $app  应用对象
     */
    public function __construct()
    {
        // 控制器初始化
        $this->initialize();
        parent::__construct();
    }
    // 初始化
    protected function initialize()
    {

    }

}