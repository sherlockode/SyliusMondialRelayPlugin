<?php

namespace Sherlockode\SyliusMondialRelayPlugin\MondialRelay\Api\Factory;

use Sherlockode\SyliusMondialRelayPlugin\Model\Ticket;

class TicketFactory
{
    /**
     * @var string
     */
    private $baseUrl;

    /**
     * @param string $baseUrl
     */
    public function __construct(string $baseUrl)
    {
        $this->baseUrl = $baseUrl;
    }

    /**
     * @param object $raw
     *
     * @return Ticket
     */
    public function create(object $raw): Ticket
    {
        $parsed = parse_url($raw->URL_Etiquette);
        parse_str($parsed['query'] ?? '', $queryString);

        $ticket = new Ticket();
        $ticket->setShippingNumber($raw->ExpeditionNum);
        $ticket->setBaseUrl($this->baseUrl);
        $ticket->setPath($parsed['path'] ?? null);
        $ticket->setQueryString($queryString);

        return $ticket;
    }
}
