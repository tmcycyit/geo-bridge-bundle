<?php
/**
 * Created by PhpStorm.
 * User: andranik
 * Date: 7/3/14
 * Time: 5:18 PM
 */

namespace Yit\GeoBridgeBundle\Model;

interface Streetable
{
    public function getStreetId();

    public function setStreetArmName();

    public function setStreetEngName();
}