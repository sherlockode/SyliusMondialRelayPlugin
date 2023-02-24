<?php

namespace Sherlockode\SyliusMondialRelayPlugin\Model;

class OpeningTimeSlot
{
    /**
     * @var int
     */
    private $day;

    /**
     * @var \DateTimeInterface
     */
    private $openingTime;

    /**
     * @var \DateTimeInterface
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
     * @return \DateTimeInterface|null
     */
    public function getOpeningTime(): ?\DateTimeInterface
    {
        return $this->openingTime;
    }

    /**
     * @param \DateTimeInterface|null $openingTime
     *
     * @return $this
     */
    public function setOpeningTime(?\DateTimeInterface $openingTime): self
    {
        $this->openingTime = $openingTime;

        return $this;
    }

    /**
     * @return \DateTimeInterface|null
     */
    public function getClosingTime(): ?\DateTimeInterface
    {
        return $this->closingTime;
    }

    /**
     * @param \DateTimeInterface|null $closingTime
     *
     * @return $this
     */
    public function setClosingTime(?\DateTimeInterface $closingTime): self
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
