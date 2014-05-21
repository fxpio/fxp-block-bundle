<?php

/**
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
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Adds all services with the tags "form.type" (converted to block type) as
 * arguments of the "sonatra_block.extension" service.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class BlockFormPass implements CompilerPassInterface
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
        foreach ($container->findTaggedServiceIds('form.type') as $serviceId => $tag) {
            $alias = isset($tag[0]['alias'])
                ? $tag[0]['alias']
                : $serviceId;

            $name = ('form' !== $alias ? 'form_' : '') . $alias;
            $definition = new Definition();
            $definition
                ->setClass('Sonatra\Bundle\BlockBundle\Block\Extension\Core\Type\FormType')
                ->setPublic(true)
                ->addArgument(new Reference('form.factory'))
                ->addArgument($alias)
                ->addTag('sonatra_block.type', array('alias' => $name))
            ;

            $container->setDefinition('sonatra_block.type.' . $name, $definition);
            $container->createService($definition, 'sonatra_block.type.' . $name);
        }
    }
}
