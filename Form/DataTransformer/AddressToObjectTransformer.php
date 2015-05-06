<?php
/**
 * Created by PhpStorm.
 * User: tigran
 * Date: 5/5/15
 * Time: 4:33 PM
 */

namespace Yit\GeoBridgeBundle\Form\DataTransformer;


use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\PersistentCollection;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Doctrine\Common\Persistence\ObjectManager;

 class AddressToObjectTransformer implements DataTransformerInterface
{
	 /**
	  * @var Container
	  */
	 private $container;

	 /**
	  * get Symfony Dependency Injection Container
	  *
	  * @param Container $container
	  */
	public function __construct(Container $container = null)
	{
		$this->container = $container;
	}

	 /**
	  *
	  * this function use end show data from field
	  * @param mixed $address
	  * @return string
	  */
	 public function transform($address)
	 {
		 if (null === $address) {
			 return "$address not found";
		 }

		 return $address->getAddressId();
	 }

	 /**
	  * Transforms a string (number) to an object (issue).
	  *
	  * @param mixed $addressId
	  *
	  * @return null
	  */
	 public function reverseTransform($addressId)
	 {
		 $container = $this->container;

		 if (!$addressId) {
			 return null;
		 }

		 $address = $container->get('yit_geo')->getAddressObjectById($addressId);

		 if (null === $address) {
			 throw new TransformationFailedException(sprintf(
				 'An issue with number "%s" does not exist!',
				 $addressId
			 ));
		 }
		 return $address;
	 }
 }