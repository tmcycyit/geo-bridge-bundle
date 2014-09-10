<?php
/**
 * Created by PhpStorm.
 * User: andranik
 * Date: 9/10/14
 * Time: 7:12 PM
 */

namespace Yit\GeoBridgeBundle\Model;

/**
 * Interface AddressStreetableInterface
 *
 * This interface is used when entity has an address_id and street_id
 * fields to set street id based on address id
 * street id is set from geo project and persist it is empty
 *
 * @package Yit\GeoBridgeBundle\Model
 */
interface AddressStreetableInterface
{
    /**
     * This function is used to get address id
     *
     * @return mixed
     */
    public function getAddressId();

    /**
     * This function is used to inject street id
     *
     * @param $id
     * @return mixed
     */
    public function setStreetId($id);

    /**
     * This function is used to get street id
     *
     * @return mixed
     */
    public function getStreetId();
}