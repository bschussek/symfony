<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Form\Extension\Core\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\Form\Extension\Core\DataTransformer\IntegerToLocalizedStringTransformer;
use Symfony\Component\OptionsResolver\OptionsResolver;

class IntegerType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder->appendClientTransformer(
            new IntegerToLocalizedStringTransformer(
                $options['precision'],
                $options['grouping'],
                $options['rounding_mode']
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            // default precision is locale specific (usually around 3)
            'precision'      => null,
            'grouping'       => false,
            // Integer cast rounds towards 0, so do the same when displaying fractions
            'rounding_mode'  => \NumberFormatter::ROUND_DOWN,
            'single_control' => true,
        ));

        $resolver->setAllowedValues(array(
            'rounding_mode' => array(
                \NumberFormatter::ROUND_FLOOR,
                \NumberFormatter::ROUND_DOWN,
                \NumberFormatter::ROUND_HALFDOWN,
                \NumberFormatter::ROUND_HALFEVEN,
                \NumberFormatter::ROUND_HALFUP,
                \NumberFormatter::ROUND_UP,
                \NumberFormatter::ROUND_CEILING,
            ),
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getParent(array $options)
    {
        return 'field';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'integer';
    }
}
