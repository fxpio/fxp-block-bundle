<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Bundle\BlockBundle\Doctrine\Block\ChoiceList;

use Symfony\Component\Form\Extension\Core\ChoiceList\ChoiceListInterface;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * A choice list presenting a list of Doctrine entities as choices.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class EntityChoiceList implements ChoiceListInterface
{
    /**
     * @var RegistryInterface
     */
    protected $registry;

    /**
     * @var string
     */
    protected $labelPath;

    /**
     * @var array
     */
    protected $remainingViews = array();

    /**
     * @var array
     */
    protected $indicesForChoices = array();

    /**
     * Creates a new block entity choice list.
     *
     * @param RegistryInterface $registry  The doctrine instance
     * @param string            $labelPath The property path used for the label
     */
    public function __construct(RegistryInterface $registry, $labelPath = null)
    {
        $this->registry = $registry;
        $this->labelPath = $labelPath;
    }

    /**
     * Returns the list of entities.
     *
     * @return array
     *
     * @see Symfony\Component\Form\Extension\Core\ChoiceList\ChoiceListInterface
     */
    public function getChoices()
    {
        return array();
    }

    /**
     * Returns the values for the entities.
     *
     * @return array
     *
     * @see Symfony\Component\Form\Extension\Core\ChoiceList\ChoiceListInterface
     */
    public function getValues()
    {
        return array();
    }

    /**
     * Returns the choice blocks of the preferred choices as nested array with
     * the choice groups as top-level keys.
     *
     * @return array
     *
     * @see Symfony\Component\Form\Extension\Core\ChoiceList\ChoiceListInterface
     */
    public function getPreferredViews()
    {
        return array();
    }

    /**
     * Returns the choice blocks of the choices that are not preferred as nested
     * array with the choice groups as top-level keys.
     *
     * @return array
     *
     * @see Symfony\Component\Form\Extension\Core\ChoiceList\ChoiceListInterface
     */
    public function getRemainingViews()
    {
        return $this->remainingViews;
    }

    /**
     * Returns the entities corresponding to the given values.
     *
     * @param array $values
     *
     * @return array
     *
     * @see Symfony\Component\Form\Extension\Core\ChoiceList\ChoiceListInterface
     */
    public function getChoicesForValues(array $values)
    {
        return array();
    }

    /**
     * Returns the values corresponding to the given entities.
     *
     * @param array $entities
     *
     * @return array
     *
     * @see Symfony\Component\Form\Extension\Core\ChoiceList\ChoiceListInterface
     */
    public function getValuesForChoices(array $entities)
    {
        return array();
    }

    /**
     * Returns the indices corresponding to the given entities.
     *
     * @param array $entities
     *
     * @return array
     *
     * @see Symfony\Component\Form\Extension\Core\ChoiceList\ChoiceListInterface
     */
    public function getIndicesForChoices(array $entities)
    {
        $this->generateChoices($entities);

        return $this->indicesForChoices;
    }

    /**
     * Returns the entities corresponding to the given values.
     *
     * @param array $values
     *
     * @return array
     *
     * @see Symfony\Component\Form\Extension\Core\ChoiceList\ChoiceListInterface
     */
    public function getIndicesForValues(array $values)
    {
        return array();
    }

    /**
     * Generate the entity choice list for block.
     *
     * @param array $entities
     */
    protected function generateChoices(array $entities)
    {
        if (count($entities) > 0 && count($this->indicesForChoices) > 0) {
            return;
        }

        $this->remainingViews = array();
        $this->indicesForChoices = array();

        if (0 === count($entities)) {
            return;
        }

        if (!is_object($entities[0])) {
            return;
        }

        $class = get_class($entities[0]);
        $manager = $this->registry->getManagerForClass($class);
        $cm = $manager->getClassMetadata($class);
        $class = $cm->getName();
        $identifier = $cm->getIdentifierFieldNames();
        $idField = null;
        $idAsValue = false;
        $idAsIndex = false;

        if (1 === count($identifier)) {
            $idField = $identifier[0];
            $idAsValue = true;

            if ('integer' === $cm->getTypeOfField($idField)) {
                $idAsIndex = true;
            }
        }

        $methodId = $this->getPropertyLabel($class, $idField);
        $methodLabel = $this->getPropertyLabel($class, $this->labelPath);

        if (null === $methodId || null === $methodLabel) {
            return;
        }

        foreach ($entities as $index => $entity) {
            $this->indicesForChoices[$entity->$methodId()] = $index;
            $this->remainingViews[$index] = array(
                    'value' => $entity->$methodId(),
                    'label' => $entity->$methodLabel(),
            );
        }
    }

    /**
     * Get the mathod name of property identifier.
     *
     * @param string $class
     * @param string $property
     *
     * @return string|null
     */
    protected function getPropertyId($class, $property)
    {
        $ref = new \ReflectionClass($class);
        $method = 'get'.ucfirst($property);

        if ($ref->hasMethod($method)) {
            return $method;
        }

        return null;
    }

    /**
     * Get the mathod name of property label.
     *
     * @param string $class
     * @param string $property
     *
     * @return string|null
     */
    protected function getPropertyLabel($class, $property)
    {
        $ref = new \ReflectionClass($class);
        $methodLabelGet = 'get'.ucfirst($property);
        $methodLabelHas = 'has'.ucfirst($property);
        $methodLabelIs = 'is'.ucfirst($property);

        if ($ref->hasMethod($methodLabelGet)) {
            return $methodLabelGet;
        }

        if ($ref->hasMethod($methodLabelHas)) {
            return $methodLabelHas;
        }

        if ($ref->hasMethod($methodLabelIs)) {
            return $methodLabelIs;
        }

        return null;
    }
}
