<?php

namespace Sherlockode\SyliusMondialRelayPlugin\Model;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\DBAL\Types\Types;

/**
 * Trait PickupPointTrait
 */
trait PickupPointTrait
{
    /**
     * @var string|null
     *
     * @ORM\Column(name="pickup_point_id", type="string", nullable=true)
     */
    #[ORM\Column(name: "pickup_point_id", type: Types::STRING, nullable: true)]
    private $pickupPointId;

    /**
     * @return string|null
     */
    public function getPickupPointId(): ?string
    {
        return $this->pickupPointId;
    }

    /**
     * @param string|null $pickupPointId
     *
     * @return $this
     */
    public function setPickupPointId(?string $pickupPointId): self
    {
        $this->pickupPointId = $pickupPointId;

        return $this;
    }
}
