<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Tests\Component\Form\Type;

require_once __DIR__ . '/LocalizedTestCase.php';

use Symfony\Component\Form\TimeField;

class TimeTypeTest extends LocalizedTestCase
{
    public function testSubmit_dateTime()
    {
        $form = $this->factory->create('time', 'name', array(
            'data_timezone' => 'UTC',
            'user_timezone' => 'UTC',
            'input' => 'datetime',
        ));

        $input = array(
            'hour' => '3',
            'minute' => '4',
        );

        $form->bind($input);

        $dateTime = new \DateTime('1970-01-01 03:04:00 UTC');

        $this->assertEquals($dateTime, $form->getData());
        $this->assertEquals($input, $form->getClientData());
    }

    public function testSubmit_dateTime_minuteIsZero()
    {
        $form = $this->factory->create('time', 'name', array(
            'data_timezone' => 'UTC',
            'user_timezone' => 'UTC',
            'input' => 'datetime',
        ));

        $input = array(
            'hour' => '3',
            'minute' => '0',
        );

        $form->bind($input);

        $dateTime = new \DateTime('1970-01-01 03:00:00 UTC');

        $this->assertEquals($dateTime, $form->getData());
        $this->assertEquals($input, $form->getClientData());
    }

    public function testSubmit_dateTime_hourIsZero()
    {
        $form = $this->factory->create('time', 'name', array(
            'data_timezone' => 'UTC',
            'user_timezone' => 'UTC',
            'input' => 'datetime',
        ));

        $input = array(
            'hour' => '0',
            'minute' => '4',
        );

        $form->bind($input);

        $dateTime = new \DateTime('1970-01-01 00:04:00 UTC');

        $this->assertEquals($dateTime, $form->getData());
        $this->assertEquals($input, $form->getClientData());
    }

    public function testSubmit_string()
    {
        $form = $this->factory->create('time', 'name', array(
            'data_timezone' => 'UTC',
            'user_timezone' => 'UTC',
            'input' => 'string',
        ));

        $input = array(
            'hour' => '3',
            'minute' => '4',
        );

        $form->bind($input);

        $this->assertEquals('03:04:00', $form->getData());
        $this->assertEquals($input, $form->getClientData());
    }

    public function testSubmit_timestamp()
    {
        $form = $this->factory->create('time', 'name', array(
            'data_timezone' => 'UTC',
            'user_timezone' => 'UTC',
            'input' => 'timestamp',
        ));

        $input = array(
            'hour' => '3',
            'minute' => '4',
        );

        $form->bind($input);

        $dateTime = new \DateTime('1970-01-01 03:04:00 UTC');

        $this->assertEquals($dateTime->format('U'), $form->getData());
        $this->assertEquals($input, $form->getClientData());
    }

    public function testSubmit_array()
    {
        $form = $this->factory->create('time', 'name', array(
            'data_timezone' => 'UTC',
            'user_timezone' => 'UTC',
            'input' => 'array',
        ));

        $input = array(
            'hour' => '3',
            'minute' => '4',
        );

        $data = array(
            'hour' => '3',
            'minute' => '4',
        );

        $form->bind($input);

        $this->assertEquals($data, $form->getData());
        $this->assertEquals($input, $form->getClientData());
    }

    public function testSetData_withSeconds()
    {
        $form = $this->factory->create('time', 'name', array(
            'data_timezone' => 'UTC',
            'user_timezone' => 'UTC',
            'input' => 'datetime',
            'with_seconds' => true,
        ));

        $form->setData(new \DateTime('03:04:05 UTC'));

        $this->assertEquals(array('hour' => 3, 'minute' => 4, 'second' => 5), $form->getClientData());
    }

