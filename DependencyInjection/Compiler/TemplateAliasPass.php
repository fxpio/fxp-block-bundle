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

use Sonatra\Bundle\BlockBundle\Block\Util\StringUtil;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

/**
 * Adds all aliases of block in template extension.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class TemplateAliasPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('sonatra_block.twig.extension')) {
            return;
        }

        $definition = $container->getDefinition('sonatra_block.twig.extension');

        $aliases = array();

        foreach ($container->findTaggedServiceIds('sonatra_block.type') as $serviceId => $tag) {
            $serviceDefinition = $container->getDefinition($serviceId);
            $class = $serviceDefinition->getClass();
            $alias = isset($tag[0]['template_alias'])
                ? $tag[0]['template_alias']
                : StringUtil::fqcnToBlockPrefix($class, true);
            $aliases[$alias] = $class;
        }

        $definition->replaceArgument(3, $aliases);
    }
}
