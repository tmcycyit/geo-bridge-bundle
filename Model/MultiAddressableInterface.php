<?php
/**
 * Created by PhpStorm.
 * User: andranik
 * Date: 7/3/14
 * Time: 5:12 PM
 */

namespace Yit\GeoBridgeBundle\Model;

/**
 * Interface MultiAddressableInterface
 *
 * this interface is used when entity has more than one address
 *
 * @package Yit\GeoBridgeBundle\Model
 */
interface MultiAddressableInterface
{
    /**
     * this function is used to get ids of addresses that must be injected
     * @return array
     */
    public function getAddressIds();

    /**
     * this function inject addresses
     *
     * @param array $addresses
     * @return mixed
     */
    public function setAddresses(array $addresses);
}