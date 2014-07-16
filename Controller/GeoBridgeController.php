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
