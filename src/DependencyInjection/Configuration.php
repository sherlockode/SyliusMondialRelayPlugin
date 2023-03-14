<?php

namespace Sherlockode\SyliusMondialRelayPlugin\DependencyInjection;

use Sherlockode\SyliusMondialRelayPlugin\Manager\MapProviderManager;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Class Configuration
 */
class Configuration implements ConfigurationInterface
{
    /**
     * @return TreeBuilder
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('sherlockode_sylius_mondial_relay');

        $treeBuilder
            ->getRootNode()
                ->children()
                    ->scalarNode('wsdl')->isRequired()->end()
                    ->scalarNode('merchant_id')->isRequired()->end()
                    ->scalarNode('private_key')->isRequired()->end()
                    ->enumNode('map_provider')
                        ->defaultValue(null)
                        ->values([
                            null,
                            MapProviderManager::MAP_PROVIDER_GOOGLE,
                            MapProviderManager::MAP_PROVIDER_OSM,
                        ])
                    ->end()
                    ->scalarNode('google_api_key')
                        ->defaultValue(null)
                    ->end()
                    ->scalarNode('mondial_relay_base_url')
                        ->cannotBeEmpty()
                        ->defaultValue('https://www.mondialrelay.com')
                    ->end()
                    ->booleanNode('enable_ticket_printing')
                        ->defaultValue(true)
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
