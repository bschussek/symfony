<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Extension\HttpFoundation\Type;

use Symfony\Component\Form\Extension\HttpFoundation\HttpFoundationExtension;
use Symfony\Component\Form\Serializer\FormSerializer;
use Symfony\Component\Form\Test\TypeTestCase;

/**
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class FormTypeHttpFoundationExtensionTest extends TypeTestCase
{
    protected function getExtensions()
    {
        return array_merge(parent::getExtensions(), [
            new HttpFoundationExtension(),
        ]);
    }

    public function testSerialize()
    {
        $serializer = new FormSerializer($this->factory, $this->registry);
        $form = $this->factory->create('form');

        $unserialized = $serializer->unserialize($serializer->serialize($form));

        $this->assertEquals($form, $unserialized);
    }
}
