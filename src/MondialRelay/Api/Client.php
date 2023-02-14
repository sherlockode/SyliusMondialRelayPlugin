<?php

namespace Sherlockode\SyliusMondialRelayPlugin\MondialRelay\Api;

use Sherlockode\SyliusMondialRelayPlugin\Model\Ticket;
use Sherlockode\SyliusMondialRelayPlugin\MondialRelay\Api\Exception\ApiException;
use Sherlockode\SyliusMondialRelayPlugin\MondialRelay\Api\Factory\PointFactory;
use Sherlockode\SyliusMondialRelayPlugin\MondialRelay\Api\Factory\TicketFactory;
use Sherlockode\SyliusMondialRelayPlugin\MondialRelay\Api\Request\GenericRequest;

/**
 * Class Client
 */
class Client
{
    /**
     * @var TicketFactory
     */
    private $ticketFactory;

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
     * @var \SoapClient
     */
    private $soap;

    /**
     * Client constructor.
     *
     * @param TicketFactory $ticketFactory
     * @param string        $wsdl
     * @param string        $merchantId
     * @param string        $privateKey
     */
    public function __construct(
        TicketFactory $ticketFactory,
        string $wsdl,
        string $merchantId,
        string $privateKey
    ) {
        $this->ticketFactory = $ticketFactory;
        $this->wsdl = $wsdl;
        $this->merchantId = $merchantId;
        $this->privateKey = $privateKey;
    }

    /**
     * @param array $payload
     *
     * @return array
     *
     * @throws ApiException
     */
    public function WSI4PointRelaisRecherche(array $payload): array
    {
        $request = new GenericRequest($this->merchantId, $this->privateKey, $payload);
        $request->sign();

        try {
            $response = $this->getClient()->WSI4_PointRelais_Recherche($request->getPayload());
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
     * @param array $payload
     *
     * @return Ticket
     *
     * @throws \Exception
     */
    public function WSI2CreationEtiquette(array $payload): Ticket
    {
        $request = new GenericRequest($this->merchantId, $this->privateKey, $payload);
        $request->sign();

        $response = $this->getClient()->WSI2_CreationEtiquette($request->getPayload());

        if ($response->WSI2_CreationEtiquetteResult->STAT) {
            throw new ApiException($response->WSI2_CreationEtiquetteResult->STAT);
        }

        return $this->ticketFactory->create($response->WSI2_CreationEtiquetteResult);
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
