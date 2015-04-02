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

		$this
			->setName('update:db:active')
			->setDescription('All DB activate');
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		// Begin run command
		$output->writeln("<info>Starting</info>");
		$em = $this->getContainer()->get("doctrine")->getManager();

		// Begin transaction
		try {
			$em->getConnection()->beginTransaction();

		// get project name
		$this->container = $this->getApplication()->getKernel()->getContainer();
		$projectName = $this->container->getParameter('yit_geo_bridge.project_name');

		// get Last Update Address
		$time = $em->getRepository('YitGeoBridgeBundle:Address')->getLastUpdate();
		$title = str_replace(" ","%20", $time);

		// get updates in Geo
		$modified = $this->getContent(self::GEO_DOMAIN .'api/addresses/'.$title.'/modified');

		// Begin synchronization address in GEO and YitGeoBridgeBundle
		if(isset($modified->address)){
			$addresses = $modified->address;
			for($i=0; $i<count($addresses); $i++){
				$object = $addresses[$i];

				// get matching address and edit, if address new and created by project insert
				$address = $em->getRepository('YitGeoBridgeBundle:Address')->findOneByAddressId($object->id);

				if(isset($address)){
					$address->setAddress($object->address);
					$em->persist($address);
				}
				elseif(($object->project) == $projectName){
					$address = new Address();
					$address->setAddressId($object->id);
					$address->setAddress($object->address);
					$em->persist($address);
				}
			}
		}
		// get merged address and replace it
		if ( isset($modified->marged)) {
			$marg = $modified->marged;

			for($i = 0;$i<count($marg); $i++){

				// get matching address in YitGeoBridgeBundle
				$addressOld = $em->getRepository('YitGeoBridgeBundle:Address')->findOneByAddressId($marg[$i]->oldId);
				if(isset($addressOld) && $addressOld =! null ){
					$oldId = $addressOld->getId();
				}
				// get real address and if isset old address and not real address insert it and replace in project
				$addressReal = $em->getRepository('YitGeoBridgeBundle:Address')->findOneByAddressId($marg[$i]->realId);
				if(isset($addressReal) && $addressReal != null){
					$realId = $addressReal->getId();
				}
				elseif(isset($oldId) && $oldId != null)
				{
					$object = $modified->margedAddress[$i];
					$address = new Address();
					$address->setAddressId($object[0]->id);
					$address->setAddress($object[0]->address);
					$em->persist($address);
				}

				if(isset($oldId) && $oldId != null){
					// call sql storage procedure for replaced, set replaced address and replace it
					$updateSql = $em->getConnection()->executeUpdate("CALL UpdateMarged".$i."(".$oldId, $realId.")");
					// call sql storage procedure for delete merged address in YitGeoBridgeBundle
					$deleteMarged = $em->getConnection()->executeUpdate("CALL DeleteMarged(".$oldId.")");
				}
			}
		}
			// flash changes
			$em->flush();

			// coll rollback for Transaction
			$em->getConnection()->commit();
			$e = " synchronization is invalid please try again ";
		} catch (\Exception $e) {
			$em->getConnection()->rollback();
			throw $e;
		}

		$output->writeln("<info>Success ..</info>");
	}

}
