<?php

namespace Sherlockode\SyliusMondialRelayPlugin\Form\Type;

use Sylius\Bundle\MoneyBundle\Form\Type\MoneyType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;
use Symfony\Component\Validator\Constraints\NotBlank;

class MondialRelayRangeType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('minWeight', IntegerType::class, [
                'label' => 'sylius.form.shipping_calculator.mondial_relay.min_weight',
                'required' => false,
                'constraints' => [
                    new GreaterThanOrEqual(['value' => 0, 'groups' => ['sylius']]),
                ]
            ])
            ->add('maxWeight', IntegerType::class, [
                'label' => 'sylius.form.shipping_calculator.mondial_relay.max_weight',
                'required' => false,
                'constraints' => [
                    new GreaterThanOrEqual(['value' => 0, 'groups' => ['sylius']]),
                ],
            ])
            ->add('amount', MoneyType::class, [
                'label' => 'sylius.form.shipping_calculator.mondial_relay.shipping_cost',
                'currency' => $options['currency'],
                'constraints' => [
                    new NotBlank(['groups' => ['sylius']]),
                    new GreaterThanOrEqual(['value' => 0, 'groups' => ['sylius']]),
                ]
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired('currency');
//        $resolver
//            ->setDefaults([
//                'data_class' => null,
//                'limit' => 10,
//            ])
//            ->setAllowedTypes('limit', 'integer')
        ;
    }
}
