<?php

namespace Sherlockode\SyliusMondialRelayPlugin\Form\Type;

use Sherlockode\SyliusMondialRelayPlugin\Calculator\MondialRelayCalculator;
use Sylius\Bundle\CoreBundle\Form\Type\Checkout\ShipmentType as BaseShipmentType;
use Sylius\Bundle\ShippingBundle\Form\Type\ShippingMethodChoiceType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class ShipmentType extends AbstractTypeExtension
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addEventListener(FormEvents::POST_SET_DATA, function (FormEvent $event) {
            $form = $event->getForm();
            $shipment = $event->getData();

            if ($form->has('method')) {
                $form->remove('method');
            }

            $form->add('method', ShippingMethodChoiceType::class, [
                'required' => true,
                'label' => 'sylius.form.checkout.shipping_method',
                'subject' => $shipment,
                'expanded' => true,
                'choice_attr' => function($choice) {
                    $attr = [];

                    if (MondialRelayCalculator::TYPE_MONDIAL_RELAY === $choice->getCalculator()) {
                        $attr['data-mr'] = 'true';
                    }

                    return $attr;
                },
            ]);

            $form->add('pickupPointId', HiddenType::class, [
                'required' => false,
                'attr' => ['class' => 'smr-pickup-point-id'],
            ]);
        });
    }

    public static function getExtendedTypes(): iterable
    {
        return [BaseShipmentType::class];
    }
}
