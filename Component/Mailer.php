<?php
namespace CarlHenkelB2B\Component;

use CarlHenkelB2B\CarlHenkelB2B;
use Shopware_Components_TemplateMail;

class Mailer
{
    public const NOTIFICATION_APPROVER_NEW_APPROVAL =  1;
    public const NOTIFICATION_REQUESTER_REJECTED    =  2;
    public const NOTIFICATION_REQUESTER_ORDERED     =  3;

    public const NOTIFICATION_ADMIN_REQUEST_USER    = 10;
    public const NOTIFICATION_ADMIN_CHANGE_USER     = 11;
    public const NOTIFICATION_ADMIN_DELETE_USER     = 12;

    private const MAIL_TEMPLATES = [
        self::NOTIFICATION_APPROVER_NEW_APPROVAL    => 'chb2b_inform_approver',
        self::NOTIFICATION_REQUESTER_REJECTED       => 'chb2b_inform_rejected',
        self::NOTIFICATION_REQUESTER_ORDERED        => 'chb2b_inform_ordered',
        self::NOTIFICATION_ADMIN_REQUEST_USER       => 'chb2b_admin_request_user',
        self::NOTIFICATION_ADMIN_CHANGE_USER        => 'chb2b_admin_change_user',
        self::NOTIFICATION_ADMIN_DELETE_USER        => 'chb2b_admin_deactivate_user'
    ];

    private $config = null;

    public function __construct()
    {
        $this->config = Shopware()->Container()->get('shopware.plugin.config_reader')
                                                ->getByPluginName('CarlHenkelB2B');
    }

    public function sendEmail(int $userId, $type, $userData, $additionalData = []): void
    {
        $logger = Shopware()->Container()->get('carl_henkel_b2_b.logger');

        $linkAccountCarts = Shopware()->Front()->Router()->assemble([
            'sViewport' => 'CarlHenkelB2B',
            'action' => 'carts',
        ]);

        $linkAccountUser = Shopware()->Front()->Router()->assemble([
            'sViewport' => 'CarlHenkelB2B',
            'action' => 'user',
        ]);

        $context = [
            'user'              => $userData['additional']['user'],
            'customerNumber'    => $userData['additional']['user'][CarlHenkelB2B::FREETEXT_USER_CUSTOMER_NUMBER],
            'customerSubNumber' => $userData['additional']['user'][CarlHenkelB2B::FREETEXT_USER_CUSTOMER_SUBNUMBER],
            'linkAccountCarts'  => $linkAccountCarts,
            'linkAccountUser'   => $linkAccountUser
        ];
        $context = array_merge($context, $additionalData);

        if($userId == 0)
        {
            $recipientEmail = trim($this->config['emailUserChanges']);
            if($recipientEmail == '')
            {
                $logger->error('plugin config "emailUserChanges" not set');
                return;
            }
        }
        else {
            $recipient = Shopware()->Container()->get('carlhenkel_b2b.user')->getUserFromId($userId);
            if( ! $recipient)
            {
                $logger->error('recipient not found', [
                    'userId' => $userId,
                ]);
                return;
            }
            $recipientEmail = $recipient['email'];
        }

        $mailTemplate = $this->getEMailTemplate($type);
        if($mailTemplate == '')
        {
            $logger->error('unknown notification type', [
                'userId' => $userId,
                'type' => $type
            ]);
            return;
        }

        $mail = Shopware()->Container()
                    ->get(Shopware_Components_TemplateMail::class)
                    ->createMail($mailTemplate, $context);

        $mail->AddAddress($recipientEmail);

        try {
            $mail->send();
        }
        catch (\Exception $ex)
        {
            $logger->error('failed sending notification mail', [
                'userId'  => $userId,
                'type'    => $type,
                'message' => $ex->getMessage()
            ]);
        }
    }

    private function getEMailTemplate($type)
    {
        if(array_key_exists($type, self::MAIL_TEMPLATES))
        {
            return self::MAIL_TEMPLATES[$type];
        }

        return '';
    }
}
