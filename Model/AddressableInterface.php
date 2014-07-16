<?php
/**
 * Created by PhpStorm.
 * User: andranik
 * Date: 7/3/14
 * Time: 5:12 PM
 */

namespace Yit\GeoBridgeBundle\Model;

interface AddressableInterface
{
    public function getAddressId();

    public function setAddressTitle($title);

    public function setAddresLatitude($latitude);

    public function setAddresLongitude($longitude);

    public function setAddressHNumber($hNumber);

    public function setAddressEngType($engType);
}