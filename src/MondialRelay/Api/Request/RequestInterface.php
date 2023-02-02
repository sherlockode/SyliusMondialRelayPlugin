<?php

namespace Sherlockode\SyliusMondialRelayPlugin\MondialRelay\Api\Request;

interface RequestInterface
{
    public function sign(): void;

    public function getPayload(): array;
}
