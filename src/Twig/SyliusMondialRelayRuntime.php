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
     * @var string|null
     */
    private $mapProvider;

    /**
     * @var bool
     */
    private $enableTicketPrinting;

    /**
     * @param MondialRelay $apiClient
     * @param bool         $enableTicketPrinting
     * @param string|null  $mapProvider
     * @param string|null  $googleApiKey
     */
    public function __construct(
        MondialRelay $apiClient,
        bool $enableTicketPrinting,
        ?string $mapProvider,
        ?string $googleApiKey
    ) {
        $this->apiClient = $apiClient;
        $this->enableTicketPrinting = $enableTicketPrinting;
        $this->mapProvider = $mapProvider;
        $this->googleApiKey = $googleApiKey;
    }

    /**
     * @return string|null
     */
    public function getMapProvider(): ?string
    {
        return $this->mapProvider;
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
