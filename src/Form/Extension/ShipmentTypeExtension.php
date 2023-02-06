<?php

namespace Sherlockode\SyliusMondialRelayPlugin\Form\Extension;

use Doctrine\ORM\EntityManagerInterface;
use Sherlockode\SyliusMondialRelayPlugin\Calculator\MondialRelayCalculator;
use Sylius\Bundle\CoreBundle\Form\Type\Checkout\ShipmentType as BaseShipmentType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class ShipmentTypeExtension extends AbstractTypeExtension
{
    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var string
     */
    private $shippingMethodClass;

    /**
     * @param EntityManagerInterface $em
     * @param string                 $shippingMethodClass
     */
    public function __construct(EntityManagerInterface $em, string $shippingMethodClass)
    {
        $this->em = $em;
        $this->shippingMethodClass = $shippingMethodClass;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $baseOptions = [
            'required' => false,
            'attr' => ['class' => 'smr-pickup-point-id'],
        ];
        $builder->add('pickupPointId', HiddenType::class, $baseOptions);

        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) use ($baseOptions) {
            $shipment = $event->getData();
            $method = $this->em->getRepository($this->shippingMethodClass)->findOneBy(['code' => $shipment['method']]);

            if (!$method || MondialRelayCalculator::TYPE_MONDIAL_RELAY !== $method->getCalculator()) {
                return;
            }

            $event->getForm()->add('pickupPointId', HiddenType::class, array_merge($baseOptions, [
                'constraints' => [new Callback([
                    'callback' => function ($pickupPointId, ExecutionContextInterface $context) {
                        if (!$pickupPointId) {
                            $context->addViolation('sylius.form.shipping_method.no_pickup_point_selected');
                        }
                    },
                    'groups' => ['sylius']
                ])],
            ]));
        });
    }

    /**
     * @return iterable
     */
    public static function getExtendedTypes(): iterable
    {
        return [BaseShipmentType::class];
    }
}
