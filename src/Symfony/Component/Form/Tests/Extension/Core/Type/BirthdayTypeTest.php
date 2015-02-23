<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Form\Tests\Extension\Core\Type;

use Symfony\Component\Form\Serializer\FormSerializer;
use Symfony\Component\Form\Test\TypeTestCase;

/**
 * @author Stepan Anchugov <kixxx1@gmail.com>
 */
class BirthdayTypeTest extends TypeTestCase
{
    /**
     * @expectedException \Symfony\Component\OptionsResolver\Exception\InvalidOptionsException
     */
    public function testSetInvalidYearsOption()
    {
        $this->factory->create('birthday', null, array(
            'years' => 'bad value',
        ));
    }

    public function testSerialize()
    {
        $serializer = new FormSerializer($this->factory, $this->registry);
        $form = $this->factory->create('birthday');

        $unserialized = $serializer->unserialize($serializer->serialize($form));

        $this->assertEquals($form, $unserialized);
    }

    protected function getTestedType()
    {
        return 'birthday';
    }
}
