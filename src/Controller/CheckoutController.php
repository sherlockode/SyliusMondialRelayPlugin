<?php

namespace Sherlockode\SyliusMondialRelayPlugin\Controller;

use Sherlockode\SyliusMondialRelayPlugin\Model\OpeningTimeSlot;
use Sherlockode\SyliusMondialRelayPlugin\Model\Point;
use Sherlockode\SyliusMondialRelayPlugin\MondialRelay\MondialRelay;
use Sylius\Component\Order\Context\CartContextInterface;
use Sylius\Component\Shipping\Model\ShipmentInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;
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
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var CartContextInterface
     */
    private $cartContext;

    /**
     * @var Environment
     */
    private $twig;

    /**
     * @var MondialRelay
     */
    private $apiClient;

    /**
     * CheckoutController constructor.
     *
     * @param TranslatorInterface  $translator
     * @param CartContextInterface $cartContext
     * @param Environment          $twig
     * @param MondialRelay         $apiClient
     */
    public function __construct(
        TranslatorInterface $translator,
        CartContextInterface $cartContext,
        Environment $twig,
        MondialRelay $apiClient
    ) {
        $this->translator = $translator;
        $this->cartContext = $cartContext;
        $this->twig = $twig;
        $this->apiClient = $apiClient;
    }

    /**
     * @return JsonResponse
     *
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function init(): JsonResponse
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
            $zipCode = $currentPickupPoint->getZipCode();
        } elseif ($shippingAddress) {
            $zipCode = $shippingAddress->getPostCode();
        }

        $current = $this->twig->render('@SherlockodeSyliusMondialRelayPlugin/Checkout/SelectShipping/_current_pickup_point.html.twig', [
            'pickupPoint' => $currentPickupPoint,
        ]);

        $address = $this->twig->render('@SherlockodeSyliusMondialRelayPlugin/Checkout/SelectShipping/_search_pickup_point.html.twig', [
            'zipCode' => $zipCode,
        ]);

        return new JsonResponse([
            'current' => $current,
            'address' => $address,
            'currentPointId' => null === $currentPickupPoint ? null : $currentPickupPoint->getId(),
        ]);
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
            $timeSlots = [];

            foreach ($point->getOpeningHours() as $timeSlot) {
                if (!isset($timeSlots[$timeSlot->getDay()])) {
                    $timeSlots[$timeSlot->getDay()] = [
                        'label' => $this->translator->trans(sprintf('sylius.mondial_relay.day.%s', $timeSlot->getDayLabel()), [], 'messages'),
                        'slots' => [],
                    ];
                }

                $timeSlots[$timeSlot->getDay()]['slots'][] = $this->translator->trans(
                    'sylius.mondial_relay.time_slot',
                    [
                        '%from%' => substr_replace($timeSlot->getOpeningTime(), ':', 2, 0),
                        '%to%' => substr_replace($timeSlot->getClosingTime(), ':', 2, 0),
                    ],
                    'messages'
                );
            }

            return [
                'id' => $point->getId(),
                'label' => $point->getName(),
                'address' => $point->getFullAddress(),
                'lat' => $point->getLatitude(),
                'lng' => $point->getLongitude(),
                'businessHours' => array_values($timeSlots),
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
