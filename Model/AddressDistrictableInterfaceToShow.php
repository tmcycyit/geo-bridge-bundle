<?php
/**
 * Created by PhpStorm.
 * User: andranik
 * Date: 9/10/14
 * Time: 6:57 PM
 */

namespace Yit\GeoBridgeBundle\Model;

/**
 * Interface AddressDistrictableInterfaceToShow
 *
 * This interface is used when entity has an address_id and district_id
 * fields to set district id based on address id
 * district id is set from geo project, and if it is empty persist it, if does not match only set district id without persist
 *
 * @package Yit\GeoBridgeBundle\Model
 */
interface AddressDistrictableInterfaceToShow extends AddressDistrictableInterface
{

}