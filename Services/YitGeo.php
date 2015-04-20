<?php
/**
 * Created by PhpStorm.
 * User: andranik
 * Date: 7/3/14
 * Time: 5:36 PM
 */

namespace Yit\GeoBridgeBundle\Services;

use Doctrine\ORM\EntityManager;
use FOS\RestBundle\Util\Codes;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Yit\GeoBridgeBundle\Entity\Address;


class YitGeo
{
//    const GEO_DOMAIN = 'http://geo.yerevan.am/';
	const GEO_DOMAIN = 'http://dev.geo.yerevan.am/';
//    const GEO_DOMAIN = 'http://geo.loc/app_dev.php/';

    protected $experience;

    protected $container;

	protected $entityManager;

    public function __construct(Container $container, EntityManager $entityManager)
    {
        $this->container = $container;
        $this->experience = $this->container->getParameter('yit_geo_bridge.experience');
		$this->em = $entityManager;
    }


    /**
     * This function is used to get content from $link
     *
     * @param $link
     * @param null $context
     * @return mixed|null|string
     */
    private function getContent($link, $context = null)
    {
        $content = @file_get_contents($link, false, $context);

        if ($content) {
            $content = json_decode($content);

            if (isset($content->status) && $content->status != 201) {
                $content = null;
            }
        }
        else {
            $content = null;
        }

        return $content;
    }

