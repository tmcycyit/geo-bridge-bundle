<?php

namespace Yit\GeoBridgeBundle\Command;

use Symfony\Component\ClassLoader\ClassCollectionLoader;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\PhpExecutableFinder;
use Composer\Script\CommandEvent;

class ManageGeoStoredProcedureCommand {

	/**
	 * This function run sql storage procedures after composer install or update
	 * @param $event CommandEvent A instance
	 */
	public static function manageGeoStoredProcedure(CommandEvent $event)
	{
		$options = self::getOptions($event);
		$appDir = $options['symfony-app-dir'];

		static::executeCommand($event, $appDir, 'geo:manage:stored:procedure');
	}

	/**
	 * Gives options command
	 *
	 * @param CommandEvent $event
	 * @return array
	 */
	protected static function getOptions(CommandEvent $event)
	{
		$options = array_merge(array(
			'symfony-app-dir' => 'app',
		), $event->getComposer()->getPackage()->getExtra());

		return $options;
	}

	/**
	 *
	 * @param CommandEvent $event
	 * @param $appDir
	 * @param $cmd
	 * @param int $timeout
	 */
	protected static function executeCommand(CommandEvent $event, $appDir, $cmd, $timeout = 300)
	{
		$php = escapeshellarg(self::getPhp());
		$console = escapeshellarg($appDir.'/console');
		if ($event->getIO()->isDecorated()) {
			$console .= ' --ansi';
		}

		$process = new Process($php.' '.$console.' '.$cmd, null, null, null, $timeout);
		$process->run(function ($type, $buffer) { echo $buffer; });
		if (!$process->isSuccessful()) {
			throw new \RuntimeException(sprintf('An error occurred when executing the "%s" command.', escapeshellarg($cmd)));
		}
	}

	/**
	 * If app console works using php
	 *
	 * @return false|string
	 */
	protected static function getPhp()
	{
		$phpFinder = new PhpExecutableFinder();
		if (!$phpPath = $phpFinder->find()) {
			throw new \RuntimeException('The php executable could not be found, add it to your PATH environment variable and try again');
		}

		return $phpPath;
	}

}