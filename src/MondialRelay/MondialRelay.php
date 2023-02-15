<?php

namespace Sherlockode\SyliusMondialRelayPlugin\MondialRelay;

use Sherlockode\SyliusMondialRelayPlugin\Model\Point;
use Sherlockode\SyliusMondialRelayPlugin\Model\Ticket;
use Sherlockode\SyliusMondialRelayPlugin\MondialRelay\Api\Client;
use Sherlockode\SyliusMondialRelayPlugin\MondialRelay\Api\Exception\ApiException;
use Sylius\Component\Addressing\Model\AddressInterface;
use Sylius\Component\Customer\Model\CustomerInterface;
use Sylius\Component\Order\Model\OrderInterface;
use Sylius\Component\Order\Model\OrderItemInterface;
use Sylius\Component\Shipping\Model\ShipmentInterface;

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
     * @param string       $zipCode
     * @param string|null  $action
     * @param string       $country
     *
     * @return Point[]
     *
     * @throws ApiException
     */
    public function findPickupPointsByZipCode(string $zipCode, ?string $action, string $country = 'FR'): array
    {
        return $this->client->WSI4PointRelaisRecherche([
            'Pays' => $country,
            'CP' => $zipCode,
            'Action' => $action,
            'DelaiEnvoi' => '0',
            'RayonRecherche' => '20',
            'NombreResultats' => '30',
        ]);
    }

    /**
     * @param ShipmentInterface $shipment
     * @param array             $userData
     *
     * @return Ticket
     *
     * @throws ApiException
     */
    public function printTicket(ShipmentInterface $shipment, array $userData): Ticket
    {
        $order = $shipment->getOrder();
        $customer = $order->getCustomer();
        $shippingAddress = $order->getShippingAddress();
        $billingAddress = $order->getBillingAddress();
        $channel = $order->getChannel();
        $shop = $channel->getShopBillingData();
        $toLocaleCode = function (string $locale) {
            return strtoupper(substr($locale, 0, 2));
        };
        $gender = CustomerInterface::FEMALE_GENDER === $customer->getGender() ? 'MME' : 'MR';
        $customerName = sprintf(
            '%s %s %s',
            $gender,
            $shippingAddress->getLastName(),
            $shippingAddress->getFirstName()
        );

        return $this->client->WSI2CreationEtiquette([
            'ModeCol' => $userData['collectionMode'] ?? 'REL',
            'ModeLiv' => $userData['deliveryMode'] ?? '24R',
            'NDossier' => $order->getNumber(),
            'NClient' => $order->getCustomer() ? $order->getCustomer()->getId() : null,
            'Expe_Langage' => $toLocaleCode($channel->getDefaultLocale()->getCode()),
            'Expe_Ad1' => $shop->getCompany(),
            'Expe_Ad2 ' => null,
            'Expe_Ad3' => $shop->getStreet(),
            'Expe_Ad4 ' => null,
            'Expe_Ville' => $shop->getCity(),
            'Expe_CP' => $shop->getPostcode(),
            'Expe_Pays' => $shop->getCountryCode(),
            'Expe_Tel1' => $channel->getContactPhoneNumber(),
            'Expe_Tel2' => null,
            'Expe_Mail' => $channel->getContactEmail(),
            'Dest_Langage' => $toLocaleCode($order->getLocaleCode()),
            'Dest_Ad1' => $customerName,
            'Dest_Ad2' => null,
            'Dest_Ad3' => substr($billingAddress->getStreet(),0, 32),
            'Dest_Ad4' => null,
            'Dest_Ville' => substr($billingAddress->getCity(), 0, 26),
            'Dest_CP' => $billingAddress->getPostcode(),
            'Dest_Pays' => $billingAddress->getCountryCode(),
            'Dest_Tel1' => $customer->getPhoneNumber(),
            'Dest_Tel2' => null,
            'Dest_Mail' => $customer->getEmail(),
            'Poids' => $userData['weight'] ?? 15,
            'Longueur' => $userData['size'] ?? null,
            'Taille' => null,
            'NbColis' => $userData['parcelCount'] ?? 1,
            'CRT_Valeur' => 0,
            'CRT_Devise' => $order->getCurrencyCode(),
            'Exp_Valeur' => $order->getItemsTotal(),
            'Exp_Devise' => $order->getCurrencyCode(),
            'COL_Rel_Pays' => $shippingAddress->getCountryCode(),
            'COL_Rel' => $shipment->getPickupPointId(),
            'LIV_Rel_Pays' => $shippingAddress->getCountryCode(),
            'LIV_Rel' => $shipment->getPickupPointId(),
        ]);
    }
}
