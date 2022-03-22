<?php

namespace Sherlockode\SyliusMondialRelayPlugin\Twig\Extension;

use Sherlockode\SyliusMondialRelayPlugin\Twig\SyliusMondialRelayRuntime;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

/**
 * Class SyliusMondialRelayExtension
 */
class SyliusMondialRelayExtension extends AbstractExtension
{
    /**
     * {@inheritdoc}
     */
    public function getFilters(): array
    {
        return [
            new TwigFilter('pickup_point_name', [SyliusMondialRelayRuntime::class, 'getPickupPointName']),
            new TwigFilter('pickup_point_address', [SyliusMondialRelayRuntime::class, 'getPickupPointAddress']),
        ];
    }
}
