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

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

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

        $definition = $container->getDefinition('sonatra_block.extension');

        // Builds an array with service IDs as keys and tag aliases as values
        $types = array();

        foreach ($container->findTaggedServiceIds('sonatra_block.type') as $serviceId => $tag) {
            $serviceDefinition = $container->getDefinition($serviceId);

            if (!$serviceDefinition->isPublic()) {
                throw new \InvalidArgumentException(sprintf('The service "%s" must be public as block types are lazy-loaded.', $serviceId));
            }

            // Support type access by FQCN
            $types[$serviceDefinition->getClass()] = $serviceId;
        }

        $definition->replaceArgument(0, $types);

        $typeExtensions = array();

        foreach ($container->findTaggedServiceIds('sonatra_block.type_extension') as $serviceId => $tag) {
            $serviceDefinition = $container->getDefinition($serviceId);
            if (!$serviceDefinition->isPublic()) {
                throw new \InvalidArgumentException(sprintf('The service "%s" must be public as block type extensions are lazy-loaded.', $serviceId));
            }

            if (isset($tag[0]['extended_type'])) {
                $extendedType = $tag[0]['extended_type'];
            } else {
                throw new \InvalidArgumentException(sprintf('Tagged block type extension must have the extended type configured using the extended_type/extended-type attribute, none was configured for the "%s" service.', $serviceId));
            }

            $typeExtensions[$extendedType][] = $serviceId;
        }

        $definition->replaceArgument(1, $typeExtensions);

        // Find all services annotated with "sonatra_block.type_guesser"
        $guessers = array_keys($container->findTaggedServiceIds('sonatra_block.type_guesser'));
        foreach ($guessers as $serviceId) {
            $serviceDefinition = $container->getDefinition($serviceId);
            if (!$serviceDefinition->isPublic()) {
                throw new \InvalidArgumentException(sprintf('The service "%s" must be public as block type guessers are lazy-loaded.', $serviceId));
            }
        }

        $definition->replaceArgument(2, $guessers);
    }
}
