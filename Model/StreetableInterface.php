<?php
/**
 * Created by PhpStorm.
 * User: andranik
 * Date: 7/3/14
 * Time: 5:18 PM
 */

namespace Yit\GeoBridgeBundle\Model;

/**
 * Interface StreetableInterface
 *
 * This interface is used when entity has street_id field
 *
 * @package Yit\GeoBridgeBundle\Model
 */
interface StreetableInterface
{
    /**
     * This function is used to get street id, the fields of which must be injected
     *
     * @return mixed
     */
    public function getStreetId();

    /**
     * This function is used to inject street arm_name
     *
     * @param $armName
     * @return mixed
     */
    public function setStreetArmName($armName);

    /**
     * This function is used to inject street eng_name
     *
     * @param $engName
     * @return mixed
     */
    public function setStreetEngName($engName);
}