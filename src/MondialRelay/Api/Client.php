<?php

namespace Sherlockode\SyliusMondialRelayPlugin\MondialRelay\Api;

use Sherlockode\SyliusMondialRelayPlugin\MondialRelay\Api\Exception\ApiException;
use Sherlockode\SyliusMondialRelayPlugin\MondialRelay\Api\Factory\PointFactory;
use Sherlockode\SyliusMondialRelayPlugin\MondialRelay\Api\Factory\RequestFactory;

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
     * @var array
     */
    private $types;

    /**
     * @var \SoapClient
     */
    private $soap;

    /**
     * Client constructor.
     *
     * @param string $wsdl
     * @param string $merchantId
     * @param string $privateKey
     * @param array  $types
     */
    public function __construct(string $wsdl, string $merchantId, string $privateKey, array $types)
    {
        $this->wsdl = $wsdl;
        $this->merchantId = $merchantId;
        $this->privateKey = $privateKey;
        $this->types = $types;
    }

    /**
     * @param array $request
     *
     * @return array
     *
     * @throws ApiException
     */
    public function WSI4PointRelaisRecherche(array $request): array
    {
        $requestFactory = new RequestFactory();
        $request = $requestFactory->create($this->merchantId, $this->privateKey, $request);

        try {
            $response = $this->getClient()->WSI4_PointRelais_Recherche($request);
        } catch (\SoapFault $soapFault) {
            throw new ApiException();
        }

        if ($response->WSI4_PointRelais_RechercheResult->STAT) {
            throw new ApiException();
        }

        $details = $response->WSI4_PointRelais_RechercheResult->PointsRelais->PointRelais_Details;

        if (!is_array($details)) {
            $details = [$details];
        }

        return array_map([new PointFactory(), 'create'], $details);
    }

    /**
     * @return \SoapClient
     *
     * @throws \SoapFault
     */
    private function getClient(): \SoapClient
    {
        if (!$this->soap) {
            $this->soap = new \SoapClient($this->wsdl);
        }

        return $this->soap;
    }
}
