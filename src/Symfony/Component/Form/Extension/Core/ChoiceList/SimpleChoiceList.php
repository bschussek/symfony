<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Form\Extension\Core\ChoiceList;


use Symfony\Component\Form\Exception\UnexpectedTypeException;

/**
 * A choice list for choices of type string or integer.
 *
 * Choices and their associated labels can be passed in a single array. Since
 * choices are passed as array keys, only strings or integer choices are
 * allowed.
 *
 * <code>
 * $choiceList = new SimpleChoiceList(array(
 *     'creditcard' => 'Credit card payment',
 *     'cash' => 'Cash payment',
 * ));
 * </code>
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class SimpleChoiceList extends ChoiceList
{
    /**
     * Creates a new simple choice list.
     *
     * @param array   $choices          The array of choices with the choices as keys and
     *                                  the labels as values. Choices may also be given
     *                                  as hierarchy of unlimited depth. Hierarchies are
     *                                  created by creating nested arrays. The title of
     *                                  the sub-hierarchy is stored in the array
     *                                  key pointing to the nested array.
     * @param array   $preferredChoices A flat array of choices that should be
     *                                  presented to the user with priority.
     */
    public function __construct(array $choices, array $preferredChoices = array())
    {
        parent::__construct($choices, $choices, $preferredChoices);
    }

    /**
     * Recursively adds the given choices to the list.
     *
     * Takes care of splitting the single $choices array passed in the
     * constructor into choices and labels.
     *
     * @param array $bucketForPreferred
     * @param array $bucketForRemaining
     * @param array $choices
     * @param array $labels
     * @param array $preferredChoices
     *
     * @throws UnexpectedTypeException
     *
     * @see parent::addChoices
     */
    protected function addChoices(&$bucketForPreferred, &$bucketForRemaining, $choices, $labels, array $preferredChoices)
    {
        // Add choices to the nested buckets
        foreach ($choices as $choice => $label) {
            if (is_array($label)) {
                // Don't do the work if the array is empty
                if (count($label) > 0) {
                    $this->addChoiceGroup(
                        $choice,
                        $bucketForPreferred,
                        $bucketForRemaining,
                        $label,
                        $label,
                        $preferredChoices
                    );
                }
            } else {
                $this->addChoice(
                    $bucketForPreferred,
                    $bucketForRemaining,
                    $choice,
                    $label,
                    $preferredChoices
                );
            }
        }
    }

    /**
     * Converts the choice to a valid PHP array key.
     *
     * @param mixed $choice The choice.
     *
     * @return string|integer A valid PHP array key.
     */
    protected function fixChoice($choice)
    {
        return $this->fixIndex($choice);
    }

    /**
     * {@inheritdoc}
     */
    protected function fixChoices(array $choices)
    {
        return $this->fixIndices($choices);
    }

    /**
     * {@inheritdoc}
     */
    protected function testScalarAndStringUnique(array $choices, array &$existingChoices = array())
    {
        // We know this already, since choices are passed as array keys
        return true;
    }
}
