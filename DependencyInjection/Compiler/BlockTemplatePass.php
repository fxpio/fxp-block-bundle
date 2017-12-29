<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Bundle\BlockBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Add a custom block template in fxp_block.twig.resources.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class BlockTemplatePass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('fxp_block.extension')) {
            return;
        }

        $resources = $container->getParameter('fxp_block.twig.resources');

        array_splice($resources, 0, 0, [
            'block_div_layout.html.twig',
        ]);

        $container->setParameter('fxp_block.twig.resources', $resources);
    }
}
