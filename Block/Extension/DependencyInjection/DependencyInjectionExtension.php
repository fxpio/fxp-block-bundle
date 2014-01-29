<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Bundle\BlockBundle\Block\Extension\DependencyInjection;

use Sonatra\Bundle\BlockBundle\Block\BlockExtensionInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Sonatra\Bundle\BlockBundle\Block\BlockTypeGuesserChain;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class DependencyInjectionExtension implements BlockExtensionInterface
{
    protected $container;
    protected $typeServiceIds;
    protected $typeExtensionServiceIds;
    protected $guesserServiceIds;
    protected $guesser;
    protected $guesserLoaded = false;

    /**
     * Constructor.
     *
     * @param ContainerInterface $container
     * @param array              $typeServiceIds
     * @param array              $typeExtensionServiceIds
     * @param array              $guesserServiceIds
     */
    public function __construct(ContainerInterface $container,
            array $typeServiceIds, array $typeExtensionServiceIds,
            array $guesserServiceIds)
    {
        $this->container = $container;
        $this->typeServiceIds = $typeServiceIds;
        $this->typeExtensionServiceIds = $typeExtensionServiceIds;
        $this->guesserServiceIds = $guesserServiceIds;
    }

    /**
     * {@inheritdoc}
     */
    public function getType($name)
    {
        if (!isset($this->typeServiceIds[$name])) {
            throw new \InvalidArgumentException(sprintf('The field type "%s" is not registered with the service container.', $name));
        }

        $type = $this->container->get($this->typeServiceIds[$name]);

        if ($type->getName() !== $name) {
            throw new \InvalidArgumentException(
                    sprintf('The type name specified for the service "%s" does not match the actual name. Expected "%s", given "%s"',
                            $this->typeServiceIds[$name],
                            $name,
                            $type->getName()
                    ));
        }

        return $type;
    }

    /**
     * {@inheritdoc}
     */
    public function hasType($name)
    {
        return isset($this->typeServiceIds[$name]);
    }

    /**
     * {@inheritdoc}
     */
    public function getTypeExtensions($name)
    {
        $extensions = array();

        if (isset($this->typeExtensionServiceIds[$name])) {
            foreach ($this->typeExtensionServiceIds[$name] as $serviceId) {
                $extensions[] = $this->container->get($serviceId);
            }
        }

        return $extensions;
    }

    /**
     * {@inheritdoc}
     */
    public function hasTypeExtensions($name)
    {
        return isset($this->typeExtensionServiceIds[$name]);
    }

    /**
     * {@inheritdoc}
     */
    public function getTypeGuesser()
    {
        if (!$this->guesserLoaded) {
            $this->guesserLoaded = true;
            $guessers = array();

            foreach ($this->guesserServiceIds as $serviceId) {
                $guessers[] = $this->container->get($serviceId);
            }

            if (count($guessers) > 0) {
                $this->guesser = new BlockTypeGuesserChain($guessers);
            }
        }

        return $this->guesser;
    }
}
