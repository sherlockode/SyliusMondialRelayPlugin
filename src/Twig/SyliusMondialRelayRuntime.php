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
     * @var bool
     */
    private $enableTicketPrinting;

    /**
     * @param string $googleMapApiKey
     * @param bool   $enableTicketPrinting
     */
    public function __construct(string $googleMapApiKey, bool $enableTicketPrinting)
    {
        $this->googleMapApiKey = $googleMapApiKey;
        $this->enableTicketPrinting = $enableTicketPrinting;
    }

    /**
     * @return string
     */
    public function getGoogleMapApiKey(): string
    {
        return $this->googleMapApiKey;
    }

    /**
     * @return bool
     */
    public function isTicketPrintingEnable(): bool
    {
        return $this->enableTicketPrinting;
    }
}
