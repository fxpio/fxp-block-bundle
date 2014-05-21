<?php

/**
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Bundle\BlockBundle\Block\Extension\Core\DataTransformer;

use Sonatra\Bundle\BlockBundle\Block\DataTransformerInterface;
use Sonatra\Bundle\BlockBundle\Block\Exception\UnexpectedTypeException;
use Symfony\Component\Form\Extension\Core\ChoiceList\ChoiceListInterface;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class ChoicesToValuesTransformer implements DataTransformerInterface
{
    private $choiceList;

    /**
     * Constructor.
     *
     * @param ChoiceListInterface $choiceList
     */
    public function __construct(ChoiceListInterface $choiceList)
    {
        $this->choiceList = $choiceList;
    }

    /**
     * @param array $array
     *
     * @return array
     *
     * @throws UnexpectedTypeException if the given value is not an array
     */
    public function transform($array)
    {
        if (null === $array) {
            return array();
        }

        if (!is_array($array)) {
            throw new UnexpectedTypeException($array, 'array');
        }

        return $this->choiceList->getValuesForChoices($array);
    }
}
