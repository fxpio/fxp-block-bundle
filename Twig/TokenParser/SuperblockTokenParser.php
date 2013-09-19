<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Bundle\BlockBundle\Twig\TokenParser;

use Sonatra\Bundle\BlockBundle\Block\Exception\InvalidConfigurationException;
use Sonatra\Bundle\BlockBundle\Twig\Node\SuperblockNode;

/**
 * Token Parser for the 'sblock' tag.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class SuperblockTokenParser extends \Twig_TokenParser
{
    /**
     * @var string
     */
    protected $tag;

    /**
     * Constructor.
     *
     * @param string $tag The tag name
     */
    public function __construct($tag = 'sblock')
    {
        $this->tag = $tag;
    }

    /**
     * Parses a token and returns a node.
     *
     * @param \Twig_Token $token A Twig_Token instance
     *
     * @return \Twig_NodeInterface A Twig_NodeInterface instance
     */
    public function parse(\Twig_Token $token)
    {
        $lineno = $token->getLine();
        $stream = $this->parser->getStream();
        $options = new \Twig_Node_Expression_Array(array(), $stream->getCurrent()->getLine());
        $variables = new \Twig_Node_Expression_Array(array(), $stream->getCurrent()->getLine());
        $assets = true;
        $skip = false;

        if (0 === strpos($this->tag, 'sblock_')) {
            $type = new \Twig_Node_Expression_Constant(substr($this->tag, 7), $lineno);
            $options = $this->parser->getExpressionParser()->parseExpression();

        } else {
            $type = $this->parser->getExpressionParser()->parseExpression();

            if ($stream->test(\Twig_Token::PUNCTUATION_TYPE, ',')) {
                $stream->next();
                $options = $this->parser->getExpressionParser()->parseExpression();
            }
        }

        if ($stream->test(\Twig_Token::NAME_TYPE, 'with')) {
            $stream->next();
            $variables = $this->parser->getExpressionParser()->parseExpression();

            if ($stream->test(\Twig_Token::PUNCTUATION_TYPE, ',')) {
                $stream->next();

                if ($stream->test(\Twig_Token::NAME_TYPE, 'noassets')) {
                    $stream->next();
                    $assets = false;
                }
            }
        }

        if ($stream->test(\Twig_Token::PUNCTUATION_TYPE, ':')) {
            $stream->next();
            $skip = true;
        }

        $stream->expect(\Twig_Token::BLOCK_END_TYPE);

        $superblock = new SuperblockNode($type, $options, $variables, $lineno, $this->getTag(), $assets);

        if ($skip) {
            return $superblock;
        }

        $body = $this->parser->subparse(array($this, 'decideBlockEnd'), true);

        foreach ($body->getIterator() as $i => $node) {
            if ($node instanceof SuperblockNode) {
                $superblock->addChild($node);
                $body->removeNode($i);

            } elseif ($node->hasNode('expr')
                    && $node->getNode('expr') instanceof \Twig_Node_Expression_Function
                    && $node->getNode('expr')->hasAttribute('name')
                    && ($this->tag === $node->getNode('expr')->getAttribute('name')
                            || 0 === strpos($node->getNode('expr')->getAttribute('name'), 'sblock_'))) {
                $superblock->addChild($this->convertTwigExpressionToNode($node->getNode('expr')));
                $body->removeNode($i);
            }
        }

        if (count($body) > 0) {
            $addBody = false;

            foreach ($body as $node) {
                if (!$node instanceof \Twig_Node_Text
                        || ($node instanceof \Twig_Node_Text && '' !== trim($node->getAttribute('data')))) {
                    $addBody = true;
                    break;
                }
            }

            if ($addBody) {
                $superblock->setNode('body', $body);
            }
        }

        $stream->expect(\Twig_Token::BLOCK_END_TYPE);

        return $superblock;
    }

    /**
     * Decide block end.
     *
     * @param \Twig_Token $token
     *
     * @return boolean
     */
    public function decideBlockEnd(\Twig_Token $token)
    {
        return $token->test('end'.$this->tag) || $token->test('endsblock');
    }

    /**
     * Gets the tag name associated with this token parser.
     *
     * @return string The tag name
     */
    public function getTag()
    {
        return $this->tag;
    }

    /**
     * Convert the twig expression function to SuperblockNode.
     *
     * @param \Twig_Node $node
     *
     * @return \Sonatra\Bundle\BlockBundle\Twig\Node\SuperblockNode
     *
     * @throws InvalidConfigurationException When the block type name is not present
     */
    protected function convertTwigExpressionToNode(\Twig_Node $node)
    {
        $args = $node->getNode('arguments');
        $pos = 0;

        if (!$args->hasNode(0)) {
            throw new InvalidConfigurationException('The block type must be present in the "sblock" twig function');
        }

        if ('sblock' === $node->getAttribute('name')) {
            $cType = $args->getNode($pos);
            $pos++;

        } else {
            $cType = $node->getAttribute('name');
            $cType = new \Twig_Node_Expression_Constant(substr($cType, 7), $node->getLine());
        }

        $cOptions = new \Twig_Node_Expression_Array(array(), $node->getLine());
        $cVariables = new \Twig_Node_Expression_Array(array(), $node->getLine());
        $cRenderAssets = true;

        if ($args->hasNode($pos)) {
            $cOptions = $args->getNode($pos);
        }

        $pos++;

        if ($args->hasNode($pos)) {
            $cVariables = $args->getNode($pos);
        }

        $pos++;

        if ($args->hasNode($pos)) {
            $cRenderAssets = $args->getNode($pos);
        }

        return new SuperblockNode($cType, $cOptions, $cVariables, $node->getLine(), $node->getNodeTag(), $cRenderAssets);
    }
}
