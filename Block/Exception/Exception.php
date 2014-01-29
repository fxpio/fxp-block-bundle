<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Bundle\BlockBundle\Block\Exception;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 *
 * @deprecated This class is a replacement for when class BlockException was
 *             used previously. It should not be used and will be removed.
 *             Occurrences of this class should be replaced by more specialized
 *             exception classes, preferably derived from SPL exceptions.
 */
class Exception extends \Exception implements ExceptionInterface
{
}
