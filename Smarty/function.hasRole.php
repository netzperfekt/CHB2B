<?php

if( ! function_exists('smarty_function_hasRole'))
{
	function smarty_function_hasRole($param, &$smarty)
	{
        $acl = Shopware()->Container()->get('carlhenkel_b2b.acl');

		$roleShort = $param['role'] ?? '';
        $userId = (int)$param['userId'];
        if($userId == 0) {
            $userId = Shopware()->Session()->get('sUserId');
        }

		// smarty function doesn't return anything but expects an output here ;-(
		echo $acl->hasRole($userId, $roleShort);
	}
}
