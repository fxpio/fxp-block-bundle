<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Bundle\BlockBundle\Block\Guess;

/**
 * Contains a guessed value
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class ValueGuess extends Guess
{
    /**
     * The guessed value.
     * @var array
     */
    private $value;

    /**
     * Constructor.
     *
     * @param string  $value      The guessed value
     * @param integer $confidence The confidence that the guessed class name
     *                            is correct
     */
    public function __construct($value, $confidence)
    {
        parent::__construct($confidence);

        $this->value = $value;
    }

    /**
     * Returns the guessed value.
     *
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }
}
