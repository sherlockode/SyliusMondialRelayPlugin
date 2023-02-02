<?php

namespace Sherlockode\SyliusMondialRelayPlugin\DependencyInjection;

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
                    ->scalarNode('google_map_api_key')->isRequired()->end()
                    ->scalarNode('mondial_relay_base_url')
                        ->cannotBeEmpty()
                        ->defaultValue('https://www.mondialrelay.com')
                    ->end()
                    ->arrayNode('pickup_point_types')
                        ->scalarPrototype()->end()
                        ->defaultValue([])
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
