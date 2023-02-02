<?php

namespace Sherlockode\SyliusMondialRelayPlugin\MondialRelay\Api\Request;

class GenericRequest implements RequestInterface
{
    /**
     * @var string
     */
    private $merchantId;

    /**
     * @var string
     */
    private $secret;

    /**
     * @var array
     */
    private $payload;

    /**
     * @param string $merchantId
     * @param string $secret
     * @param array  $payload
     */
    public function __construct(string $merchantId, string $secret, array $payload = [])
    {
        $this->merchantId = $merchantId;
        $this->secret = $secret;
        $this->payload = $payload;
    }

    public function sign(): void
    {
        $securityKey = [$this->merchantId];

        foreach ($this->payload as $value) {
            $securityKey[] = $value;
        }

        $securityKey[] = $this->secret;
        $this->payload['Enseigne'] = $this->merchantId;
        $this->payload['Security'] = strtoupper(md5(implode('', $securityKey)));
    }

    /**
     * @return array
     */
    public function getPayload(): array
    {
        return $this->payload;
    }
}
