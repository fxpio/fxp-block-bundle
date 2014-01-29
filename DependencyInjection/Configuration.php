<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Bundle\BlockBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('sonatra_block');

        $this->addBlockSection($rootNode);

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
            ->children()
                ->arrayNode('block')
                    ->addDefaultsIfNotSet()
                    ->fixXmlConfig('resource')
                    ->children()
                        ->arrayNode('resources')
                            ->addDefaultChildrenIfNoneSet()
                            ->prototype('scalar')->defaultValue('block_div_layout.html.twig')->end()
                            ->example(array('MyBundle::block.html.twig'))
                            ->validate()
                                ->ifTrue(function($v) { return !in_array('block_div_layout.html.twig', $v); })
                                ->then(function($v){
                                    return array_merge(array('block_div_layout.html.twig'), $v);
                                })
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;
    }
}
