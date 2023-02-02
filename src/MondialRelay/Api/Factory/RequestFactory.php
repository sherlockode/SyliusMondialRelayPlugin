<?php

namespace Sherlockode\SyliusMondialRelayPlugin\MondialRelay\Api\Factory;

class RequestFactory
{
    /**
     * @param string $merchantId
     * @param string $secret
     * @param array  $input
     *
     * @return array
     */
    public function create(string $merchantId, string $secret, array $input): array
    {
        $securityKey = [$merchantId];

        foreach ($input as $value) {
            $securityKey[] = $value;
        }

        $securityKey[] = $secret;

        return array_merge(
            $input,
            [
                'Enseigne' => $merchantId,
                'Security' => strtoupper(md5(implode('', $securityKey))),
            ]
        );

        $request['Enseigne'] = $merchantId;
        $request['Security'] = strtoupper(md5(implode('', $securityKey)));

        return $request;
    }
}
