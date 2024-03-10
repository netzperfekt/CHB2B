<?php

if( ! function_exists('smarty_function_hasPermission'))
{
	function smarty_function_hasPermission($param, &$smarty)
	{
		$permissionShort = $param['permission'] ?? '';
		$userId = Shopware()->Session()->get('sUserId');
		$acl = Shopware()->Container()->get('carlhenkel_b2b.acl');

		// smarty function doesn't return anything but expects an output here ;-(
		echo $acl->hasPermission($userId, $permissionShort);
	}
}
