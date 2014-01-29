<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Bundle\BlockBundle\Block\Extension\Core\DataMapper;

use Sonatra\Bundle\BlockBundle\Block\DataMapperInterface;
use Sonatra\Bundle\BlockBundle\Block\Util\VirtualBlockAwareIterator;
use Sonatra\Bundle\BlockBundle\Block\Exception\UnexpectedTypeException;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class PropertyPathMapper implements DataMapperInterface
{
    /**
     * @var PropertyAccessorInterface
     */
    private $propertyAccessor;

    /**
     * Creates a new property path mapper.
     *
     * @param PropertyAccessorInterface $propertyAccessor
     */
    public function __construct(PropertyAccessorInterface $propertyAccessor = null)
    {
        $this->propertyAccessor = $propertyAccessor ?: PropertyAccess::getPropertyAccessor();
    }

    /**
     * {@inheritdoc}
     */
    public function mapDataToViews($data, array $blocks)
    {
        if (null === $data || array() === $data) {
            return;
        }

        if (!is_array($data) && !is_object($data)) {
            throw new UnexpectedTypeException($data, 'object, array or empty');
        }

        $iterator = new VirtualBlockAwareIterator($blocks);
        $iterator = new \RecursiveIteratorIterator($iterator);

        foreach ($iterator as $block) {
            /* @var BlockInterface $block */
            $propertyPath = $block->getPropertyPath();
            $config = $block->getConfig();

            if (null !== $propertyPath && $config->getMapped()) {
                $block->setData($this->propertyAccessor->getValue($data, $propertyPath));
            }
        }
    }
}
