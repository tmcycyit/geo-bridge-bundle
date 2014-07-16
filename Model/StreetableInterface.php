<?php
/**
 * Created by PhpStorm.
 * User: andranik
 * Date: 7/3/14
 * Time: 5:18 PM
 */

namespace Yit\GeoBridgeBundle\Model;

interface StreetableInterface
{
    public function getStreetId();

    public function setStreetArmName($armName);

    public function setStreetEngName($engName);
}