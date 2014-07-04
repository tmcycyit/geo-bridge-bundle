<?php
/**
 * Created by PhpStorm.
 * User: andranik
 * Date: 7/3/14
 * Time: 5:36 PM
 */

namespace Yit\GeoBridgeBundle\Services;

class GeoBridge
{
    const GEO_DOMAIN = 'http://geo.yerevan.am/';

    /**
     * This function return address object by given id
     * If there are not any address with such id return null
     *
     * @param $id
     * @return object
     */
    public function getAddressById($id)
    {
        $address = @file_get_contents(self::GEO_DOMAIN . 'api/addresses/' . $id);

        if ($address)
        {
            return json_decode($address);
        }
        return null;
    }

    /**
     * This function is used to get $limit addresses by $search string
     * If there are not any address with such content return null
     *
     * @param $search
     * @param int $limit
     * @return mixed|null|string
     */
    public function searchAddress($search, $limit = 0)
    {
        $addresses = @file_get_contents(self::GEO_DOMAIN . 'api/addresses/'. $search .'/search/' . $limit);

        if ($addresses)
        {
            $addresses = json_decode($addresses);

            if (!isset($addresses->status) || $addresses->status == 201)
            {
                return $addresses;
            }
        }

        return null;
    }

    /**
     * This function is used to create new address in Geo Project with $addressString title
     * when access return id of created Address else return null
     *
     * @param $addressString
     * @return null|string
     */
    public function putAddress($addressString)
    {
        $opts = array('http' =>
                    array(
                        'method'  => 'PUT',
                        'header'  => "Content-Type: application/json",
                    )
        );

        $context  = stream_context_create($opts);
        $result = @file_get_contents(self::GEO_DOMAIN . "api/addresses/" . $addressString, false, $context);

        if ($result)
        {
            return $result;
        }

        return null;
    }
}