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

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

/**
 * Adds all services with the tags "sonatra.block.type" and "sonatra.block.type_guesser" as
 * arguments of the "sonatra.block.extension" service.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class BlockPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('sonatra.block.extension')) {
            return;
        }

        // Builds an array with service IDs as keys and tag aliases as values
        $types = array();

        foreach ($container->findTaggedServiceIds('sonatra.block.type') as $serviceId => $tag) {
            $alias = isset($tag[0]['alias'])
                ? $tag[0]['alias']
                : $serviceId;

            // Flip, because we want tag aliases (= type identifiers) as keys
            $types[$alias] = $serviceId;
        }

        $container->getDefinition('sonatra.block.extension')->replaceArgument(1, $types);

        $typeExtensions = array();

        foreach ($container->findTaggedServiceIds('sonatra.block.type_extension') as $serviceId => $tag) {
            $alias = isset($tag[0]['alias'])
                ? $tag[0]['alias']
                : $serviceId;

            $typeExtensions[$alias][] = $serviceId;
        }

        $container->getDefinition('sonatra.block.extension')->replaceArgument(2, $typeExtensions);

        // Find all services annotated with "block.type_guesser"
        $guessers = array_keys($container->findTaggedServiceIds('sonatra.block.type_guesser'));

        $container->getDefinition('sonatra.block.extension')->replaceArgument(3, $guessers);
    }
}
