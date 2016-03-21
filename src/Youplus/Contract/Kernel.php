<?php namespace Youplus\Contract;

interface Kernel
{
    /**
     * 当有HTTP请求进来的时候，进行的初始化操作
     *
     * @return void
     **/
    public function bootstrap();

    /**
     * 处理进来的HTTP请求
     *
     * @param \Youplus\Http\Request $request
     * @return \Youplus\Http\Response
     **/
    public function handle($request);

    /**
     * 当HTTP请求处理结束，发送之前时
     *
     * @param \Youplus\Http\Request $request
     * @param \Youplus\Http\Response $response
     * @return void
     **/
    public function terminate($request, $response);
}
