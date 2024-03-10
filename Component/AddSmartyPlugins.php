<?php
namespace CarlHenkelB2B\Component;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class AddSmartyPlugins implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $template = $container->getDefinition('template');
        $template->addMethodCall('addPluginsDir', [__DIR__ . '/../Smarty/']);
    }
}