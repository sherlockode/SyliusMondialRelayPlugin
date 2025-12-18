<?php

namespace Sherlockode\SyliusMondialRelayPlugin\Form\Type\Admin;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\UX\LiveComponent\Form\Type\LiveCollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MondialRelayRangesType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('ranges', LiveCollectionType::class, [
            'entry_type' => MondialRelayRangeType::class,
            'entry_options' => [
                'currency' => $options['currency'],
            ],
            'label' => 'sylius.form.shipping_calculator.mondial_relay.weight_ranges',
            'allow_add' => true,
            'allow_delete' => true,
            'by_reference' => false,
            'prototype' => true,
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired('currency');
    }
}