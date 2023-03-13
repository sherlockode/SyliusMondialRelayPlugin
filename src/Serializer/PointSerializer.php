<?php

namespace Sherlockode\SyliusMondialRelayPlugin\Serializer;

use Sherlockode\SyliusMondialRelayPlugin\Model\Point;
use Symfony\Contracts\Translation\TranslatorInterface;

class PointSerializer
{
    public function __construct(private TranslatorInterface $translator)
    {
    }

    /**
     * @param Point $point
     *
     * @return array
     */
    public function serialize(Point $point): array
    {
        return [
            'id' => $point->getId(),
            'label' => $point->getName(),
            'address' => $point->getShortAddress(),
            'zipCode' => $point->getZipCode(),
            'city' => $point->getCity(),
            'country' => $point->getCountry(),
            'lat' => $point->getLatitude(),
            'lng' => $point->getLongitude(),
            'businessHours' => $this->getTimeSlots($point),
        ];
    }

    /**
     * @param Point $point
     *
     * @return array
     */
    private function getTimeSlots(Point $point): array
    {
        $timeSlots = [];

        foreach ($point->getOpeningHours() as $timeSlot) {
            if (!isset($timeSlots[$timeSlot->getDay()])) {
                $timeSlots[$timeSlot->getDay()] = [
                    'day' => $timeSlot->getDay(),
                    'label' => $this->translator->trans(sprintf('sylius.mondial_relay.day.%s', $timeSlot->getDayLabel()), [], 'messages'),
                    'slots' => [],
                ];
            }

            $timeSlots[$timeSlot->getDay()]['slots'][] = [
                'from' => $timeSlot->getOpeningTime() ? $timeSlot->getOpeningTime()?->format('H\hi') : null,
                'to' => $timeSlot->getClosingTime() ? $timeSlot->getClosingTime()?->format('H\hi') : null,
            ];
        }

        return array_values($timeSlots);
    }
}
