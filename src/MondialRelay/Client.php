<?php

namespace Sherlockode\SyliusMondialRelayPlugin\MondialRelay;

use MondialRelay\ApiClient;
use MondialRelay\Point\Point;
use Sylius\Component\Addressing\Model\AddressInterface;

/**
 * Class Client
 */
class Client
{
    /**
     * @var string
     */
    private $wsdl;

    /**
     * @var string
     */
    private $merchantId;

    /**
     * @var string
     */
    private $privateKey;

    /**
     * @var ApiClient|null
     */
    private $apiClient;

    /**
     * Client constructor.
     *
     * @param string $wsdl
     * @param string $merchantId
     * @param string $privateKey
     */
    public function __construct(string $wsdl, string $merchantId, string $privateKey)
    {
        $this->wsdl = $wsdl;
        $this->merchantId = $merchantId;
        $this->privateKey = $privateKey;
    }

    /**
     * @param string $id
     * @param string $country
     *
     * @return Point|null
     *
     * @throws \Exception
     */
    public function getPickupPoint(string $id, string $country): ?Point
    {
        $points = $this->getClient()->findDeliveryPoint($id, $country);

        return $points[0] ?? null;
    }

    /**
     * @param AddressInterface $address
     *
     * @return Point[]
     *
     * @throws \Exception
     */
    public function findPickupPointsAround(AddressInterface $address): array
    {
        return $this->getClient()->findDeliveryPoints([
            'Pays'            => $address->getCountryCode(),
            'Ville'           => $address->getCity(),
            'CP'              => $address->getPostcode(),
            'DelaiEnvoi'      => '0',
            'RayonRecherche'  => '20',
            'NombreResultats' => '10',
        ]);
    }

    /**
     * @param string $zipCode
     *
     * @return Point[]
     *
     * @throws \Exception
     */
    public function findPickupPointsByZipCode(string $zipCode): array
    {
        return $this->getClient()->findDeliveryPoints([
            'Pays'            => 'FR',
            'CP'              => $zipCode,
            'DelaiEnvoi'      => '0',
            'RayonRecherche'  => '20',
            'NombreResultats' => '10',
        ]);
    }

    /**
     * @return ApiClient
     *
     * @throws \SoapFault
     */
    private function getClient(): ApiClient
    {
        if (!$this->apiClient) {
            $soapClient = new \SoapClient($this->wsdl);
            $this->apiClient = new ApiClient($soapClient, $this->merchantId, $this->privateKey);
        }

        return $this->apiClient;
    }
}
