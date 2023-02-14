<?php

namespace Sherlockode\SyliusMondialRelayPlugin\Controller\Admin;

use Sherlockode\SyliusMondialRelayPlugin\Form\Type\Admin\PrintTicketType;
use Sherlockode\SyliusMondialRelayPlugin\Manager\OrderManager;
use Sherlockode\SyliusMondialRelayPlugin\MondialRelay\Api\ErrorCode;
use Sherlockode\SyliusMondialRelayPlugin\MondialRelay\Api\Exception\ApiException;
use Sherlockode\SyliusMondialRelayPlugin\MondialRelay\MondialRelay;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Sylius\Component\Shipping\Model\ShipmentInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

class ShipmentController
{
    /**
     * @var RepositoryInterface
     */
    private $shipmentRepository;

    /**
     * @var UrlGeneratorInterface
     */
    private $urlGenerator;

    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var FormFactoryInterface
     */
    private $formFactory;

    /**
     * @var Environment
     */
    private $twig;

    /**
     * @var MondialRelay
     */
    private $mondialRelay;

    /**
     * @var OrderManager
     */
    private $orderManager;

    /**
     * @var bool
     */
    private $enableTicketPrinting;

    /**
     * @param RepositoryInterface   $shipmentRepository
     * @param UrlGeneratorInterface $urlGenerator
     * @param RequestStack          $requestStack
     * @param TranslatorInterface   $translator
     * @param FormFactoryInterface  $formFactory
     * @param Environment           $twig
     * @param MondialRelay          $mondialRelay
     * @param OrderManager          $orderManager
     * @param bool                  $enableTicketPrinting
     */
    public function __construct(
        RepositoryInterface $shipmentRepository,
        UrlGeneratorInterface $urlGenerator,
        RequestStack $requestStack,
        TranslatorInterface $translator,
        FormFactoryInterface $formFactory,
        Environment $twig,
        MondialRelay $mondialRelay,
        OrderManager $orderManager,
        bool $enableTicketPrinting
    ) {
        $this->shipmentRepository = $shipmentRepository;
        $this->urlGenerator = $urlGenerator;
        $this->requestStack = $requestStack;
        $this->translator = $translator;
        $this->formFactory = $formFactory;
        $this->twig = $twig;
        $this->mondialRelay = $mondialRelay;
        $this->orderManager = $orderManager;
        $this->enableTicketPrinting = $enableTicketPrinting;
    }

    /**
     * @param int $id
     *
     * @return Response
     */
    public function printTicket(int $id): Response
    {
        if (!$this->enableTicketPrinting) {
            throw new BadRequestException('Ticket printing is disable');
        }

        $shipment = $this->shipmentRepository->find($id);

        if (!$shipment) {
            throw new NotFoundHttpException(sprintf('Shipment with ID "%d" does not exists', $id));
        }

        if (ShipmentInterface::STATE_READY !== $shipment->getState()) {
            throw new BadRequestException(sprintf('Shipment with ID "%d" is not ready', $id));
        }

        if (!$shipment->getPickupPointId()) {
            throw new BadRequestException(sprintf('Shipment with ID "%d" has no pickup point', $id));
        }

        $form = $this->formFactory->create(
            PrintTicketType::class,
            [
                'parcelCount' => 1,
                'weight' => $this->orderManager->getOrderTotalWeight($shipment->getOrder()),
            ],
            [
                'action' => $this->urlGenerator->generate('sherlockode_sylius_mondial_relay_admin_print_ticket', [
                    'id' => $id,
                ]),
                'method' => 'POST',
            ]
        );

        $form->handleRequest($this->requestStack->getMainRequest());

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $ticket = $this->mondialRelay->printTicket($shipment, $form->getData());

                return new JsonResponse([
                    'success' => true,
                    'target_url' => $ticket->get10x15TicketUrl(),
                ]);
            } catch (\Exception $exception) {
                $message = 'sylius.mondial_relay.error_when_printing_ticket';

                if ($exception instanceof ApiException) {
                    $message = 'sylius.mondial_relay.api.errors.' . ErrorCode::getErrorMessageKey($exception->getCode());
                }

                $form->addError(new FormError($this->translator->trans($message, [], 'messages')));
            }
        }

        return new JsonResponse([
            'success' => !$form->isSubmitted() || $form->isValid(),
            'html' => $this->twig->render(
                '@SherlockodeSyliusMondialRelayPlugin/Admin/Order/Show/Shipment/_print_ticket_modal.html.twig',
                ['form' => $form->createView()]
            )
        ]);
    }
}
