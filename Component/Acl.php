<?php
namespace CarlHenkelB2B\Component;

use CarlHenkelB2B\CarlHenkelB2B;

class Acl
{
    public const ROLE_VIEWER                =  1;
    public const ROLE_REQUESTER             = 10;
    public const ROLE_REQUESTER_BUDGET      = 11;
    public const ROLE_APPROVER              = 12;
    public const ROLE_TWO_PERSON            = 13;
    public const ROLE_MAIN                  = 14;
    public const ROLE_ADMIN                 = 20;

    public const ROLE_REQUESTER_SHORT       = 'requester';
    public const ROLE_REQUESTER_BUDGET_SHORT= 'requester_budget';
    public const ROLE_APPROVER_SHORT        = 'approver';
    public const ROLE_VIEWER_SHORT          = 'viewer';
    public const ROLE_TWO_PERSON_SHORT      = 'two_person';
    public const ROLE_MAIN_SHORT            = 'main';
    public const ROLE_ADMIN_SHORT           = 'admin';

    public const PERMISSION_REQUEST         = 1;
    public const PERMISSION_REQUEST_BUDGET  = 2;
    public const PERMISSION_ORDER_UNLIMITED = 3;

    public const PERMISSION_APPROVE         = 10;

    public const PERMISSION_DELETE_CART     = 20;

    public const PERMISSION_SHOW_USER       = 30;
    public const PERMISSION_REQUEST_USER    = 31;
    public const PERMISSION_CHANGE_USER     = 32;

    public const PERMISSION_REQUEST_SHORT        = 'request';
    public const PERMISSION_REQUEST_BUDGET_SHORT = 'request_budget';
    public const PERMISSION_ORDER_UNLIMITED_SHORT= 'order_unlimited';

    public const PERMISSION_APPROVE_SHORT        = 'approve';

    public const PERMISSION_DELETE_CART_SHORT    = 'delete_cart';

    public const PERMISSION_SHOW_USER_SHORT      = 'show_user';
    public const PERMISSION_REQUEST_USER_SHORT   = 'request_new_user';
    public const PERMISSION_CHANGE_USER_SHORT    = 'request_changes';

    public function getAllRoles(): array
    {
        $sql = 'SELECT chb2b_role.*
                  FROM chb2b_role
        ';

        return Shopware()->Db()->fetchAll($sql);
    }

    public function getRoles(?int $userId): array
    {
        $sql = 'SELECT chb2b_role.*
                  FROM chb2b_user_role
                  LEFT JOIN chb2b_role
                         ON chb2b_user_role.role_id = chb2b_role.id
                 WHERE user_id = :userId
        ';

        $params = [
            'userId' => (int)$userId,
        ];

        return Shopware()->Db()->fetchAll($sql, $params);
    }

    public function getRoleByShortName(string $roleShort): array
    {
        $sql = 'SELECT chb2b_role.*
                  FROM chb2b_role
                 WHERE short = :short
        ';

        $params = [
            'short' => $roleShort,
        ];

        $role = Shopware()->Db()->fetchRow($sql, $params);

        return $role ? $role : [];
    }

    public function isB2BUser(?int $userId): bool
    {
        if(!$userId)
        {
            return false;
        }

        $sql = 'SELECT count(chb2b_role.id) as roles
                  FROM chb2b_user_role
                  LEFT JOIN chb2b_role
                         ON chb2b_user_role.role_id = chb2b_role.id
                 WHERE user_id = :userId
        ';

        $params = [
            'userId' => (int)$userId,
        ];

        $roleCount = (int)Shopware()->Db()->fetchOne($sql, $params);

        return $roleCount > 0;
    }

    public function hasRole(?int $userId, string $roleShort): bool
    {
        if(!$userId)
        {
            return false;
        }

        $sql = 'SELECT count(id)
                  FROM chb2b_user_role
                  LEFT JOIN chb2b_role
                         ON chb2b_user_role.role_id = chb2b_role.id
                 WHERE chb2b_role.short = :roleShort
                   AND user_id = :userId
        ';

        $params = [
            'userId'    => (int)$userId,
            'roleShort' => $roleShort
        ];

        return (int)Shopware()->Db()->fetchOne($sql, $params) == 1;
    }

    // checks if user has AT LEAST one of the given roles
    public function hasOneOfThisRoles(?int $userId, array $rolesShort): bool
    {
        if(!$userId)
        {
            return false;
        }

        foreach($rolesShort as $roleShort)
        {
            if($this->hasRole($userId, $roleShort))
            {
                return true;
            }
        }

        return false;
    }

    public function getPermissions(?int $userId): array
    {
        $sql = 'SELECT chb2b_permission.*
                  FROM chb2b_user_role
                  LEFT JOIN chb2b_role
                         ON chb2b_user_role.role_id = chb2b_role.id
                  LEFT JOIN chb2b_role_permission
                         ON chb2b_user_role.role_id = chb2b_role_permission.role_id
                  LEFT JOIN chb2b_permission
                         ON chb2b_role_permission.permission_id = chb2b_permission.id
                      WHERE chb2b_user_role.user_id = :userId
        ';

        $params = [
            'userId' => (int)$userId,
        ];

        return Shopware()->Db()->fetchAll($sql, $params);
    }

