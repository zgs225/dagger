<?php namespace Youplus\Http;

use Youplus\Contract\Kernel as KernelContract;

class Kernel implements KernelContract
{
    /**
     * 当前访问的路由
     *
     * @var string
     **/
    protected $router;

    /**
     * 启动时需要加载的类
     *
     * @var array
     **/
    protected $bootstrappers = array();

    /**
     * 保存全局中间件的栈
     *
     * @var array
     **/
    protected $middlewares = array();

    /**
     * 分组的中间件栈
     *
     * @var array
     **/
    protected $middlewareGroups = array();

    function __construct($router)
    {
        $this->router = $router;
    }

    /**
     * 当有HTTP请求进来的时候，进行的初始化操作
     *
     * @return void
     **/
    public function bootstrap()
    {
    }

    /**
     * 处理进来的HTTP请求
     *
     * @param \Youplus\Http\Request $request
     * @return \Youplus\Http\Response
     **/
    public function handle($request)
    {
        try {
            $request->enableHttpMethodParameterOverride();

            $response = $this->sendRequestThroughRouter($request);
        } catch (Exception $e) {
            $this->reportException($e);

            $response = $this->renderException($request, $e);
        }

        return $response;
    }

    /**
     * 当HTTP请求处理结束，发送之前时
     *
     * @param \Youplus\Http\Request $request
     * @param \Youplus\Http\Response $response
     * @return void
     **/
    public function terminate($request, $response)
    {
    }
}
