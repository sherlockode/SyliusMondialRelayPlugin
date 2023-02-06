<?php

namespace Sherlockode\SyliusMondialRelayPlugin;

use Sherlockode\SyliusMondialRelayPlugin\DependencyInjection\Compiler\SyliusUiPass;
use Sylius\Bundle\CoreBundle\Application\SyliusPluginTrait;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Class SherlockodeSyliusMondialRelayPlugin
 */
class SherlockodeSyliusMondialRelayPlugin extends Bundle
{
    use SyliusPluginTrait;

    /**
     * @param ContainerBuilder $container
     */
    public function build(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new SyliusUiPass());
    }
}
