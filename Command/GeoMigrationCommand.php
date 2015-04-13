<?php

namespace Yit\GeoBridgeBundle\Command;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\Query\ResultSetMapping;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Yit\GeoBridgeBundle\Entity\Address;
use Symfony\Component\DependencyInjection\Container;

class GeoMigrationCommand extends ContainerAwareCommand
{
	const GEO_DOMAIN = 'http://geo.yerevan.am/';
//	const GEO_DOMAIN = 'http://geo.loc/app_dev.php/';

	/**
	 * This function is used to get content from $link
	 *
	 * @param $link
	 * @param null $context
	 * @return mixed|null|string
	 */
	private function getContent($link, $context = null)
	{
		// reads a file into a string.
		$content = @file_get_contents($link, false, $context);

		if ($content) {
			// content json decode
			$content = json_decode($content);

			if (isset($content->status) && $content->status != 404) {
				$content = null;
			}
		}
		else {
			$content = null;
		}

		return $content;
	}

	/**
	 * This function give configurations command: name and description
	 */
	protected function configure()
	{
		// definition of the command name and description
		$this->setName('geo:address:migration')
			 ->setDescription('GeoBridgeBundle synchronization data manager ');
	}

	/**
	 * @param InputInterface $input
	 * @param OutputInterface $output
	 */
	protected function execute(InputInterface $input, OutputInterface $output)
	{
		// Begin run command
		$output->writeln("<info>Starting synchronization GeoBridgeBundle and Geo data manager</info>");

		// get manager
		$em = $this->getContainer()->get("doctrine")->getManager();

		$connection = $em->getConnection();
		$databaseName = $connection->getDatabase();
		// use project database
		$margeParams['db'] = $databaseName;

		// create storage procedures MySql
        //  create new temp column (by adding 'geo_' prefix) for migration by given database, table and column names.
		$geoDataCreate = "DROP PROCEDURE IF EXISTS `GeoDataMigrationCreate` ;
						 CREATE PROCEDURE  `GeoDataMigrationCreate` ( IN  `tableName` VARCHAR( 255 ) ,
															   IN  `dbName` VARCHAR( 255 ) ,
															   IN  `columnName` VARCHAR( 255 ) )
						 COMMENT  'Create duplicated address column before column name add geo_' NOT DETERMINISTIC CONTAINS SQL SQL SECURITY DEFINER
						 	BEGIN
                                IF EXISTS( SELECT NULL
                                    FROM INFORMATION_SCHEMA.COLUMNS
                                    WHERE table_name = tableName
                                    AND table_schema = dbName
                                    AND column_name = columnName)
                                THEN
                                    SET @alter = CONCAT(  'ALTER TABLE ',tableName,' ADD geo_', columnName,' int(11);') ;
                                    PREPARE stmt FROM @alter ;
                                    EXECUTE stmt;
                                END IF;
                            END
						";

		// this storage procedure update column if exist column by parameters
		// if exist geo address column insert data from geo address columns to new created column`s
		$geoDataUpdate = "DROP PROCEDURE IF EXISTS `GeoDataMigrationUpdate` ;
						 CREATE PROCEDURE  `GeoDataMigrationUpdate` ( IN  `tableName` VARCHAR( 255 ) ,
															   IN  `dbName` VARCHAR( 255 ) ,
															   IN  `columnName` VARCHAR( 255 ) )
						 COMMENT  'Duplicated address data from geo address column to created column`s' NOT DETERMINISTIC CONTAINS SQL SQL SECURITY DEFINER
						 	BEGIN
                                IF EXISTS( SELECT NULL
                                    FROM INFORMATION_SCHEMA.COLUMNS
                                    WHERE table_name = tableName
                                    AND table_schema = dbName
                                    AND column_name = columnName)
                                THEN
                                    SET @update = CONCAT(  'UPDATE  ',tableName,' SET geo_', columnName,' = ', columnName,';') ;
                                    PREPARE stmt FROM @update ;
                                    EXECUTE stmt;
                                END IF;
                            END
						";

		// this storage procedure drop column if exist table by parameters
		// if exist geo address column create, update and drop old geo address columns
		$geoDataDrop = "DROP PROCEDURE IF EXISTS `GeoDataMigration` ;
						 CREATE PROCEDURE  `GeoDataMigration` ( IN  `tableName` VARCHAR( 255 ) ,
															   IN  `dbName` VARCHAR( 255 ) ,
															   IN  `columnName` VARCHAR( 255 ) )
						 COMMENT  'Call GeoDataMigrationCreate and GeoDataMigrationUpdate stored procedures and Drop old addresses column`s' NOT DETERMINISTIC CONTAINS SQL SQL SECURITY DEFINER
						 	BEGIN
                                CALL GeoDataMigrationCreate(tableName, dbName, columnName);
                                CALL GeoDataMigrationUpdate(tableName, dbName, columnName);
                                IF EXISTS( SELECT NULL
                                    FROM INFORMATION_SCHEMA.COLUMNS
                                    WHERE table_name = tableName
                                    AND table_schema = dbName
                                    AND column_name = columnName)
                                THEN
                                    SET @drop = CONCAT(  'ALTER TABLE ',tableName,' DROP  ', columnName,';') ;
                                    PREPARE stmt FROM @drop ;
                                    EXECUTE stmt;
                                END IF;
                            END
						";

		//create storage procedures
		$connection->executeUpdate($geoDataCreate, $margeParams);
		$connection->executeUpdate($geoDataUpdate, $margeParams);
		$connection->executeUpdate($geoDataDrop, $margeParams);

		//get entity`as we used geo address
		$entities = array('Company' => 'Ads\MainBundle\Entity\BaseCompany',
						  'Place' => 'Ads\MainBundle\Entity\PlaceAddress',);

		$tables = array();

		// find geo address fields
		foreach ($entities as $className => $entity) {

			// get entity name
            $tmpData = array('name' => $em->getClassMetadata($entity)->getTableName(), 'columns' => array());

			// get address columns
			$coums = $em->getClassMetadata($entity)->getAssociationsByTargetClass('Yit\GeoBridgeBundle\Entity\Address');

            if($coums && count($coums) > 0){

                foreach ($coums as $colum) {
                    // find join column field names
                    $tmpData['columns'][] = $colum['joinColumnFieldNames'];
                }

                $tables[] = $tmpData;
            }
		}

        // LOOP ALL TABLES
		foreach ($tables as $table) {
            foreach ($table['columns'] as $columnName) {
                // if exist geo address column create new column`s storage procedure

                // call storage procedure is create new columns, insert data from old columns in new columns and drop old geo address column`s
                $connection->executeUpdate("CALL GeoDataMigration('{$table['name']}', '$databaseName', '$columnName')");
            }
		}

		// get all geo address in company table
		$addressCompany = "SELECT geo_juridical_address, geo_working_address
								FROM ads.company";

		$sthCompany = $connection->prepare("$addressCompany");
		$sthCompany->execute();
		$resultCompany = $sthCompany->fetchAll();

		// get all geo addresses in place_address table
		$addressPlace = "SELECT geo_address
 						FROM ads.place_address
 						WHERE geo_address IS NOT NULL
 						";

		$sthPlace = $connection->prepare("$addressPlace");
		$sthPlace->execute();
		$resultPlace = $sthPlace->fetchAll();
		// get all geo addresses from ads project
		$result = array_merge($resultCompany, $resultPlace);
		// insert addresses in Geo Bridge Address table all project used addresses
		for ($i = 0; $i < count($result); $i++) {

			if(isset($result[$i]['geo_juridical_address'])){

				$addressIdJuridical = $result[$i]['geo_juridical_address'];
				// get address from Geo main project
				$addressJuridical = $this->getContent(self::GEO_DOMAIN . 'api/addresses/' . $addressIdJuridical . '');

				if (isset($addressJuridical) && $addressJuridical != null) {
					// insert addresses in in Geo Bridge Address table
					$connection->executeUpdate("CALL GeoDataModified($addressIdJuridical , '$addressJuridical->title')");
				}
				// if not exist address by id in Geo main project set null
				else {
					$addressJuridical = null;
					// insert or update addresses in in Geo Bridge Address table
					$connection->executeUpdate("CALL GeoDataModified($addressIdJuridical , '$addressJuridical')");
				}
			}
			if(isset($result[$i]['geo_working_address'])) {

				$addressIdWorking = $result[$i]['geo_working_address'];
				// get address from Geo main project
				$addressWorking = $this->getContent(self::GEO_DOMAIN . 'api/addresses/' . $addressIdWorking . '');

				if (isset($addressWorking) && $addressWorking != null) {
					// insert or update addresses in in Geo Bridge Address table
					$connection->executeUpdate("CALL GeoDataModified($addressIdWorking , '$addressWorking->title')");
				}
				// if not exist address by id in Geo main project set null
				else {
					$addressWorking = null;
					// insert or update addresses in in Geo Bridge Address table
					$connection->executeUpdate("CALL GeoDataModified($addressIdWorking , '$addressWorking')");
				}
			}

			if(isset($result[$i]['geo_address'])) {

				$addressIdPlace = $result[$i]['geo_address'];
				// get address from Geo main project
				$addressPlace = $this->getContent(self::GEO_DOMAIN . 'api/addresses/' . $addressIdPlace . '');

				if (isset($addressPlace) && $addressPlace != null) {
					// insert addresses in in Geo Bridge Address table
					$connection->executeUpdate("CALL GeoDataModified($addressIdPlace , '$addressPlace->title')");
				}
				// if not exist address by id in Geo main project set null
				else {
					$addressPlace = null;
					// insert or update addresses in in Geo Bridge Address table
					$connection->executeUpdate("CALL GeoDataModified($addressIdPlace , '$addressPlace')");
				}
			}
		}

		$finishSql = "ALTER TABLE company ADD COLUMN (juridical_address INT( 11 ), working_address INT( 11 ));
		ALTER TABLE place_address ADD COLUMN (address INT( 11 ));
		ALTER TABLE place_address ADD CONSTRAINT FK_D1F4EB9A22589DE2 FOREIGN KEY (address) REFERENCES yit_geo_address (address_id) ON DELETE SET NULL ;
		ALTER TABLE company ADD CONSTRAINT FK_D1F4EB9A22488DE2 FOREIGN KEY (juridical_address) REFERENCES yit_geo_address (address_id) ON DELETE SET NULL;
		ALTER TABLE company ADD CONSTRAINT FK_D1F4EB9A13888DE3 FOREIGN KEY (working_address) REFERENCES yit_geo_address (address_id) ON DELETE SET NULL;
		UPDATE company SET working_address = (
						SELECT address_id FROM yit_geo_address WHERE address_id = geo_working_address);
		UPDATE company SET juridical_address = (
						SELECT address_id FROM yit_geo_address WHERE address_id = geo_juridical_address);
		UPDATE place_address SET  address = (
						SELECT address_id FROM yit_geo_address WHERE address_id = geo_address);
		ALTER TABLE company DROP  geo_juridical_address, DROP  geo_working_address ;
		ALTER TABLE place_address DROP  geo_address;
		DROP PROCEDURE IF EXISTS  GeoDataMigrationCreate;
		DROP PROCEDURE IF EXISTS  GeoDataMigrationUpdate;
		DROP PROCEDURE IF EXISTS  GeoDataMigration;
		";

		$connection->executeUpdate($finishSql, $margeParams);

		$output->writeln("<info>GeoBridgeBundle and Geo synchronization success ..</info>");
	}

}
