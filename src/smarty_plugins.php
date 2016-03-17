<?php
if (! function_exists('smarty_block_permission')) {
    function smarty_block_permission($params, $content, Smarty_Internal_Template $template, &$repeat)
    {
        if (! $repeat) {
            $name      = $params['name'];
            $adminUser = AdminUserModel::getCurrentAdminUser();

            if (has_permission($adminUser['admin_user_id'], $name)) {
                return $content;
            }
        }
    }
}

if (! function_exists('smarty_block_role')) {
    function smarty_block_role($params, $content, Smarty_Internal_Template $template, &$repeat)
    {
        if (! $repeat) {
            $name      = $params['name'];
            $adminUser = AdminUserModel::getCurrentAdminUser();

            if (has_role($adminUser['admin_user_id'], $name)) {
                return $content;
            }
        }
    }
}
