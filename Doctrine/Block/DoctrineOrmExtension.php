<?php

/**
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Bundle\BlockBundle\Doctrine\Block;

use Sonatra\Bundle\BlockBundle\Block\AbstractExtension;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class DoctrineOrmExtension extends AbstractExtension
{
    protected $registry;

    /**
     * Constructor.
     *
     * @param RegistryInterface $registry
     */
    public function __construct(RegistryInterface $registry)
    {
        $this->registry = $registry;
    }

    /**
     * {@inheritdoc}
     */
    protected function loadTypes()
    {
        return array(
            new Type\EntityType($this->registry),
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function loadTypeGuesser()
    {
        return new DoctrineOrmTypeGuesser($this->registry);
    }
}
