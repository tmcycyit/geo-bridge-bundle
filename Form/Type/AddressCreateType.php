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
	public function __construct(EntityManager $entityManager)
	{
		$this->em = $entityManager;
	}

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

		$dateTime = $this->em->getRepository('YitGeoBridgeBundle:Address')->getLastUpdate();

        $builder->add('armName', 'text')
				->add('engName', 'text', array('required' => false))
				->add('latitude', 'hidden', array('required' => false))
				->add('longitude', 'hidden', array('required' => false))
				->add('created', 'hidden', array('mapped' => false,'data'=> $dateTime ))
				->add('updated', 'hidden', array('mapped' => false,'data'=> $dateTime ));
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Yit\GeoBridgeBundle\Form\Type\AddressCreateType'
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
