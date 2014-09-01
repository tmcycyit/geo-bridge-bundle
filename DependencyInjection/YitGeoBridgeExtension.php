<?php

namespace Yit\GeoBridgeBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class YitGeoBridgeExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');

        if (isset($config) && isset($config['experience'])) {
            $container->setParameter($this->getAlias() . '.experience', $config['experience']);
        }
        else {
            $container->setParameter($this->getAlias() . '.experience', 86400);
        }
        if (isset($config) && isset($config['project_name'])) {
            $container->setParameter($this->getAlias() . '.project_name', $config['project_name']);
        }
        else {
            $container->setParameter($this->getAlias() . '.project_name', 'geo_bridge');
        }
    }
}
