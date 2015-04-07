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

class RelationsAddressesCommand extends ContainerAwareCommand
{
//	const GEO_DOMAIN = 'http://geo.yerevan.am/';
	const GEO_DOMAIN = 'http://geo.loc/app_dev.php/';

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

		$db = $connection->getDatabase();

		// get project name
		$this->container = $this->getApplication()->getKernel()->getContainer();
		$projectName = $this->container->getParameter('yit_geo_bridge.project_name');

		// get Last Update Address
		$time = $em->getRepository('YitGeoBridgeBundle:Address')->getLastUpdate();
		$title = str_replace(" ", "%20", $time);

		// get updates in Geo
		$modified = $this->getContent(self::GEO_DOMAIN . 'api/addresses/' . $title . '/modified');
		// Begin synchronization address in GEO and YitGeoBridgeBundle
		if (isset($modified->address)) {
			$addresses = $modified->address;
			for($i=0; $i<count($addresses); $i++){
				$object = $addresses[$i];

				// get matching address and edit, if address new and created by project insert
				$addressDataModified = $connection->executeUpdate("CALL GeoDataModified($object->id , '$object->address')");
			}
		}

		// get merged address and replace it
		if (isset($modified->marged)) {
			$marg = $modified->marged;
			$relations = " SELECT TABLE_NAME, COLUMN_NAME
 								FROM information_schema.KEY_COLUMN_USAGE
								WHERE CONSTRAINT_SCHEMA = :db
								AND REFERENCED_TABLE_SCHEMA  IS NOT NULL
								AND REFERENCED_TABLE_NAME = :tables
								AND REFERENCED_COLUMN_NAME IS NOT NULL
								";

			$sth = $connection->prepare("$relations");

			$params['db'] = $db;
			$params['tables'] = 'yit_geo_address';
			$sth->execute($params);
			$result = $sth->fetchAll();
			for ($i = 0; $i < count($result); $i++) {
				$table = $result[$i]['TABLE_NAME'];
				$columnName = $result[$i]['COLUMN_NAME'];
				for ($j = 0; $j < count($marg); $j++) {
					$margeManager = $connection->executeUpdate("CALL GeoDataManager('$columnName', '$table', '" . $marg[$j]->oldId . "', '" . $marg[$j]->realId . "' ,	'" . $marg[$j]->address . "')");
				}
			}
		}

		$output->writeln("<info>GeoBridgeBundle and Geo synchronization success ..</info>");
	}

}