    public function testSetData_differentTimezones()
    {
        $form = $this->factory->create('time', 'name', array(
            'data_timezone' => 'America/New_York',
            'user_timezone' => 'Pacific/Tahiti',
            // don't do this test with DateTime, because it leads to wrong results!
            'input' => 'string',
            'with_seconds' => true,
        ));

        $dateTime = new \DateTime('03:04:05 America/New_York');

        $form->setData($dateTime->format('H:i:s'));

        $dateTime = clone $dateTime;
        $dateTime->setTimezone(new \DateTimeZone('Pacific/Tahiti'));

        $displayedData = array(
            'hour' => (int)$dateTime->format('H'),
            'minute' => (int)$dateTime->format('i'),
            'second' => (int)$dateTime->format('s')
        );

        $this->assertEquals($displayedData, $form->getClientData());
    }

    public function testIsHourWithinRange_returnsTrueIfWithin()
    {
        $this->markTestSkipped('Needs to be reimplemented using validators');

        $form = $this->factory->create('time', 'name', array(
            'hours' => array(6, 7),
        ));

        $form->bind(array('hour' => '06', 'minute' => '12'));

        $this->assertTrue($form->isHourWithinRange());
    }

    public function testIsHourWithinRange_returnsTrueIfEmpty()
    {
        $this->markTestSkipped('Needs to be reimplemented using validators');

        $form = $this->factory->create('time', 'name', array(
            'hours' => array(6, 7),
        ));

        $form->bind(array('hour' => '', 'minute' => '06'));

        $this->assertTrue($form->isHourWithinRange());
    }

    public function testIsHourWithinRange_returnsFalseIfNotContained()
    {
        $this->markTestSkipped('Needs to be reimplemented using validators');

        $form = $this->factory->create('time', 'name', array(
            'hours' => array(6, 7),
        ));

        $form->bind(array('hour' => '08', 'minute' => '12'));

        $this->assertFalse($form->isHourWithinRange());
    }

    public function testIsMinuteWithinRange_returnsTrueIfWithin()
    {
        $this->markTestSkipped('Needs to be reimplemented using validators');

        $form = $this->factory->create('time', 'name', array(
            'minutes' => array(6, 7),
        ));

        $form->bind(array('hour' => '06', 'minute' => '06'));

        $this->assertTrue($form->isMinuteWithinRange());
    }

    public function testIsMinuteWithinRange_returnsTrueIfEmpty()
    {
        $this->markTestSkipped('Needs to be reimplemented using validators');

        $form = $this->factory->create('time', 'name', array(
            'minutes' => array(6, 7),
        ));

        $form->bind(array('hour' => '06', 'minute' => ''));

        $this->assertTrue($form->isMinuteWithinRange());
    }

    public function testIsMinuteWithinRange_returnsFalseIfNotContained()
    {
        $this->markTestSkipped('Needs to be reimplemented using validators');

        $form = $this->factory->create('time', 'name', array(
            'minutes' => array(6, 7),
        ));

        $form->bind(array('hour' => '06', 'minute' => '08'));

        $this->assertFalse($form->isMinuteWithinRange());
    }

    public function testIsSecondWithinRange_returnsTrueIfWithin()
    {
        $this->markTestSkipped('Needs to be reimplemented using validators');

        $form = $this->factory->create('time', 'name', array(
            'seconds' => array(6, 7),
            'with_seconds' => true,
        ));

        $form->bind(array('hour' => '04', 'minute' => '05', 'second' => '06'));

        $this->assertTrue($form->isSecondWithinRange());
    }

    public function testIsSecondWithinRange_returnsTrueIfEmpty()
    {
        $this->markTestSkipped('Needs to be reimplemented using validators');

        $form = $this->factory->create('time', 'name', array(
            'seconds' => array(6, 7),
            'with_seconds' => true,
        ));

        $form->bind(array('hour' => '06', 'minute' => '06', 'second' => ''));

        $this->assertTrue($form->isSecondWithinRange());
    }

