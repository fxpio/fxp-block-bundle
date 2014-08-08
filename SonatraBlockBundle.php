<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Bundle\BlockBundle;

use Sonatra\Bundle\BlockBundle\DependencyInjection\Compiler\BlockTemplatePass;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Sonatra\Bundle\BlockBundle\DependencyInjection\Compiler\BlockPass;
use Sonatra\Bundle\BlockBundle\DependencyInjection\Compiler\BlockFormPass;
use Sonatra\Bundle\BlockBundle\DependencyInjection\Compiler\AddTemplatePathPass;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class SonatraBlockBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new BlockFormPass());
        $container->addCompilerPass(new BlockPass());
        $container->addCompilerPass(new BlockTemplatePass());
        $container->addCompilerPass(new AddTemplatePathPass());
    }
}
