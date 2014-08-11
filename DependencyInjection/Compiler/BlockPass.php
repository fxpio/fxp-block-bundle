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
 * Adds all services with the tags "sonatra_block.type" and "sonatra_block.type_guesser" as
 * arguments of the "sonatra_block.extension" service.
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
        if (!$container->hasDefinition('sonatra_block.extension')) {
            return;
        }

        // Builds an array with service IDs as keys and tag aliases as values
        $types = array();

        foreach ($container->findTaggedServiceIds('sonatra_block.type') as $serviceId => $tag) {
            $alias = isset($tag[0]['alias'])
                ? $tag[0]['alias']
                : $serviceId;

            // Flip, because we want tag aliases (= type identifiers) as keys
            $types[$alias] = $serviceId;
        }

        $container->getDefinition('sonatra_block.extension')->replaceArgument(0, $types);

        $typeExtensions = array();

        foreach ($container->findTaggedServiceIds('sonatra_block.type_extension') as $serviceId => $tag) {
            $alias = isset($tag[0]['alias'])
                ? $tag[0]['alias']
                : $serviceId;

            $typeExtensions[$alias][] = $serviceId;
        }

        $container->getDefinition('sonatra_block.extension')->replaceArgument(1, $typeExtensions);

        // Find all services annotated with "block.type_guesser"
        $guessers = array_keys($container->findTaggedServiceIds('sonatra_block.type_guesser'));

        $container->getDefinition('sonatra_block.extension')->replaceArgument(2, $guessers);
    }
}
