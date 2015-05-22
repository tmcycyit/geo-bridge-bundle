<?php

namespace Yit\GeoBridgeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping\UniqueConstraint;
use JMS\Serializer\Annotation\Groups;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation\Type;
use JMS\Serializer\Annotation\VirtualProperty;

/**
 * Address
 *
 * @ORM\Table(name="yit_geo_address")
 * @ORM\Entity(repositoryClass="Yit\GeoBridgeBundle\Entity\Repository\AddressRepository")
 * @UniqueEntity("address_id")
 */
class Address
{
    /**
	 * @ORM\Id
     * @var integer
     * @ORM\Column(name="id", type="integer", unique=true)
     */
    private $addressId;

	/**
	 * @var string
	 *
	 * @Assert\Regex(pattern="/^(([Ա-ՖՈՉՊՋՌՍՎՏՐՑՒՓՔՕա-ֆևփւրցքօ\֊\.․, \/\s0-9]{0,})){1,1}$/i", message="Street Arm name type is invalid")
	 * @ORM\Column(name="arm_name", type="string", length=255, nullable=true)
	 * @Groups({"place", "placeSide", "company", "model"})
	 */
	private $armName;

	/**
	 * @var string
	 *
	 * @Assert\Regex(pattern="/^([A-z\֊\.․, 0-9]{0,}){1,1}$/i", message="Street Eng name type is invalid")
	 * @ORM\Column(name="eng_name", type="string", length=255, nullable=true)
	 */
	private $engName;

	/**
	 * @var string
	 * @ORM\Column(name="latitude", type="decimal", precision=10, scale=7, nullable=true)
	 */
	private $latitude;

	/**
	 * @var string
	 * @ORM\Column(name="longitude", type="decimal", precision=10, scale=7, nullable=true)
	 */
	private $longitude;

	/**
	 * @var datetime $created
	 *
	 * @ORM\Column(type="datetime", nullable=true)
	 */
	private $created;

	/**
	 * @var datetime $updated
	 *
	 * @ORM\Column(type="datetime", nullable=true)
	 */
	private $updated;

	/**
	 * @return string
	 */
	public function __toString()
	{
		return ($this->armName) ? $this->armName: '';
	}

    /**
     * Set addressId
     *
     * @param integer $addressId
     * @return Address
     */
    public function setAddressId($addressId)
    {
        $this->addressId = $addressId;
    
        return $this;
    }

    /**
     * Get addressId
     *
     * @return integer 
     */
		public function getAddressId()
    {
        return $this->addressId;
    }

	/**
	 * Set created
	 *
	 * @param \DateTime $created
	 * @return Address
	 */
	public function setCreated($created)
	{
		$this->created = $created;

		return $this;
	}

	/**
	 * Get created
	 *
	 * @return \DateTime
	 */
	public function getCreated()
	{
		return $this->created;
	}

	/**
	 * Set updated
	 *
	 * @param \DateTime $updated
	 * @return Address
	 */
	public function setUpdated($updated)
	{
		$this->updated = $updated;

		return $this;
	}

	/**
	 * Get updated
	 *
	 * @return \DateTime
	 */
	public function getUpdated()
	{
		return $this->updated;
	}

	/**
	 * Set latitude
	 *
	 * @param string $latitude
	 * @return Address
	 */
	public function setLatitude($latitude)
	{
		$this->latitude = $latitude;

		return $this;
	}

	/**
	 * Get latitude
	 *
	 * @return string
	 */
	public function getLatitude()
	{
		return $this->latitude;
	}

	/**
	 * Set longitude
	 *
	 * @param string $longitude
	 * @return Address
	 */
	public function setLongitude($longitude)
	{
		$this->longitude = $longitude;

		return $this;
	}

	/**
	 * Get longitude
	 *
	 * @return string
	 */
	public function getLongitude()
	{
		return $this->longitude;
	}

	/**
	 * Set armName
	 *
	 * @param  string $armName
	 * @return Address
	 */
	public function setArmName($armName)
	{
		$this->armName = $armName;
		return $this;
	}

	/**
	 * Get armName
	 *
	 * @return string
	 */
	public function getArmName()
	{
		return $this->armName;
	}

	/**
	 * Set engName
	 *
	 * @param  string $engName
	 * @return Address
	 */
	public function setEngName($engName)
	{
		$this->engName = $engName;
		return $this;
	}

	/**
	 * Get engName
	 *
	 * @return string
	 */
	public function getEngName()
	{
		return $this->engName;
	}

	///////////// Methods for position on map /////////////////////

	/**
	 * This function get latitude and longitude for show in YitMaps
	 *
	 * @return array
	 * @VirtualProperty
	 */
	public function getInmap()
	{
		$positions =  array('latitude'=>$this->latitude, 'longitude'=> $this->longitude);

		return array('inmap' =>array('lat' => $positions['latitude'],'lng' => $positions['longitude']));
	}

	/**
	 * This function set latitude and longitude from YitMaps
	 *
	 * @param $latlng
	 * @return $this
	 */
	public function setInMap($latlng)
	{
		$this->setLatitude($latlng['lat']);
		$this->setLongitude($latlng['lng']);

		return $this;
	}
}
