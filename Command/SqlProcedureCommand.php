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

class SqlProcedureCommand extends ContainerAwareCommand
{

	protected function configure()
	{
		$this
			->setName('stored:procedure:run')
			->setDescription('Run procedure SQL To DB');
	}

	/**
	 * This function create and update sql storage procedure`s
	 * @param InputInterface $input
	 * @param OutputInterface $output
	 */
	protected function execute(InputInterface $input, OutputInterface $output)
	{

		$output->writeln("<info>Starting</info>");
		$em = $this->getContainer()->get("doctrine")->getManager();

		$db = $em->getConnection()->getDatabase();

		$margeParams['db'] = $db;
		$deleteMarged ="DROP PROCEDURE IF EXISTS  `DeleteMarged`";
		$em->getConnection()->executeUpdate($deleteMarged, $margeParams);

		$delateMarged = "CREATE PROCEDURE DeleteMarged (IN  old_id INT( 11 ))
				COMMENT 'My sql procedure' NOT DETERMINISTIC MODIFIES SQL DATA SQL SECURITY DEFINER
				DELETE FROM ".$db.".yit_geo_address WHERE yit_geo_address.addressId = old_id";
		$em->getConnection()->executeUpdate($delateMarged, $margeParams);

		$relations = " SELECT CONSTRAINT_SCHEMA, TABLE_NAME, COLUMN_NAME, REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME
 								FROM information_schema.KEY_COLUMN_USAGE
								WHERE CONSTRAINT_SCHEMA = :db
								AND REFERENCED_TABLE_SCHEMA  IS NOT NULL
								AND REFERENCED_TABLE_NAME = :tables
								AND REFERENCED_COLUMN_NAME IS NOT NULL
								";

		$sth = $em->getConnection()->prepare("$relations");

		$params['db'] = $db;
		$params['tables'] = 'yit_geo_address';
		$sth->execute($params);
		$result = $sth->fetchAll();
		for($i = 0;$i<count($result); $i++) {
			$table = $db . '.' . $result[$i]['TABLE_NAME'];
			$columnName = $result[$i]['COLUMN_NAME'];
			$deleteUpdateMarged ="DROP PROCEDURE IF EXISTS  UpdateMarged".$i." ";
			$em->getConnection()->executeUpdate($deleteUpdateMarged, $margeParams);
			$updateMarged = "CREATE	PROCEDURE  UpdateMarged".$i." (IN  old_id INT( 11 ) , IN  new_id INT( 11 ) )
				COMMENT 'My sql procedure' NOT DETERMINISTIC MODIFIES SQL DATA SQL SECURITY DEFINER
				UPDATE  " . $table . " SET ".$columnName." =  new_id  WHERE ".$columnName." =  old_id";
			$updateParams['table'] = $table;
			$updateParams['columnName'] = $columnName;

			$em->getConnection()->executeUpdate($updateMarged, $updateParams);
		}

		$output->writeln("<info>Run procedure SQL To DB is Success!</info>");
	}
}