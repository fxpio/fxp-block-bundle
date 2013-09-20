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
class SuperblockNode extends \Twig_Node
{
    /**
     * @var \Twig_Node_Expression
     */
    protected $type;

    /**
     * @var \Twig_Node_Expression
     */
    protected $options;

    /**
     * @var \Twig_Node_Expression
     */
    protected $variables;

    /**
     * @var boolean
     */
    protected $assets;

    /**
     * @var SuperblockNode
     */
    protected $parent;

    /**
     * @var SuperblockNode[]
     */
    protected $children;

    /**
     * Constructor.
     *
     * @param \Twig_Node_Expression $type
     * @param \Twig_Node_Expression $options
     * @param \Twig_Node_Expression $variables
     * @param int                   $lineno
     * @param string                $tag
     * @param boolean               $assets
     */
    public function __construct(\Twig_Node_Expression $type,
            \Twig_Node_Expression $options, \Twig_Node_Expression $variables,
            $lineno, $tag = null, $assets = true)
    {
        $this->type = $type;
        $this->options = $options;
        $this->variables = $variables;
        $this->children = array();
        $this->assets = $assets;

        parent::__construct(array(), array(), $lineno, $tag);
    }

    /**
     * Compiles the node to PHP.
     *
     * @param \Twig_Compiler $compiler A Twig_Compiler instance
     */
    public function compile(\Twig_Compiler $compiler)
    {
        $compiler->addDebugInfo($this);

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

        // create the block
        $compiler
            ->raw('$this->env->getExtension(\'sonatra_block\')->createNamedBuilder(')
            ->subcompile($this->type)
            ->raw(', ')
            ->subcompile($this->options)
            ->raw(')')
        ;

        $compiler->indent();

        // children of block
        foreach ($this->children as $i => $child) {
            $compiler
                ->raw("\n")
                ->subcompile($child)
            ;
        }

        // body of block
        if ($this->hasNode('body')) {
            $compiler
                ->raw("\n")
                ->addDebugInfo($this->getNode('body'))
                ->write('->add(')
                ->raw('$this->env->getExtension(\'sonatra_block\')->createNamedBuilder(')
                ->raw('"closure"')
                ->raw(', ')
                ->raw('array("data" => function() {')
                ->write("\n")
                ->indent()
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

        $compiler->outdent();

        // renderer end
        if (null === $this->getParent()) {
            $compiler
                // create block view
                ->raw('->getBlock()->createView()')
                // renderer prefix
                ->raw(', ')
                ->raw('"widget"')
                ->raw(', ')
                // variables
                ->subcompile($this->variables)
                // assets
                ->raw(', ' . ($this->assets ? 'true' : 'false'))
                ->raw(")\n")
                ->outdent()
                ->write(";\n")
            ;

        } else {
            $compiler->raw(")");
        }
    }

    /**
     * Set parent node.
     *
     * @param SuperblockNode $parent
     */
    public function setParent(SuperblockNode $parent)
    {
        $this->parent = $parent;
    }

    /**
     * Get parent node.
     *
     * @return \Sonatra\Bundle\BlockBundle\Twig\Node\SuperblockNode
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Add child node.
     *
     * @param SuperblockNode $child
     */
    public function addChild(SuperblockNode $child)
    {
        $child->setParent($this);
        $this->children[] = $child;
    }
}
