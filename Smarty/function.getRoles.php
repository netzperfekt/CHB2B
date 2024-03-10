<?php

if( ! function_exists('smarty_function_getRoles'))
{
	function smarty_function_getRoles($param, &$smarty)
	{
		$userId = (int)$param['userId'];
		$acl = Shopware()->Container()->get('carlhenkel_b2b.acl');

        if($userId == 0) {
            $roles = $acl->getAllRoles();
        }
        else {
            $roles = $acl->getRoles($userId);
        }

		// smarty function doesn't return anything
		$smarty->assign($param['out'], $roles);
	}
}
