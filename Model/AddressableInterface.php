<?php
/**
 * Created by PhpStorm.
 * User: andranik
 * Date: 7/3/14
 * Time: 5:12 PM
 */

namespace Yit\GeoBridgeBundle\Model;

/**
 * Interface AddressableInterface
 *
 * This interface is used when entity has an address_id field
 *
 * @package Yit\GeoBridgeBundle\Model
 */
interface AddressableInterface
{
    /**
     * This function is used to get address id, the fields of which must be injected
     *
     * @return mixed
     */
    public function getAddressId();

    /**
     * This function is used to inject address title
     *
     * @param $title
     * @return mixed
     */
    public function setAddressTitle($title);

    /**
     * This function is used to inject address latitude
     *
     * @param $latitude
     * @return mixed
     */
    public function setAddresLatitude($latitude);

    /**
     * This function is used to inject address longitude
     *
     * @param $longitude
     * @return mixed
     */
    public function setAddresLongitude($longitude);

    /**
     * This function is used to inject address h_number
     *
     * @param $hNumber
     * @return mixed
     */
    public function setAddressHNumber($hNumber);

    /**
     * This function is used to inject address eng_type
     *
     * @param $engType
     * @return mixed
     */
    public function setAddressEngType($engType);
}