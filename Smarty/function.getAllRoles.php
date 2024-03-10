<?php

if( ! function_exists('smarty_function_getAllRoles'))
{
    die ("s1");
	function smarty_function_getAllRoles($param, &$smarty)
	{
		$acl = Shopware()->Container()->get('carlhenkel_b2b.acl');

		$roles = $acl->getAllRoles();

		// smarty function doesn't return anything
		$smarty->assign($param['out'], $roles);
	}
}
