<?php
/**
 * Created by PhpStorm.
 * User: andranik
 * Date: 7/3/14
 * Time: 5:26 PM
 */

namespace Yit\GeoBridgeBundle\Model;

/**
 * Interface DistrictableInterface
 *
 * This interface is used when entity has an district_id field
 *
 * @package Yit\GeoBridgeBundle\Model
 */
interface DistrictableInterface
{
    /**
     * This function is used to get district id, the fields of which must be injected
     *
     * @return mixed
     */
    public function getDistrictId();

    /**
     * This function is used to inject district title
     *
     * @param $title
     * @return mixed
     */
    public function setDistrictTitle($title);
}