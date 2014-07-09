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
     * This function is used to generate route for addresses autocomplete
     *
     * @Route("/address/autocomplete/{search}", requirements={"search" = ".+"})
     * @param $search
     * @return Response
     */
    public function getAddressesAction($search)
    {
        $addresses = $this->get('geo_bridge')->searchAddress($search, self::AUTOCOMPLETE_LIMIT);

        $locale = $this->getRequest()->getLocale();
        foreach($addresses->data as &$address) {
            if ($locale == "en") {
                $address->title = $address->eng_title;
            }
            unset($address->eng_title);
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
        $streets = $this->get('geo_bridge')->searchStreet($search, self::AUTOCOMPLETE_LIMIT);
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
        $addressId = $this->get('geo_bridge')->putAddress($addressString);

        return new Response($addressId);
    }
}
