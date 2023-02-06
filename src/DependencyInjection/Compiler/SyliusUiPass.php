<?php

namespace Sherlockode\SyliusMondialRelayPlugin\DependencyInjection\Compiler;

use Laminas\Stdlib\SplPriorityQueue;
use Sylius\Bundle\UiBundle\Registry\TemplateBlock;
use Sylius\Bundle\UiBundle\Registry\TemplateBlockRegistryInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\Yaml\Parser;

class SyliusUiPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $parser = new Parser();
        $bundleEvents = $parser->parseFile(__DIR__ . '/../../Resources/config/events.yaml');

        $templateBlockRegistryDefinition = $container->findDefinition(TemplateBlockRegistryInterface::class);
        $events = $templateBlockRegistryDefinition->getArgument(0);

        foreach ($bundleEvents['sylius_ui']['events'] as $eventName => $blocks) {
            $blocksPriorityQueue = new SplPriorityQueue();

            foreach ($events[$eventName] as $blockDefinition) {
                $priority = $blockDefinition->getArgument(4);
                $blocksPriorityQueue->insert($blockDefinition, $priority);
            }

            foreach ($blocks['blocks'] as $blockName => $eventConfiguration) {
                $priority = $eventConfiguration['priority'] ?? 0;
                $block = new Definition(TemplateBlock::class, array_values([
                    'name' => $blockName,
                    'eventName' => $eventName,
                    'template' => $eventConfiguration['template'] ?? '',
                    'context' => $eventConfiguration['context'] ?? [],
                    'priority' => $priority,
                    'enabled' => $eventConfiguration['priority'] ?? true,
                ]));

                $blocksPriorityQueue->insert($block, $priority);
            }

            $events[$eventName] = [];
            foreach ($blocksPriorityQueue->toArray() as $blockDefinition) {
                $blockName = $blockDefinition->getArgument(0);
                $events[$eventName][$blockName] = $blockDefinition;
            }
        }

        $templateBlockRegistryDefinition->setArgument(0, $events);
    }
}
