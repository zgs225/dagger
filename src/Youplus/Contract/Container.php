<?php namespace Youplus\Contract;

use Closure;

interface Container {
    /**
     * 判断一个给定的抽象类是否已经绑定到容器
     *
     * @param string $abstract
     * @return bool
     **/
    public function bound($abstract);

    /**
     * 给定类绑定别名
     *
     * @param string $abstract
     * @param string $alias
     * @return void
     **/
    public function alias($abstract, $alias);

    /**
     * 给定类指定一组标签
     *
     * @param array|string $abstracts
     * @param array|mixed ...tags
     * @return void
     **/
    public function tag($abstracts, $tags);

    /**
     * 给所有的绑定在容器中的对象添加标签
     *
     * @param array $tag
     * @return array
     **/
    public function tagged($tag);

    /**
     * 实例化一个类绑定到容器中
     *
     * @param string $abstract
     * @param \Closure|string|null $concrete
     * @param bool $shared
     * @return void
     **/
    public function bind($abstract, $concrete = null, $shared = false);

    /**
     * 实例化一个类绑定到容器中如果它还没有被绑定
     *
     * @param string $abstract
     * @param \Closure|string|null $concrete
     * @param bool $shared
     * @return void
     **/
    public function bindIf($abstract, $concrete = null, $shared = false);

    /**
     * 实例化一个类并共享这个对象绑定到容器中
     *
     * @param string $abstract
     * @param \Closure|string|null $concrete
     * @return void
     **/
    public function singleton($abstract, $concrete = null);

    /**
     * 扩展一个绑定类
     *
     * @param string $abstract
     * @param \Closure $closure
     * @return void
     **/
    public function extend($abstract, Closure $closure);

    /**
     * 将一个已经实例化的对象绑定到容器中
     *
     * @param string $abstract
     * @param mixed $instance
     * @return void
     **/
    public function instance($abstract, $instance);

    /**
     * 根据给定的类返回它的实例对象
     *
     * @param string $abstract
     * @param array $parameters
     * @return mixed
     **/
    public function make($abstract, array $parameters = array());

    /**
     * 调用给定的Closure 或者是绑定了的类的方法
     *
     * @param \Closure|string $callback
     * @param array $parameters
     * @param string|null $defaultMethod
     * @return mixed
     **/
    public function call($callback, array $parameters = array(), $defaultMethod = null);

    /**
     * 判断给定类有没有返回实例对象
     *
     * @param string $abstract
     * @return bool
     **/
    public function resolved($abstract);

    /**
     * 注册给定类的实例化过程中执行的\Closure
     *
     * @param string $abstract
     * @param \Clusre|null $callback
     * @return void
     **/
    public function resolving($abstract, Closure $callback = null);

    /**
     * 注册给定类的实例化过程后执行的\Closure
     *
     * @param string $abstract
     * @param \Clusre|null $callback
     * @return void
     **/
    public function afterResolving($abstract, Closure $callback = null);
}
?>
