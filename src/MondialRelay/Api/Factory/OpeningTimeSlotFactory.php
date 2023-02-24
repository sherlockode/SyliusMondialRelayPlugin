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
        $timeSlot->setOpeningTime($this->toDateTime($openingTime));
        $timeSlot->setClosingTime($this->toDateTime($closingTime));

        return $timeSlot;
    }

    /**
     * @param string $time
     *
     * @return \DateTimeInterface
     */
    private function toDateTime(string $time): \DateTimeInterface
    {
        $dateTime = new \DateTime();
        $dateTime->setTime((int)substr($time, 0, 2), (int)substr($time, 2, 2));

        return $dateTime;
    }
}
