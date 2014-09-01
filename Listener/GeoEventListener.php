<?php
/**
 * Created by PhpStorm.
 * User: andranik
 * Date: 7/3/14
 * Time: 8:26 PM
 */

namespace Yit\GeoBridgeBundle\Listener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Yit\GeoBridgeBundle\Model\AddressableInterface;
use Yit\GeoBridgeBundle\Model\AddressDistrictableInterface;
use Yit\GeoBridgeBundle\Model\DistrictableInterface;
use Yit\GeoBridgeBundle\Model\MultiAddressableInterface;
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

        // inject single address
        if ($entity instanceof AddressableInterface)
        {
            $address = $this->container->get('yit_geo')->getAddressById($entity->getAddressId());

            if ($address)
            {
                if (isset($address->title)) {
                    $entity->setAddressTitle($address->title);
                }
                if (isset($address->latitude)) {
                    $entity->setAddresLatitude($address->latitude);
                }
                if (isset($address->longitude)) {
                    $entity->setAddresLongitude($address->longitude);
                }
                if (isset($address->eng_type)) {
                    $entity->setAddressEngType($address->eng_type);
                }
                if (isset($address->h_number)) {
                    $entity->setAddressHNumber($address->h_number);
                }

            }
        }

        // inject multi address
        if ($entity instanceof MultiAddressableInterface)
        {
            $addresses = array();

            foreach($entity->getAddressIds() as $id){
                if($id){
                    $addresses[$id] = $this->container->get('yit_geo')->getAddressById($id);
                }
            }

            $entity->setAddresses($addresses);
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

        if ($entity instanceof AddressDistrictableInterface)
        {
            if (!$entity->getDistrictId() and $entity->getAddressId())
            {
                $address = $this->container->get('yit_geo')->getAddressById($entity->getAddressId());
            }
        }

    }
}
