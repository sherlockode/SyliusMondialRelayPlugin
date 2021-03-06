<?php

namespace Sherlockode\SyliusMondialRelayPlugin\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;

/**
 * Class SherlockodeSyliusMondialRelayExtension
 */
class SherlockodeSyliusMondialRelayExtension extends Extension
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
        $container->setParameter(
            'sherlockode_sylius_mondial_relay.pickup_types',
            $config['pickup_point_types'] ?? []
        );
        $container->setParameter(
            'sherlockode_sylius_mondial_relay.selector_type',
            $config['selector_type'] ?? 'regular'
        );
    }
}
