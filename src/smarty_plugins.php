<?php
use AccessModel as Access;

if (! function_exists('smarty_block_permission')) {
    function smarty_block_permission($params, $content, Smarty_Internal_Template $template, &$repeat)
    {
        if (! $repeat) {
            $name      = $params['name'];
            $adminUser = AdminUserModel::getCurrentAdminUser();
            $access    = new Access();

            if ($access->hasPermission($adminUser['admin_user_id'], $name)) {
                return $content;
            }
        }
    }
}
