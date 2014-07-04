<?php
/**
 * Created by PhpStorm.
 * User: andranik
 * Date: 7/3/14
 * Time: 8:26 PM
 */

namespace Yit\GeoBridgeBundle\Listener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Yit\GeoBridgeBundle\Model\Addressable;
use Yit\GeoBridgeBundle\Model\Districtable;
use Yit\GeoBridgeBundle\Model\Streetable;
use Symfony\Component\DependencyInjection\Container;

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


        if ($entity instanceof Addressable)
        {
            $address = $this->container->get('geo_bridge')->getAddressById($entity->getAddressId());

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

        if ($entity instanceof Districtable)
        {
            $district = $this->container->get('geo_bridge')->getDistrictById($entity->getDistrictId());

            if ($district)
            {
                if (isset($district->title)) {
                    $entity->setDistrictTitle($district->title);
                }
            }
        }

        if ($entity instanceof Streetable)
        {
            $street = $this->container->get('geo_bridge')->getStreetById($entity->getStreetId());

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
