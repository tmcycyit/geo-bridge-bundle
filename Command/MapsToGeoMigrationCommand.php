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

class MapsToGeoMigrationCommand extends ContainerAwareCommand
{
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

			if (isset($content->status) && $content->status == 204) {
				$content = null;
			}
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


	/**
	 * This function give configurations command: name and description
	 */
	protected function configure()
	{
		// definition of the command name and description
		$this->setName('geo:maps:address:migration')->setDescription('GeoBridgeBundle geo address migration data manager ');
	}

	/**
	 * @param InputInterface $input
	 * @param OutputInterface $output
	 */
	protected function execute(InputInterface $input, OutputInterface $output)
	{
		// Begin run command
		$output->writeln("<info>Starting GeoBridgeBundle and Project migration</info>");

		// get manager
		$em = $this->getContainer()->get("doctrine")->getManager();

		$connection = $em->getConnection();
		$databaseName = $connection->getDatabase();
		// use project database
		$margeParams['db'] = $databaseName;

		//This storage procedure to check exist column by database name, table name and column name
		//if exist return 1 else return 0
		$geoExistTable = "DROP PROCEDURE IF EXISTS `GeoExist` ;
						 CREATE PROCEDURE  `GeoExist` ( IN  `tableName` VARCHAR( 255 ) ,
															   IN  `dbName` VARCHAR( 255 ) ,
															   IN  `columnName` VARCHAR( 255 ))
						 COMMENT  'Check column' NOT DETERMINISTIC CONTAINS SQL SQL SECURITY DEFINER
						 	BEGIN
						DECLARE result INT(11);
							IF EXISTS( SELECT NULL
									  FROM INFORMATION_SCHEMA.COLUMNS
									  WHERE table_name = tableName
									  AND table_schema = dbName
									  AND column_name = columnName)
							THEN
								SET result = 1;
							ELSE
								SET result = 0;
							END IF;
								SELECT result;
						END
						";

		// create storage procedures MySql
		// create new temp column (by adding 'geo_' prefix) for migration by given database, table and column names.
		// copy data from olt columns to new columns
		// drop old columns
		$geoDataDrop = "DROP PROCEDURE IF EXISTS `GeoDataMigration` ;
						 CREATE PROCEDURE  `GeoDataMigration` ( IN  `tableName` VARCHAR( 255 ) ,
															   IN  `dbName` VARCHAR( 255 ) ,
															   IN  `columnName` VARCHAR( 255 ) )
						 COMMENT  'Stored procedures crate, update and drop column`s' NOT DETERMINISTIC CONTAINS SQL SQL SECURITY DEFINER
						 	BEGIN
                                IF EXISTS( SELECT NULL
                                    FROM INFORMATION_SCHEMA.COLUMNS
                                    WHERE table_name = tableName
                                    AND table_schema = dbName
                                    AND column_name = columnName)
                                THEN
                                    BEGIN
										SET @alter = CONCAT(  'ALTER TABLE ',tableName,' ADD geo_', columnName,' VARCHAR( 255 )  CHARSET utf8 ;') ;
										PREPARE stmt FROM @alter ;
										EXECUTE stmt;
                                    END;
                                    BEGIN
										SET @update = CONCAT(  'UPDATE  ',tableName,' SET geo_', columnName,' = ', columnName,';') ;
										PREPARE stmt FROM @update ;
										EXECUTE stmt;
                                    END;
									BEGIN
										SET @drop = CONCAT('ALTER TABLE ',tableName,' DROP ', columnName,';') ;
										PREPARE stmt FROM @drop ;
										EXECUTE stmt;
                                    END;
                                END IF;
                            END
						";



		//create storage procedures
		$connection->executeUpdate($geoExistTable, $margeParams);
		$connection->executeUpdate($geoDataDrop, $margeParams);

		//get entity`as we used geo address
		$entities = array();

		// get all entity`s in project
		$metas = $em->getMetadataFactory()->getAllMetadata();

		foreach ($metas as $meta) {
			// get entity`s names
			$entities[] = $meta->getName();
		}

		$tables = array();

		// find geo address fields
		foreach ($entities as $className => $entity) {

			// get entity table name
			$tmpData = array('name' => $em->getClassMetadata($entity)->getTableName(), 'columns' => array());

			// get address columns if entity related to YitGeoBridgeBundle:Address
			$coums = $em->getClassMetadata($entity)->getAssociationsByTargetClass('Yit\GeoBridgeBundle\Entity\Address');

			if ($coums && count($coums) > 0) {

				foreach ($coums as $colum) {
					// find join column field names
					$tmpData['columns'][] = $colum['joinColumnFieldNames'];
				}
				$tables[] = $tmpData;
			}
		}
		// stare transaction
		$connection->beginTransaction();

		try {
			// address id`s in project
			$idsResults = array();

			// get table form tables
			foreach ($tables as $table) {
				// get columns by table
				foreach ($table['columns'] as $columnNames) {

					foreach ($columnNames as $columnName) {

						// call "GeoDataMigration" that creates new columns,
						// inserts data from old columns into new columns and drop old geo address columns
						$update = "UPDATE " .$table['name']." SET " . $columnName . "  = (SELECT content
									FROM " .$table['name']."_translation
									WHERE " .$table['name']."_translation.object_id = " .$table['name'].".id
									AND " .$table['name']."_translation.locale = 'am'
									AND  " .$table['name']."_translation.field = 'address' );"
						;

						$connection->executeUpdate($update, $margeParams);

						$connection->executeUpdate("CALL GeoDataMigration('{$table['name']}', '$databaseName', '$columnName')");

						// let's check does new column "geo_$columnName" exit or not
						$sthExist = $connection->prepare("CALL GeoExist(?, ?, ?)");

						$newColumnName = 'geo_' . $columnName;

						$sthExist->bindParam(1, $table['name'], \PDO::PARAM_STR | \PDO::PARAM_INPUT_OUTPUT, 50);
						$sthExist->bindParam(2, $databaseName, \PDO::PARAM_STR | \PDO::PARAM_INPUT_OUTPUT, 50);
						$sthExist->bindParam(3, $newColumnName, \PDO::PARAM_STR | \PDO::PARAM_INPUT_OUTPUT, 50);
						$sthExist->execute();
						$exist = $sthExist->fetch();
						$sthExist->closeCursor();

						if (isset($exist['result']) && $exist['result'] > 0) {

							// get addresses id`s in project
							$addressCompany = " SELECT " . $table['name'] . "_translation.content as geo_address, SUBSTRING_INDEX(  `coordinates` ,  ',', 1 ) as latitude , SUBSTRING_INDEX(  `coordinates` ,  ',', -1 ) as longitude
												FROM  " . $databaseName . "." . $table['name'] . "
												LEFT JOIN " . $table['name'] . "_translation ON " . $table['name'] . ".id = " . $table['name'] . "_translation.object_id
												WHERE " . $table['name'] . "_translation.locale = 'am'
												AND " . $table['name'] . "_translation.field = 'address'
												AND " . $table['name'] . "_translation.content IS NOT NULL
												AND " . $table['name'] . "_translation.content != '';
										";
						}

						$sthCompany = $connection->prepare("$addressCompany");

						$sthCompany->execute();
						$idsResults[] = $sthCompany->fetchAll();
						$sthCompany->closeCursor();
					}
				}
			}

			$connection->commit();
		} //then something wrong
		catch (\Exception $e) {
			//rollback to the previously stable state
			$connection->rollback();
			//restore database to its original state.
			throw $e;
		}

