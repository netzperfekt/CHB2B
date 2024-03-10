<?php
namespace CarlHenkelB2B\Component;

use CarlHenkelB2B\CarlHenkelB2B;

class Carts
{
    private $mailer = null;
    private $user = null;
    private $acl = null;

    public function __construct()
    {
        $this->mailer = Shopware()->Container()->get('carlhenkel_b2b.mailer');
        $this->user = Shopware()->Container()->get('carlhenkel_b2b.user');
        $this->acl = Shopware()->Container()->get('carlhenkel_b2b.acl');
    }

    public function editCart(string $cartId): void
    {
        $items = $this->getCartItems($cartId);
        $this->addToBasket($items, Shopware()->Session()->sessionId);
    }

    public function deleteCart(string $cartId): void
    {
        $this->deleteCartItems($cartId);
    }

    public function rejectCart(string $cartId, int $userId, string $message = ''): void
    {
        $this->updateCart($cartId, CarlHenkelB2B::STATUS_REJECTED, $userId, $message);
    }

    /*
     * @returns array['cartApprovals', 'cartRequests', 'cartHistory', 'cartCountApprovals', 'cartCountRequests']
     */
    public function getCartsLists(): array
    {
        [$userId, $customerNumber] = $this->user->getUserIdAndCustomerNumberAndBudget();

        $cartApprovals = $this->getCartsToApprove($customerNumber, $userId);
        $cartRequests = $this->getCarts($customerNumber, $userId);
        $cartHistory = $this->getCarts($customerNumber);

        $cartCountApprovals = count($cartApprovals);
        $cartCountRequests = count(array_filter($cartRequests, function($cart)
        {
            return $cart['status'] == CarlHenkelB2B::STATUS_OPEN ||
                   $cart['status'] == CarlHenkelB2B::STATUS_REJECTED;
        }));

        return [
            'cartApprovals'      => $cartApprovals,
            'cartRequests'       => $cartRequests,
            'cartHistory'        => $cartHistory,

            'cartCountApprovals' => $cartCountApprovals,
            'cartCountRequests'  => $cartCountRequests
        ];
    }

    public function sendApprovalNotification(string $customerNumber,
                                             int $approverId,
                                             array $userData,
                                             array $cart): void
    {
        if($approverId == 0)
        {
            $userId = $userData['additional']['user']['userID'];
            $recipients = $this->user->getUserFromCustomerNumber($customerNumber);

            foreach ($recipients as $recipient)
            {
                $recipientId = $recipient['id'];
                if ($this->acl->hasPermission($recipientId, Acl::PERMISSION_APPROVE_SHORT) &&
                    $recipientId != $userId)
                {
                    $this->mailer->sendEmail(
                        $recipientId,
                        Mailer::NOTIFICATION_APPROVER_NEW_APPROVAL,
                        $userData,
                        ['cart' => $cart]
                    );
                }
            }
        }
        else
        {
            $recipient = $this->user->getUserFromId($approverId);
            if($recipient && $this->acl->hasPermission($recipient['userID'], Acl::PERMISSION_APPROVE_SHORT))
            {
                $this->mailer->sendEmail(
                    $recipient['userID'],
                    Mailer::NOTIFICATION_APPROVER_NEW_APPROVAL,
                    $userData,
                    ['cart' => $cart]
                );
            }
        }
    }

    public function sendRejectionNotification(string $requesterId,
                                              array $userData,
                                              array $cart): void
    {
        if($this->acl->hasPermission($requesterId, Acl::PERMISSION_REQUEST_SHORT))
        {
            $this->mailer->sendEmail(
                $requesterId,
                Mailer::NOTIFICATION_REQUESTER_REJECTED,
                $userData,
                ['cart' => $cart]
            );
        }
    }

    public function sendOrderedNotification(string $requesterId,
                                            array $userData,
                                            array $cart): void
    {
        if($this->acl->hasPermission($requesterId, Acl::PERMISSION_REQUEST_SHORT))
        {
            $this->mailer->sendEmail(
                $requesterId,
                Mailer::NOTIFICATION_REQUESTER_ORDERED,
                $userData,
                ['cart' => $cart]
            );
        }
    }

    public function updateCart(string $cartId, int $status, int $approverId,
                               string $message = '', string $orderNumber = ''): void
    {
        $sql = 'UPDATE chb2b_order_basket
                   SET ordered = now(),
                       status = :status,
                       approverId = :approverId, 
                       message = :message,
                       swOrderNumber = :orderNumber
                 WHERE cartId = :cartId
        ';

        $params = [
            'cartId'      => $cartId,
            'status'      => $status,
            'approverId'  => $approverId,
            'message'     => $message,
            'orderNumber' => $orderNumber
        ];

        Shopware()->Db()->query($sql, $params);
    }

    public function getBasketItems(string $sessionId, int $userId): array
    {
        $sql = '   SELECT s_order_basket.*,
                          s_order_basket_attributes.*
                     FROM s_order_basket
                LEFT JOIN s_order_basket_attributes
                       ON s_order_basket_attributes.basketID = s_order_basket.id
                    WHERE sessionID = :sessionId
                      AND userID = :userId';

        $params = [
            'sessionId' => $sessionId,
            'userId' => $userId
        ];

        return Shopware()->Db()->fetchAll($sql, $params);
    }

