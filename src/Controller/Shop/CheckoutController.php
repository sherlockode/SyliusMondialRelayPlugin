<?php

namespace Sherlockode\SyliusMondialRelayPlugin\Controller\Shop;

use Sherlockode\SyliusMondialRelayPlugin\Form\Type\Shop\SearchPickupPointType;
use Sherlockode\SyliusMondialRelayPlugin\Google\Place;
use Sherlockode\SyliusMondialRelayPlugin\MondialRelay\MondialRelay;
use Sherlockode\SyliusMondialRelayPlugin\Serializer\PointSerializer;
use Sylius\Component\Order\Context\CartContextInterface;
use Sylius\Component\Shipping\Model\ShipmentInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
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
     * @var UrlGeneratorInterface
     */
    private $urlGenerator;

    /**
     * @var FormFactoryInterface
     */
    private $formFactory;

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
     * @var Place
     */
    private $googlePlace;

    /**
     * @var PointSerializer
     */
    private $pointSerializer;

    /**
     * CheckoutController constructor.
     *
     * @param UrlGeneratorInterface $urlGenerator
     * @param FormFactoryInterface  $formFactory
     * @param CartContextInterface  $cartContext
     * @param Environment           $twig
     * @param MondialRelay          $apiClient
     * @param Place                 $googlePlace
     * @param PointSerializer       $pointSerializer
     */
    public function __construct(
        UrlGeneratorInterface $urlGenerator,
        FormFactoryInterface $formFactory,
        CartContextInterface $cartContext,
        Environment $twig,
        MondialRelay $apiClient,
        Place $googlePlace,
        PointSerializer $pointSerializer
    ) {
        $this->urlGenerator = $urlGenerator;
        $this->formFactory = $formFactory;
        $this->cartContext = $cartContext;
        $this->twig = $twig;
        $this->apiClient = $apiClient;
        $this->googlePlace = $googlePlace;
        $this->pointSerializer = $pointSerializer;
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
        $actions = '24R';
        $currentPickupPoint = $zipCode = null;
        $cart = $this->cartContext->getCart();
        $shippingAddress = $cart->getShippingAddress();
        $shipment = $cart->getShipments()->filter(function (ShipmentInterface $shipment) {
            return null !== $shipment->getPickupPointId();
        });

        if ($shipment->count()) {
            try {
                $currentPickupPoint = $this->apiClient->getPickupPoint(
                    $shipment->first()->getPickupPointId(),
                    $shippingAddress->getCountryCode(),
                );
            } catch (\Exception $e) {
            }
        }

        if ($currentPickupPoint) {
            $zipCode = $currentPickupPoint->getZipCode();
        } elseif ($shippingAddress) {
            $zipCode = $shippingAddress->getPostCode();
        }

        $form = $this->getPickupPointSearchForm(['zipCode' => $zipCode, 'types' => $actions]);

        try {
            $points = $this->apiClient->findPickupPointsByZipCode($zipCode, $actions);
        } catch (\Exception $e) {
            $points = [];
        }

        return new JsonResponse([
            'form' => $this->twig->render('@SherlockodeSyliusMondialRelayPlugin/Checkout/SelectShipping/_search_pickup_point.html.twig', [
                'form' => $form->createView(),
                'zipCode' => $zipCode,
            ]),
            'points' => array_values(array_map([$this->pointSerializer, 'serialize'], $points)),
        ]);
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function searchPickupPoints(Request $request): JsonResponse
    {
        $points = [];
        $form = $this->getPickupPointSearchForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $zipCode = $form->get('zipCode')->getData();
            $suggestion = $form->get('suggestion')->getData();
            $actions = $form->get('types')->getData();

            if ($suggestion) {
                $suggestedZipCode = $this->googlePlace->retrieveSuggestionZipCode($suggestion);

                if ($suggestedZipCode) {
                    $zipCode = $suggestedZipCode;
                }
            }

            try {
                if ($zipCode) {
                    $points = $this->apiClient->findPickupPointsByZipCode($zipCode, $actions);
                } else {
                    $cart = $this->cartContext->getCart();
                    $points = $this->apiClient->findPickupPointsAround($cart->getShippingAddress());
                }
            } catch (\Exception $e) {
            }
        }

        return new JsonResponse(array_values(array_map([$this->pointSerializer, 'serialize'], $points)));
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
            try {
                $pickupPoint = $this->apiClient->getPickupPoint(
                    $request->query->get('pickupPointId'),
                    $shippingAddress->getCountryCode()
                );
            } catch (\Exception $e) {
            }
        }

        return new Response($this->twig->render(
            '@SherlockodeSyliusMondialRelayPlugin/Checkout/SelectShipping/_current_pickup_point.html.twig',
            ['pickupPoint' => $pickupPoint]
        ));
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function autocomplete(Request $request): JsonResponse
    {
        $query = $request->query->get('query');

        if (!$query) {
            return new JsonResponse();
        }

        return new JsonResponse($this->googlePlace->getPlaces($query));
    }

    /**
     * @param array $data
     *
     * @return FormInterface
     */
    private function getPickupPointSearchForm(array $data = []): FormInterface
    {
        return $this->formFactory->create(SearchPickupPointType::class, $data, [
            'action' => $this->urlGenerator->generate('sherlockode_sylius_mondial_relay_search_pickup_points'),
            'method' => 'GET',
        ]);
    }
}
