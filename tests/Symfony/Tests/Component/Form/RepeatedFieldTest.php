<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Tests\Component\Form;

require_once __DIR__.'/TestCase.php';

use Symfony\Component\Form\RepeatedField;
use Symfony\Component\Form\Field;

class RepeatedFieldTest extends TestCase
{
    protected $field;

    protected function setUp()
    {
        parent::setUp();

        $this->field = $this->factory->getRepeatedField('name', array(
            'prototype' => $this->factory->getField('foo'),
        ));
        $this->field->setData(null);
    }

    public function testSetData()
    {
        $this->field->setData('foobar');

        $this->assertEquals('foobar', $this->field['first']->getData());
        $this->assertEquals('foobar', $this->field['second']->getData());
    }

    public function testSubmitUnequal()
    {
        $input = array('first' => 'foo', 'second' => 'bar');

        $this->field->submit($input);

        $this->assertEquals('foo', $this->field['first']->getDisplayedData());
        $this->assertEquals('bar', $this->field['second']->getDisplayedData());
        $this->assertFalse($this->field->isTransformationSuccessful());
        $this->assertEquals($input, $this->field->getDisplayedData());
        $this->assertEquals(null, $this->field->getData());
    }

    public function testSubmitEqual()
    {
        $input = array('first' => 'foo', 'second' => 'foo');

        $this->field->submit($input);

        $this->assertEquals('foo', $this->field['first']->getDisplayedData());
        $this->assertEquals('foo', $this->field['second']->getDisplayedData());
        $this->assertTrue($this->field->isTransformationSuccessful());
        $this->assertEquals($input, $this->field->getDisplayedData());
        $this->assertEquals('foo', $this->field->getData());
    }
}