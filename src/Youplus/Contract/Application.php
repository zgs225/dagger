<?php

namespace Youplus\Contract;

interface Application extends Container
{
    /**
     * 获得程序根目录
     *
     * @return string
     */
    public function basePath();

    /**
     * 获取或者检查环境配置
     *
     * @param  mixed
     * @return string
     */
    public function environment();

    /**
     * 指示程序是否处于维护状态
     *
     * @return bool
     */
    public function isDownForMaintenance();

    /**
     * 注册所有配置过的服务提供器
     *
     * @return void
     */
    public function registerConfiguredProviders();

    /**
     * 注册一个服务提供器到程序中
     *
     * @param  \Youplus\Support\ServiceProvider|string  $provider
     * @param  array  $options
     * @param  bool   $force
     * @return \Illuminate\Support\ServiceProvider
     */
    public function register($provider, $options = array(), $force = false);

    /**
     * 注册一个推迟的服务提供器到程序中
     *
     * @param  string  $provider
     * @param  string  $service
     * @return void
     */
    public function registerDeferredProvider($provider, $service = null);

    /**
     * 启动程序中的所有服务
     *
     * @return void
     */
    public function boot();

    /**
     * 注册一个启动时的监听器
     *
     * @param  mixed  $callback
     * @return void
     */
    public function booting($callback);

    /**
     * 注册一个启动后的监听器
     *
     * @param  mixed  $callback
     * @return void
     */
    public function booted($callback);

    /**
     * 获取compiled.php路径
     *
     * @return string
     */
    public function getCachedCompilePath();

    /**
     * 获取services.json路径
     *
     * @return string
     */
    public function getCachedServicesPath();
}
