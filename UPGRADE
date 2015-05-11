# Upgrade to 1.1.0

## Add entity Address
before 1.1.0 version if use address need create relation ManyToOne to Address entity
example

    /**
     * @var integer
     * @ORM\ManyToOne(targetEntity="Yit\GeoBridgeBundle\Entity\Address")
     * @ORM\JoinColumn(name="address", nullable=true)
     * @Grid\Column(field="geoAddress.armName")
     */
    protected $geoAddress;

Address entity have fields :
            $addressId, $armName, $engName, $latitude, $longitude, $created, $updated

## Remove AddressableInterface
## Remove MultiAddressableInterface
## Remove AddressChangeableInterface
## Rename AddressDistrictableInterfaceToShow to AddressDistrictAwareInterface
## Rename AddressStreetableInterfaceToChange to AddressStreetAwareInterface
## Add service yit_geo_address_trasnformer
## Add in yit_gro service function getAddressObjectById($id), this service return address object by address Id
## If use 'geo_address' form type

// in form type
//namespace YourProject\YourBundle\Form\Type\YourFormType;

 $transformer = $this->container->get('yit_geo_address_trasnformer');

        // add a normal text field, but add your transformer to it
        $builder->add(
            $builder->create('address', 'geo_address')
                ->addModelTransformer($transformer));
        ;
 // in controller call YourFormType

 $form = $this->createForm(new YourFormType($this->container));

## Update from 1.0.0 to 1.1.0
### Step 1 Configure project

#### Step 1.1 Configure composer.json
change in composer.json
    "require": {
        "yit/geo-bridge-bundle": "1.0.0",
    }
   // to
    "require": {
                    "yit/geo-bridge-bundle": "1.1.0",
                 }
    "scripts": {
            "post-install-cmd": [

                "Yit\\GeoBridgeBundle\\Command\\ManageGeoStoredProcedureCommand::manageGeoStoredProcedure"
            ],
"post-update-cmd": [

                 "Yit\\GeoBridgeBundle\\Command\\ManageGeoStoredProcedureCommand::manageGeoStoredProcedure"
                 ],
             }
#### Warning. If you use  AddressableInterface, MultiAddressableInterface, AddressChangeableInterface Interfaces remove it.
#### Update composer
### Step 2 Database config
#### Step 2.1 Run mysql command
    CREATE TABLE yit_geo_address (id INT NOT NULL, arm_name VARCHAR(255) DEFAULT NULL, eng_name VARCHAR(255) DEFAULT NULL, latitude NUMERIC(10, 7) DEFAULT NULL, longitude NUMERIC(10, 7) DEFAULT NULL, created DATETIME DEFAULT NULL, updated DATETIME DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB;
#### Warning. If you use unique kay in address fields remove it.

#### Step 2.2 Create Mysql storage procedure
     run command $ php app/console geo:manage:stored:procedure
                 $ php app/console geo:address:migration
                 $ php app/console doctrine:schema:update --force


## Enter configure config.yml
By default this config use "http://geo.yerevan.am/" . You can use dev or local domain
            example
                yit_geo_bridge:
                    project_domain: http://dev.geo.yerevan.am/


