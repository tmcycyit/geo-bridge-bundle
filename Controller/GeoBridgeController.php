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
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Yit\GeoBridgeBundle\Entity\Address;

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
     * @Route("/address/district/autocomplete/{districtId}/{search}", requirements={"search" = ".+"})
     * @param $search
     * @param $districtId
     * @return JsonResponse
     */
    public function getAddressesAction($search, $districtId = 0)
    {
        $addresses = $this->get('yit_geo')->searchAddress($search, self::AUTOCOMPLETE_LIMIT, $districtId);

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

        $addresses = (array)$addresses;
        $addresses = array_shift($addresses);

        return new JsonResponse($addresses);
    }

	/**
     * This function is used to generate route for active addresses autocomplete
     *
     * @Route("/real/address/autocomplete/{search}", requirements={"search" = ".+"})
     * @Route("/real/address/district/autocomplete/{districtId}/{search}", requirements={"search" = ".+"})
     * @param $search
     * @param $districtId
     * @return Response
     */
    public function getRealAddressesAction($search, $districtId = 0)
    {
        $addresses = $this->get('yit_geo')->searchRealAddress($search, self::AUTOCOMPLETE_LIMIT, $districtId);

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
     * This function is used to generate route for active addresses autocomplete
     *
     * @Route("/real/address/autocomplete/{search}", requirements={"search" = ".+"})
     * @Route("/addresses/{dataTime}/modified", requirements={"dataTime" = ".+"})
     * @param $dataTime
     * @return Response
     */
    public function getModifiedAddressesAction($dataTime)
    {
        $addresses = $this->get('yit_geo')->searchModifiedAddress($dataTime);

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
     * This function is used to generate route for addresses autocomplete
     *
     * @Route("/flexible/address/autocomplete/{search}", requirements={"search" = ".+"})
     * @param $search
     * @return Response
     */
    public function getAddressesFlexibleAction($search)
    {
        $result = $this->get('yit_geo')->searchFlexibleAddress($search, self::AUTOCOMPLETE_LIMIT);

        if ($result)
        {
            $locale = $this->getRequest()->getLocale();
            if ($locale == "en") {
                foreach($result->data as &$address) {
                    $address->title = $address->eng_title;
                    unset($address->eng_title);
                }
            }
        }

        $result = json_encode($result);

        return new Response($result);
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
     * This function is used to get districts or streets for flexible autocomplete
     *
     * @Route("/flexible/street/autocomplete/{search}", requirements={"search" = ".+"})
     * @param $search
     * @return Response
     */
    public function getStreetsFlexibleAction($search)
    {
        $result = $this->get('yit_geo')->searchStreetFlexible($search, self::AUTOCOMPLETE_LIMIT);

        if ($result) {
            $locale = $this->getRequest()->getLocale();
            if($locale == "en") {
                foreach($result->data as &$data) {
                    $data->title = $data->eng_title;
                    unset($data->eng_title);
                }
            }
        }

        $result = json_encode($result);

        return new Response($result);
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
		// get doctrine connection
		$em = $this->getDoctrine()->getManager();

        $addressId = $this->get('yit_geo')->putAddress($addressString);

		if(isset($addressId) && isset($addressString))
		{
			// get address by address id
			$address = $em->getRepository('YitGeoBridgeBundle:Address')->findOneByAddressId($addressId);
			// get last synchronization updated date time
			$dateTime = $em->getRepository('YitGeoBridgeBundle:Address')->getLastUpdate();
			// if exist address update
			if(isset($address) && $address != null)
			{
				// update address in YitGeoBridgeBundle
				$address->setArmName($addressString);
				$address->setLatitude(40.179496298328);
				$address->setLongitude(44.512739181518555);
				$address->setCreated(new \DateTime($dateTime));
				$address->setUpdated(new \DateTime($dateTime));
				$em->persist($address);
			}
			else{
				// insert address in YitGeoBridgeBundle
				$address = new Address();
				$address->setAddressId($addressId);
				$address->setArmName($addressString);
				$address->setLatitude(40.179496298328);
				$address->setLongitude(44.512739181518555);
				$address->setCreated(new \DateTime($dateTime));
				$address->setUpdated(new \DateTime($dateTime));
				$em->persist($address);
			}
			
			$em->flush();
		}

        return new Response($addressId);
    }

	/**
     * This function is used to put address on geo project
     * If there are any error return null
     *
     * @Route("/create/putAddress/{addressString}/{latitude}/{longitude}", requirements={"addressString" = ".+"})
     * @param $addressString
     * @return Response
     */
    public function putAddressCreateAction($addressString, $latitude, $longitude)
    {
		// get doctrine connection
		$em = $this->getDoctrine()->getManager();

        $addressId = $this->get('yit_geo')->putAddressCreate($addressString, $latitude, $longitude);

		if(isset($addressId) && isset($addressString))
		{
			// get address by address id
			$address = $em->getRepository('YitGeoBridgeBundle:Address')->findOneByAddressId($addressId);
			// get last synchronization updated date time
			$dateTime = $em->getRepository('YitGeoBridgeBundle:Address')->getLastUpdate();
			// if exist address update
			if(isset($address) && $address != null)
			{
				// update address in YitGeoBridgeBundle
				$address->setArmName($addressString);
				$address->setLatitude($latitude);
				$address->setLongitude($longitude);
				$address->setCreated(new \DateTime($dateTime));
				$address->setUpdated(new \DateTime($dateTime));
				$em->persist($address);
			}
			else{
				// insert address in YitGeoBridgeBundle
				$address = new Address();
				$address->setAddressId($addressId);
				$address->setArmName($addressString);
				$address->setLatitude($latitude);
				$address->setLongitude($longitude);
				$address->setCreated(new \DateTime($dateTime));
				$address->setUpdated(new \DateTime($dateTime));
				$em->persist($address);
			}

			$em->flush();
		}

        return new Response($addressId);
    }


}
