<?php
/**
 * Created by PhpStorm.
 * User: andranik
 * Date: 7/3/14
 * Time: 5:26 PM
 */

namespace Yit\GeoBridgeBundle\Model;

interface DistrictableInterface
{
    public function getDistrictId();

    public function setDistrictTitle($title);
}