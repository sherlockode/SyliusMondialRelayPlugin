<?php

namespace Sherlockode\SyliusMondialRelayPlugin\Manager;

use MondialRelay\Point\Point;

/**
 * Class PointAddressManager
 */
class PointAddressManager
{
    /**
     * @param Point $point
     *
     * @return string|null
     */
    public function getPointLabel(Point $point): ?string
    {
        return $point->address()[0] ?? null;
    }

    /**
     * @param Point  $point
     * @param string $separator
     *
     * @return string
     */
    public function getPointShortAddress(Point $point, string $separator = ', '): string
    {
        $items = $this->filterAddressArray($point);

        return implode($separator, $items);
    }

    /**
     * @param Point  $point
     * @param string $separator
     *
     * @return string
     */
    public function getPointFullAddress(Point $point, string $separator = ', '): string
    {
        $items = $this->filterAddressArray($point);

        if ($point->cp()) {
            $items[] = $point->cp();
        }

        if ($point->city()) {
            $items[] = $point->city();
        }

        return implode($separator, $items);
    }

    /**
     * @param Point $point

     * @return array
     */
    private function filterAddressArray(Point $point): array
    {
        $items = [];

        foreach (array_slice($point->address(), 1) as $address) {
            if (!$address) {
                continue;
            }

            $items[] = $address;
        }

        return $items;
    }
}
