<?php

namespace Yit\GeoBridgeBundle\Command;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\Query\ResultSetMapping;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Yit\GeoBridgeBundle\Entity\Address;
use Symfony\Component\DependencyInjection\Container;

class GeoDataManagerCommand extends ContainerAwareCommand
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
		$this->setName('geo:data:manager')
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

		// get database name
		$databaseName = $connection->getDatabase();

		// get Last Update Address
		$dateTime = str_replace(" ", "%20", $em->getRepository('YitGeoBridgeBundle:Address')->getLastUpdate());

		// get updates in Geo
		$modified = $this->getContent(self::GEO_DOMAIN . 'api/addresses/' . $dateTime . '/modified');

		// start Transaction
	$conn = $connection->beginTransaction();

	try{
		// Begin synchronization address in GEO and YitGeoBridgeBundle
		if (isset($modified->address)) {
			$addresses = $modified->address;

			for($i=0; $i<count($addresses); $i++){
				$object = $addresses[$i];

				// get address by address_id in YitGeoBridgeBundle
				$bridgeAddress = $em->getRepository('YitGeoBridgeBundle:Address')->findOneByAddressId($object->id);

				if(isset($bridgeAddress) && $bridgeAddress != null){

					// get matching address and update, if address isset
					$connection->executeUpdate("CALL GeoDataModified($object->id , '$object->address')");
				}
			}
		}

		// get merged address and replace it
		if (isset($modified->merged)) {
			$merge = $modified->merged;

			//get relations by database name and geo bridge Address entity
			$relations = " SELECT TABLE_NAME, COLUMN_NAME
 								FROM information_schema.KEY_COLUMN_USAGE
								WHERE CONSTRAINT_SCHEMA = :database_name
								AND REFERENCED_TABLE_SCHEMA  IS NOT NULL
								AND REFERENCED_TABLE_NAME = :referenced_table_name
								AND REFERENCED_COLUMN_NAME IS NOT NULL
								";

			$sth = $connection->prepare("$relations");

			// set parameters in query
			$params['database_name'] = $databaseName;
			$params['referenced_table_name'] = 'yit_geo_address';
			$sth->execute($params);
			$result = $sth->fetchAll();

			for ($i = 0; $i < count($result); $i++) {

				//get related table and column name
				$table = $result[$i]['TABLE_NAME'];
				$columnName = $result[$i]['COLUMN_NAME'];
				for ($j = 0; $j < count($merge); $j++) {

					// Call Geo Data Manager
					$connection->executeUpdate("CALL GeoDataManager('$table', '$columnName', '" . $merge[$j]->merged_id . "', '" . $merge[$j]->real_id . "' ,	'" . $merge[$j]->address . "')");
				}
			}
		}
	// commit oll changes
	$conn->commit();

	}
	//then something wrong
	catch(\Exception $e)
		{
	//rollback to the previously stable state
		$conn->rollback();
		//restore database to its original state.
			throw $e;
		}
		$output->writeln("<info>GeoBridgeBundle and Geo synchronization success ..</info>");
	}

}
