<?php

use CarlHenkelB2B\CarlHenkelB2B;
use CarlHenkelB2B\Component\Acl;

class Shopware_Controllers_Frontend_CarlHenkelB2B extends Enlight_Controller_Action
{
    protected $basket;
    protected $session;
    protected $carts;
    protected $user;
    protected $acl;
    protected $snippets;

    public function init()
    {
        $this->session = Shopware()->Session();
        $this->snippets = Shopware()->Container()->get('snippets')
                                                 ->getNamespace('netzperfekt/carlhenkelb2b');
        $this->basket = Shopware()->Modules()->Basket();

        $this->carts = Shopware()->Container()->get('carlhenkel_b2b.carts');
        $this->user = Shopware()->Container()->get('carlhenkel_b2b.user');
        $this->acl = Shopware()->Container()->get('carlhenkel_b2b.acl');
    }

    // ----- cart actions

    // show request carts list
    public function cartsAction()
    {
        $userId = $this->user->getLoggedInUserId();

        if( ! $this->acl->hasOneOfThisRoles($userId, [
                Acl::ROLE_REQUESTER_SHORT,
                Acl::ROLE_APPROVER_SHORT,
                Acl::ROLE_TWO_PERSON_SHORT,
                Acl::ROLE_MAIN_SHORT,
                Acl::ROLE_VIEWER_SHORT
        ]))
        {
            return $this->abort(403, 'carts');
        }

        // Liste aller (von mir) freizugebenden Anforderungen mit "Prüfen"-Button
        $canShowApprovals = $this->acl->hasOneOfThisRoles($userId, [
            Acl::ROLE_APPROVER_SHORT, Acl::ROLE_TWO_PERSON_SHORT, Acl::ROLE_MAIN_SHORT, Acl::ROLE_VIEWER_SHORT
        ]);

        // Liste aller (von mir) angeforderten Bestellungen mit "Edit" und "Delete"-Button
        $canShowRequests = $this->acl->hasOneOfThisRoles($userId, [
            Acl::ROLE_REQUESTER_SHORT, Acl::ROLE_TWO_PERSON_SHORT
        ]);

        // Liste aller Anforderungen (mit meiner Haupt-Kundennummer), ohne Buttons/Bearbeitungsmöglichkeiten
        $canShowHistory =  $this->acl->hasOneOfThisRoles($userId, [
            Acl::ROLE_APPROVER_SHORT, Acl::ROLE_TWO_PERSON_SHORT, Acl::ROLE_MAIN_SHORT, Acl::ROLE_VIEWER_SHORT
        ]);

        $cartsLists = $this->carts->getCartsLists();

        $this->View()->assign([
            'cartApprovals'      => $cartsLists['cartApprovals'],
            'cartRequests'       => $cartsLists['cartRequests'],
            'cartHistory'        => $cartsLists['cartHistory'],

            'cartCountApprovals' => $cartsLists['cartCountApprovals'],
            'cartCountRequests'  => $cartsLists['cartCountRequests'],

            'canShowApprovals'   => $canShowApprovals,
            'canShowRequests'    => $canShowRequests,
            'canShowHistory'     => $canShowHistory,

            'sAction'            => 'carts'
        ]);
    }

    // request cart
    public function cartRequestAction()
    {
        $userData = $this->checkAuth();
        if($userData === false) {
            return $this->abort(403, 'carts');
        }

        $sessionId = $this->session->getId();
        $userId = (int)$this->session->get('sUserId');

        $isAllowed = $this->acl->hasPermission($userId, Acl::PERMISSION_REQUEST_SHORT);

        if($isAllowed)
        {
            $basketItems = $this->carts->getBasketItems($sessionId, $userId);
            // all requesters are only allowed to request their own carts
            $isAllowed = (int)$basketItems[0]['userID'] == $userId;
        }

        if( ! $isAllowed)
        {
            return $this->abort(403, 'carts');
        }

        $customerNumber = $userData['additional']['user'][CarlHenkelB2B::FREETEXT_USER_CUSTOMER_NUMBER];

        $approverId = (int)$this->request->get('approverid', 0);

        $this->carts->requestBasketItems($basketItems, $customerNumber);
        $this->basket->sDeleteBasket();
        $this->carts->sendApprovalNotification($customerNumber, $approverId, $userData, $basketItems[0]);

        $this->redirectTo('carts', $this->snippets->get('msg_cart_approve'));
    }

