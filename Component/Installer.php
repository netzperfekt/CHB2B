<?php
namespace CarlHenkelB2B\Component;

use Shopware\Components\Plugin\Context\InstallContext;

class Installer
{
    public function install()
    {
        $this->createAttributes();
        $this->createTables();
        $this->seedTables();
    }

    private function createAttributes()
    {
        $service = Shopware()->Container()->get('shopware_attribute.crud_service');

        $service->update('s_order_basket_attributes', 'cartid', 'string');
        $service->update('s_order_details_attributes', 'cartid', 'string');
        $service->update('s_user_attributes', 'b2b_budget', 'float',
            [
                'label'            => 'B2B Budget',
                'displayInBackend' => true,
                'position'         => 100
            ]
        );

        Shopware()->Models()->generateAttributeModels([
            's_order_basket_attributes',
            's_order_details_attributes',
            's_user_attributes'
        ]);
    }

    private function seedTables()
    {
        $this->acl = new Acl();

        $this->seedRolesAndPermissions();
        $this->createMailTemplates();
    }

    private function executeSqlQuery(string $sqlStatement, array $params = [])
    {
        try {
            Shopware()->Db()->query($sqlStatement, $params);
        }
        catch (\Exception $ex)
        {
            //
        }
    }

    private function executeSqlQueries(array $sql)
    {
        foreach($sql as $sqlStatement)
        {
            $this->executeSqlQuery($sqlStatement);
        }
    }

