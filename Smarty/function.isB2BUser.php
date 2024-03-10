<?php

if( ! function_exists('smarty_function_isB2BUser'))
{
	function smarty_function_isB2BUser($param, &$smarty)
	{
        $acl = Shopware()->Container()->get('carlhenkel_b2b.acl');

        $userId = (int)$param['userId'];
        if($userId == 0) {
            $userId = Shopware()->Session()->get('sUserId');
        }

		// smarty function doesn't return anything but expects an output here ;-(
        echo $acl->isB2BUser($userId);
    }
}
