<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Bundle\BlockBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * This is the class that loads and manages your bundle configuration.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class FxpBlockExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('block.xml');
        $loader->load('twig.xml');

        if (count($configs) > 1) {
            $initConfig = array_pop($configs);
            $configs = array_reverse($configs);
            $configs[] = $initConfig;
        }

        $configuration = $this->getConfiguration($configs, $container);
        $config = $this->processConfiguration($configuration, $configs);

        $container->setParameter('fxp_block.twig.resources', $config['block_themes']);
        $this->registerDoctrineConfiguration($config['doctrine'], $loader);
        $this->registerProfilerConfiguration($config['profiler'], $loader);
    }

    /**
     * Loads the doctrine configuration.
     *
     * @param array         $config A doctrine configuration array
     * @param XmlFileLoader $loader An XmlFileLoader instance
     *
     * @throws \LogicException
     */
    private function registerDoctrineConfiguration(array $config, XmlFileLoader $loader)
    {
        if ($config['enabled'] && interface_exists('Doctrine\Common\Persistence\ManagerRegistry')) {
            $loader->load('doctrine.xml');
        }
    }

    /**
     * Loads the profiler configuration.
     *
     * @param array         $config A profiler configuration array
     * @param XmlFileLoader $loader An XmlFileLoader instance
     *
     * @throws \LogicException
     */
    private function registerProfilerConfiguration(array $config, XmlFileLoader $loader)
    {
        if ($config['enabled'] && $config['collect'] && class_exists('Symfony\Component\Debug\Debug')) {
            $loader->load('block_debug.xml');
            $loader->load('collectors.xml');
        }
    }
}
