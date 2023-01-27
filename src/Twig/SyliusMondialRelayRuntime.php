<?php

namespace Sherlockode\SyliusMondialRelayPlugin\Twig;

use MondialRelay\Point\Point;
use Sherlockode\SyliusMondialRelayPlugin\Manager\PointAddressManager;
use Twig\Extension\RuntimeExtensionInterface;

/**
 * Class SyliusMondialRelayRuntime
 */
class SyliusMondialRelayRuntime implements RuntimeExtensionInterface
{
    /**
     * @var PointAddressManager
     */
    private $pointAddressManager;

    /**
     * @var string
     */
    private $googleMapApiKey;

    /**
     * @param PointAddressManager $pointAddressManager
     * @param string              $googleMapApiKey
     */
    public function __construct(PointAddressManager $pointAddressManager, string $googleMapApiKey)
    {
        $this->pointAddressManager = $pointAddressManager;
        $this->googleMapApiKey = $googleMapApiKey;
    }

    /**
     * @param Point $point
     *
     * @return string|null
     */
    public function getPickupPointName(Point $point): ?string
    {
        return $this->pointAddressManager->getPointLabel($point);
    }

    /**
     * @param Point  $point
     * @param string $separator
     *
     * @return string
     */
    public function getPickupPointAddress(Point $point, string $separator = ', '): string
    {
        return $this->pointAddressManager->getPointFullAddress($point, $separator);
    }

    /**
     * @return string
     */
    public function getGoogleMapApiKey(): string
    {
        return $this->googleMapApiKey;
    }
}
