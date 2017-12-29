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

use Fxp\Component\Block\Util\StringUtil;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Adds all aliases of block in template extension.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class TemplateAliasPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('fxp_block.twig.extension')) {
            return;
        }

        $definition = $container->getDefinition('fxp_block.twig.extension');

        $aliases = [];

        foreach ($container->findTaggedServiceIds('fxp_block.type') as $serviceId => $tag) {
            $serviceDefinition = $container->getDefinition($serviceId);
            $class = $serviceDefinition->getClass();
            $alias = isset($tag[0]['template_alias'])
                ? $tag[0]['template_alias']
                : StringUtil::fqcnToBlockPrefix($class, true);
            $aliases[$alias] = $class;
        }

        $definition->replaceArgument(4, $aliases);
    }
}
