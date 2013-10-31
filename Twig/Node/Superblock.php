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
 * Represents a sblock node.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class Superblock extends \Twig_Node_Block
{
    /**
     * Constructor.
     *
     * @param \Twig_Node_Expression $type
     * @param \Twig_Node_Expression $options
     * @param int                   $lineno
     * @param string                $tag
     */
    public function __construct(\Twig_Node_Expression $type,
            \Twig_Node_Expression $options, $lineno, $tag = null)
    {
        parent::__construct(BlockUtil::createUniqueName(), new \Twig_Node(array()), $lineno, $tag);

        $this->setAttribute('type', $type);
        $this->setAttribute('options', $options);
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
            ->write('$block = ')
        ;

        // checks if the type is an block view
        if ($this->getAttribute('type') instanceof \Twig_Node_Expression_Name) {
            $compiler
                ->subcompile($this->getAttribute('type'))
                ->raw(' instanceof \Sonatra\Bundle\BlockBundle\Block\BlockView ? ')
                ->subcompile($this->getAttribute('type'))
                ->raw(' : ')
            ;
        }

        // create the block
        $compiler
            ->raw('$this->env->getExtension(\'sonatra_block\')->createNamed(')
            ->subcompile($this->getAttribute('type'))
            ->raw(', ')
            ->subcompile($this->getAttribute('options'))
            ->raw(')')
            ->raw(";\n")
        ;

        if ($this->hasNode('sblocks')) {
            $compiler->subcompile($this->getNode('sblocks'));
        }

        if ($this->hasNode('body')) {
            $compiler->subcompile($this->getNode('body'));
        }

        $compiler
            ->write('return $block;')
            ->raw("\n")
            ->outdent()
            ->write("}\n\n")
        ;

        /*$compiler->addDebugInfo($this);

        // renderer start
        if (null === $this->getParent()) {
            // master block
            $compiler
                ->write('echo $this->env->getExtension(\'sonatra_block\')->searchAndRenderBlockAssets(')
                ->raw("\n")
                ->indent()
                ->write('')
            ;

        } else {
            // add child block of master block
            $compiler->write('->add(');
        }

        // checks if the type is an block view
        if ($this->getAttribute('type') instanceof \Twig_Node_Expression_Name) {
            $compiler
                ->subcompile($this->getAttribute('type'))
                ->raw(' instanceof \Sonatra\Bundle\BlockBundle\Block\BlockView ? ')
                ->subcompile($this->getAttribute('type'))
                ->raw(' : ')
            ;
        }

        // create the block
        $compiler
            ->raw('$this->env->getExtension(\'sonatra_block\')->createNamed(')
            ->subcompile($this->getAttribute('type'))
            ->raw(', ')
            ->subcompile($this->getAttribute('options'))
            ->raw(')')
        ;

        $compiler->indent();

        // body of block
        if ($this->hasNode('body')) {
            $compiler
                ->raw("\n")
                ->addDebugInfo($this->getNode('body'))
                ->write('->add(')
                ->raw('$this->env->getExtension(\'sonatra_block\')->createNamed(')
                ->raw('"closure"')
                ->raw(', ')
                ->raw('array("data" => function () use ($context, $blocks) {')
                ->write("\n")
                ->indent()
                ->write('$context = array_merge($context, ')
                ->subcompile($this->getAttribute('variables'))
                ->raw(');')
                ->write("\n")
                ->subcompile($this->getNode('body'))
                ->write('}, "block_name" => "body", "label" => "")')
                ->raw(')')
                ->raw(')')
                ->raw("\n")
                ->outdent()
                ->outdent()
                ->write('')
                ->indent()
            ;
        }

        // children of block
        foreach ($this->children as $i => $child) {
            $compiler
                ->raw("\n")
                ->subcompile($child)
            ;
        }

        $compiler->outdent();

        // renderer end
        if (null === $this->getParent()) {
            $compiler
                // create block view
                ->raw('->createView()')
                // renderer prefix
                ->raw(', ')
                ->raw('"widget"')
                ->raw(', ')
                // variables
                ->subcompile($this->getAttribute('variables'))
                // assets
                ->raw(', ' . ($this->getAttribute('assets') ? 'true' : 'false'))
                ->raw(")\n")
                ->outdent()
                ->write(";\n")
            ;

        } else {
            $compiler->raw(")");
        }*/
    }
}
