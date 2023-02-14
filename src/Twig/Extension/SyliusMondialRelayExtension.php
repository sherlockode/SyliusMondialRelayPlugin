<?php

namespace Sherlockode\SyliusMondialRelayPlugin\Twig\Extension;

use Sherlockode\SyliusMondialRelayPlugin\Twig\SyliusMondialRelayRuntime;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

/**
 * Class SyliusMondialRelayExtension
 */
class SyliusMondialRelayExtension extends AbstractExtension
{
    public function getFilters()
    {
        return [
            new TwigFilter('mondial_relay_pickup_point', [SyliusMondialRelayRuntime::class, 'getPickupPoint'])
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return [
            new TwigFunction('google_map_api_key', [SyliusMondialRelayRuntime::class, 'getGoogleMapApiKey']),
            new TwigFunction(
                'is_mondial_relay_ticket_printing_enable',
                [SyliusMondialRelayRuntime::class, 'isTicketPrintingEnable']
            ),
        ];
    }
}
