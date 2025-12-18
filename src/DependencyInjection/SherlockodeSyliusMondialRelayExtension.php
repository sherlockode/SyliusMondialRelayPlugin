<?php

namespace Sherlockode\SyliusMondialRelayPlugin\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\Yaml\Yaml;

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
        $locator = new FileLocator(__DIR__ . '/../Resources/config');

        $loader = new XmlFileLoader($container, $locator);
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

        if (!$container->hasExtension('sylius_twig_hooks')) {
            return;
        }

        $config = Yaml::parseFile(
            __DIR__ . '/../Resources/config/sylius_twig_hooks.yaml'
        );

        $container->prependExtensionConfig(
            'sylius_twig_hooks',
            $config['sylius_twig_hooks']
        );
    }

    public function getAlias(): string
    {
        return 'sherlockode_sylius_mondial_relay';
    }
}
