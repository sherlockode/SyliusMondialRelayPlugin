<?php

namespace Sherlockode\SyliusMondialRelayPlugin\PlaceFinder;

interface PlaceFinderInterface
{
    /**
     * @param string|null $type
     *
     * @return bool
     */
    public function supports(?string $type): bool;

    /**
     * @param string $query
     *
     * @return array
     */
    public function search(string $query): array;

    /**
     * @param string $id
     *
     * @return string|null
     */
    public function find(string $id): ?string;
}
