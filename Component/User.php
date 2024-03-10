<?php
namespace CarlHenkelB2B\Component;

use CarlHenkelB2B\CarlHenkelB2B;

class User
{
    private $mailer = null;
    private $acl = null;

    public function __construct()
    {
        $this->mailer = Shopware()->Container()->get('carlhenkel_b2b.mailer');
        $this->acl = Shopware()->Container()->get('carlhenkel_b2b.acl');
    }

    public function getUserFromCustomerNumber(string $customerNumber): array
    {
        $sql = 'SELECT s_user.*
                  FROM s_user
                  LEFT JOIN s_user_attributes
                         ON s_user_attributes.userID = s_user.id
                 WHERE s_user_attributes.text1 = :customerNumber
                   AND active = 1
        ';

        $params = [
            'customerNumber' => $customerNumber
        ];

        return Shopware()->Db()->fetchAll($sql, $params);
    }


    public function getUserFromId(?int $userId): array
    {
        $sql = 'SELECT s_user.*, s_user_attributes.*
                  FROM s_user
                  LEFT JOIN s_user_attributes
                         ON s_user_attributes.userID = s_user.id
                 WHERE s_user.id = :userId
        ';

        $params = [
            'userId' => (int)$userId
        ];

        return Shopware()->Db()->fetchRow($sql, $params);
    }

    public function getB2BUser(?string $customerNumber): array
    {
        if($customerNumber == null || $customerNumber == '')
        {
            return [];
        }

        $sql = 'SELECT s_user_attributes.text1 as customerNumber,
                       s_user_attributes.text2 as subNumber,
                       s_user_attributes.b2b_budget,
                       s_user.*,
                       CAST(s_user_attributes.text2 as SIGNED) as subNumberOrder
                  FROM s_user
                  LEFT JOIN s_user_attributes
                         ON s_user_attributes.userID = s_user.id
                 WHERE s_user_attributes.text1 = :customerNumber
                   AND active = 1
                 ORDER BY subNumberOrder
        ';

        $params = [
            'customerNumber' => $customerNumber
        ];

        return Shopware()->Db()->fetchAll($sql, $params);
    }

    public function getB2BUserList(): array
    {
        [$userId, $customerNumber] = $this->getUserIdAndCustomerNumberAndBudget();

        return $this->getB2BUser($customerNumber);
    }

    public function sendRequestNewUserNotification(array $userData, array $additionalData): void
    {
        $currentUserId = Shopware()->Session()->get('sUserId');

        if($this->acl->hasPermission($currentUserId, Acl::PERMISSION_REQUEST_USER_SHORT))
        {
            $this->mailer->sendEmail(
                0,
                Mailer::NOTIFICATION_ADMIN_REQUEST_USER,
                $userData,
                $additionalData
            );
        }
    }

    public function sendRequestChangeUserNotification(int $changeUserId, array $userData, array $addionalData): void
    {
        $currentUserId = Shopware()->Session()->get('sUserId');

        if($this->acl->hasPermission($currentUserId, Acl::PERMISSION_CHANGE_USER_SHORT))
        {
            $userToBeChanged = $this->getUserFromId($changeUserId);
            if( ! $userToBeChanged)
            {
                return;
            }

            $addionalData['changeUser'] = $userToBeChanged;

            $this->mailer->sendEmail(
                0,
                Mailer::NOTIFICATION_ADMIN_CHANGE_USER,
                $userData,
                $addionalData
            );
        }
    }
    public function sendRequestDeactivateUserNotification(int $deactivateUserId, array $userData): void
    {
        $currentUserId = Shopware()->Session()->get('sUserId');

        if($this->acl->hasPermission($currentUserId, Acl::PERMISSION_CHANGE_USER_SHORT))
        {
            $userToBeDeactivated = $this->getUserFromId($deactivateUserId);
            if( ! $userToBeDeactivated)
            {
                return;
            }

            $this->mailer->sendEmail(
                0,
                Mailer::NOTIFICATION_ADMIN_DELETE_USER,
                $userData,
                ['deactivateUser' => $userToBeDeactivated]
            );
        }
    }

    public function getUsersAllowedToApprove(?int $userIdLoggedIn, ?string $customerNumber): array
    {
        $approver = [];
        if($userIdLoggedIn != null)
        {
            if($this->acl->hasPermission($userIdLoggedIn, Acl::PERMISSION_REQUEST_SHORT))
            {
                $tmpApprover = $this->getB2BUser($customerNumber);
                foreach($tmpApprover as $thisApprover)
                {
                    $approverId = $thisApprover['id'];

                    if($approverId != $userIdLoggedIn &&
                        $this->acl->hasPermission($approverId, Acl::PERMISSION_APPROVE_SHORT))
                    {
                        $approver[$approverId] = $thisApprover['lastname'] . ', ' . $thisApprover['firstname'];
                    }
                }
            }

        }

        return $approver;
    }

    /*
     * returns the userId, customerNumber and budget of the logged in user
     * @returns array[$userId, $customerNumber, $budget]
     * NULL if no user is logged in
     */
    public function getUserIdAndCustomerNumberAndBudget(): array
    {
        $userId = null;
        $customerNumber = null;
        $budget = 0.0;

        $userData = Shopware()->Modules()->Admin()->sGetUserData();
        if($userData != null)
        {
            $userId = Shopware()->Session()->sUserId;
            $customerNumber = $userData['additional']['user'][CarlHenkelB2B::FREETEXT_USER_CUSTOMER_NUMBER];
            $budget = $userData['additional']['user'][CarlHenkelB2B::FREETEXT_USER_B2B_BUDGET];
        }

        $budget = (float)$budget ?? 0.0;

        return [$userId, $customerNumber, $budget];
    }

    public function getLoggedInUserId(): int
    {
        return (int)Shopware()->Session()->sUserId;
    }
}