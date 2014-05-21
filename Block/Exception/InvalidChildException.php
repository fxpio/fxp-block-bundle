<?php

/**
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Bundle\BlockBundle\Block\Exception;

use Sonatra\Bundle\BlockBundle\Block\BlockBuilderInterface;

/**
 * Base InvalidChildException for the Block and Block builder component.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class InvalidChildException extends InvalidArgumentException
{
    /**
     * Constructor.
     *
     * @param BlockBuilderInterface $builder
     * @param BlockBuilderInterface $builderChild
     */
    public function __construct(BlockBuilderInterface $builder, BlockBuilderInterface $builderChild)
    {
        parent::__construct(sprintf('The child "%s" (%s) is not allowed for "%s" block (%s)', $builderChild->getName(), $builderChild->getType()->getName(), $builder->getName(), $builder->getType()->getName()));
    }
}