    // copy request cart to shopware cart
    public function cartEditAction()
    {
        $userData = $this->checkAuth();
        if($userData === false) {
            return $this->abort(403, 'carts');
        }

        $cartId = $this->request->get('id');
        if($cartId == null)
        {
            return $this->abort(403, 'carts');
        }

        $userId = (int)$this->session->get('sUserId');
        $isAllowed = $this->acl->hasPermission($userId, Acl::PERMISSION_REQUEST_SHORT);

        if($isAllowed)
        {
            $cart = $this->carts->getCart($cartId);
            // all requesters are only allowed to edit their own carts
            $isAllowed = $this->acl->customerNumberMatches($cart, $userData);
        }

        if( ! $isAllowed)
        {
            return $this->abort(403, 'carts');
        }

        $this->basket->sDeleteBasket();
        $this->carts->editCart($cartId);

        $this->redirect([
            'module'     => 'frontend',
            'controller' => 'checkout',
            'action'     => 'cart'
        ]);
    }

    // delete request cart
    public function cartDeleteAction()
    {
        $userData = $this->checkAuth();
        if($userData === false) {
            return $this->abort(403, 'carts');
        }

        $cartId = $this->request->get('id');
        if($cartId == null)
        {
            return $this->abort(403, 'carts');
        }

        $userId = (int)$this->session->get('sUserId');
        $isAllowed = $this->acl->hasPermission($userId, Acl::PERMISSION_DELETE_CART_SHORT);

        if($isAllowed)
        {
            // requester are only allowed to delete their own carts
            if($this->acl->hasPermission($userId, Acl::PERMISSION_REQUEST_SHORT))
            {
                $cart = $this->carts->getCart($cartId);
                $isAllowed = (int)$cart['userID'] == $userId;
            }
        }

        if($isAllowed)
        {
            // all users are only allowed to delete carts with the same (main) customer number
            $isAllowed = $this->acl->customerNumberMatches($cart, $userData);
        }

        if( ! $isAllowed)
        {
            return $this->abort(403, 'carts');
        }

        $this->carts->deleteCart($cartId);

        $this->redirectTo('carts', $this->snippets->get('msg_cart_deleted'));
    }

    // approve request cart, copy items to shopware cart
    public function cartApproveAction()
    {
        $userData = $this->checkAuth();
        if($userData === false) {
            return $this->abort(403, 'carts');
        }

        $cartId = $this->request->get('id');
        if($cartId == null)
        {
            return $this->abort(403, 'carts');
        }

        $userId = (int)$this->session->get('sUserId');
        $isAllowed = $this->acl->hasPermission($userId, Acl::PERMISSION_APPROVE_SHORT);

        if($isAllowed)
        {
            $cart = $this->carts->getCart($cartId);
            // users are only allowed to approve carts with the same (main) customer number
            // and are not allowed to approve own requestes carts
            $requestingUserId = $cart['userID'];
            $isAllowed = $this->acl->customerNumberMatches($cart, $userData) &&
                         $requestingUserId != $userId;
        }

        if( ! $isAllowed)
        {
            return $this->abort(403, 'carts');
        }

        $this->basket->sDeleteBasket();
        $this->carts->editCart($cartId);

        $this->redirect([
            'module'     => 'frontend',
            'controller' => 'checkout'
        ]);
    }

    // reject requested cart with message
    public function cartRejectAction()
    {
        $userData = $this->checkAuth();
        if($userData === false) {
            return $this->abort(403, 'carts');
        }

        $cartId = $this->request->get('cartid');
        if($cartId == null)
        {
            return $this->abort(403, 'carts');
        }

        $message = $this->request->get('rejectmessage', '');

        $userId = (int)$this->session->get('sUserId');
        $isAllowed = $this->acl->hasPermission($userId, Acl::PERMISSION_APPROVE_SHORT);

        if($isAllowed)
        {
            $cart = $this->carts->getCart($cartId);
            // users are only allowed to reject carts with the same (main) customer number
            $isAllowed = $this->acl->customerNumberMatches($cart, $userData);
        }

        if( ! $isAllowed)
        {
            return $this->abort(403, 'carts');
        }

        $this->carts->rejectCart($cartId, $userId, $message);
        // reihenfolge ist wichtig - message wird gesetzt!
        $cart = $this->carts->getCartItems($cartId)[0];

        $this->carts->sendRejectionNotification(
            (int)$cart['userID'],
            $userData,
            $cart
        );

        $this->redirectTo('carts', $this->snippets->get('msg_cart_rejected'));
    }

    // ----- user actions

    // show user list
    public function userAction()
    {
        $userId = (int)$this->session->get('sUserId');

        if( ! $this->acl->hasPermission($userId, Acl::PERMISSION_SHOW_USER_SHORT))
        {
            return $this->abort(403, 'carts');
        }

        $userList = $this->user->getB2BUserList();

        $this->View()->assign('b2bUser', $userList);
        $this->View()->assign('loggedInUserId', $userId);
        $this->View()->assign('sAction', 'user');
    }