    public function testIsSecondWithinRange_returnsTrueIfNotWithSeconds()
    {
        $this->markTestSkipped('Needs to be reimplemented using validators');

        $form = $this->factory->create('time', 'name', array(
            'seconds' => array(6, 7),
        ));

        $form->bind(array('hour' => '06', 'minute' => '06'));

        $this->assertTrue($form->isSecondWithinRange());
    }

    public function testIsSecondWithinRange_returnsFalseIfNotContained()
    {
        $this->markTestSkipped('Needs to be reimplemented using validators');

        $form = $this->factory->create('time', 'name', array(
            'seconds' => array(6, 7),
            'with_seconds' => true,
        ));

        $form->bind(array('hour' => '04', 'minute' => '05', 'second' => '08'));

        $this->assertFalse($form->isSecondWithinRange());
    }

    public function testIsPartiallyFilled_returnsFalseIfCompletelyEmpty()
    {
        $this->markTestSkipped('Needs to be reimplemented using validators');

        $form = $this->factory->create('time', 'name', array(
            'widget' => 'choice',
        ));

        $form->bind(array(
            'hour' => '',
            'minute' => '',
        ));

        $this->assertFalse($form->isPartiallyFilled());
    }

    public function testIsPartiallyFilled_returnsFalseIfCompletelyEmpty_withSeconds()
    {
        $this->markTestSkipped('Needs to be reimplemented using validators');

        $form = $this->factory->create('time', 'name', array(
            'widget' => 'choice',
            'with_seconds' => true,
        ));

        $form->bind(array(
            'hour' => '',
            'minute' => '',
            'second' => '',
        ));

        $this->assertFalse($form->isPartiallyFilled());
    }

    public function testIsPartiallyFilled_returnsFalseIfCompletelyFilled()
    {
        $this->markTestSkipped('Needs to be reimplemented using validators');

        $form = $this->factory->create('time', 'name', array(
            'widget' => 'choice',
        ));

        $form->bind(array(
            'hour' => '0',
            'minute' => '0',
        ));

        $this->assertFalse($form->isPartiallyFilled());
    }

    public function testIsPartiallyFilled_returnsFalseIfCompletelyFilled_withSeconds()
    {
        $this->markTestSkipped('Needs to be reimplemented using validators');

        $form = $this->factory->create('time', 'name', array(
            'widget' => 'choice',
            'with_seconds' => true,
        ));

        $form->bind(array(
            'hour' => '0',
            'minute' => '0',
            'second' => '0',
        ));

        $this->assertFalse($form->isPartiallyFilled());
    }

    public function testIsPartiallyFilled_returnsTrueIfChoiceAndHourEmpty()
    {
        $this->markTestSkipped('Needs to be reimplemented using validators');

        $form = $this->factory->create('time', 'name', array(
            'widget' => 'choice',
            'with_seconds' => true,
        ));

        $form->bind(array(
            'hour' => '',
            'minute' => '0',
            'second' => '0',
        ));

        $this->assertTrue($form->isPartiallyFilled());
    }

    public function testIsPartiallyFilled_returnsTrueIfChoiceAndMinuteEmpty()
    {
        $this->markTestSkipped('Needs to be reimplemented using validators');

        $form = $this->factory->create('time', 'name', array(
            'widget' => 'choice',
            'with_seconds' => true,
        ));

        $form->bind(array(
            'hour' => '0',
            'minute' => '',
            'second' => '0',
        ));

        $this->assertTrue($form->isPartiallyFilled());
    }

    public function testIsPartiallyFilled_returnsTrueIfChoiceAndSecondsEmpty()
    {
        $this->markTestSkipped('Needs to be reimplemented using validators');

        $form = $this->factory->create('time', 'name', array(
            'widget' => 'choice',
            'with_seconds' => true,
        ));

        $form->bind(array(
            'hour' => '0',
            'minute' => '0',
            'second' => '',
        ));

        $this->assertTrue($form->isPartiallyFilled());
    }
}