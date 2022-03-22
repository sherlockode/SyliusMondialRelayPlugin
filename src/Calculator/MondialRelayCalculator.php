<?php

namespace Sherlockode\SyliusMondialRelayPlugin\Calculator;

use Sylius\Component\Shipping\Calculator\CalculatorInterface;
use Sylius\Component\Shipping\Model\ShipmentInterface;

/**
 * Class MondialRelayCalculator
 */
class MondialRelayCalculator implements CalculatorInterface
{
    public const TYPE_MONDIAL_RELAY = 'mondial_relay';

    /**
     * @param ShipmentInterface $subject
     * @param array             $configuration
     *
     * @return int
     */
    public function calculate(ShipmentInterface $subject, array $configuration): int
    {
        if (
            !isset($configuration['ranges']) ||
            !is_iterable($configuration['ranges']) ||
            !count($configuration['ranges'])
        ) {
            return 0;
        }

        $weight = $subject->getShippingWeight();

        foreach ($configuration['ranges'] as $range) {
            if (null !== $range['minWeight'] && null !== $range['maxWeight']) {
                if ($weight >= $range['minWeight'] && $weight <= $range['maxWeight']) {
                    return $range['amount'];
                }
            } elseif (null !== $range['minWeight']) {
                if ($weight >= $range['minWeight']) {
                    return $range['amount'];
                }
            } elseif (null !== $range['maxWeight']) {
                if ($weight <= $range['maxWeight']) {
                    return $range['amount'];
                }
            }
        }

        return 0;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return self::TYPE_MONDIAL_RELAY;
    }
}