    // request new user via mail
    public function userNewAction()
    {
        $userData = $this->checkAuth();
        if($userData === false) {
            return $this->abort(403, 'carts');
        }

        $userId = (int)$this->session->get('sUserId');
        $isAllowed = $this->acl->hasPermission($userId, Acl::PERMISSION_REQUEST_USER_SHORT);
        if( ! $isAllowed)
        {
            return $this->abort(403, 'carts');
        }

        $newUserEMail = trim($this->request->get('user_email', ''));
        if($newUserEMail == '')
        {
            return $this->abort(403, 'carts');
        }

        $newUserData = [
            'email'     => $newUserEMail,
            'firstName' => $this->request->get('user_firstname'),
            'lastName'  => $this->request->get('user_lastname'),
            'budget'    => $this->request->get('user_budget'),
            'roles'     => implode(',', $this->request->get('user_roles') ?? [])
        ];

        $this->user->sendRequestNewUserNotification($userData, $newUserData);

        $this->redirectTo('user', $this->snippets->get('msg_user_requested'));
    }

    // request user deactivation via mail
    public function userDeactivateAction()
    {
        $userData = $this->checkAuth();
        if($userData === false) {
            return $this->abort(403, 'carts');
        }

        $deactivateUserId = (int)$this->request->get('id', 0);
        if($deactivateUserId == 0)
        {
            return $this->abort(403, 'carts');
        }

        $userId = (int)$this->session->get('sUserId');
        $isAllowed = $this->acl->hasPermission($userId, Acl::PERMISSION_CHANGE_USER_SHORT);

        if($isAllowed)
        {
            $user = $this->user->getUserFromId($deactivateUserId);
            // users are only allowed to deactivate users with the same (main) customer number
            $isAllowed = $userData['additional']['user'][CarlHenkelB2B::FREETEXT_USER_CUSTOMER_NUMBER] ==
                         $user[CarlHenkelB2B::FREETEXT_USER_CUSTOMER_NUMBER];
        }

        if( ! $isAllowed)
        {
            return $this->abort(403, 'carts');
        }

        $this->user->sendRequestDeactivateUserNotification($deactivateUserId, $userData);

        $this->redirectTo('user', $this->snippets->get('msg_user_deactivated'));
    }

    // request user change via mail
    public function userChangeAction()
    {
        $userData = $this->checkAuth();
        if($userData === false) {
            return $this->abort(403, 'carts');
        }

        $changeUserId = (int)$this->request->get('id');
        if($changeUserId == 0)
        {
            return $this->abort(403, 'carts');
        }

        $userId = (int)$this->session->get('sUserId');
        $isAllowed = $this->acl->hasPermission($userId, Acl::PERMISSION_CHANGE_USER_SHORT);

        if($isAllowed)
        {
            $user = $this->user->getUserFromId($changeUserId);
            // users are only allowed to change users with the same (main) customer number
            $isAllowed = $userData['additional']['user'][CarlHenkelB2B::FREETEXT_USER_CUSTOMER_NUMBER]
                            == $user[CarlHenkelB2B::FREETEXT_USER_CUSTOMER_NUMBER];
        }

        if( ! $isAllowed)
        {
            return $this->abort(403, 'carts');
        }

        $changeData = [
            'budget' => $this->request->get('user_budget'),
            'roles' => implode(',', $this->request->get('user_roles') ?? [])
        ];

        $this->user->sendRequestChangeUserNotification($changeUserId, $userData, $changeData);

        $this->redirectTo('user', $this->snippets->get('msg_user_changed'));
    }

    // ------ helper methods
    private function checkAuth()
    {
        $fail = $this->session == null;

        if( ! $fail)
        {
            $fail = Shopware()->Session()->sUserId == 0;
        }

        if($fail) {
            $this->redirect([
                'controller' => 'account',
                'action' => 'index'
            ]);

            return false;
        }

        return Shopware()->Modules()->Admin()->sGetUserData();
    }

    private function redirectTo($action, $message = '', $statusCode = null)
    {
        $params = [
            'module'     => 'frontend',
            'controller' => 'CarlHenkelB2B',
            'action'     => $action
        ];
        $options = [];

        if($message != null && $message != '') {
            $params['message'] = $message;
        }
        if($statusCode != null)
        {
            $options['code'] = $statusCode;
        }

        $this->redirect($params, $options);
    }

    private function abort($statusCode, $action)
    {
        $this->front->Plugins()->ViewRenderer()->setNoRender();
        $this->redirectTo($action, null, $statusCode);

        return true;
    }
}
