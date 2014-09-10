<?php
/**
 * Created by PhpStorm.
 * User: andranik
 * Date: 9/1/14
 * Time: 8:30 PM
 */

namespace Yit\GeoBridgeBundle\Model;

/**
 * Interface AddressDistrictableInterface
 *
 * This interface is used when entity has an address_id and district_id
 * fields to set district id based on address id
 * district id is set from geo project, and if it is empty persist it
 *
 * @package Yit\GeoBridgeBundle\Model
 */
interface AddressDistrictableInterface
{
    /**
     * This function is used to get address id
     *
     * @return mixed
     */
    public function getAddressId();

    /**
     * This function is used to inject district id
     *
     * @param $id
     * @return mixed
     */
    public function setDistrictId($id);

    /**
     * This function is used to get district id
     *
     * @return mixed
     */
    public function getDistrictId();
}