		// get addresses id`s in project, get addresses string from main Geo project and insert or update in Yit:GeoBridgeBundle:Address
		foreach ($idsResults as $idResult) {
			//get id from id`s
			foreach ($idResult as $ids) {

			if($ids["geo_address"] != null)
			{
				$mapsAddress = $ids["geo_address"];
				$cleanAddress = mb_eregi_replace('</p>','', mb_eregi_replace('<p>','', mb_eregi_replace('&nbsp;','', mb_eregi_replace('<span class="st">','', mb_eregi_replace('</span>','', mb_eregi_replace('<br />','', $mapsAddress))))));

				// get Geo main project domain from this project config if config is empty default set http://geo.yerevan.am/
				$geoDomain = $this->getContainer()->getParameter('yit_geo_bridge.project_domain');
				// check address exist in Geo project
				$opts = array('http' =>
					array('method' => 'PUT',
						'content' => http_build_query(
							array('project_name' => $this->getContainer()->getParameter('yit_geo_bridge.project_name')))));
				$hNumber = $this->produceUrlParameter($cleanAddress);

					if($ids["latitude"] != null && $ids["longitude"] != null)
					{
						$ladit = $this->produceUrlParameter($ids["latitude"]);
						$lodit = $this->produceUrlParameter($ids["longitude"]);
					}
					else
					{
						$ladit = $this->produceUrlParameter(40.179496298328);
						$lodit = $this->produceUrlParameter(44.512739181518);
					}

				$context = stream_context_create($opts);

				$addresses = $this->getContent($geoDomain . "api/put/address/" . $hNumber . "/" . $ladit . "/" . $lodit, $context);
				$addressEng = null;

				if (isset($addresses) && $addresses != null) {

					if (isset($mapsAddress) && $mapsAddress != null) {
						if(isset( $ids["latitude"]) &&  $ids["latitude"] != null && isset($ids["longitude"]) && $ids["longitude"] != null)
						{
							$latitude = $ids["latitude"];
							$longitude = $ids["longitude"];
						}
						else
						{
							$latitude = 40.179496298328;
							$longitude = 44.512739181518;
						}
					}
					else {
							$latitude = null;
							$longitude = null;
					}

					// insert address in YitGeoBridgeBundle:Address
					$connection->executeUpdate("CALL GeoDataModified($addresses , '$mapsAddress', '$addressEng', '$latitude', '$longitude')");
				}
			}

		}
	}
		// create MySQL storage procedure
		// This storage procedure create columns for relation, create relations, insert data, drop temp column`s
		$geoDataRelation = "DROP PROCEDURE IF EXISTS `CreateGeoRelation` ;
						 CREATE PROCEDURE  `CreateGeoRelation` ( IN  `tableName` VARCHAR( 255 ) ,
															   IN  `dbName` VARCHAR( 255 ) ,
															   IN  `columnName` VARCHAR( 255 ) )
						 COMMENT  'stored procedures create relations, insert data, drop temp column`s ' NOT DETERMINISTIC CONTAINS SQL SQL SECURITY DEFINER
						 	BEGIN
						 		DECLARE mainColumn VARCHAR(255);
                                IF EXISTS( SELECT NULL
                                    FROM INFORMATION_SCHEMA.COLUMNS
                                    WHERE table_name = tableName
                                    AND table_schema = dbName
                                    AND column_name = columnName)
                                THEN
								SET mainColumn = SUBSTRING(columnName, 5);
                                	BEGIN
										SET @alter = CONCAT('ALTER TABLE ',tableName,' ADD ', mainColumn,' int(11)') ;
										PREPARE stmt FROM @alter ;
										EXECUTE stmt;
                                    END;
                                    BEGIN
										SET @relation = CONCAT( 'ALTER TABLE ',tableName,' ADD CONSTRAINT FK_D1F', mainColumn,'',tableName,' FOREIGN KEY (', mainColumn,') REFERENCES yit_geo_address (id) ON DELETE SET NULL') ;
										PREPARE stmt FROM @relation ;
										EXECUTE stmt;
                                    END;
                                    BEGIN
										SET @update = CONCAT('UPDATE ',tableName,' SET ',mainColumn,' = (
																SELECT id FROM yit_geo_address WHERE arm_name = ', columnName,' COLLATE utf8_unicode_ci LIMIT 1)');
										PREPARE stmt FROM @update ;
										EXECUTE stmt;
                                    END;
									BEGIN
										SET @drop = CONCAT( 'ALTER TABLE ',tableName,' DROP ',columnName,' ') ;
										PREPARE stmt FROM @drop ;
										EXECUTE stmt;
                                    END;
                                END IF;
                            END
						";

		//create CreateGeoRelation storage procedure
		$connection->executeUpdate($geoDataRelation, $margeParams);

		$connection->beginTransaction();

		try {
			// get related tables
			foreach ($tables as $table) {

				//get related columns by tables
				foreach ($table['columns'] as $columnNames) {

					foreach ($columnNames as $columnName) {
						// call storage procedure is create new columns, relations, insert or update data, drop temp columns
						$connection->executeUpdate("CALL CreateGeoRelation('{$table['name']}', '$databaseName', 'geo_$columnName')");
					}
				}
			}
			$connection->commit();
		} //then something wrong
		catch (\Exception $e) {
			//rollback to the previously stable state
			$connection->rollback();
			//restore database to its original state.
			throw $e;
		}

		// drop storage procedures created for migration
		$finishSql = "DROP PROCEDURE IF EXISTS  GeoDataMigration;
		DROP PROCEDURE IF EXISTS  GeoExist;
		DROP PROCEDURE IF EXISTS  CreateGeoRelation;
		";

		$connection->executeUpdate($finishSql, $margeParams);

		$output->writeln("<info>GeoBridgeBundle and Project migration success ..</info>");
	}

}
