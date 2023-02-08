<?php

namespace Sherlockode\SyliusMondialRelayPlugin\Form\Extension;

use Sherlockode\SyliusMondialRelayPlugin\Calculator\MondialRelayCalculator;
use Sylius\Bundle\ShippingBundle\Form\Type\ShippingMethodChoiceType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ShippingMethodChoiceTypeExtension extends AbstractTypeExtension
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'choice_attr' => function($choice) {
                    $attr = [];

                    if (MondialRelayCalculator::TYPE_MONDIAL_RELAY === $choice->getCalculator()) {
                        $attr['data-mr'] = 'true';
                    }

                    return $attr;
                }
            ]);
    }

    public static function getExtendedTypes(): iterable
    {
        return [ShippingMethodChoiceType::class];
    }
}
