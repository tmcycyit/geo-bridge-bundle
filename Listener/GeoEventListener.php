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

    }
}
