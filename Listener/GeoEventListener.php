<?php
/**
 * Created by PhpStorm.
 * User: andranik
 * Date: 7/3/14
 * Time: 8:26 PM
 */

namespace Yit\GeoBridgeBundle\Listener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Yit\GeoBridgeBundle\Model\AddressDistrictableInterface;
use Yit\GeoBridgeBundle\Model\AddressDistrictAwareInterface;
use Yit\GeoBridgeBundle\Model\AddressStreetableInterface;
use Yit\GeoBridgeBundle\Model\AddressStreetAwareInterface;
use Yit\GeoBridgeBundle\Model\DistrictableInterface;
use Yit\GeoBridgeBundle\Model\StreetableInterface;
use Symfony\Component\DependencyInjection\Container;

/**
 * Class GeoEventListener
 * @package Yit\GeoBridgeBundle\Listener
 */
class GeoEventListener
{
    private $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function postLoad(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();

        //=========================================================================
        //============================ Districts ==================================
        //=========================================================================

        if ($entity instanceof AddressDistrictableInterface)
        {
            if ($entity->getAddressId())
            {
                $isNull = is_null($entity->getDistrictId());

                $address = $this->container->get('yit_geo')->getAddressById($entity->getAddressId());
                if (isset($address->street_district) && isset($address->street_district->district) &&
                        $address->street_district->district->id != $entity->getDistrictId())
                {
                    //If $entity has not district_id or if it has but it implements AddressDistrictableInterfaceToShow
                    //interface add district_id to it
                    if (!$entity->getDistrictId() || $entity instanceof AddressDistrictAwareInterface) {
                        $entity->setDistrictId($address->street_district->district->id);
                    }

                    if ($isNull) {
                        $em = $this->container->get('doctrine')->getManager();
                        $query = $em->createQuery("UPDATE ". get_class($entity). " a  SET a."
                                    . $entity->getDistrictFieldName() ." = "
                                    . $address->street_district->district->id. " WHERE a.id =  " . $entity->getId());
                        $query->execute();
                    }
                }
            }
        }

        // inject single district
        if ($entity instanceof DistrictableInterface)
        {
            $district = $this->container->get('yit_geo')->getDistrictById($entity->getDistrictId());

            if ($district)
            {
                if (isset($district->title)) {
                    $entity->setDistrictTitle($district->title);
                }
            }
        }

        //=========================================================================
        //============================== Streets ==================================
        //=========================================================================

        if ($entity instanceof AddressStreetableInterface)
        {
            if ($entity->getAddressId())
            {
                $isNull = is_null($entity->getStreetId());

                $address = $this->container->get('yit_geo')->getAddressById($entity->getAddressId());

                if (isset($address->street_district) && isset($address->street_district->street) &&
                        $address->street_district->street->id != $entity->getStreetId())
                {
                    //If $entity has not street_id or if it has but it implements AddressStreetableInterfaceToShow
                    //interface add street_id to it
                    if (!$entity->getStreetId() || $entity instanceof AddressStreetAwareInterface) {
                        $entity->setStreetId($address->street_district->street->id);
                        $isNull = true;
                    }

                    if ($isNull) {
                        $em = $this->container->get('doctrine')->getManager();
                        $query = $em->createQuery("UPDATE ". get_class($entity). " a  SET a."
                                    . $entity->getStreetFieldName() ." = "
                                    . $address->street_district->street->id. " WHERE a.id =  " . $entity->getId());
                        $query->execute();
                    }
                }
            }
        }

        // inject single street
        if ($entity instanceof StreetableInterface)
        {
            $street = $this->container->get('yit_geo')->getStreetById($entity->getStreetId());

            if ($street)
            {
                if (isset($street->arm_name)) {
                    $entity->setStreetArmName($street->arm_name);
                }
                if (isset($street->eng_name)) {
                    $entity->setStreetEngName($street->eng_name);
                }
            }
        }
    }
}
