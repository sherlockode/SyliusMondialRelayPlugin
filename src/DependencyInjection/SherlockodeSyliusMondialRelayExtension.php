<?php

namespace Sherlockode\SyliusMondialRelayPlugin\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;

/**
 * Class SherlockodeSyliusMondialRelayExtension
 */
class SherlockodeSyliusMondialRelayExtension extends Extension implements PrependExtensionInterface
{
    /**
     * @param array            $configs
     * @param ContainerBuilder $container
     *
     * @throws \Exception
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.xml');

        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $container->setParameter('sherlockode_sylius_mondial_relay.wsdl', $config['wsdl'] ?? '');
        $container->setParameter('sherlockode_sylius_mondial_relay.merchant_id', $config['merchant_id'] ?? '');
        $container->setParameter('sherlockode_sylius_mondial_relay.private_key', $config['private_key'] ?? '');
        $container->setParameter('sherlockode_sylius_mondial_relay.base_url', $config['mondial_relay_base_url'] ?? '');
        $container->setParameter(
            'sherlockode_sylius_mondial_relay.enable_ticket_printing',
                $config['enable_ticket_printing'] ?? ''
        );
        $container->setParameter('sherlockode_sylius_mondial_relay.map_provider', $config['map_provider']);
        $container->setParameter('sherlockode_sylius_mondial_relay.google_api_key', $config['google_api_key']);
    }

    /**
     * @param ContainerBuilder $container
     */
    public function prepend(ContainerBuilder $container): void
    {
        if ($container->hasExtension('twig')) {
            $container->prependExtensionConfig('twig', ['form_themes' => ['@SherlockodeSyliusMondialRelayPlugin/form_theme.html.twig']]);
        }
    }
}
