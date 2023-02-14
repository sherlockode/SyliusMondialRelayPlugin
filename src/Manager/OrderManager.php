<?php

namespace Sherlockode\SyliusMondialRelayPlugin\Manager;

use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Order\Model\OrderItemInterface;

class OrderManager
{
    /**
     * @param OrderInterface $order
     *
     * @return int
     */
    public function getOrderTotalWeight(OrderInterface $order): int
    {
        $totalWeight = array_reduce($order->getItems()->toArray(), function ($sum, OrderItemInterface $orderItem) {
            if ($orderItem->getVariant()) {
                $sum += $orderItem->getVariant()->getWeight();
            }

            return $sum;
        }, 0);

        return min(15, $totalWeight);
    }
}
