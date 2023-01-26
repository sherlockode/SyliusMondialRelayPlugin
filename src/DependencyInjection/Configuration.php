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
                    ->arrayNode('pickup_point_types')
                        ->scalarPrototype()->end()
                        ->defaultValue([])
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
