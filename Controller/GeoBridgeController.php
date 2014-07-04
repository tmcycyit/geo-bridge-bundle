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
    /**
     * This function is used to generate route for addresses autocomplete
     *
     * @Route("/autocomplete/{search}/{limit}", defaults={"limit" = 10})
     * @param $search
     * @param $limit
     * @return Response
     */
    public function getAddressesAction($search, $limit)
    {
        $addresses = $this->get('geo_bridge')->searchAddress($search, $limit);
        $addresses = json_encode($addresses);

        return new Response($addresses);
    }
}
