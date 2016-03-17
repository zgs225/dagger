<?php namespace Youplus\Foundation;

use Router;
use Message;
use Configure;
use \AdminUserModel as Admin;

class AccessValidator
{
    /**
     * 需要登录后才能访问的路由
     *
     * @var array
     **/
    protected $needsLogin = array();

    /**
     * 带有通配符的需要登录后才能访问的路由
     *
     * @var array
     **/
    protected $needsLoginWithWildcard = array();

    /**
     * 不需要登录就可以访问的路由
     *
     * @var array
     **/
    protected $dontNeedsLogin = array();

    /**
     * 需要验证角色才能访问的路由
     *
     * @var array
     **/
    protected $needsRole = array();

    /**
     * 需要验证权限才能访问的路由
     *
     * @var array
     **/
    protected $needsPermission = array();

    /**
     * 通配符
     *
     * @var string
     **/
    protected $wildcard = "*";

    /**
     * 对于访问的请求进行权限验证
     *
     * @return void
     **/
    public static function verify()
    {
        $accessValidator  = new static;

        include DAGGER_PATH_ROOT . 'libs/access.php';

        $router           = $accessValidator->getRouter();
        $currentAdminUser = Admin::getCurrentAdminUser();
        $adminUserId      = $currentAdminUser['admin_user_id'];

        if ($accessValidator->cant($adminUserId, $router)) {
            // TODO Ajax 对于JSON请求要换一种返回方式
            if ($accessValidator->isNeedsLogin($router) && ! $accessValidator->validLogin($adminUserId, $router)) {
                Message::showError('尚未登录:(', null, null, Router::createUrl('admin_user', 'login', null, 'admin'));
            } else {
                Message::showError('没有权限访问:(');
            }
        }
    }

    /**
     * 判断用户是否能够访问某个路由
     *
     * @param int $adminUserId
     * @param string $router
     * @return bool
     **/
    public function can($adminUserId, $router)
    {
        return $this->validLogin($adminUserId, $router) &&
               $this->validRole ($adminUserId, $router) &&
               $this->validPermission($adminUserId, $router);
    }

    /**
     * 判断用户不能访问某个路由
     *
     * @param int $adminUserId
     * @param string $router
     * @return bool
     **/
    public function cant($adminUserId, $router)
    {
        return ! $this->can($adminUserId, $router);
    }

    /**
     * 添加需要验证的路由
     *
     * @param array|string $router
     * @return void
     **/
    public function needsLogin($router)
    {
        if (! is_array($router)) {
            $router = array($router);
        }

        foreach ($router as $r) {
            if ($this->isWildcardRouter($r)) {
                $this->needsLoginWithWildcard[] = $r;
            } else {
                $this->needsLogin[] = $r;
            }
        }

        $this->needsLogin =
            array_unique($this->needsLogin);
        $this->needsLoginWithWildcard =
            array_unique($this->needsLoginWithWildcard);
    }

    /**
     * 添加不需要登录验证的路由
     *
     * @param array|string $router
     * @return void
     **/
    public function dontNeedsLogin($router)
    {
        if (! is_array($router)) {
            $router = array($router);
        }

        foreach ($router as $r) {
            $this->dontNeedsLogin[] = $r;
        }

        $this->dontNeedsLogin =
            array_unique($this->dontNeedsLogin);
    }

    /**
     * 验证路由是否需要登录
     *
     * @param string $router
     * @return bool
     **/
    public function isNeedsLogin($router)
    {
        if (in_array($router, $this->dontNeedsLogin)) {
            return false;
        }

        $wildcardCheck = false;

        foreach ($this->needsLoginWithWildcard as $pattern) {
            if (fnmatch($pattern, $router)) {
                $wildcardCheck = true;
                break;
            }
        }

        $normalCheck   = in_array($router, $this->needsLogin);

        return $wildcardCheck || $normalCheck;
    }

    /**
     * 添加需要验证角色的路由
     *
     * @param array|string $router
     * @param string $role
     * @return void
     **/
    public function needsRole($router, $role)
    {
        if (! is_array($router)) {
            $router = array($router);
        }

        foreach ($router as $key) {
            $this->needsRole[$key] = $role;
        }
    }

    /**
     * 验证访问路由是否需要角色
     *
     * @param string $router
     * @param string $role
     * @return bool;
     **/
    public function isNeedsRole($router, $role)
    {
        return isset($this->needsRole[$router]) && $this->needsRole[$router] === $role;
    }

    /**
     * 添加访问路由需要验证权限
     *
     * @param array|string $router
     * @param string $permission
     * @param bool $needsAll
     * @return void
     **/
    public function needsPermission($router, $permission, $needsAll = false)
    {
        if (! is_array($router)) {
            $router = array($router);
        }

        foreach ($router as $r) {
            $key = $needsAll ? 'and' : 'or';

            if (! isset($this->needsPermission[$r])) {
                $this->needsPermission[$r] = array();
            }

            if (! isset($this->needsPermission[$r][$key])) {
                $this->needsPermission[$r][$key] = array();
            }

            if (! in_array($permission, $this->needsPermission[$r][$key])) {
                $this->needsPermission[$r][$key][] = $permission;
            }

        }
    }

    /**
     * 添加路由需要多个权限
     *
     * @param string|array $router
     * @param array $permissions
     * @param bool $needsAll
     * @return void
     **/
    public function needsPermissions($router, $permissions, $needsAll = false)
    {
        if (! is_array($router)) {
            $router = array($router);
        }

        foreach ($router as $r) {
            foreach ($permissions as $permission) {
                $this->needsPermission($r, $permission, $needsAll);
            }
        }
    }

    /**
     * 验证访问路由是否需要权限
     *
     * @param string $router
     * @param string $permission
     * @return bool
     **/
    public function isNeedsPermission($router, $permission)
    {
        return isset($this->needsPermission[$router]) &&
            (
                (
                    isset($this->needsPermission[$router]['and']) &&
                    in_array($permission, $this->needsPermission[$router]['and'])
                )
                ||
                (
                    isset($this->needsPermission[$router]['or']) &&
                    in_array($permission, $this->needsPermission[$router]['or'])
                )
            );
    }

    protected function validPermission($adminUserId, $router)
    {
        if (! isset($this->needsPermission[$router])) {
            return true;
        }

        $and = $or = true;

        if (isset($this->needsPermission[$router]['or'])) {
            $permissions = $this->needsPermission[$router]['or'];

            $or = has_permissions($adminUserId, $permissions);
        }

        if (isset($this->needsPermission[$router]['and'])) {
            $permissions = $this->needsPermission[$router]['and'];

            $and = has_permissions($adminUserId, $permissions, true);
        }

        return $and && $or;
    }

    protected function validRole($adminUserId, $router)
    {
        if (! isset($this->needsRole[$router])) {
            return true;
        }

        $role = $this->needsRole[$router];

        return has_role($adminUserId, $role);
    }

    protected function validLogin($adminUserId, $router)
    {
        if (! $this->isNeedsLogin($router)) {
            return true;
        }

        $currentAdminUser = Admin::getCurrentAdminUser();

        return ! empty($currentAdminUser) &&
                 $currentAdminUser['admin_user_id'] == $adminUserId;
    }

    protected function getRouter()
    {
        return implode('.', array(
            Configure::$app, $_GET['c'], $_GET['a']
        ));
    }

    protected function isWildcardRouter($router)
    {
        return strpos($router, $this->wildcard);
    }
}
