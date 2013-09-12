<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Bundle\BlockBundle\Twig\Node;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class SearchAndRenderBlockGlobalNode extends SearchAndRenderBlockNode
{
    /**
     * {@inheritdoc}
     */
    public function compile(\Twig_Compiler $compiler)
    {
        parent::compile($compiler);

        $compiler
            ->raw(";\n")
            ->addDebugInfo($this)
            ->write('$this->env->getExtension(\'sonatra_block\')->addAssetBlock(')
        ;

        $arguments = iterator_to_array($this->getNode('arguments'));

        if (isset($arguments[0])) {
            $compiler->subcompile($arguments[0]);
        }

        if (isset($arguments[2])) {
            $compiler->raw(', ');
            $compiler->subcompile($arguments[2]);

        } else {
            $compiler->raw(', array()');
        }

        if (isset($arguments[3])) {
            $compiler->raw(', ');
            $compiler->subcompile($arguments[3]);
        }

        $compiler->raw(')');
    }
}