    public function getCartItems(?string $cartId): array
    {
        $sql = '   SELECT chb2b_order_basket.*,
                          chb2b_order_basket_attributes.*
                     FROM chb2b_order_basket
                LEFT JOIN chb2b_order_basket_attributes
                       ON chb2b_order_basket_attributes.basketID = chb2b_order_basket.id
                    WHERE chb2b_order_basket.cartId = :cartId
        ';

        $params = [
            'cartId' => $cartId
        ];

        return Shopware()->Db()->fetchAll($sql, $params);
    }

    /*
     * gets a request cart by id
     * @returns array from database
     */
    public function getCart(?string $cartId): array
    {
        if( ! $cartId)
        {
            return [];
        }

        $sql = 'SELECT chb2b_order_basket.*,
                       chb2b_order_basket_attributes.*
                  FROM chb2b_order_basket
                  LEFT JOIN chb2b_order_basket_attributes
                         ON chb2b_order_basket_attributes.basketID = chb2b_order_basket.id
                 WHERE chb2b_order_basket.cartId = :cartId
        ';

        $params = [
            'cartId' => $cartId
        ];

        $carts = Shopware()->Db()->fetchAll($sql, $params);
        if($carts && count($carts) > 0)
        {
            return $carts[0];
        }

        return [];
    }

    public function deleteCartItems(?string $cartId): void
    {
        $sql = 'DELETE
                  FROM chb2b_order_basket
                 WHERE cartId = :cartId
        ';

        $params = [
            'cartId' => $cartId
        ];

        Shopware()->Db()->query($sql, $params);
    }

    public function requestBasketItems(array $items, string $customerNumber): void
    {
        $cartId = uniqid('', true);

        foreach($items as $item)
        {
            $sql = 'INSERT IGNORE INTO chb2b_order_basket
                                (id, sessionID, userID, articlename, articleID, ordernumber,
                                 shippingfree, quantity, price, netprice, tax_rate, datum,
                                 modus, esdarticle, partnerID, lastviewport, useragent,
                                 config, currencyFactor,
                                 cartId, status, customerNumber, ordered, requested)
                         VALUES (:id, :sessionId, :userId, :articleName, :articleId, :orderNumber,
                                 :shippingFree, :quantity, :price, :netPrice, :taxRate, :datum,
                                 :modus, :esdArticle, :partnerId, :lastViewPort, :userAgent,
                                 :config, :currencyFactor,
                                 :cartId, :status, :customerNumber, :ordered, :requested)
            ';
            
            $params = [
                'id'            => $item['id'],
                'sessionId'     => $item['sessionID'],
                'userId'        => $item['userID'],
                'articleName'   => $item['articlename'],
                'articleId'     => $item['articleID'],
                'orderNumber'   => $item['ordernumber'],
                'shippingFree'  => $item['shippingfree'],
                'quantity'      => $item['quantity'],
                'price'         => $item['price'],
                'netPrice'      => $item['netprice'],
                'taxRate'       => $item['tax_rate'],
                'datum'         => $item['datum'],
                'modus'         => $item['modus'],
                'esdArticle'    => $item['esdarticle'],
                'partnerId'     => $item['partnerID'],
                'lastViewPort'  => $item['lastviewport'],
                'userAgent'     => $item['useragent'],
                'config'        => $item['config'],
                'currencyFactor'=> $item['currencyFactor'],

                'cartId'        => $cartId,
                'status'        => 0,
                'customerNumber'=> $customerNumber,
                'ordered'       => null,
                'requested'     => \Zend_Date::now()
            ];

            Shopware()->Db()->query($sql, $params);

            $sqlAttributes = 'INSERT IGNORE INTO chb2b_order_basket_attributes
                                     (basketID, attribute1, attribute2, attribute3,
                                      attribute4, attribute5, attribute6,
                                      swp_article_input_fields_data)
                              VALUES (:basketId, :attribute1, :attribute2, :attribute3,
                                      :attribute4, :attribute5, :attribute6,
                                      :swpArticleInputFieldsData)
            ';

            $paramsAttributes = [
                'basketId'                  => $item['id'],
                'attribute1'                => $item['attribute1'],
                'attribute2'                => $item['attribute2'],
                'attribute3'                => $item['attribute3'],
                'attribute4'                => $item['attribute4'],
                'attribute5'                => $item['attribute5'],
                'attribute6'                => $item['attribute6'],
                'swpArticleInputFieldsData' => $item['swp_article_input_fields_data']
            ];

            Shopware()->Db()->query($sqlAttributes, $paramsAttributes);
        }
    }

