<?php

namespace Sherlockode\SyliusMondialRelayPlugin\Twig;

use Sherlockode\SyliusMondialRelayPlugin\Model\Point;
use Sherlockode\SyliusMondialRelayPlugin\MondialRelay\Api\Exception\ApiException;
use Sherlockode\SyliusMondialRelayPlugin\MondialRelay\MondialRelay;
use Sylius\Component\Core\Model\Shipment;
use Twig\Extension\RuntimeExtensionInterface;

/**
 * Class SyliusMondialRelayRuntime
 */
class SyliusMondialRelayRuntime implements RuntimeExtensionInterface
{
    /**
     * @var MondialRelay
     */
    private $apiClient;

    /**
     * @var string
     */
    private $googleApiKey;

    /**
     * @var bool
     */
    private $enableTicketPrinting;

    /**
     * @param MondialRelay $apiClient
     * @param string       $googleApiKey
     * @param bool         $enableTicketPrinting
     */
    public function __construct(MondialRelay $apiClient, string $googleApiKey, bool $enableTicketPrinting)
    {
        $this->apiClient = $apiClient;
        $this->googleApiKey = $googleApiKey;
        $this->enableTicketPrinting = $enableTicketPrinting;
    }

    /**
     * @return string
     */
    public function getGoogleApiKey(): string
    {
        return $this->googleApiKey;
    }

    /**
     * @return bool
     */
    public function isTicketPrintingEnable(): bool
    {
        return $this->enableTicketPrinting;
    }

    /**
     * @param Shipment $shipment
     *
     * @return Point|null
     *
     * @throws ApiException
     */
    public function getPickupPoint(Shipment $shipment): ?Point
    {
        if ($shipment->getPickupPointId()) {
            try {
                return $this->apiClient->getPickupPoint($shipment->getPickupPointId());
            } catch (\Exception $exception) {
            }
        }

        return null;
    }
}
