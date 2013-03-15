<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Intl\Tests\Collator;

use Symfony\Component\Intl\Collator\StubCollator;
use Symfony\Component\Intl\Locale;
use Symfony\Component\Intl\Tests\IntlTestCase;

/**
 * Test case for Collator implementations.
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
abstract class AbstractCollatorTest extends IntlTestCase
{
    /**
     * @dataProvider asortProvider
     */
    public function testAsort($array, $sortFlag, $expected)
    {
        $collator = $this->getCollator('en');
        $collator->asort($array, $sortFlag);
        $this->assertSame($expected, $array);
    }

    public function asortProvider()
    {
        return array(
            /* array, sortFlag, expected */
            array(
                array('a', 'b', 'c'),
                StubCollator::SORT_REGULAR,
                array('a', 'b', 'c'),
            ),
            array(
                array('c', 'b', 'a'),
                StubCollator::SORT_REGULAR,
                array(2 => 'a', 1 => 'b',  0 => 'c'),
            ),
            array(
                array('b', 'c', 'a'),
                StubCollator::SORT_REGULAR,
                array(2 => 'a', 0 => 'b', 1 => 'c'),
            ),
        );
    }

    /**
     * @param string $locale
     *
     * @return \Collator
     */
    abstract protected function getCollator($locale);
}
