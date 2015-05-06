<?php

namespace Yit\GeoBridgeBundle\Form\Type;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManager;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Yit\GeoBridgeBundle\Form\DataTransformer\AddressToObjectTransformer;
use Yit\GeoBridgeBundle\Services\YitGeo;

class AddressCreateType extends AbstractType
{
	/**
	 * The EntityManager is the central access point to ORM functionality.
	 */
	protected $entityManager;

	/**
	 * @var Container
	 */
	protected $container;

	/**
	 * @param EntityManager $entityManager
	 */
	public function __construct(ObjectManager $objectManager = null, Container $container = null)
	{
		$this->entityManager = $objectManager;
		$this->container = $container;
	}

	/**
	 * @param FormBuilderInterface $builder
	 * @param array $options
	 */
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$container = $this->container;
//		$builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) use ($container) {
//			// get form data
//			$data = $event->getData();
//			$id = $data['addressId'];
//			// call yit geo service return address object
//			$address = $container->get('yit_geo')->getAddressObjectById($id);
//			// set address object in form
//			$addresses = array('addressId'=>$address->getAddressId(),
//								'armName'=>$address->getArmName(),
//								'engName'=>$address->getEngName(),
//								'latitude'=>$address->getLatitude(),
//								'longitude'=>$address->getLongitude(),
//								'created'=>$address->getCreated(),
//								'updated'=>$address->getUpdated());
////			$event->getForm()->get('addressId')->setData($address->getAddressId());
//			$event->getForm()->get('armName')->setData($address->getArmName());
//			$event->getForm()->get('engName')->setData($address->getEngName());
//			$event->getForm()->get('latitude')->setData($address->getLatitude());
////			var_dump($event->getData());
////			$event->setData($addresses);
//		});
		$entityManager = $this->entityManager;

		$transformer = new AddressToObjectTransformer($entityManager, $container);

		// add a normal text field, but add your transformer to it
		$builder->add(
			$builder->create('addressId', 'geo_address',  array(
				'required' => false))->addModelTransformer($transformer)

//				->add('addressId', 'hidden', array(
//						'required' => false))
//				->add('armName', 'hidden', array(
//						'required' => false))
//				->add('armName', 'hidden', array(
//						'required' => false))
//				->add('engName', 'hidden', array(
//						'required' => false))
//				->add('latitude', 'hidden', array(
//						'required' => false))
//				->add('longitude', 'hidden', array(
//						'required' => false))
//				->add('created', 'hidden', array(
//						'required' => false))
//				->add('updated', 'hidden', array(
//						'required' => false)
				);
	}

	/**
	 * @param OptionsResolverInterface $resolver
	 */
	public function setDefaultOptions(OptionsResolverInterface $resolver)
{
	$resolver->setDefaults(array('data_class' => 'Yit\GeoBridgeBundle\Entity\Address'));
}

	/**
	 * @return string
	 */
	public function getName()
{
	return 'geo_address_create';
}
}
