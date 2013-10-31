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

use Sonatra\Bundle\BlockBundle\Block\Util\BlockUtil;

/**
 * Represents a sblock closure node.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class SuperblockClosure extends \Twig_Node implements \Twig_NodeOutputInterface
{
    /**
     * Constructor.
     *
     * @param \Twig_NodeInterface $body
     * @param integer             $lineno
     * @param string              $tag
     */
    public function __construct(\Twig_NodeInterface $body, $lineno, $tag = null)
    {
        parent::__construct(array('body' => $body), array('name' => BlockUtil::createUniqueName()), $lineno, $tag);
    }

    /**
     * Compiles the node to PHP.
     *
     * @param \Twig_Compiler $compiler A Twig_Compiler instance
     */
    public function compile(\Twig_Compiler $compiler)
    {
        $compiler
            ->addDebugInfo($this)
            ->write('$block->add(')
            ->raw('$this->env->getExtension(\'sonatra_block\')->createNamed(')
            ->raw('"closure"')
            ->raw(', ')
            ->raw('array("data" => function () use ($context, $blocks) {')
            ->raw("\n")
            ->indent()
            ->raw("\n")
            ->subcompile($this->getNode('body'))
            ->write(sprintf('}, "block_name" => "%s", "label" => "")', $this->getAttribute('name')))
            ->raw(')')
            ->outdent()
            ->raw("\n")
            ->write(')')
            ->raw(";\n")
        ;
    }
}
