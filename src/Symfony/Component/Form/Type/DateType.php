<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Form\Type;

use Symfony\Component\Form\FormBuilder;
use Symfony\Component\Form\ChoiceList\PaddedChoiceList;
use Symfony\Component\Form\ChoiceList\MonthChoiceList;
use Symfony\Component\Form\Renderer\Plugin\DatePatternPlugin;
use Symfony\Component\Form\DataTransformer\DateTimeToLocalizedStringTransformer;
use Symfony\Component\Form\DataTransformer\DateTimeToArrayTransformer;
use Symfony\Component\Form\DataTransformer\DateTimeToStringTransformer;
use Symfony\Component\Form\DataTransformer\DateTimeToTimestampTransformer;
use Symfony\Component\Form\DataTransformer\ReversedTransformer;

class DateType extends AbstractType
{
    public function configure(FormBuilder $builder, array $options)
    {
        $formatter = new \IntlDateFormatter(
            \Locale::getDefault(),
            $options['format'],
            \IntlDateFormatter::NONE
        );

        if ($options['widget'] === 'text') {
            $builder->setClientTransformer(new DateTimeToLocalizedStringTransformer($options['data_timezone'], $options['user_timezone'], $options['format'], \IntlDateFormatter::NONE));
        } else {
            // Only pass a subset of the options to children
            $yearOptions = array(
                'choice_list' => new PaddedChoiceList(
                    $options['years'], 4, '0', STR_PAD_LEFT
                ),
            );
            $monthOptions = array(
                'choice_list' => new MonthChoiceList(
                    $formatter, $options['months']
                ),
            );
            $dayOptions = array(
                'choice_list' => new PaddedChoiceList(
                    $options['days'], 2, '0', STR_PAD_LEFT
                ),
            );

            $builder->add('year', 'choice', $yearOptions)
                ->add('month', 'choice', $monthOptions)
                ->add('day', 'choice', $dayOptions)
                ->setClientTransformer(new DateTimeToArrayTransformer($options['data_timezone'], $options['user_timezone'], array('year', 'month', 'day')))
                ->addRendererPlugin(new DatePatternPlugin($formatter));
        }

        if ($options['input'] === 'string') {
            $builder->setNormTransformer(new ReversedTransformer(
                new DateTimeToStringTransformer($options['data_timezone'], $options['data_timezone'], 'Y-m-d')
            ));
        } else if ($options['input'] === 'timestamp') {
            $builder->setNormTransformer(new ReversedTransformer(
                new DateTimeToTimestampTransformer($options['data_timezone'], $options['data_timezone'])
            ));
        } else if ($options['input'] === 'array') {
            $builder->setNormTransformer(new ReversedTransformer(
                new DateTimeToArrayTransformer($options['data_timezone'], $options['data_timezone'], array('year', 'month', 'day'))
            ));
        }

        $builder->setRendererVar('widget', $options['widget']);
    }

    public function getDefaultOptions(array $options)
    {
        return array(
            'template' => 'date',
            'years' => range(date('Y') - 5, date('Y') + 5),
            'months' => range(1, 12),
            'days' => range(1, 31),
            'widget' => 'choice',
            'input' => 'datetime',
            'pattern' => null,
            'format' => \IntlDateFormatter::MEDIUM,
            'data_timezone' => null,
            'user_timezone' => null,
            'csrf_protection' => false,
            // Don't modify \DateTime classes by reference, we treat
            // them like immutable value objects
            'by_reference' => false,
        );
    }

    public function getParent(array $options)
    {
        return $options['widget'] === 'text' ? 'field' : 'form';
    }

    public function getName()
    {
        return 'date';
    }
}