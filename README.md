GeoBridgeBundle
======================

## Installation
-----------------------

### Step1: Download GeoBridgeBundle using composer

Add GeoBridgeBundle in your composer.json:

```js
{
    "require": {
        ...
        "yit/geo-bridge-bundle": "dev-master",
    }
}
```

Now update composer.phar

Composer will install the bundle to your project's `vendor/yit` directory.

### Step 2: Enable the bundle

Enable the bundle in the kernel:

``` php
<?php
// app/AppKernel.php

public function registerBundles()
{
    $bundles = array(
        // ...
        new Yit\GeoBridgeBundle\YitGeoBridgeBundle(),
    );
}
```

### If you will use geo_address form field in your project

#### Step 3: Add the following configuration in app/config/config.yml

``` yaml
# app/config/config.yml
twig:
    /...
    form:
            resources:
                - 'YitGeoBridgeBundle:Geo:address.html.twig'
```

#### Step 4: Import GeoBridgeBundle routing files

``` yaml
# app/config/routing.yml
yit_geo_bridge:
    resource: "@YitGeoBridgeBundle/Resources/config/routing.yml"
    prefix:   /
```

And then add YitAutocomplete Angularjs module from Bower

Then you can add a new form field`

``` php
$this->createFormBuilder()
             ...
             ->add('address', 'geo_address', array('attr' => array('data_id' => 1)))
             ...
```

data_id attribute must be unique for each geo_address field.
you can also add other attributes to the form field.

``` php
$this->createFormBuilder()
             ...
             ->add('address', 'geo_address', array(
                'attr' => array(
                    'data_id'       => 1,                   //must be unique for each such field
                    'placeholder'   => 'text for input',    //placeholder text
                    'allow_new'     => true,                //set true to show new button when address not found
                    'button_name'   => 'buttonName',        //text to show on new button
                    'button_class'  => 'buttonClass',       //new button class
                    'input_class'   => 'inputClass'         //input class
                )))
             ...
```

Now the bundle is configured and ready to use, if you need to use in entity address, street or district which will
a relation with GeoBundle addresses, streets and districts, then you will implements the AddressableInterface,
StreetableInterface, MultiAddressableInterface, DistrictableInterface and other interfaces accordingly in your entites.
And than when you will load your entity from db, GeoBridgeBundle will automatically call interface functions with
corresponding arguments, to set all necessary information.

## The interface implementations here`

``` php
namespace Yit\GeoBridgeBundle\Model;

//This interface is used when entity has an address_id field
interface AddressableInterface
{
    //This function is used to get address id, the fields of which must be injected
    public function getAddressId();

    //This function is used to inject address title
    public function setAddressTitle($title);

    //This function is used to inject address latitude
    public function setAddresLatitude($latitude);

    //This function is used to inject address longitude
    public function setAddresLongitude($longitude);

    //This function is used to inject address h_number
    public function setAddressHNumber($hNumber);

    //This function is used to inject address eng_type
    public function setAddressEngType($engType);
}


namespace Yit\GeoBridgeBundle\Model;

//This interface is used when entity has an district_id field
interface DistrictableInterface
{
    //This function is used to get district id, the fields of which must be injected
    public function getDistrictId();

    //This function is used to inject district title
    public function setDistrictTitle($title);
}


namespace Yit\GeoBridgeBundle\Model;

//This interface is used when entity has more than one address
interface MultiAddressableInterface
{
    //This function is used to get ids of addresses that must be injected
    public function getAddressIds();

    //This function inject addresses
    public function setAddresses(array $addresses);
}


namespace Yit\GeoBridgeBundle\Model;

//This interface is used when entity has street_id field
interface StreetableInterface
{
    //This function is used to get street id, the fields of which must be injected
    public function getStreetId();

    //This function is used to inject street arm_name
    public function setStreetArmName($armName);

    //This function is used to inject street eng_name
    public function setStreetEngName($engName);
}


namespace Yit\GeoBridgeBundle\Model;

//This interface is used when entity has an address_id and district_id
//fields to set district id based on address id
interface AddressDistrictableInterface
{
    //This function is used to get address id
    public function getAddressId();

    //This function is used to inject district id
    public function setDistrictId($id);

    //This function is used to get district id
    public function getDistrictId();
}


namespace Yit\GeoBridgeBundle\Model;

//This interface is used to change address_id field
interface AddressChangeableInterface
{
    //This function is used to get address id, the fields of which must be injected
    public function getAddressId();

    //This function is used to set Address Id
    public function setAddressId($addressId);

    //This function is used to get AddressId field name
    public function getAddressField();
}


namespace Yit\GeoBridgeBundle\Model;

/**
 * This interface is used when entity has an address_id and district_id
 * fields to set district id based on address id
 * district id is set from geo project, and if it is empty persist it, if does not match only set district id without persist
 */
interface AddressDistrictableInterfaceToShow extends AddressDistrictableInterface
{

}


namespace Yit\GeoBridgeBundle\Model;

/**
 * This interface is used when entity has an address_id and street_id
 * fields to set street id based on address id
 * street id is set from geo project and persist it is empty
 */
interface AddressStreetableInterface
{
    //This function is used to get address id
    public function getAddressId();

    //This function is used to inject street id
    public function setStreetId($id);

    //This function is used to get street id
    public function getStreetId();

    //This function is used to get streetId field name
    public function getStreetFieldName();
}


namespace Yit\GeoBridgeBundle\Model;

/**
 * This interface is used when entity has an address_id and street_id
 * fields to set street id based on address id
 * street id is set from geo project and persist it is empty, if does not match only set street id without persist
 */
interface AddressStreetableInterfaceToChange extends AddressStreetableInterface
{

}
```

### You can also use 'yit_geo' service to use some functions, there are here`

``` php
//This function return address object by given id
//If there are not any address with such id return null
public function getAddressById($id)

//This function is used to get $limit addresses by $search string
//If there are not any address with such content return null
public function searchAddress($search, $limit = 0)

//This function is used to create new address in Geo Project with $addressString title
//when access return id of created Address else return null
public function putAddress($addressString)

//This function return district object by given id
//If there are not any district with such id return null
public function getDistrictById($id)

//This function is used to get districts
//If there are not any district return null
public function getDistricts()

//This function is used to get districts in array 'id' => 'title'
//If there are not any district return empty array
public function getDistrictList()

//This function is used to get all streets by district id
//If there are not any street return null
public function getStreetsByDistrict($districtID)

//This function is used get street by given id
//If there are not any street by given id return null
public function getStreetById($id)

//This function is used to get $limit streets by $search string
//If there are not any street with such content return null
public function searchStreet($search, $limit = 0)
```

The bundle use apc_cache and by default save addresses in the cache during 24 hours,
you can change the time of experience by add the fallowing in your config.yml

```yml
yit_geo_bridge:
    experience: experience_time_in_seconds
```

You can also add project name in the config to save it in the geo project, this
done by add the following in the config.yml

```yml
yit_geo_bridge:
    project_name: your_project_name
```




