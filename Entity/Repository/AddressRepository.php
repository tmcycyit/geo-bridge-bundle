<?php
/**
 * Created by PhpStorm.
 * User: tigran
 * Date: 3/31/15
 * Time: 4:37 PM
 */
namespace Yit\GeoBridgeBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;

class AddressRepository extends EntityRepository
{
	/**
	 * This function find last updated date time
	 *
	 * @return mixed
	 * @throws \Doctrine\ORM\NoResultException
	 * @throws \Doctrine\ORM\NonUniqueResultException
	 */
	public function getLastUpdate()
	{
		$result =  $this->getEntityManager()
			->createQuery("
				SELECT MAX(a.updated)
				FROM YitGeoBridgeBundle:Address a")
			->getSingleResult();

		return max($result);

	}
}