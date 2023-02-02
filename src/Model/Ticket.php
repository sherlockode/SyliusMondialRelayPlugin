<?php

namespace Sherlockode\SyliusMondialRelayPlugin\Model;

class Ticket
{
    /**
     * @var string
     */
    private $shippingNumber;

    /**
     * @var string
     */
    private $baseUrl;

    /**
     * @var string
     */
    private $path;

    /**
     * @var array
     */
    private $queryString;

    public function __construct()
    {
        $this->queryString = [];
    }

    /**
     * @return string|null
     */
    public function getShippingNumber(): ?string
    {
        return $this->shippingNumber;
    }

    /**
     * @param string|null $shippingNumber
     *
     * @return $this
     */
    public function setShippingNumber(?string $shippingNumber): self
    {
        $this->shippingNumber = $shippingNumber;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getBaseUrl(): ?string
    {
        return $this->baseUrl;
    }

    /**
     * @param string|null $baseUrl
     *
     * @return $this
     */
    public function setBaseUrl(?string $baseUrl): self
    {
        $this->baseUrl = $baseUrl;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getPath(): ?string
    {
        return $this->path;
    }

    /**
     * @param string|null $path
     *
     * @return $this
     */
    public function setPath(?string $path): self
    {
        $this->path = $path;

        return $this;
    }

    /**
     * @return array
     */
    public function getQueryString(): array
    {
        return $this->queryString;
    }

    /**
     * @param array $queryString
     *
     * @return $this
     */
    public function setQueryString(array $queryString): self
    {
        $this->queryString = $queryString;

        return $this;
    }

    /**
     * @return string
     */
    public function getA4TicketUrl(): string
    {
        return $this->generateTicketUrl('A4');
    }

    /**
     * @return string
     */
    public function getA5TicketUrl(): string
    {
        return $this->generateTicketUrl('A5');
    }

    /**
     * @return string
     */
    public function get10x15TicketUrl(): string
    {
        return $this->generateTicketUrl('10x15');
    }

    /**
     * @param string $format
     *
     * @return string
     */
    private function generateTicketUrl(string $format): string
    {
        return sprintf(
            '%s/%s?%s',
            $this->getBaseUrl(),
            $this->getPath(),
            http_build_query(array_merge($this->getQueryString(), ['format' => $format]))
        );
    }
}
