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
        } else {
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
            ->setDescription('GeoBridgeBundle geo address migration data manager ');
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
        //  create new temp column (by adding 'geo_' prefix) for migration by given database, table and column names.
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
										SET @alter = CONCAT(  'ALTER TABLE ',tableName,' ADD geo_', columnName,' int(11);') ;
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
                        $connection->executeUpdate("CALL GeoDataMigration('{$table['name']}', '$databaseName', '$columnName')");

                        // let's check does new column "geo_$columnName" exit or not
                        $sthExist = $connection->prepare("CALL GeoExist('{$table['name']}', '$databaseName', 'geo_$columnName')");

                        // give parameters
                        $params['tableName'] = $table['name'];
                        $params['database_name'] = $databaseName;
                        $params['columnName'] = "geo_{$columnName}";

                        // execute parameters
                        $sthExist->execute($params);
                        $exist = $sthExist->fetch();
                        $sthExist->closeCursor();

                        if (isset($exist['result']) && $exist['result'] > 0) {
                            // get addresses id`s in project
                            $addressCompany = "SELECT geo_" . $columnName . "
											FROM " . $databaseName . "." . $table['name'] . "
											WHERE geo_" . $columnName . " IS NOT NULL
											";
                            $sthCompany = $connection->prepare("$addressCompany");
                            $sthCompany->execute();
                            $idsResults[] = $sthCompany->fetchAll();
                            $sthCompany->closeCursor();
                        }
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

                foreach ($ids as $id) {

                    if (isset($id) && $id != null) {
                        // connect to main Geo project, get addresses by id
                        $addresses = $this->getContent(self::GEO_DOMAIN . 'api/addresses/' . $id . '');

                        // checking address title
                        if (isset($addresses->title) && $addresses->title != null) {
                            $address = $addresses->title;
                        } else {
                            $address = null;
                        }

                        // insert address in YitGeoBridgeBundle:Address
                        $connection->executeUpdate("CALL GeoDataModified($id , '$address')");
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
										SET @alter = CONCAT('ALTER TABLE ',tableName,' ADD ', mainColumn,' int(11);') ;
										PREPARE stmt FROM @alter ;
										EXECUTE stmt;
                                    END;
                                    BEGIN
										SET @relation = CONCAT( 'ALTER TABLE ',tableName,' ADD CONSTRAINT FK_D1F', mainColumn,' FOREIGN KEY (', mainColumn,') REFERENCES yit_geo_address (address_id) ON DELETE SET NULL;') ;
										PREPARE stmt FROM @relation ;
										EXECUTE stmt;
                                    END;
                                    BEGIN
										SET @update = CONCAT('UPDATE ',tableName,' SET ',mainColumn,' = (
																SELECT address_id FROM yit_geo_address WHERE address_id = ', columnName,');');
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
