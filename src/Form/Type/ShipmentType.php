<?php

namespace Sherlockode\SyliusMondialRelayPlugin\Form\Type;

use Doctrine\ORM\EntityManagerInterface;
use Sherlockode\SyliusMondialRelayPlugin\Calculator\MondialRelayCalculator;
use Sylius\Bundle\CoreBundle\Form\Type\Checkout\ShipmentType as BaseShipmentType;
use Sylius\Bundle\ShippingBundle\Form\Type\ShippingMethodChoiceType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Contracts\Translation\TranslatorInterface;

class ShipmentType extends AbstractTypeExtension
{
    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var string
     */
    private $shippingMethodClass;

    /**
     * @param EntityManagerInterface $em
     * @param TranslatorInterface    $translator
     * @param string                 $shippingMethodClass
     */
    public function __construct(EntityManagerInterface $em, TranslatorInterface $translator, string $shippingMethodClass)
    {
        $this->em = $em;
        $this->translator = $translator;
        $this->shippingMethodClass = $shippingMethodClass;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $dataClass = $options['data_class'] ?? null;
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

        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) use ($dataClass) {
            $data = $event->getData();

            if (!array_key_exists('pickupPointId', $data) || !empty($data['pickupPointId'])) {
                return;
            }

            $method = $this->em->getRepository($this->shippingMethodClass)->findOneBy(['code' => $data['method']]);
            if (!$method) {
                return;
            }

            if (MondialRelayCalculator::TYPE_MONDIAL_RELAY === $method->getCalculator()) {
                $form = $event->getForm();
                $form->addError(new FormError(
                    $this->translator->trans('sylius.form.shipping_method.no_pickup_point_selected', [], 'messages')
                ));
            }
        });
    }

    public static function getExtendedTypes(): iterable
    {
        return [BaseShipmentType::class];
    }
}
