<?php
/**
 * Created by PhpStorm.
 * User: andranik
 * Date: 7/3/14
 * Time: 7:30 PM
 */

namespace Yit\GeoBridgeBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;

class GeoBridgeController extends Controller
{
    const AUTOCOMPLETE_LIMIT = 10;

    /**
     * This function is used to get address by id
     *
     * @Route("/address/{addressId}", requirements={"addressId" = "\d+"})
     * @param $addressId
     * @return mixed
     */
    public function getAddress($addressId)
    {
        $address = $this->get('yit_geo')->getAddressById($addressId);
        $address = json_encode($address);

        return new Response($address);
    }

    /**
     * This function is used to get address by id
     *
     * @Route("/street/{streetId}", requirements={"streetId" = "\d+"})
     * @param $streetId
     * @return mixed
     */
    public function getStreet($streetId)
    {
        $street = $this->get('yit_geo')->getStreetById($streetId);
        $street = json_encode($street);

        return new Response($street);
    }

    /**
     * This function is used to get district by id
     *
     * @Route("/district/{districtId}", requirements={"districtId" = "\d+"})
     * @param $districtId
     * @return mixed
     */
    public function getDistrictById($districtId)
    {
        $district = $this->get('yit_geo')->getDistrictById($districtId);
        $district = json_encode($district);

        return new Response($district);
    }

    /**
     * This function is used to generate route for addresses autocomplete
     *
     * @Route("/address/autocomplete/{search}", requirements={"search" = ".+"})
     * @param $search
     * @return Response
     */
    public function getAddressesAction($search)
    {
        $addresses = $this->get('yit_geo')->searchAddress($search, self::AUTOCOMPLETE_LIMIT);

        if ($addresses)
        {
            $locale = $this->getRequest()->getLocale();
            foreach($addresses->data as &$address) {
                if ($locale == "en") {
                    $address->title = $address->eng_title;
                }
                unset($address->eng_title);
            }
        }

        $addresses = json_encode($addresses);

        return new Response($addresses);
    }

    /**
     * This function is used to generate route for street autocomplete
     *
     * @Route("/street/autocomplete/{search}", requirements={"search" = ".+"})
     * @param $search
     * @return Response
     */
    public function getStreetsAction($search)
    {
        $streets = $this->get('yit_geo')->searchStreet($search, self::AUTOCOMPLETE_LIMIT);
        $streets = json_encode($streets);

        return new Response($streets);
    }

    /**
     * This function is used to generate route for street autocomplete
     *
     * @Route("/district/autocomplete/{search}")
     * @param $search
     * @return Response
     */
    public function getDistrictAction($search)
    {
        $district = $this->get('yit_geo')->searchDistrict($search);
        $district = json_encode($district);

        return new Response($district);
    }

    /**
     * This function is used to generate route for street autocomplete
     *
     * @Route("/street/district/autocomplete/{district}/{search}", requirements={"district" = "\d+"})
     * @param $search
     * @param $district
     * @return Response
     */
    public function getStreetsByDistrictAction($search, $district)
    {

        $streets = $this->get('yit_geo')->getSearchStreetsByDistrict($search, $district, self::AUTOCOMPLETE_LIMIT);
        $streets = json_encode($streets);

        return new Response($streets);
    }

    /**
     * This function is used to put address on geo project
     * If there are any error return null
     *
     * @Route("/putAddress/{addressString}", requirements={"addressString" = ".+"})
     * @param $addressString
     * @return Response
     */
    public function putAddressAction($addressString)
    {
        $addressId = $this->get('yit_geo')->putAddress($addressString);

        return new Response($addressId);
    }
}
