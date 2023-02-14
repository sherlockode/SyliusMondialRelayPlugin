<?php

namespace Sherlockode\SyliusMondialRelayPlugin\Form\Type\Admin;

use Symfony\Component\Form\AbstractType;
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
