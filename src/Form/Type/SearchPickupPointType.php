<?php

namespace Sherlockode\SyliusMondialRelayPlugin\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class SearchPickupPointType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('zipCode', TextType::class, [
                'label' => 'sylius.mondial_relay.zipcode',
                'required' => false,
                'attr' => [
                    'placeholder' => 'sylius.mondial_relay.search_zipcode',
                ],
            ])
            ->add('types', ChoiceType::class, [
                'label' => false,
                'expanded' => true,
                'choices' => [
                    'sylius.mondial_relay.pickup_point_types.24r' => '24R',
                    'sylius.mondial_relay.pickup_point_types.drive' => 'DRI',
                ],
            ])
        ;
    }
}
