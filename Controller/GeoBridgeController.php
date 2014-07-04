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
     * @Route("/autocomplete/{search}")
     * @param $search
     * @return mixed
     */
    public function getAddressesAction($search)
    {
        $limit = 10;

        $addresses = $this->get('geo_bridge')->searchAddress($search, $limit);
        $addresses = json_encode($addresses);

        return new Response($addresses);
    }
}
