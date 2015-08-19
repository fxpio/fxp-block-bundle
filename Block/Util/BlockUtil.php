<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Bundle\BlockBundle\Block\Util;

use Sonatra\Bundle\BlockBundle\Block\BlockInterface;
use Sonatra\Bundle\BlockBundle\Block\ResolvedBlockTypeInterface;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class BlockUtil
{
    /**
     * This class should not be instantiated.
     */
    private function __construct() {}

    /**
     * Returns whether the given data is empty.
     *
     * This logic is reused multiple times throughout the processing of
     * a block and needs to be consistent. PHP's keyword `empty` cannot
     * be used as it also considers 0 and "0" to be empty.
     *
     * @param mixed $data
     *
     * @return Boolean
     */
    public static function isEmpty($data)
    {
        // Should not do a check for array() === $data!!!
        // This method is used in occurrences where arrays are
        // not considered to be empty, ever.
        return null === $data || '' === $data;
    }

    /**
     * Create a unique block name.
     * Uses the open ssl random function if presents, otherwise the uniqid function.
     *
     * @param string $prefix
     *
     * @return string
     */
    public static function createUniqueName($prefix = 'block')
    {
        return $prefix.(function_exists('openssl_random_pseudo_bytes')
            ? bin2hex(openssl_random_pseudo_bytes(5))
            : uniqid());
    }

    /**
     * Creates the block id.
     *
     * @param BlockInterface $block
     *
     * @return string
     */
    public static function createBlockId(BlockInterface $block)
    {
        $id = '_'.$block->getName();

        if ($block->getParent() && $block->getOption('chained_block')) {
            $id = static::createBlockId($block->getParent()).$id;
        }

        return ltrim($id, '_0123456789');
    }

    /**
     * Check if block is allowed.
     *
     * @param string|array   $allowed
     * @param BlockInterface $block
     *
     * @return bool
     */
    public static function isValidBlock($allowed, BlockInterface $block)
    {
        return static::isValidType((array) $allowed, $block->getConfig()->getType());
    }

    /**
     * Check if the parent type of the current type is allowed.
     *
     * @param array                      $allowed
     * @param ResolvedBlockTypeInterface $rType
     *
     * @return bool
     */
    protected static function isValidType(array $allowed, ResolvedBlockTypeInterface $rType = null)
    {
        if (null === $rType) {
            return false;

        } elseif (!in_array($rType->getName(), $allowed)) {
            return static::isValidType($allowed, $rType->getParent());
        }

        return true;
    }
}
