<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Bundle\BlockBundle\DependencyInjection\Compiler;

use Sonatra\Component\Block\Block;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * This compiler pass adds the path for the Block template in the twig loader.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class AddTemplatePathPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('twig.loader.filesystem')) {
            return;
        }

        $refl = new \ReflectionClass(Block::class);

        $path = dirname($refl->getFileName()).'/Resources/views/Block';
        $container->getDefinition('twig.loader.filesystem')->addMethodCall('addPath', array($path));
    }
}
