<?php

use AccessModel as Access;

if (! function_exists('has_role')) {
    function has_role($adminUserId, $nameOrId)
    {
        $access = Access::getInstance();

        return $access->hasRole($adminUserId, $nameOrId);
    }
}

if (! function_exists('has_permission')) {
    function has_permission($adminUserId, $nameOrId)
    {
        $access = Access::getInstance();

        return $access->hasPermission($adminUserId, $nameOrId);
    }
}

if (! function_exists('has_permissions')) {
    function has_permissions($adminUserId, $permissions, $needsAll = false)
    {
        $access = Access::getInstance();

        return $access->hasPermissions($adminUserId, $permissions, $needsAll);
    }
}

if (! function_exists('attach_role')) {
    function attach_role($adminUserId, $roleId)
    {
        $access = Access::getInstance();

        return $access->attachRole($adminUserId, $roleId);
    }
}

if (! function_exists('detach_role')) {
    function detach_role($adminUserId)
    {
        $access = Access::getInstance();

        return $access->detachRole($adminUserId);
    }
}

if (! function_exists('assign_permissions')) {
    function assign_permissions($roleId, $permissionIds)
    {
        $access = Access::getInstance();

        return $access->assignPermissions($roleId, $permissionIds);
    }
}

if (! function_exists('unassign_permissions')) {
    function unassign_permissions($roleId, $permissionIds)
    {
        $access = Access::getInstance();

        return $access->unassignPermissions($roleId, $permissionIds);
    }
}

if (! function_exists('empty_assigned_permissions')) {
    function empty_assigned_permissions($roleId)
    {
        $access = Access::getInstance();

        $access->emptyAssignedPermissions($roleId);
    }
}

/**
 * 因为fnmatch在non-POSIX系统上无法运行
 **/
if(!function_exists('fnmatch')) {
    function fnmatch($pattern, $string) {
        return preg_match("#^".strtr(preg_quote($pattern, '#'), array('\*' => '.*', '\?' => '.'))."$#i", $string);
    }
}
