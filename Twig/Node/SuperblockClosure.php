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
class SuperblockClosure extends \Twig_Node_Block
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
        $name = sprintf('%s_%s', strtolower(get_class($body)), BlockUtil::createUniqueName());

        parent::__construct($name, $body, $lineno, $tag);
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
            ->write(sprintf("public function block_%s(\$context, array \$blocks = array())\n", $this->getAttribute('name')), "{\n")
            ->indent()
            ->subcompile($this->getNode('body'))
            ->raw("\n")
            ->outdent()
            ->write("}\n\n")
        ;
    }
}
