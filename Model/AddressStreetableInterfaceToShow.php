<?php
/**
 * Created by PhpStorm.
 * User: andranik
 * Date: 9/10/14
 * Time: 7:21 PM
 */

namespace Yit\GeoBridgeBundle\Model;

/**
 * Interface AddressStreetableInterfaceToShow
 *
 * This interface is used when entity has an address_id and street_id
 * fields to set street id based on address id
 * street id is set from geo project and persist it is empty, if does not match only set street id without persist
 *
 * @package Yit\GeoBridgeBundle\Model
 */
interface AddressStreetableInterfaceToShow extends AddressStreetableInterface
{
}