    public function getCarts(?string $customerNumber, ?int $userId = 0): array
    {
        if( ! $customerNumber)
        {
            return [];
        }

        $params = [
            'customerNumber' => $customerNumber,
        ];

        $sql = 'SELECT chb2b_order_basket.id,
                       count(chb2b_order_basket.id) as positions,
                       sum(quantity * price) as total,
                       cartId, status, ordered, requested, swOrderNumber, message,

                       s_user.firstName as firstName,
                       s_user.lastName as lastName,

                       approverId as approver,
                       s_user2.firstName as approverFirstName,
                       s_user2.lastName as approverLastName

                  FROM chb2b_order_basket

                  LEFT JOIN s_user
                         ON chb2b_order_basket.userID = s_user.ID

                  LEFT JOIN s_user AS s_user2
                         ON chb2b_order_basket.approverId = s_user2.ID

                  WHERE chb2b_order_basket.customerNumber = :customerNumber
        ';

        if((int)$userId > 0)
        {
            $sql .= ' AND chb2b_order_basket.userID = :userId';
            $params['userId'] = (int)$userId;
        }

        $sql .= ' GROUP BY requested
                  ORDER BY requested DESC, datum
        ';

        return Shopware()->Db()->fetchAll($sql, $params);
    }

    public function getCartsToApprove(?string $customerNumber, ?int $userId): array
    {
        if( ! $customerNumber || $userId == 0)
        {
            return [];
        }

        $sql = 'SELECT chb2b_order_basket.id,
                       count(chb2b_order_basket.id) as positions,
                       sum(quantity * price) as total,
                       cartId, status, chb2b_order_basket.customerNumber,
                       ordered, requested, message,

                       userId as user, s_user.firstName, s_user.lastName

                  FROM chb2b_order_basket

                  LEFT JOIN s_user
                         ON chb2b_order_basket.userID = s_user.ID

                 WHERE status = :status
                   AND chb2b_order_basket.customerNumber = :customerNumber
                   AND userId <> :userId
                 GROUP BY requested
                 ORDER BY requested DESC, datum
        ';

        $params = [
            'status'         => CarlHenkelB2B::STATUS_OPEN,
            'customerNumber' => $customerNumber,
            'userId'         => $userId
        ];

        return Shopware()->Db()->fetchAll($sql, $params);
    }

    public function addToBasket(array $items, string $sessionId): void
    {
        foreach($items as $item)
        {
            $sql = 'INSERT IGNORE INTO s_order_basket
                                (sessionID, userID, articlename, articleID, ordernumber,
                                 shippingfree, quantity, price, netprice, tax_rate, datum,
                                 modus, esdarticle, partnerID, lastviewport, useragent,
                                 config, currencyFactor)
                         VALUES (:sessionId, :userId, :articleName, :articleId, :orderNumber,
                                 :shippingFree, :quantity, :price, :netPrice, :taxRate, :datum,
                                 :modus, :esdArticle, :partnerId, :lastViewPort, :userAgent,
                                 :config, :currencyFactor)
            ';
            
            $params = [
                'sessionId'     => $sessionId,
                'userId'        => $item['userID'],
                'articleName'   => $item['articlename'],
                'articleId'     => $item['articleID'],
                'orderNumber'   => $item['ordernumber'],
                'shippingFree'  => $item['shippingfree'],
                'quantity'      => $item['quantity'],
                'price'         => $item['price'],
                'netPrice'      => $item['netprice'],
                'taxRate'       => $item['tax_rate'],
                'datum'         => $item['datum'],
                'modus'         => $item['modus'],
                'esdArticle'    => $item['esdarticle'],
                'partnerId'     => $item['partnerID'],
                'lastViewPort'  => $item['lastviewport'],
                'userAgent'     => $item['useragent'],
                'config'        => $item['config'],
                'currencyFactor'=> $item['currencyFactor']
            ];

            Shopware()->Db()->query($sql, $params);

            $basketId = Shopware()->Db()->lastInsertId('s_order_basket');

            $sqlAttributes = 'INSERT IGNORE INTO s_order_basket_attributes
                                (basketID, attribute1, attribute2, attribute3,
                                 attribute4, attribute5, attribute6,
                                 swp_article_input_fields_data, cartid)
                         VALUES (:basketId, :attribute1, :attribute2, :attribute3,
                                 :attribute4, :attribute5, :attribute6,
                                 :swpArticleInputFieldsData, :cartId)
            ';

            $paramsAttributes = [
                'basketId'                  => $basketId,
                'attribute1'                => $item['attribute1'],
                'attribute2'                => $item['attribute2'],
                'attribute3'                => $item['attribute3'],
                'attribute4'                => $item['attribute4'],
                'attribute5'                => $item['attribute5'],
                'attribute6'                => $item['attribute6'],
                'swpArticleInputFieldsData' => $item['swp_article_input_fields_data'],
                'cartId'                    => $item['cartId']
            ];

            Shopware()->Db()->query($sqlAttributes, $paramsAttributes);
        }
    }

    public function getRequestCartIdFromShopwareBasket(?array $shopwareBasketContent): ?string
    {
        if( ! $shopwareBasketContent)
        {
            return null;
        }

        return $shopwareBasketContent[0]['__s_order_basket_attributes_cartid'];
    }
}