    private function createTables()
    {
        $sql = [];
        // carts
        $sql[] = 'CREATE TABLE IF NOT EXISTS `chb2b_order_basket` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `sessionID` varchar(128) DEFAULT NULL,
          `userID` int(11) NOT NULL DEFAULT 0,
          `articlename` varchar(255) NOT NULL,
          `articleID` int(11) NOT NULL DEFAULT 0,
          `ordernumber` varchar(255) NOT NULL,
          `shippingfree` int(1) NOT NULL DEFAULT 0,
          `quantity` int(11) NOT NULL DEFAULT 0,
          `price` double NOT NULL DEFAULT 0,
          `netprice` double NOT NULL DEFAULT 0,
          `tax_rate` double NOT NULL,
          `datum` datetime DEFAULT NULL,
          `modus` int(11) NOT NULL DEFAULT 0,
          `esdarticle` int(1) NOT NULL,
          `partnerID` varchar(45) NOT NULL,
          `lastviewport` varchar(255) NOT NULL,
          `useragent` varchar(255) NOT NULL,
          `config` mediumtext NOT NULL,
          `currencyFactor` double NOT NULL,

          `cartId` varchar(128) DEFAULT NULL,
          `status` int(11) NOT NULL DEFAULT 0,
          `customerNumber` varchar(255) DEFAULT NULL,
          `approverID` int(11) NOT NULL DEFAULT 0,
          `requested` datetime DEFAULT NULL,
          `ordered` datetime DEFAULT NULL,
          `swOrderNumber` varchar(255) DEFAULT NULL,
          `message` varchar(2048) DEFAULT NULL,

          PRIMARY KEY (`id`),
          KEY `sessionID` (`sessionID`),
          KEY `articleID` (`articleID`),
          KEY `datum` (`datum`),
          KEY `get_basket` (`sessionID`,`id`,`datum`),
          KEY `ordernumber` (`ordernumber`),

          KEY `cartId` (`cartId`),
          KEY `customerNumber` (`customerNumber`),
          KEY `status` (`status`),
          KEY `requested` (`requested`),
          KEY `ordered` (`ordered`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
        ';

        $sql[] = 'CREATE TABLE IF NOT EXISTS `chb2b_order_basket_attributes` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `basketID` int(11) DEFAULT NULL,
          `attribute1` text DEFAULT NULL,
          `attribute2` text DEFAULT NULL,
          `attribute3` text DEFAULT NULL,
          `attribute4` text DEFAULT NULL,
          `attribute5` text DEFAULT NULL,
          `attribute6` text DEFAULT NULL,
          `swp_article_input_fields_data` text DEFAULT NULL,
          PRIMARY KEY (`id`),
          UNIQUE KEY `basketID` (`basketID`),
          CONSTRAINT `chb2b_order_basket_attributes_ibfk_2`
            FOREIGN KEY (`basketID`)
            REFERENCES `chb2b_order_basket` (`id`)
            ON DELETE CASCADE
            ON UPDATE NO ACTION
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
        ';

        // roles and permissions
        $sql[] = 'CREATE TABLE IF NOT EXISTS `chb2b_role` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `title` varchar(255) NOT NULL,
          `short` varchar(20) NOT NULL,
          `description` varchar(255) NOT NULL DEFAULT "",

          PRIMARY KEY (`id`),
          KEY `short` (`short`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;';

        $sql[] = 'CREATE TABLE IF NOT EXISTS `chb2b_permission` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `title` varchar(255) NOT NULL,
          `short` varchar(20) NOT NULL,
          `description` varchar(255) NOT NULL DEFAULT "",

          PRIMARY KEY (`id`),
          KEY `short` (`short`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
        ';

        $sql[] = 'CREATE TABLE `chb2b_role_permission` (
          `role_id` int(11) NOT NULL,
          `permission_id` int(11) NOT NULL,
          
          PRIMARY KEY (`permission_id`,`role_id`),
          KEY `b2b_role_permissions` (`role_id`),

          CONSTRAINT `b2b_permission`
            FOREIGN KEY (`permission_id`)
            REFERENCES `chb2b_permission` (`id`)
            ON DELETE CASCADE ON UPDATE CASCADE,
  
          CONSTRAINT `b2b_role`
            FOREIGN KEY (`role_id`)
            REFERENCES `chb2b_role` (`id`)
            ON DELETE CASCADE ON UPDATE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
        ';

        $sql[] = 'CREATE TABLE `chb2b_user_role` (
          `user_id` int(11) NOT NULL,
          `role_id` int(11) NOT NULL,
          
          PRIMARY KEY (`user_id`,`role_id`),
          KEY `b2b_role_role` (`role_id`),
  
          CONSTRAINT `b2b_role_role`
            FOREIGN KEY (`role_id`)
            REFERENCES `chb2b_role` (`id`)
            ON DELETE CASCADE ON UPDATE CASCADE,
  
          CONSTRAINT `b2b_role_user`
            FOREIGN KEY (`user_id`)
            REFERENCES `s_user` (`id`)
            ON DELETE CASCADE ON UPDATE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
        ';

        $this->executeSqlQueries($sql);
    }

    private function createMailTemplate(string $name, string $subject,
                                        string $fileNamePlain, string $fileNameHtml)
    {
        $contentPlain = file_get_contents(__DIR__ . '/..' . $fileNamePlain);
        $contentHtml = file_get_contents(__DIR__ . '/..' . $fileNameHtml);

        $sql = 'INSERT IGNORE INTO `s_core_config_mails` (
                    name, frommail, fromname, subject,
                    content, contentHTML,
                    ishtml, mailtype
                )
                VALUES (
                    :name, :fromMail, :fromName, :subject, 
                    :contentPlain, :contentHtml, 
                    :isHtml, :mailType
                )
        ';

        $params = [
            'name'          => $name,
            'fromMail'      => '{config name=mail}',
            'fromName'      => '{config name=shopName}',
            'subject'       => $subject,
            'contentPlain'  => $contentPlain,
            'contentHtml'   => $contentHtml,
            'isHtml'        => 1,
            'mailType'      => 1 // 1 = MAILTYPE_USER
        ];

        $this->executeSqlQuery($sql, $params);
    }

    private function createMailTemplates()
    {
        $this->createMailTemplate(
            'chb2b_inform_approver',
            'Neue Bestellanforderung',
            '/templates/chb2b_inform_approver.txt',
            '/templates/chb2b_inform_approver.html'
        );

        $this->createMailTemplate(
            'chb2b_inform_rejected',
            'Bestellanforderung zurückgewiesen',
            '/templates/chb2b_cart_rejected.txt',
            '/templates/chb2b_cart_rejected.html'
        );

        $this->createMailTemplate(
            'chb2b_inform_ordered',
            'Bestellanforderung freigegeben',
            '/templates/chb2b_cart_ordered.txt',
            '/templates/chb2b_cart_ordered.html'
        );

        $this->createMailTemplate(
            'chb2b_admin_request_user',
            'Anforderung neuer Nutzer',
            '/templates/chb2b_user_request.txt',
            '/templates/chb2b_user_request.html'
        );

        $this->createMailTemplate(
            'chb2b_admin_change_user',
            'Anforderung Änderung Nutzer',
            '/templates/chb2b_user_change.txt',
            '/templates/chb2b_user_change.html'
        );

        $this->createMailTemplate(
            'chb2b_admin_deactivate_user',
            'Anforderung Deaktivierung Nutzer',
            '/templates/chb2b_user_deactivate.txt',
            '/templates/chb2b_user_deactivate.html'
        );
    }

    // --- Rollen
    private function seedRoles()
    {
        $this->acl->createRole(Acl::ROLE_VIEWER,
            Acl::ROLE_VIEWER_SHORT,
            'Nur ansehen',
            'Diese Rolle kann nur Information ansehen, aber nicht bestellen oder anfordern.'
        );

        $this->acl->createRole(Acl::ROLE_REQUESTER,
            Acl::ROLE_REQUESTER_SHORT,
            'Zusammensteller',
            'Diese Rolle kann Warenkörbe zusammenstellen, aber nicht bestellen.'
        );

        $this->acl->createRole(Acl::ROLE_REQUESTER_BUDGET,
            Acl::ROLE_REQUESTER_BUDGET_SHORT,
            'Bestellen mit Budget',
            'Diese Rolle kann bis zu einer festgelegten Budget-Grenze bestellen.'
        );

        $this->acl->createRole(Acl::ROLE_APPROVER,
            Acl::ROLE_APPROVER_SHORT,
            'Freigeber',
            'Diese Rolle kann zusammengestellte Warenkörbe freigeben'
        );

        $this->acl->createRole(Acl::ROLE_TWO_PERSON,
            Acl::ROLE_TWO_PERSON_SHORT,
            '4-Augen-Prinzip',
            'Diese Rolle kann Bestellungen anfordern, aber nur fremde Anforderungen freigeben.'
        );

        $this->acl->createRole(Acl::ROLE_MAIN,
            Acl::ROLE_MAIN_SHORT,
            'Hauptnutzer',
            'Diese Rolle kann Bestellungen freigeben und selbst unlimitiert bestellen.'
        );

        $this->acl->createRole(Acl::ROLE_ADMIN,
            Acl::ROLE_ADMIN_SHORT,
            'Admininistrator',
            'Diese Rolle kann Verwaltungstätigkeiten durchführen.'
        );
    }

    // --- Berechtigungen
    private function seedPermissions()
    {
        $this->acl->createPermission(Acl::PERMISSION_REQUEST,
            Acl::PERMISSION_REQUEST_SHORT,
            'Zusammenstellen',
            'Diese Berechtigung kann Warenkörbe zusammenstellen.'
        );

        $this->acl->createPermission(Acl::PERMISSION_REQUEST_BUDGET,
            Acl::PERMISSION_REQUEST_BUDGET_SHORT,
            'Bestellen mit Budget',
            'Diese Berechtigung kann Warenkörbe bis zur hinterlegten Bestellgrenze ohne Freigabe bestellen.'
        );

        $this->acl->createPermission(Acl::PERMISSION_APPROVE,
            Acl::PERMISSION_APPROVE_SHORT,
            'Bestellen fremde WK',
            'Diese Berechtigung kann fremde Warenkörbe bestellen.'
        );

        $this->acl->createPermission(Acl::PERMISSION_ORDER_UNLIMITED,
            Acl::PERMISSION_ORDER_UNLIMITED_SHORT,
            'Bestellen ohne Limitierung',
            'Diese Berechtigung kann ohne Limitierung bestellen.'
        );

        $this->acl->createPermission(Acl::PERMISSION_DELETE_CART,
            Acl::PERMISSION_DELETE_CART_SHORT,
            'Warenkorb löschen',
            'Diese Berechtigung kann Warenkörbe löschen.'
        );

        $this->acl->createPermission(Acl::PERMISSION_SHOW_USER,
            Acl::PERMISSION_SHOW_USER_SHORT,
            'Nutzer auflisten',
            'Diese Berechtigung kann andere Nutzer anzeigen.'
        );

        $this->acl->createPermission(Acl::PERMISSION_REQUEST_USER,
            Acl::PERMISSION_REQUEST_USER_SHORT,
            'Konten anfordern',
            'Diese Berechtigung kann andere Nutzerkonten anfordern.'
        );

        $this->acl->createPermission(Acl::PERMISSION_CHANGE_USER,
            Acl::PERMISSION_CHANGE_USER_SHORT,
            'Änderungen Nutzer veranlassen',
            'Diese Berechtigung kann Änderungen an anderen Nutzerkonten veranlassen.'
        );
    }

    private function seedRolesAndPermissions()
    {
        $this->seedRoles();
        $this->seedPermissions();

        // --- add permissions to roles
        $this->acl->addPermissionToRole(Acl::ROLE_REQUESTER,        Acl::PERMISSION_REQUEST);
        $this->acl->addPermissionToRole(Acl::ROLE_REQUESTER,        Acl::PERMISSION_DELETE_CART);

        $this->acl->addPermissionToRole(Acl::ROLE_REQUESTER_BUDGET, Acl::PERMISSION_REQUEST_BUDGET);

        $this->acl->addPermissionToRole(Acl::ROLE_APPROVER,         Acl::PERMISSION_APPROVE);
        $this->acl->addPermissionToRole(Acl::ROLE_APPROVER,         Acl::PERMISSION_DELETE_CART);

        $this->acl->addPermissionToRole(Acl::ROLE_TWO_PERSON,       Acl::PERMISSION_APPROVE);
        $this->acl->addPermissionToRole(Acl::ROLE_TWO_PERSON,       Acl::PERMISSION_REQUEST);
        $this->acl->addPermissionToRole(Acl::ROLE_TWO_PERSON,       Acl::PERMISSION_DELETE_CART);

        $this->acl->addPermissionToRole(Acl::ROLE_MAIN,             Acl::PERMISSION_APPROVE);
        $this->acl->addPermissionToRole(Acl::ROLE_MAIN,             Acl::PERMISSION_ORDER_UNLIMITED);
        $this->acl->addPermissionToRole(Acl::ROLE_MAIN,             Acl::PERMISSION_DELETE_CART);

        $this->acl->addPermissionToRole(Acl::ROLE_ADMIN,            Acl::PERMISSION_SHOW_USER);
        $this->acl->addPermissionToRole(Acl::ROLE_ADMIN,            Acl::PERMISSION_REQUEST_USER);
        $this->acl->addPermissionToRole(Acl::ROLE_ADMIN,            Acl::PERMISSION_CHANGE_USER);

        // seed test user only on staging-server b2b
        $isB2BTestShop = strpos(Shopware()->AppPath(), '__B2B__') !== false;
        if($isB2BTestShop)
        {
            $this->seedTestUser();
        }
    }

    private function seedTestUser()
    {
        // --- add roles to users
        $userReq1  = 550; // info+req1@netzperfekt.de  - Rolle requester, request_budget
        $userReq2  = 551; // info+req2@netzperfekt.de  - Rolle requester_budget
        $userApp1  = 552; // info+app1@netzperfekt.de  - Rolle approver
        $userApp2  = 553; // info+app2@netzperfekt.de  - Rolle approver, admin
        $userView  = 554; // info+view@netzperfekt.de  - Rolle viewer
        $user4Eyes = 555; // info+4eyes@netzperfekt.de - Rolle twoperson, requester_budget
        $userMain  = 563; // info+main@netzperfekt.de  - Rolle main

        $this->acl->assignRolesToUser($userReq1, [
            Acl::ROLE_REQUESTER,
            Acl::ROLE_REQUESTER_BUDGET
        ]);

        $this->acl->assignRolesToUser($userReq2, [
            Acl::ROLE_REQUESTER_BUDGET
        ]);

        $this->acl->assignRolesToUser($userApp1, [
            Acl::ROLE_APPROVER
        ]);

        $this->acl->assignRolesToUser($userApp2, [
            Acl::ROLE_APPROVER,
            Acl::ROLE_ADMIN
        ]);

        $this->acl->assignRolesToUser($userView, [
            Acl::ROLE_VIEWER
        ]);

        $this->acl->assignRolesToUser($user4Eyes, [
            Acl::ROLE_TWO_PERSON,
            Acl::ROLE_REQUESTER_BUDGET
        ]);

        $this->acl->assignRolesToUser($userMain, [
            Acl::ROLE_MAIN
        ]);
    }
}
