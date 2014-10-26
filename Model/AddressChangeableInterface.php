<?php
/**
 * Created by PhpStorm.
 * User: andranik
 * Date: 10/26/14
 * Time: 9:55 PM
 */

namespace Yit\GeoBridgeBundle\Model;

/**
 * Interface AddressChangeableInterface
 *
 * This interface is used to change address_id field
 *
 * @package Yit\GeoBridgeBundle\Model
 */
interface AddressChangeableInterface
{
    /**
     * This function is used to get address id, the fields of which must be injected
     *
     * @return mixed
     */
    public function getAddressId();

    /**
     * This function is used to set Address Id
     *
     * @param $addressId
     * @return mixed
     */
    public function setAddressId($addressId);

    /**
     * This function is used to get AddressId field name
     * @return mixed
     */
    public function getAddressField();
}
