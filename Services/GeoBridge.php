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

    //******************************************
    //*************** Address ******************
    //******************************************

    /**
     * This function return address object by given id
     * If there are not any address with such id return null
     *
     * @param $id
     * @return object
     */
    public function getAddressById($id)
    {
        $address = apc_fetch('address_' . $id);

        if ($address === false)
        {
            $address = @file_get_contents(self::GEO_DOMAIN . 'api/addresses/' . $id);

            if ($address)
            {
                $address = json_decode($address);
            }
            else
            {
                $address = null;
            }

            //Store address in cache 24 hours
            apc_add('address_' . $id, $address, 86400);
        }

        return $address;
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

        $addresses = apc_fetch('addressSearch_' . $search . '_' . $limit);

        if ($addresses === false)
        {
            $addresses = @file_get_contents(self::GEO_DOMAIN . 'api/addresses/'. $search .'/search/' . $limit);

            if ($addresses)
            {
                $addresses = json_decode($addresses);

                if (isset($addresses->status) && !$addresses->status == 201) {
                    $addresses = null;
                }
            }
            else {
                $addresses = null;
            }

            apc_add('addressSearch_' . $search . '_' . $limit, $addresses, 86400);
        }

        return $addresses;
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

    //******************************************
    //************** District ******************
    //******************************************

    /**
     * This function return district object by given id
     * If there are not any district with such id return null
     *
     * @param $id
     * @return mixed|null|string
     */
    public function getDistrictById($id)
    {
        $district = apc_fetch('district_' . $id);

        if ($district === false)
        {
            $district = @file_get_contents(self::GEO_DOMAIN . 'api/districts/' . $id);

            if ($district)
            {
                $district = json_decode($district);
            }
            else
            {
                $district = null;
            }

            //Store district in cache 24 hours
            apc_add('district_' . $id, $district, 86400);
        }

        return $district;
    }


    /**
     * This function is used to get districts
     * If there are not any district return null
     *
     * @return mixed|null|string
     */
    public function getDistricts()
    {
        $districts = apc_fetch('districts');

        if ($districts === false)
        {
            $districts = @file_get_contents(self::GEO_DOMAIN . 'api/districts');

            if ($districts)
            {
                $districts = json_decode($districts);
            }
            else
            {
                $districts = null;
            }

            //Store districts in cache 24 hours
            apc_add('districts', $districts, 86400);
        }

        return $districts;
    }

    /**
     * This function is used to get districts in array 'id' => 'title'
     * If there are not any district return empty array
     *
     * @return array|mixed|string
     */
    public function getDistrictList()
    {
        $districtsList = apc_fetch('districtsList');

        if ($districtsList === false)
        {
            $districts = $this->getDistricts();
            $districtsList = array();

            if ($districts) {
                foreach($districts as $value) {
                    $districtsList[$value->id] = $value->title;
                }
            }

            //Store districtList in cache 24 hours
            apc_add('districtsList', $districtsList, 86400);
        }

        return $districtsList;
    }

    /**
     * This function is used to get all streets by district id
     * If there are not any street return null
     *
     * @param $districtID
     * @return mixed|null|string
     */
    public function getStreetsByDistrict($districtID)
    {
        $streets = apc_fetch('district_streets');

        if ($streets === false)
        {
            $streets = @file_get_contents(self::GEO_DOMAIN . 'api/districts/' . $districtID . '/streets');

            if ($streets)
            {
                $streets = json_decode($streets);
            }
            else
            {
                $streets = null;
            }

            //Store streets in cache 24 hours
            apc_add('district_streets', $streets, 86400);
        }

        return $streets;
    }


    //******************************************
    //**************** Street ******************
    //******************************************

    /**
     * This function is used get street by given id
     * If there are not any street by given id return null
     *
     * @param $id
     * @return mixed|null|string
     */
    public function getStreetById($id)
    {
        $street = apc_fetch('street_' . $id);

        if ($street === false)
        {
            $street = @file_get_contents(self::GEO_DOMAIN . 'api/streets/' . $id);

            if ($street)
            {
                $street = json_decode($street);
            }
            else
            {
                $street = null;
            }

            //Store district in cache 24 hours
            apc_add('street_' . $id, $street, 86400);
        }

        return $street;
    }


    /**
     * This function is used to get $limit streets by $search string
     * If there are not any street with such content return null
     *
     * @param $search
     * @param int $limit
     * @return mixed|null|string
     */
    public function searchStreet($search, $limit = 0)
    {

        $streets = apc_fetch('streetSearch_' . $search . '_' . $limit);

        if ($streets === false)
        {
            $streets = @file_get_contents(self::GEO_DOMAIN . 'api/streets/'. $search .'/search/' . $limit);

            if ($streets)
            {
                $streets = json_decode($streets);

                if (isset($streets->status) && !$streets->status == 201) {
                    $streets = null;
                }
            }
            else {
                $streets = null;
            }

            apc_add('streetSearch_' . $search . '_' . $limit, $streets, 86400);
        }

        return $streets;
    }
}