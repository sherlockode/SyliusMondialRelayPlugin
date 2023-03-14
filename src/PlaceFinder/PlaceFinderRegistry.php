<?php

namespace Sherlockode\SyliusMondialRelayPlugin\PlaceFinder;

class PlaceFinderRegistry
{
    /**
     * @var PlaceFinderInterface[]
     */
    private $finders;

    /**
     * @param iterable $finders
     */
    public function __construct(iterable $finders)
    {
        $this->finders = $finders;
    }

    /**
     * @param string|null $type
     *
     * @return PlaceFinderInterface|null
     */
    public function getFinder(?string $type): ?PlaceFinderInterface
    {
        foreach ($this->finders as $finder) {
            if ($finder->supports($type)) {
                return $finder;
            }
        }

        return null;
    }
}
