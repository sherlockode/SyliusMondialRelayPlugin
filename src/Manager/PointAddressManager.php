<?php

namespace Sherlockode\SyliusMondialRelayPlugin\Manager;

use MondialRelay\BussinessHours\BussinessHours;
use MondialRelay\Point\Point;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class PointAddressManager
 */
class PointAddressManager
{
    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @param TranslatorInterface $translator
     */
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

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

    /**
     * @param Point $point
     *
     * @return array
     */
    public function getBusinessHours(Point $point): array
    {
        $listHours = [];

        /** @var BussinessHours $businessHour */
        foreach ($point->business_hours() as $key => $businessHour) {
            $day = $this->translator->trans(sprintf('sylius.mondial_relay.day.%s', $businessHour->day()), [], 'messages');
            $listHours[$key]['label'] = $day;

            if ($this->isPointOpen($businessHour, 1)) {
                $listHours[$key]['open1'] = $this->formatTime($businessHour->openingTime1());
                $listHours[$key]['close1'] = $this->formatTime($businessHour->closingTime1());
            } else {
                $listHours[$key]['open1'] = '';
                $listHours[$key]['close1'] = '';
            }

            if ($this->isPointOpen($businessHour, 2)) {
                $listHours[$key]['open2'] = $this->formatTime($businessHour->openingTime2());
                $listHours[$key]['close2'] = $this->formatTime($businessHour->closingTime2());
            } else {
                $listHours[$key]['open2'] = '';
                $listHours[$key]['close2'] = '';
            }
        }

        return $listHours;
    }

    /**
     * @param BussinessHours $businessHours
     * @param int            $period
     *
     * @return bool
     */
    private function isPointOpen(BussinessHours $businessHours, int $period): bool {
        if (1 === $period) {
            return '0000' !== $businessHours->openingTime1() && '0000' !== $businessHours->closingTime1();
        }

        if (2 === $period) {
            return '0000' !== $businessHours->openingTime2() && '0000' !== $businessHours->closingTime2();
        }

        return false;
    }

    /**
     * @param string $time
     *
     * @return string
     */
    private function formatTime(string $time): string
    {
        if ('0000' === $time) {
            return '';
        }

        return sprintf('%sh%s', substr($time, 0, 2), substr($time, 2, 2));
    }
}
