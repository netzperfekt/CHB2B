<?php
namespace CarlHenkelB2B;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Shopware\Components\Plugin;
use Shopware\Components\Plugin\Context\ActivateContext;
use Shopware\Components\Plugin\Context\DeactivateContext;
use Shopware\Components\Plugin\Context\InstallContext;
use Shopware\Components\Plugin\Context\UpdateContext;
use Shopware\Components\Plugin\Context\UninstallContext;
use CarlHenkelB2B\Subscriber\Subscriber;
use CarlHenkelB2B\Component\Installer;
use CarlHenkelB2B\Component\AddSmartyPlugins;
use CarlHenkelB2B\Component\Carts;
use CarlHenkelB2B\Component\Mailer;
use CarlHenkelB2B\Component\User;
use CarlHenkelB2B\Component\Acl;


class CarlHenkelB2B extends Plugin
{
    public const STATUS_OPEN                =  0;
    public const STATUS_ORDERED             =  1;
    public const STATUS_REJECTED            =  9;

    public const FREETEXT_USER_CUSTOMER_NUMBER      = 'text1';
    public const FREETEXT_USER_CUSTOMER_SUBNUMBER   = 'text2';
    public const FREETEXT_USER_B2B_BUDGET           = 'b2b_budget';

    protected $acl = null;

    public static function getSubscribedEvents()
    {
        return [
            'Enlight_Bootstrap_InitResource_carlhenkel_b2b.carts'   => 'onInitServiceCarts',
            'Enlight_Bootstrap_InitResource_carlhenkel_b2b.mailer'  => 'onInitServiceMailer',
            'Enlight_Bootstrap_InitResource_carlhenkel_b2b.user'    => 'onInitServiceUser',
            'Enlight_Bootstrap_InitResource_carlhenkel_b2b.acl'     => 'onInitServiceAcl',

            'Enlight_Controller_Front_StartDispatch'                => 'onStartDispatch',
        ];
    }

    public function install(InstallContext $context)
    {
        parent::install($context);

        (new Installer())->install();

        $this->container->get('template')->addTemplateDir($this->getPath() . '/Resources/views/');
    }

    public function uninstall(UninstallContext $context)
    {
        parent::uninstall($context);
    }

    public function update(UpdateContext $context)
    {
        parent::update($context);
    }

    public function activate(ActivateContext $context)
    {
        $context->scheduleClearCache(InstallContext::CACHE_LIST_ALL);
    }

    public function deactivate(DeactivateContext $context)
    {
        parent::deactivate($context);
    }

    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new AddSmartyPlugins());
    }

    public function onInitServiceCarts()
    {
        return new Carts();
    }

    public function onInitServiceMailer()
    {
        return new Mailer();
    }

    public function onInitServiceUser()
    {
        return new User();
    }

    public function onInitServiceAcl()
    {
        return new Acl();
    }

    public function onStartDispatch(\Enlight_Event_EventArgs $args)
    {
        Shopware()->Events()->addSubscriber(new Subscriber());
    }
}
