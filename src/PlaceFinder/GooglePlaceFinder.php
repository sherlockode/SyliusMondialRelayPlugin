<?php

namespace Sherlockode\SyliusMondialRelayPlugin\PlaceFinder;

use Sherlockode\SyliusMondialRelayPlugin\Manager\MapProviderManager;

class GooglePlaceFinder implements PlaceFinderInterface
{
    /**
     * @var string|null
     */
    private $apiKey;

    /**
     * @param string|null $apiKey
     */
    public function __construct(?string $apiKey)
    {
        $this->apiKey = $apiKey;
    }

    /**
     * @param string|null $type
     *
     * @return bool
     */
    public function supports(?string $type): bool
    {
        return MapProviderManager::MAP_PROVIDER_GOOGLE === $type;
    }

    /**
     * @param string $query
     *
     * @return array
     */
    public function search(string $query): array
    {
        $url = 'https://maps.googleapis.com/maps/api/place/autocomplete/json?' . http_build_query([
            'input' => $query,
            'key' => $this->apiKey,
            'types' => 'administrative_area_level_2|locality|postal_code',
            'components' => 'country:fr',
        ]);
        $response = $this->get($url);

        return array_values(array_map(function ($item) {
            return [
                'id' => $item['place_id'],
                'label' => $item['description'],
            ];
        }, $response['predictions'] ?? []));
    }

    /**
     * @param string $id
     *
     * @return string|null
     */
    public function find(string $id): ?string
    {
        $url = 'https://maps.googleapis.com/maps/api/place/details/json?' . http_build_query([
            'place_id' => $id,
            'key' => $this->apiKey,
        ]);
        $response = $this->get($url);

        foreach ($response['result']['address_components'] ?? [] as $component) {
            if (in_array('postal_code', $component['types'])) {
                return $component['long_name'];
            }
        }

        return null;
    }

    /**
     * @param string $url
     *
     * @return array
     */
    private function get(string $url): array
    {
        $ch = curl_init();

        try {
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_HEADER, false);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
            curl_setopt($ch, CURLOPT_USERAGENT, 'Curl');

            $raw = curl_exec($ch);
            $response = json_decode($raw, true);
        } catch (\Exception $exception) {
            $response = [];
        } finally {
            curl_close($ch);
        }

        return $response;
    }
}
