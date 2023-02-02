<?php

namespace Sherlockode\SyliusMondialRelayPlugin\MondialRelay\Api\Factory;

use Sherlockode\SyliusMondialRelayPlugin\Model\OpeningTimeSlot;

class OpeningTimeSlotFactory
{
    /**
     * @param int    $day
     * @param string $openingTime
     * @param string $closingTime
     *
     * @return OpeningTimeSlot
     */
    public function create(int $day, string $openingTime, string $closingTime): OpeningTimeSlot
    {
        $timeSlot = new OpeningTimeSlot();
        $timeSlot->setDay($day);
        $timeSlot->setOpeningTime($openingTime);
        $timeSlot->setClosingTime($closingTime);

        return $timeSlot;
    }
}
