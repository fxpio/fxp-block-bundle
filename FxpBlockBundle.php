<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Bundle\BlockBundle;

use Fxp\Bundle\BlockBundle\DependencyInjection\Compiler\AddTemplatePathPass;
use Fxp\Bundle\BlockBundle\DependencyInjection\Compiler\BlockPass;
use Fxp\Bundle\BlockBundle\DependencyInjection\Compiler\BlockTemplatePass;
use Fxp\Bundle\BlockBundle\DependencyInjection\Compiler\TemplateAliasPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class FxpBlockBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new BlockPass());
        $container->addCompilerPass(new BlockTemplatePass());
        $container->addCompilerPass(new AddTemplatePathPass());
        $container->addCompilerPass(new TemplateAliasPass());
    }
}
