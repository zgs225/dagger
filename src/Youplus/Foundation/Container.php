<?php namespace Youplus\Foundation;

use Closure;
use ArrayAccess;
use Youplus\Contract\Container as ContainerContract;
use Youplus\Exceptions\BindingResolutionException;

class Container implements ArrayAccess, ContainerContract
{
    /**
     * Container类的全局实例对象
     *
     * @var static
     **/
    protected static $instance;

    /**
     * 已经实例化的类的数组
     *
     * @var array
     **/
    protected $resolved = array();

    /**
     * 绑定在容器中的类
     *
     * @var array
     **/
    protected $bindings = array();

    /**
     * 容器中的共享实例
     *
     * @var array
     **/
    protected $instances = array();

    /**
     * 注册的类的别名
     *
     * @var string
     **/
    protected $aliases = array();

    /**
     * 为服务提供扩展的Closure
     *
     * @var array
     **/
    protected $extenders = array();

    /**
     * 所有注册过的标签
     *
     * @var array
     **/
    protected $tags = array();

    /**
     * 实例话对象编译过程栈
     *
     * @var array
     **/
    protected $buildStack = array();

    /**
     * 所有注册的类重新绑定回调
     *
     * @var array
     **/
    protected $reboundCallbacks = array();

    /**
     * 全局实例化过程中回调
     *
     * @var array
     **/
    protected $globalResolvingCallbacks = array();

    /**
     * 全局实例化过程后回调
     *
     * @var array
     **/
    protected $globalAfterResolvingCallbacks = array();

    /**
     * 实例化类过程中的回调
     *
     * @var array
     **/
    protected $resovingCallbacks = array();

    /**
     * 实例化类过程后的回调
     *
     * @var array
     **/
    protected $afterResovingCallbacks = array();

    public function offsetExists($key) {
        return isset($this->bindings[$this->normalize($key)]);
    }

    public function offsetGet($key) {
        return $this->make($key);
    }

    public function offsetSet($key, $value) {
        if (!$value instanceof Closure) {
            $value = function () use ($value) {
                return $value;
            };
        }

        $this->bind($key, $value);
    }

    public function offsetUnset($key) {
        $key = $this->normalize($key);

        unset($this->bindings[$key], $this->instances[$key], $this->resolved[$key]);
    }

    public function bound($abstract) {
        $abstract = $this->normalize($abstract);

        return isset($this->bindings[$abstract]) || isset($this->instances[$abstract]) || $this->isAlias($abstract);
    }

    public function alias($abstract, $alias) {
        $this->aliases[$alias] = $this->normalize($abstract);
    }

    public function isAlias($name)
    {
        return isset($this->aliases[$this->normalize($name)]);
    }

    public function tag($abstracts, $tags) {
    }

    public function tagged($tag) {
    }

    public function bind($abstract, $concrete = null, $shared = false) {
        $abstract = $this->normalize($abstract);
        $concrete = $this->normalize($concrete);

        if (is_array($abstract)) {
            list($abstract, $alias) = $this->extractAlias($abstract);
            $this->alias($abstract, $alias);
        }

        $this->dropStaleInstances($abstract);

        if (is_null($concrete)) {
            $concrete = $abstract;
        }

        if (!$concrete instanceof Closure) {
            $concrete = $this->getClosure($abstract, $concrete);
        }

        $this->bindings[$abstract] = compact('concrete', 'shared');

        if ($this->resolved($abstract)) {
            $this->rebound($abstract);
        }
    }

    public function bindIf($abstract, $concrete = null, $shared = false) {
        if (!$this->bound($abstract)) {
            $this->bind($abstract, $concrete, $shared);
        }
    }

    public function singleton($abstract, $concrete = null) {
        $this->bind($abstract, $concrete, true);
    }

    public function extend($abstract, Closure $closure) {
        $abstract = $this->normalize($abstract);

        if (isset($this->instances[$abstract])) {
            $this->instances[$abstract] = $closure($this->instances[$abstract], $this);

            $this->rebound($abstract);
        } else {
            $this->extenders[$abstract][] = $closure;
        }
    }

    public function instance($abstract, $instance) {
        $abstract = $this->normalize($abstract);

        if (is_array($abstract)) {
            list($abstract, $alias) = $this->extractAlias($abstract);

            $this->alias($abstract, $alias);
        }

        unset($this->aliases[$abstract]);

        $this->instances[$abstract] = $instance;

        if ($this->bound($abstract)) {
            $this->rebound($abstract);
        }
    }

