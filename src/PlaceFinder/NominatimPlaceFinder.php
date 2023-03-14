<?php

namespace Sherlockode\SyliusMondialRelayPlugin\PlaceFinder;

use Sherlockode\SyliusMondialRelayPlugin\Manager\MapProviderManager;

class NominatimPlaceFinder implements PlaceFinderInterface
{
    /**
     * @param string|null $type
     *
     * @return bool
     */
    public function supports(?string $type): bool
    {
        return MapProviderManager::MAP_PROVIDER_OSM === $type;
    }

    /**
     * @param string $query
     *
     * @return array
     */
    public function search(string $query): array
    {
        $places = $this->get(sprintf(
            'https://nominatim.openstreetmap.org/search?q=%s&format=jsonv2&countrycodes=FR',
            $query
        ));

        return array_values(array_map(function ($place) {
            return [
                'id' => $place['place_id'],
                'label' => $place['display_name'],
            ];
        }, $places));
    }

    /**
     * @param string $id
     *
     * @return string|null
     */
    public function find(string $id): ?string
    {
        $place = $this->get(sprintf('https://nominatim.openstreetmap.org/details?place_id=%s&format=json', $id));

        return $place['addresstags']['postcode'] ?? null;
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
