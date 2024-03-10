<?php
namespace CarlHenkelB2B\Subscriber;

use CarlHenkelB2B\CarlHenkelB2B;
use CarlHenkelB2B\Component\Acl;

class Subscriber implements \Enlight\Event\SubscriberInterface
{
    private $acl = null;
    private $user = null;
    private $carts = null;

    public function __construct()
    {
        $this->acl = Shopware()->Container()->get('carlhenkel_b2b.acl');
        $this->user = Shopware()->Container()->get('carlhenkel_b2b.user');
        $this->carts = Shopware()->Container()->get('carlhenkel_b2b.carts');
    }

    public static function getSubscribedEvents(): array
    {
        return [
            'Theme_Inheritance_Template_Directories_Collected'
                => 'onCollectDirectories',

            'Enlight_Controller_Action_PostDispatchSecure_Frontend_CarlHenkelB2B'
                => 'onCarlHenkelB2B',

            'Enlight_Controller_Action_PostDispatchSecure_Frontend_Checkout'
                => 'onCheckout',

            'Enlight_Controller_Action_PostDispatchSecure_Frontend_Account'
                => 'onAccount',

            'Enlight_Controller_Action_PostDispatchSecure_Frontend_Detail'
                => 'onDetailPage',

            'Shopware_Modules_Order_SaveOrder_OrderCreated'
                => 'onOrderCreated'
        ];
    }

    public function onCollectDirectories(\Enlight_Event_EventArgs $args)
    {
        $directories = $args->getReturn();
        array_push($directories, __DIR__ . "/../Resources/Views/");

        return $directories;
    }

    // add message variable to all views in controller "CarlHenkelB2B"
    public function onCarlHenkelB2B(\Enlight_Controller_ActionEventArgs $args)
    {
        $subject = $args->getSubject();
        $request = $subject->request();
        $view = $subject->View();

        $view->assign('CHB2BMessage', $request->get('message', ''));
    }

    // add badge counts to account/sidebar
    public function onAccount(\Enlight_Event_EventArgs $eventArgs)
    {
        $cartsLists = $this->carts->getCartsLists();

        $view = $eventArgs->getSubject()->View();

        $view->assign('cartCountApprovals', $cartsLists['cartCountApprovals']);
        $view->assign('cartCountRequests', $cartsLists['cartCountRequests']);
    }

    // assign view variables and check allowances on checkout/cart
    // also ensure that an user is allowed to go to the checkout page -> otherwise redirect to home page
    public function onCheckout(\Enlight_Event_EventArgs $eventArgs)
    {
        $subject = $eventArgs->getSubject();
        $request = $subject->request();
        $action = $request->getActionName();
        $view = $subject->View();

        [$userId, $customerNumber, $userBudget] = $this->user->getUserIdAndCustomerNumberAndBudget();

        $shopwareBasket = $subject->getBasket();
        $requestCartId = $this->carts->getRequestCartIdFromShopwareBasket($shopwareBasket['content']);

        $basketValue = $this->floatvalue($shopwareBasket['AmountNet']);
        $budgetExceeded = $basketValue > $userBudget;

        $cart = $this->carts->getCart($requestCartId);
        $requestingUserId = $cart['userID'];

        [$isAllowedToOrderCart, $isAllowedToRequestCart] = $this->acl->getCartAllowance(
            $userId, $budgetExceeded, $requestingUserId
        );

        $view->assign('cartId', $requestCartId);

        if($action == 'cart' || $action == 'ajaxCart')
        {
            $view->assign([
                'approver'               => $this->user->getUsersAllowedToApprove($userId, $customerNumber),
                'isLoggedIn'             => $userId != null,
                'userBudget'             => $userBudget,
                'budgetExceeded'         => $budgetExceeded,
                'isAllowedToOrderCart'   => $isAllowedToOrderCart,
                'isAllowedToRequestCart' => $isAllowedToRequestCart,
            ]);
        }

        // redirect users without cart allowance to index
        if($action == 'confirm' && ! $isAllowedToOrderCart)
        {
            $eventArgs->getSubject()->forward('index', 'index');
        }
    }

    public function onDetailPage(\Enlight_Event_EventArgs $args)
    {
        $view = $args->getSubject()->View();

        $requestCartId = null;
        $shopwareBasket = Shopware()->Modules()->Basket()->sGetBasket();
        if(is_array($shopwareBasket) && ! empty($shopwareBasket))
        {
            $requestCartId = $this->carts->getRequestCartIdFromShopwareBasket($shopwareBasket['content']);
        }

        $view->assign('cartId', $requestCartId);
    }

    // if an order has been placed for a requested cart, set the status of the requested cart accordingly
    public function onOrderCreated(\Enlight_Event_EventArgs $eventArgs): void
    {
        $shopwareBasket = $eventArgs->getDetails();

        if(is_array($shopwareBasket) && ! empty($shopwareBasket))
        {
            $requestCartId = $this->carts->getRequestCartIdFromShopwareBasket($shopwareBasket);
            if($requestCartId != null)
            {
                $userId = Shopware()->Session()->sUserId;
                $userData = Shopware()->Modules()->Admin()->sGetUserData();

                $this->carts->updateCart(
                    $requestCartId,
                    CarlHenkelB2B::STATUS_ORDERED,
                    $userId,
                    '',
                    $eventArgs->get('orderNumber')
                );

                $cart = $this->carts->getCartItems($requestCartId)[0];
                $this->carts->sendOrderedNotification($cart['userID'], $userData, $cart);
            }
        }
    }

    private function floatvalue(?string $val): float
    {
        if(empty(val)) {
            return 0.00;
        }

        $val = str_replace(",",".",$val);
        $val = preg_replace('/\.(?=.*\.)/', '', $val);

        return floatval($val);
    }
}
