<?php
/**
 * Created by PhpStorm.
 * User: tigran
 * Date: 3/27/15
 * Time: 4:38 PM
 */

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
		$content = @file_get_contents($link, false, $context);

		if ($content) {
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

	protected $container;

	protected function configure()
	{

		$this->setName('geo:data:manager')
			->setDescription('GeoBridgeBundle synchronization data manager ');
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		// Begin run command
		$output->writeln("<info>Starting synchronization GeoBridgeBundle and Geo data manager</info>");

		$em = $this->getContainer()->get("doctrine")->getManager();
		$connection = $em->getConnection();

		$databaseName = $connection->getDatabase();

		// get project name
		$this->container = $this->getApplication()->getKernel()->getContainer();
		$projectName = $this->container->getParameter('yit_geo_bridge.project_name');

		// get Last Update Address
		$dateTime = str_replace(" ", "%20", $em->getRepository('YitGeoBridgeBundle:Address')->getLastUpdate());

		// get updates in Geo
		$modified = $this->getContent(self::GEO_DOMAIN . 'api/addresses/' . $dateTime . '/modified');

		// Begin synchronization address in GEO and YitGeoBridgeBundle
		if (isset($modified->address)) {
			$addresses = $modified->address;
			for($i=0; $i<count($addresses); $i++){
				$object = $addresses[$i];

				$bridgeAddress = $em->getRepository('YitGeoBridgeBundle:Address')->findOneByAddressId($object->id);

				if(isset($bridgeAddress) && $bridgeAddress != null){

					// get matching address and update, if address isset
					$addressDataModified = $connection->executeUpdate("CALL GeoDataModified($object->id , '$object->address')");
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
					$mergeManager = $connection->executeUpdate("CALL GeoDataManager('$table', '$columnName', '" . $merge[$j]->merged_id . "', '" . $merge[$j]->real_id . "' ,	'" . $merge[$j]->address . "')");
				}
			}
		}

		$output->writeln("<info>GeoBridgeBundle and Geo synchronization success ..</info>");
	}

}
