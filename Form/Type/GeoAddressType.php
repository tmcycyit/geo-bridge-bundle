<?php

namespace Yit\GeoBridgeBundle\Form\Type;

use Symfony\Component\Form\AbstractType;

class GeoAddressType extends AbstractType
{
    public function getParent()
    {
        return 'text';
    }

    public function getName()
    {
        return 'geo_address';
    }
}