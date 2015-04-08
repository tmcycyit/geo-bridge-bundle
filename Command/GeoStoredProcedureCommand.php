<?php

namespace Yit\GeoBridgeBundle\Command;

use Doctrine\ORM\Query\ResultSetMapping;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class GeoStoredProcedureCommand
 * @package Yit\GeoBridgeBundle\Command
 */
class GeoStoredProcedureCommand extends ContainerAwareCommand
{

	protected function configure()
	{
		$this
			->setName('geo:manage:stored:procedure')
			->setDescription('Run manage GeoBridgeBundle Stored Procedure mySql');
	}

	/**
	 * This function create and update sql storage procedure`s
	 * @param InputInterface $input
	 * @param OutputInterface $output
	 */
	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$output->writeln("<info>Starting manage GeoBridgeBundle Stored Procedure mySql</info>");
		$em = $this->getContainer()->get("doctrine")->getManager()->getConnection();

		//get database
		$db = $em->getDatabase();

		$margeParams['db'] = $db;

		// create Geo Data Modified storage procedure
		$geoDataModified = "DROP PROCEDURE IF EXISTS `GeoDataModified` ;
						 CREATE PROCEDURE  `GeoDataModified` ( IN  `real_id` INT( 11 ) ,
															   IN  `address` VARCHAR( 255 ) )
						 COMMENT  'Geo data modified stored procedure' NOT DETERMINISTIC CONTAINS SQL SQL SECURITY DEFINER
						 	BEGIN
						 DECLARE geo_address_id DOUBLE;
							DECLARE EXIT HANDLER FOR SQLEXCEPTION
							 BEGIN
								  ROLLBACK;
							 END;
							START TRANSACTION;

						 SELECT id INTO geo_address_id
						FROM  `yit_geo_address` WHERE address_id = real_id;

						IF geo_address_id > -1 THEN
						UPDATE  `yit_geo_address` SET address = address, updated = NOW() WHERE address_id = real_id;
						ELSE
						INSERT INTO  `yit_geo_address` (  `id` ,  `address_id` ,  `address` ,  `created` ,  `updated` )
						VALUES (
						NULL , real_id, address, NOW( ) , NOW( )
						);
						END IF ;
						COMMIT ;
						END
						";

		$em->executeUpdate($geoDataModified, $margeParams);

		// create Geo Data Manager storage procedure
		$geoDataManager = "DROP PROCEDURE IF EXISTS `GeoDataManager` ;
						CREATE PROCEDURE  `GeoDataManager` ( IN  `table_name` VARCHAR( 100 ) ,
															 IN  `column_name` VARCHAR( 100 ) ,
															 IN  `merged_id` INT( 11 ) ,
															 IN  `real_id` INT( 11 ) ,
															 IN  `address` VARCHAR( 255 ) )
					    COMMENT 'Geo data manager stored procedure'
					    NOT DETERMINISTIC MODIFIES SQL DATA SQL SECURITY DEFINER
						BEGIN
							DECLARE EXIT HANDLER FOR SQLEXCEPTION
						 		BEGIN
							  		ROLLBACK;
						 		END;
							START TRANSACTION;
					     CALL GeoDataModified(real_id, address);
						SET @update = CONCAT(  'UPDATE ', table_name,  ' SET ', column_name,  '= (
                     		SELECT id FROM yit_geo_address WHERE address_id = ', real_id,  ')
                     	WHERE ', column_name,  '= (
                     		SELECT id FROM yit_geo_address WHERE address_id = ', merged_id,  ')' ) ;
						PREPARE stmt FROM @update ;
						EXECUTE stmt;
						DELETE FROM yit_geo_address WHERE yit_geo_address.address_id = merged_id;
							COMMIT ;
						END";

		$em->executeUpdate($geoDataManager, $margeParams);

		$output->writeln("<info>GeoBridgeBundle storage procedures successfully created or updated!</info>");
	}
}