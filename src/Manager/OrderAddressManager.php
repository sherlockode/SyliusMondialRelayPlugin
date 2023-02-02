<?php

namespace Sherlockode\SyliusMondialRelayPlugin\Manager;

use Sherlockode\SyliusMondialRelayPlugin\MondialRelay\MondialRelay;
use Sylius\Component\Core\Factory\AddressFactoryInterface;
use Sylius\Component\Core\Model\AddressInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * Class OrderAddressManager
 */
class OrderAddressManager
{
    /**
     * @var AddressFactoryInterface
     */
    private $addressFactory;

    /**
     * @var MondialRelay
     */
    private $apiClient;

    /**
     * @param AddressFactoryInterface $addressFactory
     * @param MondialRelay            $apiClient
     */
    public function __construct(AddressFactoryInterface $addressFactory, MondialRelay $apiClient)
    {
        $this->addressFactory = $addressFactory;
        $this->apiClient = $apiClient;
    }

    /**
     * @param GenericEvent $event
     */
    public function updateShippingAddress(GenericEvent $event): void
    {
        $order = $event->getSubject();

        foreach ($order->getShipments() as $shipment) {
            if (!$shipment->getPickupPointId()) {
                continue;
            }

            $this->setShippingAddress($order, $shipment->getPickupPointId());
        }
    }

    /**
     * @param OrderInterface $order
     * @param string         $pickupPointId
     */
    private function setShippingAddress(OrderInterface $order, string $pickupPointId): void
    {
        try {
            $pickupPoint = $this->apiClient->getPickupPoint(
                $pickupPointId,
                $order->getShippingAddress()->getCountryCode()
            );
        } catch (\Exception $e) {
            return;
        }

        if (!$pickupPoint) {
            return;
        }

        $shippingAddress = $order->getShippingAddress();

        /** @var AddressInterface $address */
        $address = $this->addressFactory->createNew();
        $address->setStreet(sprintf(
            '%s, %s',
            $pickupPoint->getName(),
            $pickupPoint->getShortAddress(),
        ));
        $address->setFirstName($shippingAddress->getFirstName());
        $address->setLastName($shippingAddress->getLastName());
        $address->setPhoneNumber($shippingAddress->getPhoneNumber());
        $address->setCity($pickupPoint->getCity());
        $address->setPostcode($pickupPoint->getZipCode());
        $address->setCountryCode($pickupPoint->getCountry());
        $order->setShippingAddress($address);
    }
}
