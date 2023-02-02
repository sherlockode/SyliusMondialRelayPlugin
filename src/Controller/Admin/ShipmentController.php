<?php

namespace Sherlockode\SyliusMondialRelayPlugin\Controller\Admin;

use Sherlockode\SyliusMondialRelayPlugin\MondialRelay\MondialRelay;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Sylius\Component\Shipping\Model\ShipmentInterface;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

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
     * @var MondialRelay
     */
    private $mondialRelay;

    /**
     * @var bool
     */
    private $enableTicketPrinting;

    /**
     * @param RepositoryInterface   $shipmentRepository
     * @param UrlGeneratorInterface $urlGenerator
     * @param RequestStack          $requestStack
     * @param TranslatorInterface   $translator
     * @param MondialRelay          $mondialRelay
     * @param bool                  $enableTicketPrinting
     */
    public function __construct(
        RepositoryInterface $shipmentRepository,
        UrlGeneratorInterface $urlGenerator,
        RequestStack $requestStack,
        TranslatorInterface $translator,
        MondialRelay $mondialRelay,
        bool $enableTicketPrinting
    ) {
        $this->shipmentRepository = $shipmentRepository;
        $this->urlGenerator = $urlGenerator;
        $this->requestStack = $requestStack;
        $this->translator = $translator;
        $this->mondialRelay = $mondialRelay;
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

        try {
            $ticket = $this->mondialRelay->printTicket($shipment);
        } catch (\Exception $exception) {
            $message = $this->translator->trans('sylius.mondial_relay.error_when_printing_ticket', [], 'messages');
            $this->requestStack->getSession()
                ->getFlashBag()
                ->add('error', $message);

            return new RedirectResponse($this->urlGenerator->generate('sylius_admin_order_show', [
                'id' => $shipment->getOrder()->getId(),
            ]));
        }

        return new RedirectResponse($ticket->get10x15TicketUrl());
    }
}