    public function hasPermission(?int $userId, string $permissionShort): bool
    {
        if(!$userId)
        {
            return false;
        }

        $sql = 'SELECT count(*)
                  FROM chb2b_user_role
                  LEFT JOIN chb2b_role
                         ON chb2b_user_role.role_id = chb2b_role.id
                  LEFT JOIN chb2b_role_permission
                         ON chb2b_user_role.role_id = chb2b_role_permission.role_id
                  LEFT JOIN chb2b_permission
                         ON chb2b_role_permission.permission_id = chb2b_permission.id
                      WHERE chb2b_user_role.user_id = :userId
                        AND chb2b_permission.short = :permissionShort
        ';

        $params = [
            'userId'          => (int)$userId,
            'permissionShort' => $permissionShort
        ];

        return (int)Shopware()->Db()->fetchOne($sql, $params) == 1;
    }

    public function createRole(?int $roleId, string $roleShort, string $title, string $description): bool
    {
        $sql = 'INSERT IGNORE INTO `chb2b_role` (id, title, short, description)
                              VALUES (:id, :title, :short, :description)
        ';

        $params = [
            'id'          => $roleId,
            'short'       => $roleShort,
            'title'       => $title,
            'description' => $description
        ];

        return $this->executeSqlQuery($sql, $params);
    }

    public function createPermission(int $permissionId, string $permissionShort,
                                     string $title, string $description): bool
    {
        $sql = 'INSERT IGNORE INTO `chb2b_permission` (id, title, short, description)
                              VALUES (:id, :title, :short, :description)
        ';

        $params = [
            'id'          => $permissionId,
            'short'       => $permissionShort,
            'title'       => $title,
            'description' => $description
        ];

        return $this->executeSqlQuery($sql, $params);
    }

    public function addPermissionToRole(int $roleId, int $permissionId): bool
    {
        $sql = 'INSERT IGNORE INTO `chb2b_role_permission` (role_id, permission_id)
                            VALUES (:roleId, :permissionId)
        ';

        $params = [
            'roleId'       => $roleId,
            'permissionId' => $permissionId
        ];

        return $this->executeSqlQuery($sql, $params);
    }

    public function assignRolesToUser(int $userId, array $roles): bool
    {
        $sqlDelete = 'DELETE FROM `chb2b_user_role` WHERE user_id = :userId';
        Shopware()->Db()->query($sqlDelete, ['userId' => (int)$userId]);

        if( ! empty($roles))
        {
            $sql = 'INSERT INTO `chb2b_user_role` (user_id, role_id) VALUES ';
            $first = true;
            foreach ($roles as $role)
            {
                if (!$first) {
                    $sql .= ',';
                }
                $sql .= '(:userId, ' . (int)$role . ')';
                $first = false;
            }

            $params = [
                'userId' => (int)$userId
            ];

            return $this->executeSqlQuery($sql, $params);
        }

        return true;
    }

    public function customerNumberMatches(array $cart, array $userData): bool
    {
        return $cart && $userData &&
               $cart['customerNumber'] == $userData['additional']['user'][CarlHenkelB2B::FREETEXT_USER_CUSTOMER_NUMBER];
    }

    /*
     * determines which ordering rights a user has in relation to a shopware shopping cart
     * @returns array[$isAllowedToOrderCart, $isAllowedToRequestCart]
    */
    public function getCartAllowance(?int $userId, bool $budgetExceeded, ?int $requestingUserId): array
    {
        $isAllowedToOrderCart = false;
        $isAllowedToRequestCart = false;

        if($this->isB2BUser($userId))
        {
            if($this->hasPermission($userId, Acl::PERMISSION_REQUEST_BUDGET_SHORT) && ! $budgetExceeded)
            {
                $isAllowedToOrderCart = true;
            }

            if($this->hasPermission($userId, Acl::PERMISSION_REQUEST_SHORT))
            {
                $isAllowedToRequestCart = true;
            }

            if($this->hasPermission($userId, Acl::PERMISSION_APPROVE_SHORT) &&
                $requestingUserId != null && $requestingUserId != $userId)
            {
                $isAllowedToOrderCart = true;
            }

            if($this->hasPermission($userId, Acl::PERMISSION_ORDER_UNLIMITED_SHORT))
            {
                $isAllowedToOrderCart = true;
            }
        }
        else
        {
            // standard users are always allowed to order
            $isAllowedToOrderCart = true;
        }

        return [$isAllowedToOrderCart, $isAllowedToRequestCart];
    }

    // ----- helper methods
    private function executeSqlQuery(string $sqlStatement, array $params = [])
    {
        try {
            Shopware()->Db()->query($sqlStatement, $params);

            return true;
        }
        catch (\Exception $ex)
        {
            //
        }

        return false;
    }
}