<?php
/**
 * Created by PhpStorm.
 * User: tigran
 * Date: 3/30/15
 * Time: 5:17 PM
 */

namespace Yit\GeoBridgeBundle\Command;


use Doctrine\ORM\Query\ResultSetMapping;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

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
						 CREATE PROCEDURE  `GeoDataModified` ( IN  `realId` INT( 11 ) ,
															   IN  `address` VARCHAR( 255 ) )
						 COMMENT  'Geo data modified stored procedure' NOT DETERMINISTIC CONTAINS SQL SQL SECURITY DEFINER
						 BEGIN
						 DECLARE created_item DOUBLE;
						 SELECT id INTO created_item
						FROM  `yit_geo_address` WHERE addressId = realId;

						IF created_item > -1 THEN
						UPDATE  `yit_geo_address` SET address = address, updated = NOW() WHERE addressId = realId;
						ELSE
						INSERT INTO  `yit_geo_address` (  `id` ,  `addressId` ,  `address` ,  `created` ,  `updated` )
						VALUES (
						NULL , realId, address, NOW( ) , NOW( )
						);
						END IF ;
						COMMIT ;
						END
						";

		$em->executeUpdate($geoDataModified, $margeParams);

		// create Geo Data Manager storage procedure
		$geoDataManager = "DROP PROCEDURE IF EXISTS `GeoDataManager` ;
						CREATE PROCEDURE  `GeoDataManager` ( IN  `column_name` VARCHAR( 100 ) ,
															 IN  `newsInfoTable` VARCHAR( 100 ) ,
															 IN  `oldId` INT( 11 ) ,
															 IN  `newId` INT( 11 ) ,
															 IN  `address` VARCHAR( 255 ) )
					    COMMENT 'Geo data manager stored procedure'
					    NOT DETERMINISTIC MODIFIES SQL DATA SQL SECURITY DEFINER
							BEGIN
					     CALL GeoDataModified(newId, address);
						SET @update = CONCAT(  'UPDATE ', newsInfoTable,  ' SET ', column_name,  '= (
                     		SELECT id FROM yit_geo_address WHERE addressId = ', newId,  ')
                     	WHERE ', column_name,  '= (
                     		SELECT id FROM yit_geo_address WHERE addressId = ', oldId,  ')' ) ;
						PREPARE stmt FROM @update ;
						EXECUTE stmt;
						DELETE FROM yit_geo_address WHERE yit_geo_address.addressId = oldId;
						COMMIT ;
						END";
		$em->executeUpdate($geoDataManager, $margeParams);

		$output->writeln("<info>GeoBridgeBundle storage procedures successfully created or updated!</info>");
	}
}