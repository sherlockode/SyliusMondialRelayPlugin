<?php

namespace Sherlockode\SyliusMondialRelayPlugin\MondialRelay\Api\Factory;

use Sherlockode\SyliusMondialRelayPlugin\Model\Point;

class PointFactory
{
    /**
     * @param object $raw
     *
     * @return Point
     */
    public function create(object $raw): Point
    {
        $point = new Point();
        $point->setId($raw->Num ?? null);
        $point->setName($raw->LgAdr1 ?? null);
        $point->setNameComplement($raw->LgAdr2 ?? null);
        $point->setStreet($raw->LgAdr3 ?? null);
        $point->setStreetComplement($raw->LgAdr4 ?? null);
        $point->setZipCode($raw->CP ?? null);
        $point->setCity($raw->Ville ?? null);
        $point->setCountry($raw->Pays ?? null);
        $point->setActivityType($raw->TypeActivite ?? null);
        $point->setDistance($raw->Distance ?? null);
        $point->setLocalisation($raw->Localisation1 ?? null);
        $point->setLocalisationComplement($raw->Localisation2 ?? null);
        $point->setPlanUrl($raw->URL_Plan ?? null);
        $point->setPictureUrl($raw->URL_Photo ?? null);
        $point->setOpeningHours($this->getOpeningHours($raw));

        if (isset($raw->Latitude)) {
            $point->setLatitude((float)str_replace(',', '.', $raw->Latitude));
        }

        if (isset($raw->Longitude)) {
            $point->setLongitude((float)str_replace(',', '.', $raw->Longitude));
        }

        return $point;
    }

    /**
     * @param object $raw
     *
     * @return array
     */
    private function getOpeningHours(object $raw): array
    {
        $factory = new OpeningTimeSlotFactory();
        $openingHours = [];
        $days = [
            'Lundi',
            'Mardi',
            'Mercredi',
            'Jeudi',
            'Vendredi',
            'Samedi',
            'Dimanche'
        ];

        foreach ($days as $index => $day) {
            $property = sprintf('Horaires_%s', $day);

            if (!isset($raw->$property->string) || !is_array($raw->$property->string)) {
                continue;
            }

            $times = $raw->$property->string;

            if ('0000' !== ($times[0] ?? '0000') && '0000' !== ($times[1] ?? '0000')) {
                $openingHours[] = $factory->create($index + 1, $times[0], $times[1]);
            }

            if ('0000' !== ($times[2] ?? '0000') && '0000' !== ($times[3] ?? '0000')) {
                $openingHours[] = $factory->create($index + 1, $times[2], $times[3]);
            }
        }

        return $openingHours;
    }
}
