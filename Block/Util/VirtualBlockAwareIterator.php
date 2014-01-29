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

/**
 * Iterator that traverses fields of a field group.
 *
 * If the iterator encounters a virtual field group, it enters the field
 * group and traverses its children as well.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class VirtualBlockAwareIterator extends \ArrayIterator implements \RecursiveIterator
{
    /**
     * {@inheritdoc}
     */
    public function getChildren()
    {
        return new self($this->current()->all());
    }

    /**
     * {@inheritdoc}
     */
    public function hasChildren()
    {
        return $this->current()->getConfig()->getVirtual();
    }
}
