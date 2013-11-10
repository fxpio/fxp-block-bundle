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
 * Represents a sblock call node.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class SuperblockReference extends \Twig_Node implements \Twig_NodeOutputInterface
{
    /**
     * Constructor.
     *
     * @param string  $name
     * @param integer $lineno
     * @param string  $tag
     */
    public function __construct($name, \Twig_Node_Expression $variables, $lineno, $tag = null)
    {
        $attr = array('name' => $name, 'variables' => $variables, 'is_master' => true, 'is_closure' => false);

        parent::__construct(array(), $attr, $lineno, $tag);
    }

    /**
     * Compiles the node to PHP.
     *
     * @param \Twig_Compiler $compiler A Twig_Compiler instance
     */
    public function compile(\Twig_Compiler $compiler)
    {
        $name = $this->getAttribute('name');

        // closure block
        if ($this->getAttribute('is_closure')) {
            $compiler
            ->addDebugInfo($this)
            ->write('$block->add(')
            ->raw('$this->env->getExtension(\'sonatra_block\')->createNamed(')
            ->raw('"closure"')
            ->raw(', ')
            ->raw(sprintf('array("data" => function ($blockView) use ($context, $blocks) {$this->block_%s(array_merge($context, array(\'closure\' => $blockView)), $blocks);}', $name))
            ->raw(sprintf(', "block_name" => "%s", "label" => "")', $name))
            ->raw('))')
            ->raw(";\n")
            ;

        // master block
        } elseif ($this->getAttribute('is_master')) {
            $compiler
                ->addDebugInfo($this)
                ->write('echo $this->env->getExtension(\'sonatra_block\')->renderer->searchAndRenderBlock(')
                ->raw("\n")
                ->indent()
                ->write(sprintf("(\$b_%s = \$this->block_%s(\$context, \$blocks)) ", $name, $name))
                ->raw(sprintf("instanceof \Sonatra\Bundle\BlockBundle\Block\BlockView ? \$b_%s : \$b_%s->createView()", $name, $name))
                // renderer prefix
                ->raw(',')
                ->raw("\n")
                ->write('"widget"')
                ->raw(', ')
                // variables
                ->raw("\n")
                ->write('')
                ->subcompile($this->getAttribute('variables'))
                ->raw(")\n")
                ->outdent()
                ->write(";\n")
            ;

        // child block
        } else {
            $compiler
                ->addDebugInfo($this)
                ->write(sprintf("\$block->add(\$this->block_%s(\$context, \$blocks));\n", $name))
            ;
        }
    }
}
