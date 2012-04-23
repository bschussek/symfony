<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\OptionsParser;

use Symfony\Component\OptionsParser\Exception\OptionDefinitionException;
use Symfony\Component\OptionsParser\Exception\InvalidOptionsException;
use Symfony\Component\OptionsParser\Exception\MissingOptionsException;

/**
 * Helper for merging default and concrete option values.
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class OptionsParser
{
    /**
     * The default option values.
     * @var Options
     */
    private $defaultOptions;

    /**
     * The options known by the parser.
     * @var array
     */
    private $knownOptions = array();

    /**
     * The options required to be passed to parse().
     * @var array
     */
    private $requiredOptions = array();

    /**
     * A list of accepted values for each option.
     * @var array
     */
    private $allowedValues = array();

    /**
     * Creates a new instance.
     */
    public function __construct()
    {
        $this->defaultOptions = new Options();
    }

    /**
     * Sets default option values.
     *
     * @param array $options A list of option names as keys and default values
     *                       as values. The option values may be closures
     *                       of the following signatures:
     *
     *                         - function (Options $options)
     *                         - function (Options $options, $previousValue)
     */
    public function setDefaults(array $defaultValues)
    {
        foreach ($defaultValues as $option => $value) {
            $this->defaultOptions[$option] = $value;
            $this->knownOptions[$option] = true;
        }
    }

    /**
     * Sets optional options.
     *
     * This method is identical to `setDefaults`, only that no default values
     * are configured for the options. If these options are not passed to
     * parse(), they will be missing in the final options array. This can be
     * helpful if you want to determine whether an option has been set or not.
     *
     * @param  array $optionNames  A list of option names.
     *
     * @throws OptionDefinitionException  When trying to pass default values.
     */
    public function setOptional(array $optionNames)
    {
        foreach ($optionNames as $key => $option) {
            if (!is_int($key)) {
                throw new OptionDefinitionException('You should not pass default values to setOptional()');
            }

            $this->knownOptions[$option] = true;
        }
    }

    /**
     * Sets required options.
     *
     * If these options are not passed to parse(), an exception will be thrown.
     *
     * @param  array $optionNames  A list of option names.
     *
     * @throws OptionDefinitionException  When trying to pass default values.
     */
    public function setRequired(array $optionNames)
    {
        foreach ($optionNames as $key => $option) {
            if (!is_int($key)) {
                throw new OptionDefinitionException('You should not pass default values to setRequired()');
            }

            $this->knownOptions[$option] = true;
            $this->requiredOptions[$option] = true;
        }
    }

    /**
     * Sets allowed values for a list of options.
     *
     * @param array $allowedValues A list of option names as keys and arrays
     *                             with values acceptable for that option as
     *                             values.
     *
     * @throws InvalidOptionsException If an option has not been defined for
     *                                 which an allowed value is set.
     */
    public function setAllowedValues(array $allowedValues)
    {
        $this->validateOptionNames(array_keys($allowedValues));

        $this->allowedValues = array_replace($this->allowedValues, $allowedValues);
    }

    /**
     * Adds allowed values for a list of options.
     *
     * The values are merged with the allowed values defined previously.
     *
     * @param array $allowedValues A list of option names as keys and arrays
     *                             with values acceptable for that option as
     *                             values.
     *
     * @throws InvalidOptionsException If an option has not been defined for
     *                                 which an allowed value is set.
     */
    public function addAllowedValues(array $allowedValues)
    {
        $this->validateOptionNames(array_keys($allowedValues));

        $this->allowedValues = array_merge_recursive($this->allowedValues, $allowedValues);
    }

    /**
     * Returns the combination of the default and the passed options.
     *
     * @param  array $options  The custom option values.
     *
     * @return array  A list of options and their values.
     *
     * @throws InvalidOptionsException   If any of the passed options has not
     *                                   been defined or does not contain an
     *                                   allowed value.
     * @throws MissingOptionsException   If a required option is missing.
     * @throws OptionDefinitionException If a cyclic dependency is detected
     *                                   between two lazy options.
     */
    public function parse(array $options)
    {
        $this->validateOptionNames(array_keys($options));

        // Make sure this method can be called multiple times
        $combinedOptions = clone $this->defaultOptions;

        // Override options set by the user
        foreach ($options as $option => $value) {
            $combinedOptions[$option] = $value;
        }

        // Resolve options
        $combinedOptions = iterator_to_array($combinedOptions);

        // Validate against allowed values
        $this->validateOptionValues($combinedOptions);

        return $combinedOptions;
    }

    /**
     * Validates that the given option names exist and throws an exception
     * otherwise.
     *
     * @param  array $optionNames  A list of option names.
     *
     * @throws InvalidOptionsException  If any of the options has not been
     *                                  defined.
     */
    private function validateOptionNames(array $optionNames)
    {
        ksort($this->knownOptions);

        $knownOptions = array_keys($this->knownOptions);
        $diff = array_diff($optionNames, $knownOptions);

        sort($diff);

        if (count($diff) > 1) {
            throw new InvalidOptionsException(sprintf('The options "%s" do not exist. Known options are: "%s"', implode('", "', $diff), implode('", "', $knownOptions)));
        }

        if (count($diff) > 0) {
            throw new InvalidOptionsException(sprintf('The option "%s" does not exist. Known options are: "%s"', current($diff), implode('", "', $knownOptions)));
        }

        ksort($this->requiredOptions);

        $requiredOptions = array_keys($this->requiredOptions);
        $diff = array_diff($requiredOptions, $optionNames);

        sort($diff);

        if (count($diff) > 1) {
            throw new MissingOptionsException(sprintf('The options "%s" are missing.', implode('", "', $diff)));
        }

        if (count($diff) > 0) {
            throw new MissingOptionsException(sprintf('The option "%s" is missing.', current($diff)));
        }
    }

    /**
     * Validates that the given option values match the allowed values and
     * throws an exception otherwise.
     *
     * @param  array $options  A list of option values.
     *
     * @throws InvalidOptionsException  If any of the values does not match the
     *                                  allowed values of the option.
     */
    private function validateOptionValues(array $options)
    {
        foreach ($this->allowedValues as $option => $allowedValues) {
            if (!in_array($options[$option], $allowedValues, true)) {
                throw new InvalidOptionsException(sprintf('The option "%s" has the value "%s", but is expected to be one of "%s"', $option, $options[$option], implode('", "', $allowedValues)));
            }
        }
    }
}