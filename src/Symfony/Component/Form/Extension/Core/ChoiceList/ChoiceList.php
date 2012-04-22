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

use Traversable;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\Form\Exception\InvalidConfigurationException;
use Symfony\Component\Form\Exception\InvalidPropertyException;
use Symfony\Component\Form\Exception\StringCastException;
use Symfony\Component\Form\Exception\FormException;
use Symfony\Component\Form\Extension\Core\View\ChoiceView;
use Symfony\Component\Form\Util\PropertyPath;

/**
 * A choice list for choices of arbitrary data types.
 *
 * Choices and labels are passed in two arrays. The indices of the choices
 * and the labels should match.
 *
 * <code>
 * $choices = array(true, false);
 * $labels = array('Agree', 'Disagree');
 * $choiceList = new ChoiceList($choices, $labels);
 * </code>
 *
 * @author Bernhard Schussek <bschussek@gmail.<com>
 */
class ChoiceList implements ChoiceListInterface
{
    /**
     * The choices with their indices as keys.
     *
     * @var array
     */
    private $choices = array();

    /**
     * The choice values with the indices of the matching choices as keys.
     *
     * @var array
     */
    private $values = array();

    /**
     * The preferred view objects as hierarchy containing also the choice groups
     * with the indices of the matching choices as bottom-level keys.
     *
     * @var array
     */
    private $preferredViews = array();

    /**
     * The non-preferred view objects as hierarchy containing also the choice
     * groups with the indices of the matching choices as bottom-level keys.
     *
     * @var array
     */
    private $remainingViews = array();

    /**
     * Whether choices can be used directly as values in order to improve
     * performance.
     * @var Boolean
     */
    private $choicesAsValues;

    /**
     * Creates a new choice list.
     *
     * @param array|\Traversable $choices    The array of choices. Choices may also be given
     *                                       as hierarchy of unlimited depth. Hierarchies are
     *                                       created by creating nested arrays. The title of
     *                                       the sub-hierarchy can be stored in the array
     *                                       key pointing to the nested array.
     * @param array $preferredChoices        A flat array of choices that should be
     *                                       presented to the user with priority.
     * @param array|callable|string $labels  The array of labels. The structure of this array
     *                                       should match the structure of $choices.
     *                                       If a callable is passed, it will be evaluated
     *                                       for each choice. If a string is passed, the choices
     *                                       must be objects. The string is then interpreted as
     *                                       property path for reading the label.
     * @param array|callable|string $values  The array of values. The structure of this array
     *                                       should match the structure of $choices.
     *                                       If a callable is passed, it will be evaluated
     *                                       for each choice. If a string is passed, the choices
     *                                       must be objects. The string is then interpreted as
     *                                       property path for reading the value.
     * @param callable|string $groupBy       The callable for determining the group(s) of each
     *                                       choice. If a string is passed, the choices
     *                                       must be objects. The string is then interpreted as
     *                                       property path for reading the group(s).
     */
    public function __construct($choices, $preferredChoices = array(), $labels = null, $values = null, $groupBy = null)
    {
        $this->initialize($choices, $preferredChoices, $labels, $values, $groupBy);
    }

