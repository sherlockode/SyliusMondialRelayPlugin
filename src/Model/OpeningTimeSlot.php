<?php

namespace Sherlockode\SyliusMondialRelayPlugin\Model;

class OpeningTimeSlot
{
    /**
     * @var int
     */
    private $day;

    /**
     * @var string
     */
    private $openingTime;

    /**
     * @var string
     */
    private $closingTime;

    /**
     * @return int|null
     */
    public function getDay(): ?int
    {
        return $this->day;
    }

    /**
     * @param int|null $day
     *
     * @return $this
     */
    public function setDay(?int $day): self
    {
        $this->day = $day;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getOpeningTime(): ?string
    {
        return $this->openingTime;
    }

    /**
     * @param string|null $openingTime
     *
     * @return $this
     */
    public function setOpeningTime(?string $openingTime): self
    {
        $this->openingTime = $openingTime;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getClosingTime(): ?string
    {
        return $this->closingTime;
    }

    /**
     * @param string|null $closingTime
     *
     * @return $this
     */
    public function setClosingTime(?string $closingTime): self
    {
        $this->closingTime = $closingTime;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getDayLabel(): ?string
    {
        $days = [
            1 => 'monday',
            2 => 'tuesday',
            3 => 'wednesday',
            4 => 'thursday',
            5 => 'friday',
            6 => 'saturday',
            7 => 'sunday',
        ];

        return $days[$this->getDay()] ?? null;
    }
}
