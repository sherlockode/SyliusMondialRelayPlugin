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
    private $selectorType;

    /**
     * SyliusMondialRelayRuntime constructor.
     *
     * @param PointAddressManager $pointAddressManager
     * @param string              $selectorType
     */
    public function __construct(PointAddressManager $pointAddressManager, string $selectorType)
    {
        $this->pointAddressManager = $pointAddressManager;
        $this->selectorType = $selectorType;
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
    public function getSelectorType(): string
    {
        return $this->selectorType;
    }
}
