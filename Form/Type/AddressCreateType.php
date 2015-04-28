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

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

//		$lat = $this->configurationPool->getContainer()->getParameter('latitude');
//		//get coordinate for longitude
//		$lng = $this->configurationPool->getContainer()->getParameter('longitude');
		$dateTime = $this->entityManager->getRepository('YitGeoBridgeBundle:Address')->getLastUpdate();
//		var_dump($dateTime); exit;

        $builder->add('armName', 'text')
				->add('engName', 'text', array('required' => false))
				->add('latitude', 'hidden', array('required' => false))
				->add('longitude', 'hidden', array('required' => false))
				->add('created', 'hidden', array('mapped' => false,'data'=> (new \DateTime($dateTime))))
				->add('updated', 'hidden', array('mapped' => false,'data'=> (new \DateTime($dateTime))))
				->add('inmap', 'mapmarker', array('attr' =>
						array('draggable' => true,
							'limit' => 1,
							'zoom' => 12) ));
    }

//    /**
//     * @param OptionsResolverInterface $resolver
//     */
//    public function setDefaultOptions(OptionsResolverInterface $resolver)
//    {
//        $resolver->setDefaults(array(
//            'data_class' => 'Yit\GeoBridgeBundle\Form\Type\AddressCreateType'
//        ));
//    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'geo_address_create';
    }
}
