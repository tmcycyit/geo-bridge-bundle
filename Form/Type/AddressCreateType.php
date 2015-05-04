<?php

namespace Yit\GeoBridgeBundle\Form\Type;

use Doctrine\ORM\EntityManager;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Yit\GeoBridgeBundle\Services\YitGeo;

class AddressCreateType extends AbstractType
{
	/**
	 * The EntityManager is the central access point to ORM functionality.
	 */
	protected $entityManager;
	protected $container;

	/**
	 * @param EntityManager $entityManager
	 */
	public function __construct(EntityManager $entityManager = null, Container $container = null)
	{
		$this->entityManager = $entityManager;
		$this->container = $container;
	}

	/**
	 * @param FormBuilderInterface $builder
	 * @param array $options
	 */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder->add('addressId', 'geo_address');

		$builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {

				$id = $event->getData()['addressId'];
				$address = $this->container->get('yit_geo')->getAddressObjectById($id);
				$event->setData($address);
				 });
	;
    }

	public function setDefaultOptions(OptionsResolverInterface $resolver)
	{
		$resolver->setDefaults(array(
			'data_class' => 'Yit\GeoBridgeBundle\Entity\Address'
		));
	}

    /**
     * @return string
     */
    public function getName()
    {
        return 'geo_address_create';
    }
}
