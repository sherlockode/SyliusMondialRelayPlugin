<?php

namespace Sherlockode\SyliusMondialRelayPlugin\Twig;

use Twig\Extension\RuntimeExtensionInterface;

/**
 * Class SyliusMondialRelayRuntime
 */
class SyliusMondialRelayRuntime implements RuntimeExtensionInterface
{
    /**
     * @var string
     */
    private $googleMapApiKey;

    /**
     * @param string $googleMapApiKey
     */
    public function __construct(string $googleMapApiKey)
    {
        $this->googleMapApiKey = $googleMapApiKey;
    }

    /**
     * @return string
     */
    public function getGoogleMapApiKey(): string
    {
        return $this->googleMapApiKey;
    }
}
