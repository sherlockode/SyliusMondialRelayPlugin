<?php

namespace Sherlockode\SyliusMondialRelayPlugin\Form\Type\Admin;

use Sylius\Bundle\CoreBundle\Form\Type\ChannelCollectionType;
use Sylius\Component\Core\Model\ChannelInterface;
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
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'entry_type' => MondialRelayRangesType::class,
                'entry_options' => fn (ChannelInterface $channel): array => [
                    'currency' => $channel->getBaseCurrency()->getCode(),
                    'label' => $channel->getName(),
                ],
                'currency' => $this->channelContext->getChannel()->getBaseCurrency()->getCode(),
                'limit' => 10
            ])

            ->setAllowedTypes('limit', 'integer')
            ->setAllowedTypes('currency', ['null', 'string']);
    }

    /**
     * {@inheritdoc}
     */
    public function getParent(): string
    {
        return ChannelCollectionType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix(): string
    {
        return 'sylius_shipping_calculator_mondial_relay_collection';
    }
}
