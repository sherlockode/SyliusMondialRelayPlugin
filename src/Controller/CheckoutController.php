<?php

namespace Sherlockode\SyliusMondialRelayPlugin\Controller;

use MondialRelay\Point\Point;
use Sherlockode\SyliusMondialRelayPlugin\Manager\PointAddressManager;
use Sherlockode\SyliusMondialRelayPlugin\MondialRelay\Client as MondialRelayClient;
use Sylius\Component\Order\Context\CartContextInterface;
use Sylius\Component\Shipping\Model\ShipmentInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

/**
 * Class CheckoutController
 */
class CheckoutController
{
    /**
     * @var CartContextInterface
     */
    private $cartContext;

    /**
     * @var Environment
     */
    private $twig;

    /**
     * @var MondialRelayClient
     */
    private $apiClient;

    /**
     * @var PointAddressManager
     */
    private $pointAddressManager;

    /**
     * CheckoutController constructor.
     *
     * @param CartContextInterface $cartContext
     * @param Environment          $twig
     * @param MondialRelayClient   $apiClient
     * @param PointAddressManager  $pointAddressManager
     */
    public function __construct(
        CartContextInterface $cartContext,
        Environment $twig,
        MondialRelayClient $apiClient,
        PointAddressManager $pointAddressManager
    ) {
        $this->cartContext = $cartContext;
        $this->twig = $twig;
        $this->apiClient = $apiClient;
        $this->pointAddressManager = $pointAddressManager;
    }

    /**
     * @return Response
     *
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function init(): Response
    {
        $currentPickupPoint = $zipCode = null;
        $cart = $this->cartContext->getCart();
        $shippingAddress = $cart->getShippingAddress();
        $shipment = $cart->getShipments()->filter(function (ShipmentInterface $shipment) {
            return null !== $shipment->getPickupPointId();
        });

        if ($shipment->count()) {
            $currentPickupPoint = $this->apiClient->getPickupPoint(
                $shipment->first()->getPickupPointId(),
                $shippingAddress->getCountryCode(),
            );
        }

        if ($currentPickupPoint) {
            $zipCode = $currentPickupPoint->cp();
        } elseif ($shippingAddress) {
            $zipCode = $shippingAddress->getPostCode();
        }

        return new Response($this->twig->render(
            '@SherlockodeSyliusMondialRelayPlugin/Checkout/SelectShipping/_init.html.twig',
            ['currentPickupPoint' => $currentPickupPoint, 'zipCode' => $zipCode]
        ));
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function searchPickupPoints(Request $request): JsonResponse
    {
        $zipCode = $request->query->get('zipCode');

        try {
            if ($zipCode) {
                $points = $this->apiClient->findPickupPointsByZipCode($zipCode);
            } else {
                $cart = $this->cartContext->getCart();
                $points = $this->apiClient->findPickupPointsAround($cart->getShippingAddress());
            }
        } catch (\Exception $e) {
            $points = [];
        }

        return new JsonResponse(array_values(array_map(function (Point $point) {
            return [
                'id' => $point->id(),
                'label' => $this->pointAddressManager->getPointLabel($point),
                'address' => $this->pointAddressManager->getPointFullAddress($point),
                'lat' => $point->latitude(),
                'lng' => $point->longitude(),
                'businessHours' => $this->pointAddressManager->getBusinessHours($point),
            ];
        }, $points)));
    }

    /**
     * @return Response
     *
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function getSelectedPickupPoint(): Response
    {
        $pickupPoint = null;
        $cart = $this->cartContext->getCart();
        $shippingAddress = $cart->getShippingAddress();
        $shipment = $cart->getShipments()->filter(function (ShipmentInterface $shipment) {
            return null !== $shipment->getPickupPointId();
        });

        if ($shipment->count()) {
            $pickupPoint = $this->apiClient->getPickupPoint(
                $shipment->first()->getPickupPointId(),
                $shippingAddress->getCountryCode()
            );
        }

        $availablePoints = $this->apiClient->findPickupPointsAround($shippingAddress);

        return new Response($this->twig->render(
            '@SherlockodeSyliusMondialRelayPlugin/Checkout/current_pickup_point.html.twig',
            ['pickupPoint' => $pickupPoint, 'availablePoints' => $availablePoints]
        ));
    }

    /**
     * @param Request $request
     *
     * @return Response
     *
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function getPickupPoint(Request $request): Response
    {
        $pickupPoint = null;
        $cart = $this->cartContext->getCart();
        $shippingAddress = $cart->getShippingAddress();

        if ($request->query->has('pickupPointId')) {
            $pickupPoint = $this->apiClient->getPickupPoint(
                $request->query->get('pickupPointId'),
                $shippingAddress->getCountryCode()
            );
        }

        return new Response($this->twig->render(
            '@SherlockodeSyliusMondialRelayPlugin/Checkout/SelectShipping/_current_pickup_point.html.twig',
            ['pickupPoint' => $pickupPoint]
        ));
    }
}