    /**
     * Initializes the list with choices.
     *
     * Safe to be called multiple times. The list is cleared on every call.
     *
     * @see __construct
     */
    protected function initialize($choices, $preferredChoices, $labels, $values, $groupBy)
    {
        if (!is_array($choices) && !$choices instanceof Traversable) {
            throw new UnexpectedTypeException($choices, 'array or \Traversable');
        }

        if (!is_array($preferredChoices) && !is_callable($preferredChoices) && !is_string($preferredChoices)) {
            throw new UnexpectedTypeException($preferredChoices, 'array, callable or string');
        }

        if (null !== $labels && !is_array($labels) && !is_callable($labels) && !is_string($labels)) {
            throw new UnexpectedTypeException($labels, 'null, array, callable or string');
        }

        if (null !== $values && !is_array($values) && !is_callable($values) && !is_string($values)) {
            throw new UnexpectedTypeException($values, 'null, array, callable or string');
        }

        if (null !== $groupBy && !is_callable($groupBy) && !is_string($groupBy)) {
            throw new UnexpectedTypeException($groupBy, 'null, callable or string');
        }

        $this->choices = array();
        $this->values = array();
        $this->preferredViews = array();
        $this->remainingViews = array();
        $this->choicesAsValues = null === $values
            ? $this->testScalarAndStringUnique($choices)
            : false;

        $objectsOnly = $this->choicesAsValues
            ? false
            : $this->testObjectsOnly($choices);

        // Groups as property paths
        if (is_string($groupBy)) {
            if (!$objectsOnly) {
                throw new FormException('The choice groups can only be configured as property path if the choices are objects.');
            }

            $groupPath = new PropertyPath($groupBy);
            $groupBy = function ($choice) use ($groupPath) {
                try {
                    return $groupPath->getValue($choice);
                } catch (InvalidPropertyException $e) {
                    return null;
                }
            };
        }

        // Preferred as property path
        if (is_string($preferredChoices)) {
            if (!$objectsOnly) {
                throw new FormException('The preferred choices can only be configured as property path if the choices are objects.');
            }

            $preferredPath = new PropertyPath($preferredChoices);
            $preferredChoices = function ($choice) use ($preferredPath) {
                try {
                    return (Boolean) $preferredPath->getValue($choice);
                } catch (InvalidPropertyException $e) {
                    return false;
                }
            };
        }

        // Labels as property paths
        if (is_string($labels)) {
            if (!$objectsOnly) {
                throw new FormException('The choice labels can only be configured as property path if the choices are objects.');
            }

            $labelPath = new PropertyPath($labels);
            $labels = function ($choice) use ($labelPath) {
                return (string) $labelPath->getValue($choice);
            };
        } elseif (null === $labels) {
            if ($objectsOnly) {
                $labels = function ($choice) {
                    if (!method_exists($choice, '__toString')) {
                        throw new StringCastException('A "__toString()" method was not found on the objects of type "' . get_class($choice) . '" passed to the choice field. To read a custom getter instead, set the choice labels to the desired property path.');
                    }

                    return $choice->__toString();
                };
            } else {
                $labels = $choices;
            }
        }

        // Values as property paths
        if (is_string($values)) {
            if (!$objectsOnly) {
                throw new FormException('The choice values can only be configured as property path if the choices are objects.');
            }

            $valuePath = new PropertyPath($values);
            $values = function ($choice) use ($valuePath) {
                return (string) $valuePath->getValue($choice);
            };
        } elseif (null === $values) {
            // If all of the choices are scalar and if the choices array contains
            // no duplicates (when converted to string), use choices directly as
            // values
            if ($this->choicesAsValues) {
                $values = function ($choice) {
                    return (string) $choice;
                };
            // Else generate integer numbers
            } else {
                $nextVal = 0;
                $values = function ($choice) use (&$nextVal) {
                    return (string) $nextVal++;
                };
            }
        }

        if (is_callable($groupBy)) {
            // This closure identifies the groups of every choice and stores the
            // result in an array with the same structure as the original
            // $choices array
            $groupBy = function ($choice) use ($groupBy) {
                $groups = call_user_func($groupBy, $choice);

                if (null !== $groups && !is_string($groups) && !is_array($groups)) {
                    throw new FormException('The callable generating the choice group should return a string or an array of strings, but returned a ' . gettype($groups) . ' instead.');
                }

                if (is_array($groups)) {
                    foreach ($groups as $group) {
                        if (!is_string($group)) {
                            throw new FormException('The callable generating the choice group should return an array of strings, but returned an array with a ' . gettype($group) . ' instead.');
                        }
                    }
                }

                return (array) $groups;
            };

            $groupInto = function (&$output) {
                // Sorts $choice into $output at the level specified by
                // $groups[$key]
                return function ($choice, $key, array $groups) use (&$output) {
                    if (is_array($choice)) {
                        throw new FormException('The passed arrays should be flat when using choice grouping. A nested array was found at index ' . $key . '.');
                    }

                    if (!array_key_exists($key, $groups)) {
                        throw new FormException('The choice values/labels should have the same keys as the choices.');
                    }

                    foreach ($groups[$key] as $group) {
                        if (!isset($output[$group])) {
                            $output[$group] = array();
                        }

                        $output = &$output[$group];
                    }

                    $output[] = $choice;
                };
            };

            $groups = array_map($groupBy, $choices);

            $groupedChoices = array();
            array_walk($choices, $groupInto($groupedChoices), $groups);
            $choices = $groupedChoices;

            if (is_array($labels)) {
                $groupedLabels = array();
                array_walk($labels, $groupInto($groupedLabels), $groups);
                $labels = $groupedLabels;
            }

            if (is_array($values)) {
                $groupedValues = array();
                array_walk($values, $groupInto($groupedValues), $groups);
                $values = $groupedValues;
            }
        }

        if (is_callable($labels)) {
            // Wrap callable to test its return value
            $labels = self::arrayMapRecursive($choices, function ($choice) use ($labels) {
                $label = call_user_func($labels, $choice);

                if (!is_string($label)) {
                    throw new FormException('The callable generating the choice labels should return strings, but returned a ' . gettype($label) . ' instead.');
                }

                return $label;
            });
        }

        if (is_callable($preferredChoices)) {
            // Wrap callable to test its return value
            $preferredChoices = self::arrayFilterRecursive($choices, function ($choice) use ($preferredChoices) {
                $isPreferred = call_user_func($preferredChoices, $choice);

                if (!is_bool($isPreferred)) {
                    throw new FormException('The callable deciding whether a choice is preferred should return a Boolean, but returned a ' . gettype($isPreferred) . ' instead.');
                }

                return $isPreferred;
            });
        }

        if (is_callable($values)) {
            // Wrap callable to test its return value
            $values = self::arrayMapRecursive($choices, function ($choice) use ($values) {
                $value = call_user_func($values, $choice);

                if (!is_string($value)) {
                    throw new FormException('The callable generating the choice values should return strings, but returned a ' . gettype($value) . ' instead.');
                }

                return $value;
            });
        };

        // Flip preferred choices to speed up lookup if our choices can be
        // converted to unique strings
        if ($this->choicesAsValues) {
            $preferredChoices = array_flip($preferredChoices);
        }

        $this->addChoices(
            $this->preferredViews,
            $this->remainingViews,
            $choices,
            $labels,
            $preferredChoices,
            $values
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getChoices()
    {
        return $this->choices;
    }

    /**
     * {@inheritdoc}
     */
    public function getValues()
    {
        return $this->values;
    }

    /**
     * {@inheritdoc}
     */
    public function getPreferredViews()
    {
        return $this->preferredViews;
    }

    /**
     * {@inheritdoc}
     */
    public function getRemainingViews()
    {
        return $this->remainingViews;
    }

    /**
     * {@inheritdoc}
     */
    public function getChoicesForValues(array $values)
    {
        $values = $this->fixValues($values);

        if ($this->choicesAsValues) {
            // The values are identical to the choices, so we can just return them
            // to improve performance a little bit
            return $this->fixChoices(array_intersect($values, $this->getValues()));
        }

        $choices = array();

        foreach ($values as $j => $givenValue) {
            foreach ($this->values as $i => $value) {
                if ($value === $givenValue) {
                    $choices[] = $this->choices[$i];
                    unset($values[$j]);

                    if (0 === count($values)) {
                        break 2;
                    }
                }
            }
        }

        return $choices;
    }

    /**
     * {@inheritdoc}
     */
    public function getValuesForChoices(array $choices)
    {
        $choices = $this->fixChoices($choices);

        if ($this->choicesAsValues) {
            // The choices are identical to the values, so we can just return them
            // to improve performance a little bit
            return $this->fixValues(array_intersect($choices, $this->getValues()));
        }

        $values = array();

        foreach ($this->choices as $i => $choice) {
            foreach ($choices as $j => $givenChoice) {
                if ($choice === $givenChoice) {
                    $values[] = $this->values[$i];
                    unset($choices[$j]);

                    if (0 === count($choices)) {
                        break 2;
                    }
                }
            }
        }

        return $values;
    }

    /**
     * {@inheritdoc}
     */
    public function getIndicesForValues(array $values)
    {
        $values = $this->fixValues($values);
        $indices = array();

        foreach ($this->values as $i => $value) {
            foreach ($values as $j => $givenValue) {
                if ($value === $givenValue) {
                    $indices[] = $i;
                    unset($values[$j]);

                    if (0 === count($values)) {
                        break 2;
                    }
                }
            }
        }

        return $indices;
    }

    /**
     * Recursively adds the given choices to the list.
     *
     * @param array $bucketForPreferred The bucket where to store the preferred
     *                                  view objects.
     * @param array $bucketForRemaining The bucket where to store the
     *                                  non-preferred view objects.
     * @param array $choices The list of choices.
     * @param array $labels The labels corresponding to the choices.
     * @param array $preferredChoices The preferred choices.
     *
     * @throws UnexpectedTypeException If the structure of the $labels array
     *                                 does not match the structure of the
     *                                 $choices array.
     */
    protected function addChoices(&$bucketForPreferred, &$bucketForRemaining, $choices, array $labels, array $preferredChoices, array $values)
    {
        // Add choices to the nested buckets
        foreach ($choices as $group => $choice) {
            $choiceGroup = is_array($choice);

            if (!isset($labels[$group]) || $choiceGroup !== is_array($labels[$group])) {
                throw new FormException('The choice labels should have the same structure as the choices.');
            }

            if (!isset($values[$group]) || $choiceGroup !== is_array($values[$group])) {
                throw new FormException('The choice values should have the same structure as the choices.');
            }

            if (is_array($choice)) {
                // Don't do the work if the array is empty
                if (count($choice) > 0) {
                    $this->addChoiceGroup(
                        $group,
                        $bucketForPreferred,
                        $bucketForRemaining,
                        $choice,
                        $labels[$group],
                        $preferredChoices,
                        $values[$group]
                    );
                }
            } else {
                $this->addChoice(
                    $bucketForPreferred,
                    $bucketForRemaining,
                    $choice,
                    $labels[$group],
                    $preferredChoices,
                    $values[$group]
                );
            }
        }
    }

    /**
     * Recursively adds a choice group.
     *
     * @param string $group The name of the group.
     * @param array $bucketForPreferred The bucket where to store the preferred
     *                                  view objects.
     * @param array $bucketForRemaining The bucket where to store the
     *                                  non-preferred view objects.
     * @param array $choices The list of choices in the group.
     * @param array $labels The labels corresponding to the choices in the group.
     * @param array $preferredChoices The preferred choices.
     */
    protected function addChoiceGroup($group, &$bucketForPreferred, &$bucketForRemaining, $choices, $labels, array $preferredChoices, array $values)
    {
        // If this is a choice group, create a new level in the choice
        // key hierarchy
        $bucketForPreferred[$group] = array();
        $bucketForRemaining[$group] = array();

        $this->addChoices(
            $bucketForPreferred[$group],
            $bucketForRemaining[$group],
            $choices,
            $labels,
            $preferredChoices,
            $values
        );

        // Remove child levels if empty
        if (empty($bucketForPreferred[$group])) {
            unset($bucketForPreferred[$group]);
        }
        if (empty($bucketForRemaining[$group])) {
            unset($bucketForRemaining[$group]);
        }
    }

    /**
     * Adds a new choice.
     *
     * @param array $bucketForPreferred The bucket where to store the preferred
     *                                  view objects.
     * @param array $bucketForRemaining The bucket where to store the
     *                                  non-preferred view objects.
     * @param mixed $choice The choice to add.
     * @param string $label The label for the choice.
     * @param array $preferredChoices The preferred choices.
     */
    protected function addChoice(&$bucketForPreferred, &$bucketForRemaining, $choice, $label, array $preferredChoices, $value)
    {
        // Outsource index creation in order to allow changing the indexing
        // strategy. This is generally not recommended, but may allow
        // performance improvements for certain implementations (such as
        // EntityChoiceList)
        $index = $this->createIndex($choice);

        $this->choices[$index] = $this->fixChoice($choice);
        $this->values[$index] = (string) $value;

        $view = new ChoiceView((string) $value, (string) $label);

        if ($this->isPreferred($choice, $preferredChoices)) {
            $bucketForPreferred[$index] = $view;
        } else {
            $bucketForRemaining[$index] = $view;
        }
    }

    /**
     * Returns whether the given choice should be preferred judging by the
     * given array of preferred choices.
     *
     * Extension point to optimize performance by changing the structure of the
     * $preferredChoices array.
     *
     * @param mixed $choice The choice to test.
     * @param array $preferredChoices An array of preferred choices.
     */
    protected function isPreferred($choice, $preferredChoices)
    {
        if ($this->choicesAsValues) {
            // Optimize performance over the default implementation
            return isset($preferredChoices[$choice]);
        }

        return false !== array_search($choice, $preferredChoices, true);
    }

    /**
     * Creates a new unique index for this choice.
     *
     * Extension point to change the indexing strategy.
     *
     * @param mixed $choice The choice to create an index for
     *
     * @return integer|string A unique index containing only ASCII letters,
     *                        digits and underscores.
     */
    protected function createIndex($choice)
    {
        return count($this->choices);
    }

    /**
     * Fixes the data type of the given choice value to avoid comparison
     * problems.
     *
     * @param mixed $value The choice value.
     *
     * @return string The value as string.
     */
    protected function fixValue($value)
    {
        return (string) $value;
    }

    /**
     * Fixes the data types of the given choice values to avoid comparison
     * problems.
     *
     * @param array $values The choice values.
     *
     * @return array The values as strings.
     */
    protected function fixValues(array $values)
    {
        foreach ($values as $i => $value) {
            $values[$i] = $this->fixValue($value);
        }

        return $values;
    }

    /**
     * Fixes the data type of the given choice index to avoid comparison
     * problems.
     *
     * @param mixed $index The choice index.
     *
     * @return integer|string The index as PHP array key.
     */
    protected function fixIndex($index)
    {
        if (is_bool($index) || (string) (int) $index === (string) $index) {
            return (int) $index;
        }

        return (string) $index;
    }

    /**
     * Fixes the data types of the given choice indices to avoid comparison
     * problems.
     *
     * @param array $indices The choice indices.
     *
     * @return array The indices as strings.
     */
    protected function fixIndices(array $indices)
    {
        foreach ($indices as $i => $index) {
            $indices[$i] = $this->fixIndex($index);
        }

        return $indices;
    }

    /**
     * Fixes the data type of the given choice to avoid comparison problems.
     *
     * Extension point. In this implementation, choices are guaranteed to
     * always maintain their type and thus can be typesafely compared.
     *
     * @param mixed $choice The choice.
     *
     * @return mixed The fixed choice.
     */
    protected function fixChoice($choice)
    {
        return $choice;
    }

    /**
    * Fixes the data type of the given choices to avoid comparison problems.
     *
    * @param array $choice The choices.
    *
    * @return array The fixed choices.
    *
    * @see fixChoice
    */
    protected function fixChoices(array $choices)
    {
        return $choices;
    }

    /**
     * Checks that all of the given choices are scalar and contain no
     * duplicates when converted to strings.
     *
     * @param  array $choices  The choices to check.
     *
     * @return Boolean  Whether all choices are scalar and string-unique.
     */
    protected function testScalarAndStringUnique(array $choices, array &$existingChoices = array())
    {
        foreach ($choices as $choice) {
            // Support for choice groups
            if (is_array($choice)) {
                if (!$this->testScalarAndStringUnique($choice, $existingChoices)) {
                    return false;
                }

                // Go to next choice (group)
                continue;
            }

            if (!is_scalar($choice)) {
                return false;
            }

            $choice = (string) $choice;

            if (isset($existingChoices[$choice])) {
                return false;
            }

            $existingChoices[$choice] = true;
        }

        return true;
    }

    /**
     * Checks whether all of the choices are objects.
     *
     * @param  array $choices  The choices to check.
     *
     * @return Boolean  Whether all of the choices are objects.
     */
    protected function testObjectsOnly(array $choices)
    {
        foreach ($choices as $choice) {
            // Support for choice groups
            if (is_array($choice)) {
                if (!$this->testObjectsOnly($choice)) {
                    return false;
                }

                // Go to next choice (group)
                continue;
            }

            if (!is_object($choice)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Recursive implementation of array_map().
     *
     * @param  array $array        The input array.
     * @param  callable $callable  The callable to apply onto each element.
     *
     * @return array  The array with the results of the function calls.
     */
    private static function arrayMapRecursive(array $array, $callable)
    {
        $result = array();

        foreach ($array as $key => $value) {
            $result[$key] = is_array($value)
                ? self::arrayMapRecursive($value, $callable)
                : call_user_func($callable, $value);
        }

        return $result;
    }

    /**
     * Recursive implementation of array_filter().
     *
     * @param  array $array        The array to filter.
     * @param  callable $callable  The callable deciding whether to accept an
     *                             element of the input array.
     *
     * @return array  The filtered array.
     */
    private static function arrayFilterRecursive(array $array, $callable, array &$result = array())
    {
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                self::arrayFilterRecursive($value, $callable, $result);
            } elseif (call_user_func($callable, $value)) {
                $result[] = $value;
            }
        }

        return $result;
    }
}
