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
use Symfony\Component\Validator\Constraints\IsNull;
use Yit\GeoBridgeBundle\Entity\Address;
use Yit\GeoBridgeBundle\Form\DataTransformer\AddressToObjectTransformer;


class YitGeo
{

	/**
	 * Container is a dependency injection container.
	 */
	protected $container;

	/**
	 * Gets container a parameter.
	 */
	protected $experience;

	/**
	 * The EntityManager is the central access point to ORM functionality.
	 */
	protected $entityManager;

	/**
	 * This function get geo main project domain from config
	 * If config not use default set http://geo.yerevan.am/
	 *
	 * @return mixed
	 */
	protected $geoDomain;

	/**
	 * @param Container $container
	 * @param EntityManager $entityManager
	 */
	public function __construct(Container $container, EntityManager $entityManager)
	{
		$this->container = $container;
		$this->experience = $this->container->getParameter('yit_geo_bridge.experience');
		$this->em = $entityManager;
		$this->geoDomain = $this->container->getParameter('yit_geo_bridge.project_domain');
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
		$address = $this->getContent($this->geoDomain . 'api/addresses/' . $id);

		return $address;
	}

	/**
	 * This service return address object by addressId
	 * If there are not found return exception
	 *
	 * @param $id
	 * @return Address
	 * @throws \Exception
	 */
	public function getAddressObjectById($id)
	{
		// get address by id
		$address = $this->em->getRepository('YitGeoBridgeBundle:Address')->findOneByAddressId($id);

		if ($address != null) {
			// return address object if exist in YitGeoBridgeBundle:Address entity
			return $address;
		}
		else {
			// get address from Geo Main project
			$addresses = $this->getContent($this->geoDomain . 'api/addresses/' . $id);

			if (isset($addresses->title) && $addresses->title != null) {

				// get last updated data time in YitGeoBridgeBundle Address entity
				$dateTime = $this->em->getRepository('YitGeoBridgeBundle:Address')->getLastUpdate();

				// create address in yit geo bridge
				$address = new Address();
				$address->setAddressId($id);
				$address->setArmName($addresses->title);
				$address->setEngName($addresses->eng_title);
				$address->setLatitude($addresses->latitude);
				$address->setLongitude($addresses->longitude);
				$address->setCreated(new \DateTime($dateTime));
				$address->setUpdated(new \DateTime($dateTime));
				$this->em->persist($address);
				$this->em->flush();

				// return created address object
				return $address;
			}
			else {
				throw new \Exception('Address not found!');
			}
		}
	}

	/**
	 * This function is used get address synonym ids
	 *
	 * @param $addressId
	 * @return mixed|null|string
	 */
	public function getSynonymIds($addressId)
	{
		return $this->getContent($this->geoDomain . 'api/addresses/' . $addressId . '/synonyms');
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
		return $this->getContent($this->geoDomain . 'api/addresses/' . $search . '/search/' . $limit . '/' . $districtId);
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
		return $this->getContent($this->geoDomain . 'api/addresses/' . $dateTime . '/modified');
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
		return $this->getContent($this->geoDomain . 'api/real/addresses/' . $search . '/search/' . $limit . '/' . $districtId);
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
		return $this->getContent($this->geoDomain . 'api/flexible/addresses/' . $search . '/search/' . $limit);
	}

	/**
	 * This function is used to get addresses by given id's array
	 *
	 * @param array $ids
	 * @return mixed|null|string
	 */
	public function getAddresses(array $ids)
	{
		$opts = array('http' => array('method' => 'POST', 'content' => json_encode($ids)));

		$context = stream_context_create($opts);

		return $this->getContent($this->geoDomain . "api/addresses/multiples", $context);
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
		if ($token && $token->getUser() != "anon.") {
			$user = $token->getUser()->getUserName();
		}
		else {
			$user = 'Geo_Bridge';
		}

		$opts = array('http' =>
			array('method' => 'PUT',
				   'content' => http_build_query(
					   array('project_name' => $this->container->getParameter('yit_geo_bridge.project_name'),
						     'author' => $user,))));
		$addressString = $this->produceUrlParameter($addressString);
		$context = stream_context_create($opts);

		return $this->getContent($this->geoDomain . "api/addresses/" . $addressString, $context);
	}

	/**
	 * This function create address by used addressString, latitude and longitude
	 *
	 * @param $addressString
	 * @param $latitude
	 * @param $longitude
	 * @return mixed|null|string
	 */
	public function putAddressCreate($addressString, $latitude, $longitude)
	{
		$token = $this->container->get('security.context')->getToken();
		if ($token->getUser()!='anon.') {

			$user = $token->getUser()->getUserName();

		}
		else {
			$user = 'Geo_Bridge';
		}

		$opts = array('http' => array('method' => 'PUT',
			'content' => http_build_query(array(
				'project_name' => $this->container->getParameter('yit_geo_bridge.project_name'),
				'author' => $user,))));
		$addressString = $this->produceUrlParameter($addressString);
		$context = stream_context_create($opts);
		return $this->getContent($this->geoDomain . "api/put/address/" . $addressString . "/" . $latitude . "/" . $longitude, $context);
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
		$opts = array('http' => array('method' => 'PUT', 'header' => "Content-Type: application/json",));
		$streetType = $streetType? $streetType : 'փողոց';
		$hNumber = $hNumber? $hNumber : 0;
		$hNumber = $this->produceUrlParameter($hNumber);
		$street = $this->produceUrlParameter($street);
		$context = stream_context_create($opts);

		return $this->getContent($this->geoDomain . "api/put/addresses/" . $street . "/" . $streetType . "/" . $district . "/" . $hNumber, $context);
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
		$type = $type? $type : 'փողոց';
		$hNumber = $hNumber? $hNumber : 0;
		$street = $this->produceUrlParameter($street);
		$hNumber = $this->produceUrlParameter($hNumber);
		return $this->getContent($this->geoDomain . 'api/param/addresses/' . $street . '/' . $type . '/' . $hNumber);
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
		$district = $this->getContent($this->geoDomain . 'api/districts/' . $id);

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
		$districts = $this->getContent($this->geoDomain . 'api/districts');

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
		$districts = $this->getDistricts();
		$districtsList = array();

		if ($districts) {
			foreach ($districts as $value) {
				$districtsList[$value->id] = $value->title;
			}
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
		$streets = $this->getContent($this->geoDomain . 'api/districts/' . $districtID . '/streets');

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
		return $this->getContent($this->geoDomain . 'api/districts/' . $search . '/search');
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
		$street = $this->getContent($this->geoDomain . 'api/streets/' . $id);

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
		$street = $this->getContent($this->geoDomain . 'api/addresses/' . $id . '/street');

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
		return $this->getContent($this->geoDomain . 'api/streets/' . $search . '/search/' . $limit);
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
		return $this->getContent($this->geoDomain . 'api/flexible/streets/' . $search . '/search/' . $limit);
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
		$limit = ($limit)? $limit : 10;

		return $this->getContent($this->geoDomain . 'api/streets/' . $search . '/search/' . $limit . '/' . $district);
	}


}