    /**
     * 实例化给定抽象类
     *
     * @param string $abstract
     * @param array $parameters
     * @return mixed
     **/
    public function make($abstract, array $parameters = array()) {
        $abstract = $this->getAlias($this->normalize($abstract));

        if (isset($this->instances[$abstract])) {
            return $this->instances[$abstract];
        }

        $concrete = $this->getConceret($abstract);

        if ($this->isBuildable($concrete, $abstract)) {
            $object = $this->build($concrete, $parameters);
        } else {
            $object = $this->make($concrete, $parameters);
        }

        foreach ($this->getExtenders($abstract) as $extender) {
            $object = $extender($object, $this);
        }

        if ($this->isShared($abstract)) {
            $this->instances[$abstract] = $object;
        }

        $this->fireResolvingCallbacks($abstract, $object);

        $this->resolved[$abstract] = true;

        return $object;
    }

    /**
     * 将一个实体类实例化
     *
     * @param mixed $concrete
     * @param array $parameters
     * @return mixed
     **/
    public function build($concrete, $parameters = array())
    {
        if ($concrete instanceof Closure) {
            return $concrete($this, $parameters);
        }

        $reflector = new \ReflectionClass($concrete);

        if (!$reflector->isInstantiable()) {
            if (!empty($this->buildStack)) {
                $previous = implode(', ', $this->buildStack);

                $message = "目标[$concrete]无法被实例化，[$previous]。";
            } else {
                $message = "目标[$concrete]无法被实例化。";
            }

            throw new BindingResolutionException($message);
        }

        $this->buildStack[] = $concrete;

        $constructor = $reflector->getConstructor();

        if (is_null($constructor)) {
            array_pop($this->buildStack);

            return new $concrete;
        }

        $dependecies = $constructor->getParameters();

        $parameters = $this->keyParametersByArgument($dependecies, $parameters);

        $instances = $this->getDependencies($dependecies, $parameters);

        array_pop($this->buildStack);

        return $reflector->newInstanceArgs($instances);
    }

    public function call($callback, array $parameters = array(), $defaultMethod = null) {
    }

    public function resolved($abstract) {
        $abstract = $this->normalize($abstract);

        if ($this->isAlias($abstract)) {
            $abstract = $this->getAlias($abstract);
        }

        return isset($this->resolved[$abstract]) || isset($this->instances[$abstract]);
    }

    public function resolving($abstract, Closure $callback = null) {
    }

    public function afterResolving($abstract, Closure $callback = null) {
    }

    /**
     * 获取容器中的服务
     *
     * @param string $key
     * @return mixed
     **/
    public function __get($key)
    {
        return $this[$key];
    }

    /**
     * 设置容器中的服务
     *
     * @param string $key
     * @param mixed $value
     * @return void
     **/
    public function __set($key, $value)
    {
        $this[$key] = $value;
    }

    /**
     * 将给定的类名称标准化
     *
     * @param mixed $service
     * @return mixed
     **/
    protected function normalize($service)
    {
        return is_string($service) ? ltrim($service, '\\') : $service;
    }

    /**
     * 从一个数组中解析类和别名
     *
     * @param array $definition
     * @return array
     **/
    protected function extractAlias(array $definition)
    {
        return array(key($definition), current($definition));
    }

    /**
     * 移除旧的别名和实例对象
     *
     * @param string $abstract
     * @return void
     **/
    protected function dropStaleInstances($abstract)
    {
        unset($this->instances[$abstract], $this->aliases[$abstract]);
    }

    /**
     * 获取一个可以实例化指定类的Closure
     *
     * @param string $abstract
     * @param string $concrete
     * @return \Closure
     **/
    protected function getClosure($abstract, $concrete)
    {
        return function($c, $parameters = array()) use ($abstract, $concrete) {
            $method = ($abstract == $concrete) ? 'build' : 'make';

            return $c->$method($concrete, $parameters);
        };
    }

    /**
     * 根据别名获取完整类名
     *
     * @param string $abstract
     * @return string
     **/
    protected function getAlias($abstract)
    {
        return isset($this->aliases[$abstract]) ? $this->aliases[$abstract] : $abstract;
    }

    /**
     * 调用给定类的rebound回调
     *
     * @param string $abstract
     * @return void
     **/
    protected function rebound($abstract)
    {
        $instance = $this->make($abstract);

        foreach ($this->getReboundCallbacks($abstract) as $callback) {
            call_user_func($callback, $this, $instance);
        }
    }

    /**
     * 根据给定类获取重绑定回调
     *
     * @param string $abstract
     * @return array
     **/
    protected function getReboundCallbacks($abstract)
    {
        if (isset($this->reboundCallbacks[$abstract])) {
            return $this->reboundCallbacks[$abstract];
        }

        return array();
    }

