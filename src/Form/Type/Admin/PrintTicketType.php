<?php

namespace Sherlockode\SyliusMondialRelayPlugin\Form\Type\Admin;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;
use Symfony\Component\Validator\Constraints\NotBlank;

class PrintTicketType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('collectionMode', ChoiceType::class, [
                'label' => 'sylius.mondial_relay.collection_mode',
                'constraints' => [new NotBlank()],
                'placeholder' => 'sylius.mondial_relay.please_select_a_value',
                'choices' => [
                    'sylius.mondial_relay.collection_modes.rel' => 'REL',
                    'sylius.mondial_relay.collection_modes.ccc' => 'CCC',
                    'sylius.mondial_relay.collection_modes.cdr' => 'CDR',
                    'sylius.mondial_relay.collection_modes.cds' => 'CDS',
                ],
            ])
            ->add('deliveryMode', ChoiceType::class, [
                'label' => 'sylius.mondial_relay.delivery_mode',
                'constraints' => [new NotBlank()],
                'placeholder' => 'sylius.mondial_relay.please_select_a_value',
                'choices' => [
                    'sylius.mondial_relay.delivery_modes.24r' => '24R',
                    'sylius.mondial_relay.delivery_modes.24l' => '24L',
                    'sylius.mondial_relay.delivery_modes.hom' => 'HOM',
                    'sylius.mondial_relay.delivery_modes.ld1' => 'LD1',
                    'sylius.mondial_relay.delivery_modes.lds' => 'LDS',
                    'sylius.mondial_relay.delivery_modes.lcc' => 'LCC',
                ],
            ])
            ->add('parcelCount', IntegerType::class, [
                'label' => 'sylius.mondial_relay.parcel_count',
                'constraints' => [new NotBlank(), new GreaterThanOrEqual(['value' => 1])],
            ])
            ->add('weight', IntegerType::class, [
                'label' => 'sylius.mondial_relay.parcel_weight',
                'constraints' => [new NotBlank(), new GreaterThanOrEqual(['value' => 15])],
            ])
            ->add('size', IntegerType::class, [
                'label' => 'sylius.mondial_relay.parcel_size',
                'required' => false,
            ])
        ;
    }
}
