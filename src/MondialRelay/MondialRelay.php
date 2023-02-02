<?php

namespace Sherlockode\SyliusMondialRelayPlugin\MondialRelay;

use Sherlockode\SyliusMondialRelayPlugin\Model\Point;
use Sherlockode\SyliusMondialRelayPlugin\MondialRelay\Api\Client;
use Sherlockode\SyliusMondialRelayPlugin\MondialRelay\Api\Exception\ApiException;
use Sylius\Component\Addressing\Model\AddressInterface;

class MondialRelay
{
    /**
     * @var Client
     */
    private $client;

    /**
     * @param Client $client
     */
    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * @param string $id
     * @param string $country
     *
     * @return Point|null
     *
     * @throws ApiException
     */
    public function getPickupPoint(string $id, string $country = 'FR'): ?Point
    {
        $response = $this->client->WSI4PointRelaisRecherche([
            'NumPointRelais' => $id,
            'Pays' => $country,
        ]);

        return $response[0] ?? null;
    }

    /**
     * @param AddressInterface $address
     *
     * @return Point[]
     *
     * @throws ApiException
     */
    public function findPickupPointsAround(AddressInterface $address): array
    {
        return $this->client->WSI4PointRelaisRecherche([
            'Pays'            => $address->getCountryCode(),
            'Ville'           => $address->getCity(),
            'CP'              => $address->getPostcode(),
            'DelaiEnvoi'      => '0',
            'RayonRecherche'  => '20',
            'NombreResultats' => '30',
        ]);
    }

    /**
     * @param string $zipCode
     * @param string $country
     *
     * @return Point[]
     *
     * @throws ApiException
     */
    public function findPickupPointsByZipCode(string $zipCode, string $country = 'FR'): array
    {
        return $this->client->WSI4PointRelaisRecherche([
            'Pays' => $country,
            'CP'   => $zipCode,
            'DelaiEnvoi'      => '0',
            'RayonRecherche'  => '20',
            'NombreResultats' => '30',
        ]);
    }
}
