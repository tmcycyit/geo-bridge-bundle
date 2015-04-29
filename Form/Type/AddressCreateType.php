<?php

namespace Yit\GeoBridgeBundle\Form\Type;

use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class AddressCreateType extends AbstractType
{
	/**
	 * The EntityManager is the central access point to ORM functionality.
	 */
	protected $entityManager;

	/**
	 * @param EntityManager $entityManager
	 */
	public function __construct(EntityManager $entityManager = null)
	{
		$this->entityManager = $entityManager;
	}

	/**
	 * @param FormBuilderInterface $builder
	 * @param array $options
	 */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

		$dateTime = $this->entityManager->getRepository('YitGeoBridgeBundle:Address')->getLastUpdate();

        $builder->add('addressId', 'hidden')
				->add('armName', 'text')
				->add('search', 'button', array('label'=>'Search'))
				->add('engName', 'hidden', array('required' => false))
				->add('latitude', 'hidden', array('required' => false))
				->add('longitude', 'hidden', array('required' => false))
				->add('created', 'hidden', array('mapped' => false,'data'=> $dateTime))
				->add('updated', 'hidden', array('mapped' => false,'data'=> $dateTime))
				->add('inmap', 'mapmarker', array('attr' =>
						array('draggable' => true,
							'limit' => 1,
							'zoom' => 12) ))
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