    /**
     * This function is used to produce url parameters for special symbols
     *
     * @param $string
     * @return mixed
     */
    private function produceUrlParameter($string)
    {
        $result = rawurlencode($string);
        return str_replace('%2F', '/', $result);
    }


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
            $address = $this->getContent(self::GEO_DOMAIN . 'api/addresses/' . $id);
            //Store address in cache 24 hours
            apc_add('address_' . $id, $address, $this->experience);
        }

        return $address;
    }

    /**
     * This function is used get address synonym ids
     *
     * @param $addressId
     * @return mixed|null|string
     */
    public function getSynonymIds($addressId)
    {
        return $this->getContent(self::GEO_DOMAIN . 'api/addresses/'. $addressId .'/synonyms');
    }

    /**
     * This function is used to get $limit addresses by $search string
     * If there are not any address with such content return null
     *
     * @param $search
     * @param int $limit
     * @param $districtId
     * @return mixed|null|string
     */
    public function searchAddress($search, $limit = 0, $districtId = 0)
    {
        $search = $this->produceUrlParameter($search);
        return $this->getContent(self::GEO_DOMAIN . 'api/addresses/'. $search .'/search/' . $limit . '/' .  $districtId);
    }

	/**
     * This function is used to get $limit addresses by $search string
     * If there are not any address with such content return null
     *
     * @param $dateTime
     * @return mixed|null|string
     */
    public function searchModifiedAddress($dateTime)
    {
		$dateTime = $this->produceUrlParameter($dateTime);
        return $this->getContent(self::GEO_DOMAIN . 'api/addresses/'.$dateTime.'/modified');
    }

	/**
     * This function is used to get $limit real addresses by $search string
     * If there are not any address with such content return null
     *
     * @param $search
     * @param int $limit
     * @param $districtId
     * @return mixed|null|string
     */
    public function searchRealAddress($search, $limit = 0, $districtId = 0)
    {
        $search = $this->produceUrlParameter($search);
        return $this->getContent(self::GEO_DOMAIN . 'api/real/addresses/'. $search .'/search/' . $limit . '/' .  $districtId);
    }

    /**
     * This function is used to get $limit streets or addresses by $search string
     * If there are not any address with such content return null
     *
     * @param $search
     * @param int $limit
     * @return mixed|null|string
     */
    public function searchFlexibleAddress($search, $limit = 0)
    {
        $search = $this->produceUrlParameter($search);
        return $this->getContent(self::GEO_DOMAIN . 'api/flexible/addresses/'. $search .'/search/' . $limit);
    }

    /**
     * This function is used to get addresses by given id's array
     *
     * @param array $ids
     * @return mixed|null|string
     */
    public function getAddresses(array $ids)
    {
        $opts = array('http' =>
            array(
                'method'  => 'POST',
                'content' => json_encode($ids)
            )
        );

        $context  = stream_context_create($opts);

        return $this->getContent(self::GEO_DOMAIN . "api/addresses/multiples", $context);
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
        $token = $this->container->get('security.context')->getToken();
        if ($token) {
            $user = $token->getUser()->getUserName();
        }
        else {
            $user = 'Geo_Bridge';
        }

        $opts = array('http' =>
            array(
                'method'  => 'PUT',
                'content' => http_build_query(array(
                    'project_name'  => $this->container->getParameter('yit_geo_bridge.project_name'),
                    'author'        => $user,
                )))
        );
        $addressString = $this->produceUrlParameter($addressString);
        $context  = stream_context_create($opts);

        return $this->getContent(self::GEO_DOMAIN . "api/addresses/" . $addressString, $context);
    }

    /**
     * This function is used to create new address in Geo Project with $street, $streetType, $district & $hNumber title
     * when access return id of created Address else return null
     *
     * @param $street
     * @param $streetType
     * @param $district
     * @param $hNumber
     * @return mixed|null|string
     */
    public function putNewAddress($street, $streetType, $district, $hNumber)
    {
        $opts = array('http' =>
            array(
                'method'  => 'PUT',
                'header'  => "Content-Type: application/json",
            )
        );
        $streetType = $streetType ? $streetType : 'փողոց';
        $hNumber = $hNumber ? $hNumber : 0;
        $hNumber = $this->produceUrlParameter($hNumber);
        $street = $this->produceUrlParameter($street);
        $context  = stream_context_create($opts);

        return $this->getContent(self::GEO_DOMAIN . "api/put/addresses/" . $street . "/" . $streetType . "/" . $district . "/" . $hNumber, $context);
    }

    /**
     * This function is used to get addresses by $street, $type and $hNumber string
     * If there are not any address with such content return null
     *
     * @param $street
     * @param $type
     * @param $hNumber
     * @return mixed|null|string
     */
    public function findAddress($street, $type, $hNumber)
    {
        $type = $type ? $type : 'փողոց';
        $hNumber = $hNumber ? $hNumber : 0;
        $street = $this->produceUrlParameter($street);
        $hNumber = $this->produceUrlParameter($hNumber);
        return $this->getContent(self::GEO_DOMAIN . 'api/param/addresses/' . $street . '/' . $type . '/' . $hNumber);
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
            $district = $this->getContent(self::GEO_DOMAIN . 'api/districts/' . $id);
            //Store district in cache 24 hours
            apc_add('district_' . $id, $district, $this->experience);
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
            $districts = $this->getContent(self::GEO_DOMAIN . 'api/districts');
            //Store districts in cache 24 hours
            apc_add('districts', $districts, $this->experience);
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
            apc_add('districtsList', $districtsList, $this->experience);
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
            $streets = $this->getContent(self::GEO_DOMAIN . 'api/districts/' . $districtID . '/streets');

            //Store streets in cache 24 hours
            apc_add('district_streets', $streets, $this->experience);
        }

        return $streets;
    }


    /**
     * This function is used to get district by $search string
     * If there are not any district with such content return null
     *
     * @param $search
     * @return mixed|null|string
     */
    public function searchDistrict($search)
    {
        $search = $this->produceUrlParameter($search);
        return $this->getContent(self::GEO_DOMAIN . 'api/districts/'. $search .'/search');
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
            $street = $this->getContent(self::GEO_DOMAIN . 'api/streets/' . $id);

            //Store district in cache 24 hours
            apc_add('street_' . $id, $street, $this->experience);
        }

        return $street;
    }


    /**
     * This function is used get street by given address id
     * If there are not any address by given id return null
     *
     * @param $id
     * @return mixed|null|string
     */
    public function getStreetByAddressId($id)
    {
        $street = apc_fetch('address_street_' . $id);

        if ($street === false)
        {
            $street = $this->getContent(self::GEO_DOMAIN . 'api/addresses/'. $id .'/street');

            //Store district in cache 24 hours
            apc_add('address_street_' . $id, $street, $this->experience);
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
        $search = $this->produceUrlParameter($search);
        return $this->getContent(self::GEO_DOMAIN . 'api/streets/'. $search .'/search/' . $limit);
    }

    /**
     * This function is used to get $limit streets by $search string
     * If there are not any street with such content return null
     *
     * @param $search
     * @param int $limit
     * @return mixed|null|string
     */
    public function searchStreetFlexible($search, $limit = 0)
    {
        $search = $this->produceUrlParameter($search);
        return $this->getContent(self::GEO_DOMAIN . 'api/flexible/streets/'. $search .'/search/' . $limit);
    }


    /**
     * This function is used to get $limit streets by $search string and district id
     * If there are not any street with such content return null
     *
     * @param $search
     * @param $district
     * @param int $limit
     * @return mixed|null|string
     */
    public function getSearchStreetsByDistrict($search, $district, $limit = 0)
    {
        $limit = ($limit) ? $limit : 10;

        return $this->getContent(self::GEO_DOMAIN . 'api/streets/' . $search . '/search/' . $limit . '/' . $district);
    }

	/**
	 * This service return address object by addressId
	 *
	 * @param $id
	 * @return mixed
	 */
	public function getAddressByIdObject($id)
	{
		// get address by id
		$address = $this->em->getRepository('YitGeoBridgeBundle:Address')->findOneByAddressId($id);

		if(isset($address) && $address != null)
		{
			// return address object
			return $address;
		}
		else{
			// get address from Geo Main project
			$addresses = $this->getContent(self::GEO_DOMAIN . 'api/addresses/' . $id . '');

				if (isset($addresses->title) && $addresses->title != null) {

					$dateTime = $this->em->getRepository('YitGeoBridgeBundle:Address')->getLastUpdate();
					// create address in yit geo bridge
					$address = new Address();
					$address->setAddressId($id);
					$address->setAddress($addresses->title);
					$address->setCreated(new \DateTime($dateTime));
					$address->setUpdated(new \DateTime($dateTime));
					$this->em->persist($address);
					$this->em->flush();

					return $address;
				}
					else {

						throw new HttpException(Codes::HTTP_NOT_FOUND, 'Address not found in YitGeoBridgeBundle:Address entity');
					}
			}
	}
}
