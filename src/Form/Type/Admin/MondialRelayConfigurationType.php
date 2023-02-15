<?php

namespace Sherlockode\SyliusMondialRelayPlugin\Form\Type\Admin;

use Sylius\Component\Channel\Context\ChannelContextInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MondialRelayConfigurationType extends AbstractType
{
    /**
     * @var ChannelContextInterface
     */
    private $channelContext;

    /**
     * MondialRelayConfigurationType constructor.
     *
     * @param ChannelContextInterface $channelContext
     */
    public function __construct(ChannelContextInterface $channelContext)
    {
        $this->channelContext = $channelContext;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('ranges', CollectionType::class, [
                'label' => 'sylius.form.shipping_calculator.mondial_relay.weight_ranges',
                'allow_add' => true,
                'allow_delete' => true,
                'entry_type' => MondialRelayRangeType::class,
                'entry_options' => ['currency' => $this->channelContext->getChannel()->getBaseCurrency()->getCode()],
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'data_class' => null,
                'limit' => 10,
            ])
            ->setAllowedTypes('limit', 'integer')
        ;
    }
}
