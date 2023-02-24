<?php

namespace Sherlockode\SyliusMondialRelayPlugin\Model;

class Point
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $nameComplement;

    /**
     * @var string
     */
    private $street;

    /**
     * @var string
     */
    private $streetComplement;

    /**
     * @var string
     */
    private $zipCode;

    /**
     * @var string
     */
    private $city;

    /**
     * @var string
     */
    private $country;

    /**
     * @var float
     */
    private $latitude;

    /**
     * @var float
     */
    private $longitude;

    /**
     * @var string
     */
    private $activityType;

    /**
     * @var int
     */
    private $distance;

    /**
     * @var string
     */
    private $localisation;

    /**
     * @var string
     */
    private $localisationComplement;

    /**
     * @var array
     */
    private $openingHours;

    /**
     * @var string
     */
    private $planUrl;

    /**
     * @var string
     */
    private $pictureUrl;

    public function __construct()
    {
        $this->openingHours = [];
    }

    /**
     * @return string|null
     */
    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * @param string|null $id
     *
     * @return $this
     */
    public function setId(?string $id): self
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string|null $name
     *
     * @return $this
     */
    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getNameComplement(): ?string
    {
        return $this->nameComplement;
    }

    /**
     * @param string|null $nameComplement
     *
     * @return $this
     */
    public function setNameComplement(?string $nameComplement): self
    {
        $this->nameComplement = $nameComplement;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getStreet(): ?string
    {
        return $this->street;
    }

    /**
     * @param string|null $street
     *
     * @return $this
     */
    public function setStreet(?string $street): self
    {
        $this->street = $street;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getStreetComplement(): ?string
    {
        return $this->streetComplement;
    }

    /**
     * @param string|null $streetComplement
     *
     * @return $this
     */
    public function setStreetComplement(?string $streetComplement): self
    {
        $this->streetComplement = $streetComplement;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getZipCode(): ?string
    {
        return $this->zipCode;
    }

    /**
     * @param string|null $zipCode
     *
     * @return $this
     */
    public function setZipCode(?string $zipCode): self
    {
        $this->zipCode = $zipCode;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getCity(): ?string
    {
        return $this->city;
    }

    /**
     * @param string|null $city
     *
     * @return $this
     */
    public function setCity(?string $city): self
    {
        $this->city = $city;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getCountry(): ?string
    {
        return $this->country;
    }

    /**
     * @param string|null $country
     *
     * @return $this
     */
    public function setCountry(?string $country): self
    {
        $this->country = $country;

        return $this;
    }

    /**
     * @return float|null
     */
    public function getLatitude(): ?float
    {
        return $this->latitude;
    }

    /**
     * @param float|null $latitude
     *
     * @return $this
     */
    public function setLatitude(?float $latitude): self
    {
        $this->latitude = $latitude;

        return $this;
    }

    /**
     * @return float|null
     */
    public function getLongitude(): ?float
    {
        return $this->longitude;
    }

    /**
     * @param float|null $longitude
     *
     * @return $this
     */
    public function setLongitude(?float $longitude): self
    {
        $this->longitude = $longitude;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getActivityType(): ?string
    {
        return $this->activityType;
    }

    /**
     * @param string|null $activityType
     *
     * @return $this
     */
    public function setActivityType(?string $activityType): self
    {
        $this->activityType = $activityType;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getDistance(): ?int
    {
        return $this->distance;
    }

    /**
     * @param int|null $distance
     *
     * @return $this
     */
    public function setDistance(?int $distance): self
    {
        $this->distance = $distance;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getLocalisation(): ?string
    {
        return $this->localisation;
    }

    /**
     * @param string|null $localisation
     *
     * @return $this
     */
    public function setLocalisation(?string $localisation): self
    {
        $this->localisation = $localisation;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getLocalisationComplement(): ?string
    {
        return $this->localisationComplement;
    }

    /**
     * @param string|null $localisationComplement
     *
     * @return $this
     */
    public function setLocalisationComplement(?string $localisationComplement): self
    {
        $this->localisationComplement = $localisationComplement;

        return $this;
    }

    /**
     * @return array
     */
    public function getOpeningHours(): array
    {
        return $this->openingHours;
    }

    /**
     * @param array $openingHours
     *
     * @return $this
     */
    public function setOpeningHours(array $openingHours): self
    {
        $this->openingHours = $openingHours;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getPlanUrl(): ?string
    {
        return $this->planUrl;
    }

    /**
     * @param string|null $planUrl
     *
     * @return $this
     */
    public function setPlanUrl(?string $planUrl): self
    {
        $this->planUrl = $planUrl;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getPictureUrl(): ?string
    {
        return $this->pictureUrl;
    }

    /**
     * @param string|null $pictureUrl
     *
     * @return $this
     */
    public function setPictureUrl(?string $pictureUrl): self
    {
        $this->pictureUrl = $pictureUrl;

        return $this;
    }

    /**
     * @return string
     */
    public function getFullAddress(): string
    {
        $address = array_filter([
            $this->getStreet(),
            $this->getStreetComplement(),
            $this->getZipCode(),
            $this->getCity()
        ]);

        return implode(', ', $address);
    }

    /**
     * @return string
     */
    public function getShortAddress(): string
    {
        $address = array_filter([
            trim($this->getStreet()),
            trim($this->getStreetComplement())
        ]);

        return implode(', ', $address);
    }
}
