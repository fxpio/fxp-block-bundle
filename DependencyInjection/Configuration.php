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

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('fxp_block');

        $this->addBlockSection($rootNode);
        $this->addDoctrineSection($rootNode);
        $this->addProfilerSection($rootNode);

        return $treeBuilder;
    }

    /**
     * Add block section.
     *
     * @param ArrayNodeDefinition $rootNode
     */
    private function addBlockSection(ArrayNodeDefinition $rootNode)
    {
        $rootNode
            ->fixXmlConfig('block_theme')
            ->children()
                ->arrayNode('block_themes')
                    ->prototype('scalar')->end()
                    ->example(array('@App/block.html.twig'))
                ->end()
            ->end()
        ;
    }

    /**
     * Add doctrine section.
     *
     * @param ArrayNodeDefinition $rootNode
     */
    private function addDoctrineSection(ArrayNodeDefinition $rootNode)
    {
        $rootNode
            ->children()
                ->arrayNode('doctrine')
                    ->info('doctrine configuration')
                    ->canBeEnabled()
                    ->children()
                        ->booleanNode('enabled')->defaultTrue()->end()
                    ->end()
                ->end()
            ->end()
        ;
    }

    /**
     * Add profiler section.
     *
     * @param ArrayNodeDefinition $rootNode
     */
    private function addProfilerSection(ArrayNodeDefinition $rootNode)
    {
        $rootNode
            ->children()
                ->arrayNode('profiler')
                    ->info('profiler configuration')
                    ->canBeEnabled()
                    ->children()
                        ->booleanNode('collect')->defaultTrue()->end()
                    ->end()
                ->end()
            ->end()
        ;
    }
}
