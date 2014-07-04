GeoBridgeBundle
======================

## Installation
-----------------------

### Step1: Download GeoBridgeBundle using composer

Add GeoBridgeBundle in your composer.json:

```js
{
    "require": {
        "yit/geo-bridge-bundle": "dev-master",
    }
}
```

Now update composer.

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

### Step 3: If you will use geo_address form field in your project

Add the following configuration in app/config/config.yml

``` yaml
# app/config/config.yml
twig:
    /...
    form:
            resources:
                - 'YitGeoBridgeBundle:Geo:address.html.twig'


### Step 4: If you will use geo_address form field in your project

Import GeoBridgeBundle routing files

``` yaml
# app/config/routing.yml
yit_geo_bridge:
    resource: "@YitGeoBridgeBundle/Resources/config/routing.yml"
    prefix:   /


### Now the bundle is configured and ready to use, if you need to use in entity address, street or district which will
a relation with GeoBundle addresses, streets and districts, then you will implements the Addressable, Streetable and
Districtable interfaces accordingly in your interfaces. And than when you will load your entity from db, GeoBridgeBundle
will automatically call interface functions with corresponding arguments, to set all necessary information.

### The interface implementations here`


namespace Yit\GeoBridgeBundle\Model;
interface Addressable
{
    public function getAddressId();
    public function setAddressTitle($title);
    public function setAddresLatitude($latitude);
    public function setAddresLongitude($longitude);
    public function setAddressHNumber($hNumber);
    public function setAddressEngType($engType);
}


namespace Yit\GeoBridgeBundle\Model;
interface Districtable
{
    public function getDistrictId();
    public function setDistrictTitle($title);
}


namespace Yit\GeoBridgeBundle\Model;
interface Streetable
{
    public function getStreetId();
    public function setStreetArmName($armName);
    public function setStreetEngName($engName);
}

### You can also use 'geo-bridge' service to use some functions, there are here`

# This function return address object by given id
# If there are not any address with such id return null
public function getAddressById($id)

# This function is used to get $limit addresses by $search string
# If there are not any address with such content return null
public function searchAddress($search, $limit = 0)

# This function is used to create new address in Geo Project with $addressString title
# when access return id of created Address else return null
public function putAddress($addressString)

# This function return district object by given id
# If there are not any district with such id return null
public function getDistrictById($id)

# This function is used to get districts
# If there are not any district return null
public function getDistricts()

# This function is used to get districts in array 'id' => 'title'
# If there are not any district return empty array
public function getDistrictList()

# This function is used to get all streets by district id
# If there are not any street return null
public function getStreetsByDistrict($districtID)

# This function is used get street by given id
# If there are not any street by given id return null
public function getStreetById($id)