    /**
     * 获取指定抽象类的实例化类型
     *
     * @param string $abstract
     * @return mixed $concrete
     **/
    protected function getConceret($abstract)
    {
        if (!isset($this->bindings[$abstract])) {
            return $abstract;
        }

        return $this->bindings[$abstract]['concrete'];
    }

    /**
     * 判断给定的实例化对象能否被build
     *
     * @param mixed $concrete
     * @param string $abstract
     * @return bool
     **/
    protected function isBuildable($concrete, $abstract)
    {
        return $concrete === $abstract || $concrete instanceof Closure;
    }

    /**
     * 如果显式参数是用数字做数组标识的，那么根据需要的参数重新定义数组键
     *
     * @param array $dependencies
     * @param array $parameters
     * @return array
     **/
    protected function keyParametersByArgument(array $dependencies, array $parameters)
    {
        foreach ($parameters as $key => $value) {
            if (is_numeric($key)) {
                unset($parameters[$key]);
                $parameters[$dependencies[$key]->name] = $value;
            }
        }

        return $parameters;
    }

    /**
     * 从ReflectionParameters中获取所有构建对象需要的依赖
     *
     * @param array $parameters
     * @param array $primitives
     * @return array
     **/
    protected function getDependencies(array $parameters, array $primitives = array())
    {
        $dependencies = array();

        foreach ($parameters as $parameter) {
            $dependency = $parameter->getClass();

            if (array_key_exists($parameter->name, $primitives)) {
                $dependencies[] = $primitives[$parameter->name];
            } elseif (is_null($dependency)) {
                $dependencies[] = $this->resolveNonClass($parameter);
            } else {
                $dependencies[] = $this->resolveClass($parameter);
            }
        }

        return $dependencies;
    }

    protected function resolveNonClass(ReflectionParameter $parameter)
    {
        if ($parameter->isDefaultValueAvailable()) {
            return $parameter->getDefaultValue();
        }

        $message = "在类{$parameter->getDeclaringClass()->getName()}无法获取构建时依赖：[$parameter]。";

        throw new BindingResolutionException($message);
    }

    protected function resolveClass(ReflectionParameter $parameter)
    {
        try {
            return $this->make($parameter->getClass()->name);
        } catch (BindingResolutionException $e) {
            if ($parameter->isOptional()) {
                return $parameter->getDefaultValue();
            }

            throw $e;
        }
    }

    /**
     * 获取指定类的扩展回调
     *
     * @param string $abstract
     * @return array
     **/
    protected function getExtenders($abstract)
    {
        if (isset($this->extenders[$abstract])) {
            return $this->extenders[$abstract];
        }

        return array();
    }

    /**
     * 判断一个给定类是否是共享的
     *
     * @param string $abstract
     * @return bool
     **/
    protected function isShared($abstract)
    {
        $abstract = $this->normalize($abstract);

        if (isset($this->instances[$abstract])) {
            return true;
        }

        if (!isset($this->bindings[$abstract]['shared'])) {
            return false;
        }

        return $this->bindings[$abstract]['shared'] === true;
    }

    /**
     * 调用所有构建回调
     *
     * @param string $abstract
     * @param mixed $object
     * @return void
     **/
    protected function fireResolvingCallbacks($abstract, $object)
    {
        $this->fireCallbackArray($object, $this->globalResolvingCallbacks);

        $this->fireCallbackArray(
            $object, $this->getCallbacksForType(
                $abstract, $object, $this->resovingCallbacks
            )
        );

        $this->fireCallbackArray($object, $this->globalAfterResolvingCallbacks);

        $this->fireCallbackArray(
            $object, $this->getCallbacksForType(
                $abstract, $object, $this->afterResovingCallbacks
            )
        );
    }

    /**
     * 获取给定类型的回调
     *
     * @param string $abstract
     * @param mixed $object
     * @param array $callbacksPerType
     * @return array
     **/
    protected function getCallbacksForType($abstract, $object, array $callbacksPerType)
    {
        $result = array();

        foreach ($callbacksPerType as $type => $callbacks) {
            if ($type === $abstract || $object instanceof $type) {
                $results = array_merge($result, $callbacks);
            }
        }

        return $results;
    }

    /**
     * 调用一个数组的回调
     *
     * @param mixed $object
     * @param array $callbacks
     * @return void
     **/
    protected function fireCallbackArray($object, $callbacks)
    {
        foreach ($callbacks as $callback) {
            $callbacks($object, $this);
        }
    }
 }
