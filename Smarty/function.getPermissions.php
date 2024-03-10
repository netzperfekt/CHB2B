<?php

if( ! function_exists('smarty_function_getPermissions'))
{
	function smarty_function_getPermissions($param, &$smarty)
	{
		$userId = (int)$param['userId'];
		$acl = Shopware()->Container()->get('carlhenkel_b2b.acl');

		$permissions = $acl->getPermissions($userId);

		// smarty function doesn't return anything
		$smarty->assign($param['out'], $permissions);
	}
}
